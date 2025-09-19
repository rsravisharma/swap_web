<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ChildSubcategory;
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
                $query = Category::with(['subcategories.childSubcategories'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');

                // Search functionality across all levels
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%")
                          ->orWhereHas('subcategories', function ($sq) use ($search) {
                              $sq->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('description', 'LIKE', "%{$search}%")
                                ->orWhereHas('childSubcategories', function ($csq) use ($search) {
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
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            return [
                                'id' => (string) $subcategory->id,
                                'category_id' => (string) $subcategory->category_id,
                                'name' => $subcategory->name,
                                'slug' => $subcategory->slug,
                                'description' => $subcategory->description,
                                'icon' => $subcategory->icon,
                                'color' => $subcategory->color,
                                'sort_order' => $subcategory->sort_order,
                                'items_count' => $this->getSubcategoryItemCount($subcategory->id),
                                'child_subcategories' => $subcategory->childSubcategories->map(function ($childSubcategory) {
                                    return [
                                        'id' => (string) $childSubcategory->id,
                                        'subcategory_id' => (string) $childSubcategory->subcategory_id,
                                        'name' => $childSubcategory->name,
                                        'slug' => $childSubcategory->slug,
                                        'description' => $childSubcategory->description,
                                        'icon' => $childSubcategory->icon,
                                        'color' => $childSubcategory->color,
                                        'sort_order' => $childSubcategory->sort_order,
                                        'items_count' => $this->getChildSubcategoryItemCount($childSubcategory->id),
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
                $categories = Category::with(['subcategories.childSubcategories'])
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
                        'subcategory_id' => null,
                        'child_subcategory_id' => null,
                        'icon' => $category->icon,
                        'description' => $category->description,
                        'items_count' => $this->getCategoryItemCount($category->id),
                    ];

                    // Add subcategories
                    foreach ($category->subcategories as $subcategory) {
                        $flatCategories[] = [
                            'id' => (string) $subcategory->id,
                            'name' => $subcategory->name,
                            'full_path' => $category->name . ' > ' . $subcategory->name,
                            'level' => 'subcategory',
                            'category_id' => (string) $category->id,
                            'subcategory_id' => (string) $subcategory->id,
                            'child_subcategory_id' => null,
                            'icon' => $subcategory->icon ?: $category->icon,
                            'description' => $subcategory->description,
                            'items_count' => $this->getSubcategoryItemCount($subcategory->id),
                        ];

                        // Add child subcategories
                        foreach ($subcategory->childSubcategories as $childSubcategory) {
                            $flatCategories[] = [
                                'id' => (string) $childSubcategory->id,
                                'name' => $childSubcategory->name,
                                'full_path' => $category->name . ' > ' . $subcategory->name . ' > ' . $childSubcategory->name,
                                'level' => 'child_subcategory',
                                'category_id' => (string) $category->id,
                                'subcategory_id' => (string) $subcategory->id,
                                'child_subcategory_id' => (string) $childSubcategory->id,
                                'icon' => $childSubcategory->icon ?: $subcategory->icon ?: $category->icon,
                                'description' => $childSubcategory->description,
                                'items_count' => $this->getChildSubcategoryItemCount($childSubcategory->id),
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
    public function getSubcategories(string $categoryId): JsonResponse
    {
        try {
            $subcategories = Subcategory::with('childSubcategories')
                ->where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories->map(function ($subcategory) {
                    return [
                        'id' => (string) $subcategory->id,
                        'category_id' => (string) $subcategory->category_id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                        'description' => $subcategory->description,
                        'icon' => $subcategory->icon,
                        'items_count' => $this->getSubcategoryItemCount($subcategory->id),
                        'child_subcategories' => $subcategory->childSubcategories->map(function ($child) {
                            return [
                                'id' => (string) $child->id,
                                'subcategory_id' => (string) $child->subcategory_id,
                                'name' => $child->name,
                                'slug' => $child->slug,
                                'description' => $child->description,
                                'icon' => $child->icon,
                                'items_count' => $this->getChildSubcategoryItemCount($child->id),
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
    public function getChildSubcategories(string $subcategoryId): JsonResponse
    {
        try {
            $childSubcategories = ChildSubcategory::where('subcategory_id', $subcategoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $childSubcategories->map(function ($child) {
                    return [
                        'id' => (string) $child->id,
                        'subcategory_id' => (string) $child->subcategory_id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                        'icon' => $child->icon,
                        'items_count' => $this->getChildSubcategoryItemCount($child->id),
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
            $subcategoryId = $request->query('subcategory_id');
            $childSubcategoryId = $request->query('child_subcategory_id');

            $path = [];

            if ($childSubcategoryId) {
                $childSubcategory = ChildSubcategory::with('subcategory.category')->find($childSubcategoryId);
                if ($childSubcategory) {
                    $path = [
                        'category' => [
                            'id' => (string) $childSubcategory->subcategory->category->id,
                            'name' => $childSubcategory->subcategory->category->name,
                        ],
                        'subcategory' => [
                            'id' => (string) $childSubcategory->subcategory->id,
                            'name' => $childSubcategory->subcategory->name,
                        ],
                        'child_subcategory' => [
                            'id' => (string) $childSubcategory->id,
                            'name' => $childSubcategory->name,
                        ],
                    ];
                }
            } elseif ($subcategoryId) {
                $subcategory = Subcategory::with('category')->find($subcategoryId);
                if ($subcategory) {
                    $path = [
                        'category' => [
                            'id' => (string) $subcategory->category->id,
                            'name' => $subcategory->category->name,
                        ],
                        'subcategory' => [
                            'id' => (string) $subcategory->id,
                            'name' => $subcategory->name,
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

    private function getSubcategoryItemCount(int $subcategoryId): int
    {
        return Item::where('subcategory_id', $subcategoryId)
            ->where('status', 'active')
            ->count();
    }

    private function getChildSubcategoryItemCount(int $childSubcategoryId): int
    {
        return Item::where('child_subcategory_id', $childSubcategoryId)
            ->where('status', 'active')
            ->count();
    }
}
