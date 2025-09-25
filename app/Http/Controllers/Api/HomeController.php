<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Get popular items for home screen
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $items = Cache::remember("home_popular_items_{$limit}", 300, function() use ($limit) {
            return Item::active()
                ->with(['user:id,name,profile_image', 'primaryImage'])
                ->select(['id', 'user_id', 'title', 'description', 'price', 'condition', 
                         'category_name', 'category_id', 'location', 'created_at'])
                ->withCount(['favorites', 'views']) // Assuming you have these relationships
                ->orderByDesc('favorites_count')
                ->orderByDesc('views_count')
                ->orderByDesc('is_promoted')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items),
            'message' => 'Popular items retrieved successfully'
        ]);
    }

    /**
     * Get recent listings for home screen
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $items = Cache::remember("home_recent_items_{$limit}", 300, function() use ($limit) {
            return Item::active()
                ->with(['user:id,name,profile_image', 'primaryImage'])
                ->select(['id', 'user_id', 'title', 'description', 'price', 'condition', 
                         'category_name', 'category_id', 'location', 'created_at'])
                ->latest()
                ->limit($limit)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items),
            'message' => 'Recent items retrieved successfully'
        ]);
    }

    /**
     * Get trending items for home screen
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $items = Cache::remember("home_trending_items_{$limit}", 300, function() use ($limit) {
            return Item::active()
                ->with(['user:id,name,profile_image', 'primaryImage'])
                ->select(['id', 'user_id', 'title', 'description', 'price', 'condition', 
                         'category_name', 'category_id', 'location', 'created_at'])
                ->where('created_at', '>=', now()->subDays(7)) // Last 7 days
                ->withCount(['favorites', 'views'])
                ->orderByDesc('views_count')
                ->orderByDesc('favorites_count')
                ->orderByDesc('is_promoted')
                ->limit($limit)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items),
            'message' => 'Trending items retrieved successfully'
        ]);
    }

    /**
     * Get featured items for home screen
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);
        
        $items = Cache::remember("home_featured_items_{$limit}", 600, function() use ($limit) {
            return Item::active()
                ->promoted()
                ->with(['user:id,name,profile_image', 'primaryImage'])
                ->select(['id', 'user_id', 'title', 'description', 'price', 'condition', 
                         'category_name', 'category_id', 'location', 'is_promoted', 
                         'promoted_until', 'created_at'])
                ->orderByDesc('promoted_until')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items),
            'message' => 'Featured items retrieved successfully'
        ]);
    }

    /**
     * Get category statistics
     */
    public function categoryStats(): JsonResponse
    {
        $stats = Cache::remember('category_stats', 1800, function() {
            return Category::active()
                ->withCount('items')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'items_count'])
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'icon' => $category->icon,
                        'items_count' => $category->items_count,
                        'active_items_count' => $category->items()->active()->count(),
                    ];
                });
        });

        $totalStats = [
            'total_items' => Item::active()->count(),
            'total_categories' => Category::active()->count(),
            'items_today' => Item::active()->whereDate('created_at', today())->count(),
            'promoted_items' => Item::promoted()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $stats,
                'totals' => $totalStats,
            ],
            'message' => 'Category statistics retrieved successfully'
        ]);
    }
}
