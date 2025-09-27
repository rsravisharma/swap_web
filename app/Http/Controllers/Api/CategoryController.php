<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildSubCategory;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    // Cache duration in minutes (1 hour)
    private const CACHE_DURATION = 60;

    /**
     * Get hierarchical category structure for item selection
     * Endpoint: GET /categories/hierarchy
     */
    public function getCategoryHierarchy(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'category_hierarchy_' . md5($request->getQueryString() ?? '');

            $data = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($request) {
                $query = Category::with(['subCategories.childSubCategories'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');

                // Search functionality across all levels
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%")
                          // FIXED: Changed from 'subcategories' to 'subCategories'
                          ->orWhereHas('subCategories', function ($sq) use ($search) {
                              $sq->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('description', 'LIKE', "%{$search}%")
                                // FIXED: Changed from 'childSubcategories' to 'childSubCategories'
                                ->orWhereHas('childSubCategories', function ($csq) use ($search) {
                                    $csq->where('name', 'LIKE', "%{$search}%")
                                       ->orWhere('description', 'LIKE', "%{$search}%");
                                });
                          });
                    });
                }

                $categories = $query->get();

                return $categories->map(function ($category) {
                    return [
                        'id' => (string) $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'icon' => $category->icon,
                        'color' => $category->color,
                        'sort_order' => $category->sort_order,
                        'items_count' => $this->getCategoryItemCount($category->id),
                        // FIXED: Changed key from 'subcategories' to 'sub_categories'
                        'sub_categories' => $category->subCategories->map(function ($subCategory) {
                            return [
                                'id' => (string) $subCategory->id,
                                'category_id' => (string) $subCategory->category_id,
                                'name' => $subCategory->name,
                                'slug' => $subCategory->slug,
                                'description' => $subCategory->description,
                                'icon' => $subCategory->icon,
                                'color' => $subCategory->color,
                                'sort_order' => $subCategory->sort_order,
                                'items_count' => $this->getSubCategoryItemCount($subCategory->id),
                                // FIXED: Changed key from 'child_subcategories' to 'child_sub_categories'
                                'child_sub_categories' => $subCategory->childSubCategories->map(function ($childSubCategory) {
                                    return [
                                        'id' => (string) $childSubCategory->id,
                                        'sub_category_id' => (string) $childSubCategory->sub_category_id,
                                        'name' => $childSubCategory->name,
                                        'slug' => $childSubCategory->slug,
                                        'description' => $childSubCategory->description,
                                        'icon' => $childSubCategory->icon,
                                        'color' => $childSubCategory->color,
                                        'sort_order' => $childSubCategory->sort_order,
                                        // FIXED: Corrected variable name from $childSubcategory to $childSubCategory
                                        'items_count' => $this->getChildSubCategoryItemCount($childSubCategory->id),
                                    ];
                                })->toArray(),
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_categories' => count($data),
                'category_names' => $this->getCategoryNames(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category hierarchy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flattened categories for easy selection
     * Endpoint: GET /categories/flat
     */
    public function getFlatCategories(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'flat_categories_' . md5($request->getQueryString() ?? '');

            $data = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($request) {
                $flatCategories = [];

                // Get categories with their relationships
                // FIXED: Changed from 'subcategories.childSubcategories' to 'subCategories.childSubCategories'
                $categories = Category::with(['subCategories.childSubCategories'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();

                foreach ($categories as $category) {
                    // Add main category
                    $flatCategories[] = [
                        'id' => (string) $category->id,
                        'name' => $category->name,
                        'full_path' => $category->name,
                        'level' => 'category',
                        'category_id' => (string) $category->id,
                        // FIXED: Changed from 'subcategory_id' to 'sub_category_id'
                        'sub_category_id' => null,
                        // FIXED: Changed from 'child_subcategory_id' to 'child_sub_category_id'
                        'child_sub_category_id' => null,
                        'icon' => $category->icon,
                        'description' => $category->description,
                        'items_count' => $this->getCategoryItemCount($category->id),
                    ];

                    // Add subcategories
                    // FIXED: Changed from 'subcategories' to 'subCategories'
                    foreach ($category->subCategories as $subCategory) {
                        $flatCategories[] = [
                            'id' => (string) $subCategory->id,
                            'name' => $subCategory->name,
                            'full_path' => $category->name . ' > ' . $subCategory->name,
                            'level' => 'sub_category',
                            'category_id' => (string) $category->id,
                            'sub_category_id' => (string) $subCategory->id,
                            'child_sub_category_id' => null,
                            'icon' => $subCategory->icon ?: $category->icon,
                            'description' => $subCategory->description,
                            'items_count' => $this->getSubCategoryItemCount($subCategory->id),
                        ];

                        // Add child subcategories
                        // FIXED: Changed from 'childSubcategories' to 'childSubCategories'
                        foreach ($subCategory->childSubCategories as $childSubCategory) {
                            $flatCategories[] = [
                                'id' => (string) $childSubCategory->id,
                                'name' => $childSubCategory->name,
                                'full_path' => $category->name . ' > ' . $subCategory->name . ' > ' . $childSubCategory->name,
                                'level' => 'child_sub_category',
                                'category_id' => (string) $category->id,
                                'sub_category_id' => (string) $subCategory->id,
                                'child_sub_category_id' => (string) $childSubCategory->id,
                                'icon' => $childSubCategory->icon ?: $subCategory->icon ?: $category->icon,
                                'description' => $childSubCategory->description,
                                'items_count' => $this->getChildSubCategoryItemCount($childSubCategory->id),
                            ];
                        }
                    }
                }

                // Apply search filter if provided
                if ($request->has('search') && !empty($request->search)) {
                    $search = strtolower($request->search);
                    $flatCategories = array_filter($flatCategories, function ($item) use ($search) {
                        return strpos(strtolower($item['name']), $search) !== false ||
                               strpos(strtolower($item['full_path']), $search) !== false ||
                               strpos(strtolower($item['description'] ?? ''), $search) !== false;
                    });
                }

                return array_values($flatCategories);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => count($data),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flat categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subcategories by category ID
     * Endpoint: GET /categories/{categoryId}/subcategories
     */
    public function getSubCategories(string $categoryId): JsonResponse
    {
        try {
            // FIXED: Changed from 'Subcategory' to 'SubCategory' and 'childSubcategories' to 'childSubCategories'
            $subCategories = SubCategory::with('childSubCategories')
                ->where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subCategories->map(function ($subCategory) {
                    return [
                        'id' => (string) $subCategory->id,
                        'category_id' => (string) $subCategory->category_id,
                        'name' => $subCategory->name,
                        'slug' => $subCategory->slug,
                        'description' => $subCategory->description,
                        'icon' => $subCategory->icon,
                        'items_count' => $this->getSubCategoryItemCount($subCategory->id),
                        // FIXED: Changed key from 'child_subcategories' to 'child_sub_categories'
                        'child_sub_categories' => $subCategory->childSubCategories->map(function ($child) {
                            return [
                                'id' => (string) $child->id,
                                // FIXED: Changed from 'subcategory_id' to 'sub_category_id'
                                'sub_category_id' => (string) $child->sub_category_id,
                                'name' => $child->name,
                                'slug' => $child->slug,
                                'description' => $child->description,
                                'icon' => $child->icon,
                                'items_count' => $this->getChildSubCategoryItemCount($child->id),
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get child subcategories by subcategory ID
     * Endpoint: GET /subcategories/{subcategoryId}/children
     */
    public function getChildSubCategories(string $subCategoryId): JsonResponse
    {
        try {
            // FIXED: Changed from 'ChildSubcategory' to 'ChildSubCategory' and 'subcategory_id' to 'sub_category_id'
            $childSubCategories = ChildSubCategory::where('sub_category_id', $subCategoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $childSubCategories->map(function ($child) {
                    return [
                        'id' => (string) $child->id,
                        // FIXED: Changed from 'subcategory_id' to 'sub_category_id'
                        'sub_category_id' => (string) $child->sub_category_id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                        'icon' => $child->icon,
                        'items_count' => $this->getChildSubCategoryItemCount($child->id),
                    ];
                })->toArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch child subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category path by any level ID
     * Endpoint: GET /categories/path
     */
    public function getCategoryPath(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->query('category_id');
            // FIXED: Changed from 'subcategory_id' to 'sub_category_id'
            $subCategoryId = $request->query('sub_category_id');
            // FIXED: Changed from 'child_subcategory_id' to 'child_sub_category_id'
            $childSubCategoryId = $request->query('child_sub_category_id');

            $path = [];

            if ($childSubCategoryId) {
                // FIXED: Changed from 'ChildSubcategory' to 'ChildSubCategory' and 'subcategory.category' to 'subCategory.category'
                $childSubCategory = ChildSubCategory::with('subCategory.category')->find($childSubCategoryId);
                if ($childSubCategory) {
                    $path = [
                        'category' => [
                            'id' => (string) $childSubCategory->subCategory->category->id,
                            'name' => $childSubCategory->subCategory->category->name,
                        ],
                        // FIXED: Changed from 'subcategory' to 'sub_category'
                        'sub_category' => [
                            'id' => (string) $childSubCategory->subCategory->id,
                            'name' => $childSubCategory->subCategory->name,
                        ],
                        // FIXED: Changed from 'child_subcategory' to 'child_sub_category'
                        'child_sub_category' => [
                            'id' => (string) $childSubCategory->id,
                            'name' => $childSubCategory->name,
                        ],
                    ];
                }
            } elseif ($subCategoryId) {
                // FIXED: Changed from 'Subcategory' to 'SubCategory'
                $subCategory = SubCategory::with('category')->find($subCategoryId);
                if ($subCategory) {
                    $path = [
                        'category' => [
                            'id' => (string) $subCategory->category->id,
                            'name' => $subCategory->category->name,
                        ],
                        'sub_category' => [
                            'id' => (string) $subCategory->id,
                            'name' => $subCategory->name,
                        ],
                    ];
                }
            } elseif ($categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    $path = [
                        'category' => [
                            'id' => (string) $category->id,
                            'name' => $category->name,
                        ],
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $path,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get category path',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all category names for filtering
     */
    public function getCategoryNames(): array
    {
        return Cache::remember('category_names', self::CACHE_DURATION, function () {
            return Category::where('is_active', true)
                ->orderBy('name')
                ->pluck('name')
                ->prepend('All')
                ->toArray();
        });
    }

    /**
     * Clear all category-related cache
     */
    public function clearCache(): JsonResponse
    {
        $keys = [
            'category_hierarchy_*',
            'flat_categories_*',
            'category_names',
        ];

        Cache::flush(); // Clear all cache for simplicity

        return response()->json([
            'success' => true,
            'message' => 'Category cache cleared successfully'
        ]);
    }

    // Helper methods for item counts
    private function getCategoryItemCount(int $categoryId): int
    {
        return Item::where('category_id', $categoryId)
            ->where('status', 'active')
            ->count();
    }

    // FIXED: Changed method name from 'getSubcategoryItemCount' to 'getSubCategoryItemCount'
    private function getSubCategoryItemCount(int $subCategoryId): int
    {
        // FIXED: Changed from 'subcategory_id' to 'sub_category_id'
        return Item::where('sub_category_id', $subCategoryId)
            ->where('status', 'active')
            ->count();
    }

    // FIXED: Changed method name from 'getChildSubcategoryItemCount' to 'getChildSubCategoryItemCount'
    private function getChildSubCategoryItemCount(int $childSubCategoryId): int
    {
        // FIXED: Changed from 'child_subcategory_id' to 'child_sub_category_id'
        return Item::where('child_sub_category_id', $childSubCategoryId)
            ->where('status', 'active')
            ->count();
    }
}
