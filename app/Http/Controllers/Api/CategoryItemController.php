<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use App\Models\Item;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildSubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryItemController extends Controller
{
    /**
     * Get items by category ID
     */
    public function categoryItems(Request $request, $categoryId): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'condition' => 'string|in:new,like_new,good,fair,poor',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'sort_by' => 'string|in:latest,oldest,price_low,price_high,popular,name',
        ]);

        // Verify category exists
        $category = Category::active()->findOrFail($categoryId);

        $query = Item::active()
            ->with(['user:id,name,profile_image', 'primaryImage'])
            ->where(function($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('subCategory.category', function($sq) use ($categoryId) {
                      $sq->where('id', $categoryId);
                  })
                  ->orWhereHas('childSubCategory.subCategory.category', function($csq) use ($categoryId) {
                      $csq->where('id', $categoryId);
                  });
            });

        // Apply filters
        $this->applyFilters($query, $validated);

        // Apply sorting
        $this->applySorting($query, $validated['sort_by'] ?? 'latest');

        // Paginate
        $limit = $validated['limit'] ?? 20;
        $items = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)->response()->getData(true)['data'],
            'meta' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'icon' => $category->icon,
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
                    return !in_array($key, ['page', 'limit']) && !is_null($value);
                }, ARRAY_FILTER_USE_BOTH),
            ],
            'message' => "Items for {$category->name} retrieved successfully"
        ]);
    }

    /**
     * Get items by subcategory ID
     */
    public function subcategoryItems(Request $request, $subcategoryId): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'condition' => 'string|in:new,like_new,good,fair,poor',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'sort_by' => 'string|in:latest,oldest,price_low,price_high,popular,name',
        ]);

        // Verify subcategory exists
        $subcategory = SubCategory::active()->with('category')->findOrFail($subcategoryId);

        $query = Item::active()
            ->with(['user:id,name,profile_image', 'primaryImage'])
            ->where(function($q) use ($subcategoryId) {
                $q->where('sub_category_id', $subcategoryId)
                  ->orWhereHas('childSubCategory', function($csq) use ($subcategoryId) {
                      $csq->where('sub_category_id', $subcategoryId);
                  });
            });

        // Apply filters and sorting
        $this->applyFilters($query, $validated);
        $this->applySorting($query, $validated['sort_by'] ?? 'latest');

        // Paginate
        $limit = $validated['limit'] ?? 20;
        $items = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)->response()->getData(true)['data'],
            'meta' => [
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'description' => $subcategory->description,
                    'icon' => $subcategory->icon,
                ],
                'category' => [
                    'id' => $subcategory->category->id,
                    'name' => $subcategory->category->name,
                ],
                'pagination' => [
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ],
            ],
            'message' => "Items for {$subcategory->name} retrieved successfully"
        ]);
    }

    /**
     * Get items by child subcategory ID
     */
    public function childSubcategoryItems(Request $request, $childSubcategoryId): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'condition' => 'string|in:new,like_new,good,fair,poor',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'sort_by' => 'string|in:latest,oldest,price_low,price_high,popular,name',
        ]);

        // Verify child subcategory exists
        $childSubcategory = ChildSubCategory::active()
            ->with(['subCategory.category'])
            ->findOrFail($childSubcategoryId);

        $query = Item::active()
            ->with(['user:id,name,profile_image', 'primaryImage'])
            ->where('child_sub_category_id', $childSubcategoryId);

        // Apply filters and sorting
        $this->applyFilters($query, $validated);
        $this->applySorting($query, $validated['sort_by'] ?? 'latest');

        // Paginate
        $limit = $validated['limit'] ?? 20;
        $items = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)->response()->getData(true)['data'],
            'meta' => [
                'child_subcategory' => [
                    'id' => $childSubcategory->id,
                    'name' => $childSubcategory->name,
                    'description' => $childSubcategory->description,
                ],
                'subcategory' => [
                    'id' => $childSubcategory->subCategory->id,
                    'name' => $childSubcategory->subCategory->name,
                ],
                'category' => [
                    'id' => $childSubcategory->subCategory->category->id,
                    'name' => $childSubcategory->subCategory->category->name,
                ],
                'pagination' => [
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                ],
            ],
            'message' => "Items for {$childSubcategory->name} retrieved successfully"
        ]);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, string $sortBy): void
    {
        switch ($sortBy) {
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
            case 'name':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->latest();
        }

        // Always prioritize promoted items
        $query->orderByDesc('is_promoted');
    }
}
