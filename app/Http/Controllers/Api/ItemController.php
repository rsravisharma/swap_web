<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Favorite;
use App\Models\Location;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildSubCategory;
use App\Services\HistoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Transaction;

class ItemController extends Controller
{
    /**
     * List items with pagination
     * GET /items
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'search' => 'nullable|string',
            'category' => 'nullable|string',
            'status' => 'nullable|string|in:active,sold,archived',
            'condition' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Item::with(['user:id,name,profile_image', 'category'])
                ->where('user_id', Auth::id());

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                // Default to active items only
                $query->where('status', 'active');
            }

            if ($request->filled('condition')) {
                $query->where('condition', $request->condition);
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            $limit = $request->query('limit', 20);
            $items = $query->orderBy('created_at', 'desc')->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $items->items(),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'has_more' => $items->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items
     * GET /items/search
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ], 422);
        }

        try {
            $userId = Auth::id();

            $items = Item::with(['user:id,name,profile_image', 'category'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%");
                })
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // Add to search history
            HistoryService::addSearchHistory($userId, $query, [], $items->count());

            return response()->json([
                'success' => true,
                'data' => $items,
                'total_results' => $items->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item detail
     * GET /items/{id}
     */
    // For viewing any item (public view)
    public function show(Item $item): JsonResponse
    {
        try {
            // Don't check ownership - any user can view items
            // But ensure item is active (unless it's the owner viewing)
            if ($item->status !== 'active' && $item->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Load relationships
            $item->load([
                'user:id,name,profile_image,university,course',
                'category:id,name',
                'subCategory:id,name',
                'childSubCategory:id,name',
                'images'
            ]);

            // Add to view history (only if not viewing own item)
            if ($item->user_id !== Auth::id()) {
                HistoryService::addViewHistory(Auth::id(), $item->id, $item->title);
            }

            // Format the response to match your Flutter expectations
            $responseData = [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'title' => $item->title,
                'description' => $item->description,
                'category_name' => $item->category?->name ?? 'Unknown',
                'category_id' => $item->category_id,
                'sub_category_id' => $item->sub_category_id,
                'child_sub_category_id' => $item->child_sub_category_id,
                'price' => $item->price, // This should be a string like "300.00"
                'condition' => $item->condition,
                'status' => $item->status,
                'location_id' => $item->location_id,
                'location' => $item->location,
                'contact_method' => $item->contact_method,
                'tags' => $item->tags ?? [],
                'is_sold' => (bool) $item->is_sold,
                'is_archived' => (bool) $item->is_archived,
                'is_promoted' => (bool) $item->is_promoted,
                'promotion_type' => $item->promotion_type,
                'promoted_until' => $item->promoted_until,
                'sold_at' => $item->sold_at,
                'archived_at' => $item->archived_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'user' => [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
                    'profile_image' => $item->user->profile_image,
                    'university' => $item->user->university ?? '',
                    'course' => $item->user->course ?? '',
                ],
                'images' => $item->images ?? []
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch item details',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Add a new method for editing (owner-only access)
    public function edit(Item $item): JsonResponse
    {
        try {
            // Check if user owns the item for editing
            if ($item->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Load relationships
            $item->load([
                'user:id,name,profile_image,university,course',
                'category',
                'images'
            ]);

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch item details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new item
     * POST /items
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'condition' => 'required|string|in:new,like_new,good,fair,poor',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'location' => 'nullable|string|max:255',
            'location_data' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_method' => 'nullable|string|in:chat,phone,email',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'category_type' => 'nullable|string|in:category,subcategory,child_subcategory',
            'category_id' => 'nullable|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:sub_categories,id',
            'child_subcategory_id' => 'nullable|integer|exists:child_sub_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ✅ STEP 1: Check if user has enough coins BEFORE starting transaction
            $user = Auth::user();
            $requiredCoins = 10;

            if ($user->coins < $requiredCoins) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient coins. You need 10 coins to create a listing.',
                    'current_coins' => $user->coins,
                    'required_coins' => $requiredCoins
                ], 400);
            }

            // ✅ STEP 2: Use database transaction for atomic operations
            $item = DB::transaction(function () use ($request, $user, $requiredCoins) {

                // Deduct coins first (using the method from your User model)
                if (!$user->deductCoins($requiredCoins)) {
                    throw new \Exception('Failed to deduct coins');
                }

                // Base item data
                $data = [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'price' => $request->input('price'),
                    'condition' => $request->input('condition'),
                    'contact_method' => $request->input('contact_method', 'chat'),
                    'user_id' => $user->id,
                    'status' => 'active',
                    'tags' => $request->input('tags', []),
                ];

                // Handle category - hybrid approach (your existing logic)
                if ($request->has('category')) {
                    $categoryName = $request->input('category');
                    $categoryType = $request->input('category_type', null);
                    $data['category_name'] = $categoryName;

                    if ($request->has('category_id') && $categoryType) {
                        if ($categoryType === 'child_subcategory' && $request->has('child_subcategory_id')) {
                            $childSubCategory = ChildSubCategory::where('id', $request->input('child_subcategory_id'))
                                ->where('is_active', true)
                                ->with('subCategory.category')
                                ->first();

                            if ($childSubCategory) {
                                $data['child_sub_category_id'] = $childSubCategory->id;
                                $data['sub_category_id'] = $childSubCategory->sub_category_id;
                                $data['category_id'] = $childSubCategory->subCategory->category_id;
                            }
                        } elseif ($categoryType === 'subcategory' && $request->has('subcategory_id')) {
                            $subCategory = SubCategory::where('id', $request->input('subcategory_id'))
                                ->where('is_active', true)
                                ->with('category')
                                ->first();

                            if ($subCategory) {
                                $data['sub_category_id'] = $subCategory->id;
                                $data['category_id'] = $subCategory->category_id;
                                $data['child_sub_category_id'] = null;
                            }
                        } elseif ($categoryType === 'category') {
                            $category = Category::where('id', $request->input('category_id'))
                                ->where('is_active', true)
                                ->first();

                            if ($category) {
                                $data['category_id'] = $category->id;
                                $data['sub_category_id'] = null;
                                $data['child_sub_category_id'] = null;
                            }
                        }
                    } else {
                        Log::info('⚠️ Category IDs not provided, falling back to name search');
                        $category = Category::where('name', $categoryName)
                            ->where('is_active', true)
                            ->first();

                        if ($category) {
                            $data['category_id'] = $category->id;
                            $data['sub_category_id'] = null;
                            $data['child_sub_category_id'] = null;
                        } else {
                            $subCategory = SubCategory::where('name', $categoryName)
                                ->where('is_active', true)
                                ->with('category')
                                ->first();

                            if ($subCategory) {
                                $data['sub_category_id'] = $subCategory->id;
                                $data['category_id'] = $subCategory->category_id;
                                $data['child_sub_category_id'] = null;
                            } else {
                                $childSubCategory = ChildSubCategory::where('name', $categoryName)
                                    ->where('is_active', true)
                                    ->with('subCategory.category')
                                    ->first();

                                if ($childSubCategory) {
                                    $data['child_sub_category_id'] = $childSubCategory->id;
                                    $data['sub_category_id'] = $childSubCategory->sub_category_id;
                                    $data['category_id'] = $childSubCategory->subCategory->category_id;
                                } else {
                                    $category = Category::create([
                                        'name' => $categoryName,
                                        'slug' => Str::slug($categoryName),
                                        'description' => "Auto-created category for {$categoryName}",
                                        'is_active' => true,
                                    ]);

                                    $data['category_id'] = $category->id;
                                    $data['sub_category_id'] = null;
                                    $data['child_sub_category_id'] = null;
                                }
                            }
                        }
                    }
                }

                // Handle location (your existing logic)
                if ($request->has('location') && !empty($request->input('location'))) {
                    $locationString = $request->input('location');
                    $data['location'] = $locationString;

                    if ($request->has('latitude') && $request->has('longitude')) {
                        $locationData = [
                            'name' => $locationString,
                            'full_address' => $locationString,
                            'latitude' => $request->input('latitude'),
                            'longitude' => $request->input('longitude'),
                        ];

                        if ($request->has('location_data')) {
                            $additionalData = json_decode($request->input('location_data'), true);
                            if ($additionalData && is_array($additionalData)) {
                                $locationData = array_merge($locationData, [
                                    'city' => $additionalData['city'] ?? null,
                                    'state' => $additionalData['state'] ?? null,
                                    'country' => $additionalData['country'] ?? null,
                                    'postal_code' => $additionalData['postal_code'] ?? null,
                                    'type' => 'user_selected',
                                ]);
                            }
                        }

                        $location = Location::where('latitude', $locationData['latitude'])
                            ->where('longitude', $locationData['longitude'])
                            ->first();

                        if (!$location) {
                            $location = Location::create($locationData);
                            Log::info("New location created", ['location_id' => $location->id]);
                        } else {
                            Log::info("Existing location found", ['location_id' => $location->id]);
                        }

                        $data['location_id'] = $location->id;
                    }
                }

                // Create the item
                $item = Item::create($data);

                // Handle image uploads
                if ($request->hasFile('images')) {
                    $this->handleImageUploads($item, $request->file('images'));
                }

                // ✅ STEP 3: Log coin transaction for audit trail
                $this->logCoinTransaction($user, $requiredCoins, 'item_listing', $item->id);

                return $item;
            });

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'create');

            // Load relationships for response
            $item->load(['user', 'category', 'location', 'images']);

            // ✅ STEP 4: Include updated coin balance in response
            $user->refresh(); // Refresh to get updated coins value

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item created successfully',
                'coins_deducted' => 10,
                'remaining_coins' => $user->coins
            ], 201);
        } catch (\Exception $e) {
            Log::error('Item creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['images'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // ✅ STEP 5: Add helper method for logging coin transactions
    private function logCoinTransaction($user, $amount, $type, $itemId = null)
    {
        // Create a coin transaction record for audit
        DB::table('coin_transactions')->insert([
            'user_id' => $user->id,
            'amount' => -$amount, // Negative for deduction
            'type' => $type,
            'description' => "Deducted {$amount} coins for creating item listing",
            'item_id' => $itemId,
            'balance_after' => $user->coins,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }



    /**
     * Update item
     * PUT /items/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = Item::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:2000',
            'category' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'condition' => 'sometimes|required|string|in:new,like_new,good,fair,poor',
            'location' => 'nullable|string|max:255',
            'location_data' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_method' => 'nullable|string|in:chat,phone,email',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'category_type' => 'nullable|string|in:category,subcategory,child_subcategory',
            'category_id' => 'nullable|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:sub_categories,id',
            'child_subcategory_id' => 'nullable|integer|exists:child_sub_categories,id',
            'category_full_path' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only([
                'title',
                'description',
                'price',
                'condition',
                'contact_method'
            ]);

            if ($request->has('category')) {
                $categoryName = $request->input('category');
                $categoryType = $request->input('category_type', null);
                $updateData['category_name'] = $categoryName;

                if ($request->has('category_id') && $categoryType) {
                    if ($categoryType === 'child_subcategory' && $request->has('child_subcategory_id')) {
                        // It's a child subcategory - use the provided IDs
                        $childSubCategory = ChildSubCategory::where('id', $request->input('child_subcategory_id'))
                            ->where('is_active', true)
                            ->with('subCategory.category')
                            ->first();

                        if ($childSubCategory) {
                            $updateData['child_sub_category_id'] = $childSubCategory->id;
                            $updateData['sub_category_id'] = $childSubCategory->sub_category_id;
                            $updateData['category_id'] = $childSubCategory->subCategory->category_id;
                        }
                    } elseif ($categoryType === 'subcategory' && $request->has('subcategory_id')) {
                        // It's a subcategory - use the provided IDs
                        $subCategory = SubCategory::where('id', $request->input('subcategory_id'))
                            ->where('is_active', true)
                            ->with('category')
                            ->first();

                        if ($subCategory) {
                            $updateData['sub_category_id'] = $subCategory->id;
                            $updateData['category_id'] = $subCategory->category_id;
                            $updateData['child_sub_category_id'] = null;
                        }
                    } elseif ($categoryType === 'category') {
                        // It's a main category - use the provided ID
                        $category = Category::where('id', $request->input('category_id'))
                            ->where('is_active', true)
                            ->first();

                        if ($category) {
                            $updateData['category_id'] = $category->id;
                            $updateData['sub_category_id'] = null;
                            $updateData['child_sub_category_id'] = null;
                        }
                    }
                } else {
                    $category = Category::where('name', $categoryName)
                        ->where('is_active', true)
                        ->first();

                    if ($category) {
                        // Found a main category
                        $updateData['category_id'] = $category->id;
                        $updateData['sub_category_id'] = null;
                        $updateData['child_sub_category_id'] = null;
                    } else {
                        // Try subcategory
                        $subCategory = SubCategory::where('name', $categoryName)
                            ->where('is_active', true)
                            ->with('category')
                            ->first();

                        if ($subCategory) {
                            $updateData['sub_category_id'] = $subCategory->id;
                            $updateData['category_id'] = $subCategory->category_id;
                            $updateData['child_sub_category_id'] = null;
                        } else {
                            // Try child subcategory
                            $childSubCategory = ChildSubCategory::where('name', $categoryName)
                                ->where('is_active', true)
                                ->with('subCategory.category')
                                ->first();

                            if ($childSubCategory) {
                                $updateData['child_sub_category_id'] = $childSubCategory->id;
                                $updateData['sub_category_id'] = $childSubCategory->sub_category_id;
                                $updateData['category_id'] = $childSubCategory->subCategory->category_id;
                            } else {
                                // Create new category as fallback
                                $category = Category::create([
                                    'name' => $categoryName,
                                    'slug' => Str::slug($categoryName),
                                    'description' => "Auto-created category for {$categoryName}",
                                    'is_active' => true,
                                ]);

                                $updateData['category_id'] = $category->id;
                                $updateData['sub_category_id'] = null;
                                $updateData['child_sub_category_id'] = null;
                            }
                        }
                    }
                }
            }

            // Handle location - same as store method
            if ($request->has('location') && !empty($request->input('location'))) {
                $locationString = $request->input('location');
                $updateData['location'] = $locationString;

                // Try to create structured location if we have coordinates
                if ($request->has('latitude') && $request->has('longitude')) {
                    $locationData = [
                        'name' => $locationString,
                        'full_address' => $locationString,
                        'latitude' => $request->input('latitude'),
                        'longitude' => $request->input('longitude'),
                    ];

                    // Parse additional location data if available
                    if ($request->has('location_data')) {
                        $additionalData = json_decode($request->input('location_data'), true);
                        if ($additionalData && is_array($additionalData)) {
                            $locationData = array_merge($locationData, [
                                'city' => $additionalData['city'] ?? null,
                                'state' => $additionalData['state'] ?? null,
                                'country' => $additionalData['country'] ?? null,
                                'postal_code' => $additionalData['postal_code'] ?? null,
                                'type' => 'user_selected',
                            ]);
                        }
                    }

                    // Find existing location by coordinates or create new one
                    $location = Location::where('latitude', $locationData['latitude'])
                        ->where('longitude', $locationData['longitude'])
                        ->first();

                    if (!$location) {
                        $location = Location::create($locationData);
                    } else {
                        Log::info("Existing location found for update", ['location_id' => $location->id]);
                    }

                    $updateData['location_id'] = $location->id;
                }
            }

            // Handle tags
            if ($request->has('tags') && is_array($request->input('tags'))) {
                $tags = array_filter($request->input('tags'));
                $updateData['tags'] = json_encode($tags);
            } else {
                $updateData['tags'] = json_encode([]);
            }

            $item->update($updateData);

            // Handle image updates
            $this->updateItemImages($item, $request);

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'update');

            // Load relationships including category and location
            $updatedItem = $item->fresh()->load(['user', 'category', 'location', 'images']);

            return response()->json([
                'success' => true,
                'data' => $updatedItem,
                'message' => 'Item updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function updateItemImages(Item $item, Request $request)
    {
        $existingImages = $request->input('existing_images', []);
        $newImages = $request->file('new_images', []);

        Log::info('Existing images count: ' . count($existingImages));
        Log::info('New images count: ' . count($newImages));

        // If we have existing_images array, it means user wants to keep specific images
        if ($request->has('existing_images')) {
            $imagesToKeep = [];

            // Extract clean paths from URLs
            foreach ($existingImages as $imageUrl) {
                // Get the filename from the URL
                $filename = basename(parse_url($imageUrl, PHP_URL_PATH));

                // Find matching image in database by filename
                $matchingImage = $item->images()->where('filename', $filename)->first();
                if ($matchingImage) {
                    $imagesToKeep[] = $matchingImage->image_path;
                    Log::info('Will keep image: ' . $matchingImage->image_path);
                }
            }

            // Delete images not in keep list
            $currentImages = $item->images;
            foreach ($currentImages as $currentImage) {
                if (!in_array($currentImage->image_path, $imagesToKeep)) {
                    Log::info('Deleting image: ' . $currentImage->image_path);
                    Storage::disk('public')->delete($currentImage->image_path);
                    $currentImage->delete();
                }
            }
        }
        // If no existing_images specified, keep all current images

        // Add new images
        if (!empty($newImages)) {
            $this->handleImageUploads($item, $newImages);
            Log::info('Added ' . count($newImages) . ' new images');
        }

        // Update image order
        $remainingImages = $item->fresh()->images()->orderBy('created_at')->get();
        foreach ($remainingImages as $index => $image) {
            $image->update([
                'order' => $index + 1,
                'is_primary' => $index === 0
            ]);
        }
    }


    /**
     * Delete item
     * DELETE /items/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $item = Item::where('id', $id)->where('user_id', Auth::id())->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $itemTitle = $item->title;

            // Delete associated images
            if ($item->images) {
                foreach ($item->images as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            $item->delete();

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $itemTitle, 'delete');

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark item as sold
     * POST /items/{id}/mark-sold
     */
    public function markAsSold(int $id): JsonResponse
    {
        try {
            $item = Item::where('id', $id)->where('user_id', Auth::id())->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $item->update([
                'status' => 'sold',
                'is_sold' => true,
                'sold_at' => now()
            ]);

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'sold');

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item marked as sold successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark item as sold',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive item
     * POST /items/{id}/archive
     */
    public function archive(int $id): JsonResponse
    {
        try {
            $item = Item::where('id', $id)->where('user_id', Auth::id())->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // ✅ The observer will automatically handle active_listings decrement
            $item->update([
                'status' => 'archived',
                'is_archived' => true,
                'archived_at' => now()
            ]);

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'archive');

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item archived successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unarchive(int $id): JsonResponse
    {
        try {
            $item = Item::where('id', $id)->where('user_id', Auth::id())->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // ✅ The observer will automatically handle active_listings increment
            $item->update([
                'status' => 'active',
                'is_archived' => false,
                'archived_at' => null
            ]);

            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'unarchive');

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item unarchived successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unarchive item',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get items purchased by the authenticated user
     * GET /items/my-purchases
     */
    public function getMyPurchases(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $limit  = $request->query('limit', 20);
            $status = $request->query('status'); // optional filter: completed, pending, cancelled

            // ─── Approach 1: via Orders table (preferred if you have it) ─────────────
            // Check if the orders table exists and has a buyer_id column
            if (Schema::hasTable('orders')) {

                $query = Order::with([
                    'item.images',
                    'item.category',
                    'item.user:id,name,profile_image,university',  // seller info
                ])
                    ->where('buyer_id', $userId)
                    ->latest();

                // Optional status filter
                if ($status) {
                    $query->where('status', $status);
                }

                $orders = $query->paginate($limit);

                $data = $orders->getCollection()->map(function ($order) {
                    $item   = $order->item;
                    $seller = $item?->user;

                    return [
                        'id'         => $order->id,
                        'order_id'   => $order->id,
                        'status'     => $order->status ?? 'completed',
                        'amount'     => $order->amount ?? $item?->price,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,

                        // Item details
                        'item' => $item ? [
                            'id'          => $item->id,
                            'title'       => $item->title,
                            'description' => $item->description,
                            'price'       => $item->price,
                            'condition'   => $item->condition,
                            'status'      => $item->status,
                            'images'      => $item->images ?? [],
                        ] : null,

                        // Seller details
                        'seller' => $seller ? [
                            'id'            => $seller->id,
                            'name'          => $seller->name,
                            'profile_image' => $seller->profile_image,
                            'university'    => $seller->university ?? '',
                        ] : null,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data'    => $data,
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'last_page'    => $orders->lastPage(),
                        'per_page'     => $orders->perPage(),
                        'total'        => $orders->total(),
                        'has_more'     => $orders->hasMorePages(),
                    ],
                ]);
            }

            // ─── Approach 2: fallback via Items table (buyer_id column) ─────────────
            if (Schema::hasColumn('items', 'buyer_id')) {

                $query = Item::with([
                    'images',
                    'category:id,name',
                    'user:id,name,profile_image,university', 
                ])
                    ->where('buyer_id', $userId)
                    ->where('is_sold', true)
                    ->latest('sold_at');

                if ($status === 'completed') {
                    $query->where('status', 'sold');
                }

                $items = $query->paginate($limit);

                $data = $items->getCollection()->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'order_id'   => $item->id,
                        'status'     => 'completed',
                        'amount'     => $item->price,
                        'created_at' => $item->sold_at ?? $item->created_at,
                        'updated_at' => $item->updated_at,

                        'item' => [
                            'id'          => $item->id,
                            'title'       => $item->title,
                            'description' => $item->description,
                            'price'       => $item->price,
                            'condition'   => $item->condition,
                            'status'      => $item->status,
                            'images'      => $item->images ?? [],
                        ],

                        'seller' => $item->user ? [
                            'id'            => $item->user->id,
                            'name'          => $item->user->name,
                            'profile_image' => $item->user->profile_image,
                            'university'    => $item->user->university ?? '',
                        ] : null,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data'    => $data,
                    'pagination' => [
                        'current_page' => $items->currentPage(),
                        'last_page'    => $items->lastPage(),
                        'per_page'     => $items->perPage(),
                        'total'        => $items->total(),
                        'has_more'     => $items->hasMorePages(),
                    ],
                ]);
            }

            // ─── Approach 3: via Transactions table ─────────────────────────────────
            if (Schema::hasTable('transactions')) {

                $query = Transaction::with([
                    'item.images',
                    'item.category:id,name',
                    'item.user:id,name,profile_image,university',
                ])
                    ->where('buyer_id', $userId)
                    ->latest();

                if ($status) {
                    $query->where('status', $status);
                }

                $transactions = $query->paginate($limit);

                $data = $transactions->getCollection()->map(function ($tx) {
                    $item   = $tx->item;
                    $seller = $item?->user;

                    return [
                        'id'         => $tx->id,
                        'order_id'   => $tx->id,
                        'status'     => $tx->status ?? 'completed',
                        'amount'     => $tx->amount ?? $item?->price,
                        'created_at' => $tx->created_at,
                        'updated_at' => $tx->updated_at,

                        'item' => $item ? [
                            'id'          => $item->id,
                            'title'       => $item->title,
                            'price'       => $item->price,
                            'condition'   => $item->condition,
                            'status'      => $item->status,
                            'images'      => $item->images ?? [],
                        ] : null,

                        'seller' => $seller ? [
                            'id'            => $seller->id,
                            'name'          => $seller->name,
                            'profile_image' => $seller->profile_image,
                            'university'    => $seller->university ?? '',
                        ] : null,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data'    => $data,
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page'    => $transactions->lastPage(),
                        'per_page'     => $transactions->perPage(),
                        'total'        => $transactions->total(),
                        'has_more'     => $transactions->hasMorePages(),
                    ],
                ]);
            }

            // ─── No purchase tracking found ─────────────────────────────────────────
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'No purchase records found',
            ]);
        } catch (\Exception $e) {
            Log::error('getMyPurchases error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchases',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }


    /**
     * Promote item
     * POST /items/{id}/promote
     */
    public function promote(Request $request, int $id): JsonResponse
    {
        $item = Item::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'promotion_type' => 'required|string|in:featured,urgent,highlighted',
            'duration_days' => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $promotionData = [
                'promotion_type' => $request->promotion_type,
                'promoted_until' => now()->addDays($request->duration_days),
                'is_promoted' => true,
            ];

            $item->update($promotionData);

            // Add to history
            HistoryService::addPromotionHistory(Auth::id(), $item->id, $item->title, $request->promotion_type, $request->duration_days);

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item promoted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to promote item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle image uploads for item
     */
    private function handleImageUploads(Item $item, array $images)
    {
        $userId = Auth::id();
        $itemId = $item->id;

        // Get the current maximum order
        $maxOrder = $item->images()->max('order') ?? 0;

        foreach ($images as $index => $image) {
            $extension = $image->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '_' . ($maxOrder + $index + 1) . '.' . $extension;

            // Store in storage/app/public/items/user_{userId}/item_{itemId}/
            $directory = "items/user_{$userId}/item_{$itemId}";
            $path = $image->storeAs($directory, $filename, 'public');

            ItemImage::create([
                'item_id' => $item->id,
                'image_path' => $path,
                'filename' => $filename,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'order' => $maxOrder + $index + 1,
                'is_primary' => $item->images()->count() === 0 && $index === 0, // Only first image is primary if no images exist
            ]);
        }
    }

    public function getMyListings(): JsonResponse
    {
        try {
            Log::info('getMyListings called for user: ' . Auth::id());

            $items = Item::with(['images', 'category'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            Log::info('Found ' . $items->count() . ' items for user: ' . Auth::id());

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            Log::error('getMyListings error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $item = Item::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,sold,archived'
        ]);

        $item->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    public function getFavorites()
    {
        // You'll need to create a Favorite model that references items
        $favorites = Favorite::with(['item.images', 'item.user'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    public function toggleFavorite($id)
    {
        $item = Item::findOrFail($id);

        $favorite = Favorite::where([
            'user_id' => Auth::id(),
            'item_id' => $id
        ])->first();

        if ($favorite) {
            $favorite->delete();
            $favorited = false;
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'item_id' => $id
            ]);
            $favorited = true;
        }

        return response()->json([
            'success' => true,
            'favorited' => $favorited
        ]);
    }

    public function getRelated($id)
    {
        $item = Item::findOrFail($id);

        $related = Item::where('category_id', $item->category)
            ->where('id', '!=', $id)
            ->where('status', 'active')
            ->with(['images', 'user'])
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $related
        ]);
    }

    public function getUserItems(string $userId): JsonResponse
    {
        try {
            $items = Item::where('user_id', $userId)
                ->where('status', 'active')
                ->with(['user:id,name,profile_image', 'images'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Add favorite status for authenticated user
            if (Auth::check()) {
                $authUserId = Auth::id();
                $favoriteIds = Favorite::where('user_id', $authUserId)
                    ->pluck('item_id')
                    ->toArray();

                $items->each(function ($item) use ($favoriteIds) {
                    $item->is_favorited = in_array($item->id, $favoriteIds);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
