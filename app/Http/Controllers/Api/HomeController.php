<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Category;
use App\Services\LocationFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;


class HomeController extends Controller
{
    public function __construct(protected LocationFilterService $locationFilter) {}

    // ─── Shared builder ────────────────────────────────────────────────────────

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Item::active()
            ->with(['user:id,name,profile_image', 'primaryImage'])
            ->select([
                'id',
                'user_id',
                'title',
                'description',
                'price',
                'condition',
                'category_name',
                'category_id',
                'location',
                'location_id',
                'created_at',
            ]);
    }

    private function paginationMeta(\Illuminate\Pagination\LengthAwarePaginator $items): array
    {
        return [
            'current_page' => $items->currentPage(),
            'last_page'    => $items->lastPage(),
            'per_page'     => $items->perPage(),
            'total'        => $items->total(),
            'has_more'     => $items->hasMorePages(),
        ];
    }

    // ─── Popular ───────────────────────────────────────────────────────────────

    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page  = $request->get('page', 1);
        ['city' => $city, 'state' => $state] = $this->locationFilter->resolve($request->all());

        $cacheKey = $this->locationFilter->cacheKey('home_popular', $city, $state, $limit, $page);

        $items = Cache::remember($cacheKey, 300, function () use ($limit, $city, $state) {
            return $this->baseQuery()
                ->byLocationHierarchy($city, $state)
                ->withCount(['favorites', 'views'])
                ->orderByDesc('favorites_count')
                ->orderByDesc('views_count')
                ->orderByDesc('is_promoted')
                ->orderByDesc('created_at')
                ->paginate($limit);
        });

        return response()->json([
            'success'    => true,
            'data'       => ItemResource::collection($items),
            'message'    => 'Popular items retrieved successfully',
            'meta'       => ['city' => $city, 'state' => $state],
            'pagination' => $this->paginationMeta($items),
        ]);
    }

    // ─── Recent ────────────────────────────────────────────────────────────────

    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page  = $request->get('page', 1);
        ['city' => $city, 'state' => $state] = $this->locationFilter->resolve($request->all());

        $cacheKey = $this->locationFilter->cacheKey('home_recent', $city, $state, $limit, $page);

        $items = Cache::remember($cacheKey, 300, function () use ($limit, $city, $state) {
            return $this->baseQuery()
                ->byLocationHierarchy($city, $state)
                ->latest()
                ->paginate($limit);
        });

        return response()->json([
            'success'    => true,
            'data'       => ItemResource::collection($items),
            'message'    => 'Recent items retrieved successfully',
            'meta'       => ['city' => $city, 'state' => $state],
            'pagination' => $this->paginationMeta($items),
        ]);
    }

    // ─── Trending ──────────────────────────────────────────────────────────────

    public function trending(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page  = $request->get('page', 1);
        ['city' => $city, 'state' => $state] = $this->locationFilter->resolve($request->all());

        $cacheKey = $this->locationFilter->cacheKey('home_trending', $city, $state, $limit, $page);

        $items = Cache::remember($cacheKey, 300, function () use ($limit, $city, $state) {
            return $this->baseQuery()
                ->byLocationHierarchy($city, $state)
                ->where('created_at', '>=', now()->subDays(7))
                ->withCount(['favorites', 'views'])
                ->orderByDesc('views_count')
                ->orderByDesc('favorites_count')
                ->orderByDesc('is_promoted')
                ->paginate($limit);
        });

        return response()->json([
            'success'    => true,
            'data'       => ItemResource::collection($items),
            'message'    => 'Trending items retrieved successfully',
            'meta'       => ['city' => $city, 'state' => $state],
            'pagination' => $this->paginationMeta($items),
        ]);
    }

    // ─── Featured ──────────────────────────────────────────────────────────────

    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);
        $page  = $request->get('page', 1);
        ['city' => $city, 'state' => $state] = $this->locationFilter->resolve($request->all());

        $cacheKey = $this->locationFilter->cacheKey('home_featured', $city, $state, $limit, $page);

        $items = Cache::remember($cacheKey, 600, function () use ($limit, $city, $state) {
            return $this->baseQuery()
                ->promoted()
                ->byLocationHierarchy($city, $state)
                ->select(array_merge(
                    [
                        'id',
                        'user_id',
                        'title',
                        'description',
                        'price',
                        'condition',
                        'category_name',
                        'category_id',
                        'location',
                        'location_id',
                        'created_at'
                    ],
                    ['is_promoted', 'promoted_until']
                ))
                ->orderByDesc('promoted_until')
                ->orderByDesc('created_at')
                ->paginate($limit);
        });

        return response()->json([
            'success'    => true,
            'data'       => ItemResource::collection($items),
            'message'    => 'Featured items retrieved successfully',
            'meta'       => ['city' => $city, 'state' => $state],
            'pagination' => $this->paginationMeta($items),
        ]);
    }

    /**
     * Get category statistics
     */
    public function categoryStats(): JsonResponse
    {
        $stats = Cache::remember('category_stats', 1800, function () {
            return Category::active()
                ->withCount('items')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'items_count'])
                ->map(fn($category) => [
                    'id'                 => $category->id,
                    'name'               => $category->name,
                    'slug'               => $category->slug,
                    'icon'               => $category->icon,
                    'items_count'        => $category->items_count,
                    'active_items_count' => $category->items()->active()->count(),
                ]);
        });

        $totalStats = [
            'total_items'      => Item::active()->count(),
            'total_categories' => Category::active()->count(),
            'items_today'      => Item::active()->whereDate('created_at', today())->count(),
            'promoted_items'   => Item::promoted()->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'categories' => $stats,
                'totals'     => $totalStats,
            ],
            'message' => 'Category statistics retrieved successfully',
        ]);
    }
}
