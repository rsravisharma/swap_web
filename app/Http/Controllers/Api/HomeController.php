<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Get popular items for home screen
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page  = $request->get('page', 1);

        $items = Cache::remember("home_popular_items_{$limit}_page_{$page}", 300, function () use ($limit) {
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
                    'created_at',
                ])
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
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get recent listings for home screen
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page  = $request->get('page', 1);

        $items = Cache::remember("home_recent_items_{$limit}_page_{$page}", 300, function () use ($limit) {
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
                    'created_at',
                ])
                ->latest()
                ->paginate($limit);
        });

        return response()->json([
            'success'    => true,
            'data'       => ItemResource::collection($items),
            'message'    => 'Recent items retrieved successfully',
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get trending items for home screen
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page  = $request->get('page', 1);

        $items = Cache::remember("home_trending_items_{$limit}_page_{$page}", 300, function () use ($limit) {
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
                    'created_at',
                ])
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
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get featured items for home screen
     * Featured items are promoted only — no pagination needed
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);
        $page  = $request->get('page', 1);

        $items = Cache::remember("home_featured_items_{$limit}_page_{$page}", 600, function () use ($limit, $page) {
            return Item::active()
                ->promoted()
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
                    'is_promoted',
                    'promoted_until',
                    'created_at',
                ])
                ->orderByDesc('promoted_until')
                ->orderByDesc('created_at')
                ->paginate($limit);
        });

        return response()->json([
            'success'    => true,
            'data'       => ItemResource::collection($items),
            'message'    => 'Featured items retrieved successfully',
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
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
