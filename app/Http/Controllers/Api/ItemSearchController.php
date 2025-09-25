<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ItemSearchController extends Controller
{
    /**
     * Search items with advanced filtering
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:255',
            'category_id' => 'integer|exists:categories,id',
            'subcategory_id' => 'integer|exists:sub_categories,id',
            'condition' => 'string|in:new,like_new,good,fair,poor',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'sort_by' => 'string|in:relevance,latest,oldest,price_low,price_high,popular',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ]);

        $query = Item::active()
            ->with(['user:id,name,profile_image', 'primaryImage']);

        // Full-text search
        $searchTerm = $validated['q'];
        $query->whereRaw(
            "MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$searchTerm]
        )->orWhere('title', 'LIKE', "%{$searchTerm}%")
         ->orWhere('description', 'LIKE', "%{$searchTerm}%")
         ->orWhere('category_name', 'LIKE', "%{$searchTerm}%")
         ->orWhereJsonContains('tags', $searchTerm);

        // Apply category filters
        if (isset($validated['category_id'])) {
            $query->where(function($q) use ($validated) {
                $q->where('category_id', $validated['category_id'])
                  ->orWhereHas('subCategory.category', function($sq) use ($validated) {
                      $sq->where('id', $validated['category_id']);
                  })
                  ->orWhereHas('childSubCategory.subCategory.category', function($csq) use ($validated) {
                      $csq->where('id', $validated['category_id']);
                  });
            });
        }

        if (isset($validated['subcategory_id'])) {
            $query->where(function($q) use ($validated) {
                $q->where('sub_category_id', $validated['subcategory_id'])
                  ->orWhereHas('childSubCategory', function($csq) use ($validated) {
                      $csq->where('sub_category_id', $validated['subcategory_id']);
                  });
            });
        }

        // Apply other filters
        if (isset($validated['condition'])) {
            $query->where('condition', $validated['condition']);
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        // Apply sorting
        $this->applySearchSorting($query, $validated['sort_by'] ?? 'relevance', $searchTerm);

        // Paginate
        $limit = $validated['limit'] ?? 20;
        $items = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)->response()->getData(true)['data'],
            'meta' => [
                'search' => [
                    'query' => $searchTerm,
                    'total_results' => $items->total(),
                    'search_time' => microtime(true) - LARAVEL_START,
                ],
                'pagination' => [
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ],
                'filters_applied' => array_filter($validated, function($value, $key) {
                    return !in_array($key, ['q', 'page', 'limit', 'sort_by']) && !is_null($value);
                }, ARRAY_FILTER_USE_BOTH),
            ],
            'message' => 'Search completed successfully'
        ]);
    }

    /**
     * Apply search-specific sorting
     */
    private function applySearchSorting($query, string $sortBy, string $searchTerm): void
    {
        switch ($sortBy) {
            case 'relevance':
                $query->orderByRaw(
                    "CASE 
                        WHEN title LIKE ? THEN 1
                        WHEN title LIKE ? THEN 2  
                        WHEN description LIKE ? THEN 3
                        WHEN category_name LIKE ? THEN 4
                        ELSE 5
                    END",
                    [
                        $searchTerm,
                        "%{$searchTerm}%",
                        "%{$searchTerm}%", 
                        "%{$searchTerm}%"
                    ]
                );
                break;
            case 'latest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->withCount(['favorites', 'views'])
                      ->orderByDesc('favorites_count')
                      ->orderByDesc('views_count');
                break;
            default:
                $query->latest();
        }

        // Always prioritize promoted items
        $query->orderByDesc('is_promoted');
    }
}
