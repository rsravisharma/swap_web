<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemImage;
use App\Services\HistoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
    public function show(int $id): JsonResponse
    {
        try {
            $item = Item::with([
                'user:id,name,profile_image,university,course',
                'category',
                'images'
            ])->where('id', $id)->where('user_id', Auth::id())->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Add to view history
            HistoryService::addViewHistory(Auth::id(), $item->id, $item->title);

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
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max per image
            'location' => 'nullable|string|max:255',
            'contact_method' => 'nullable|string|in:chat,phone,email',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'title',
                'description',
                'category',
                'price',
                'condition',
                'location',
                'contact_method'
            ]);
            $data['user_id'] = Auth::id();
            $data['status'] = 'active';
            $data['tags'] = $request->input('tags', []);

            $item = Item::create($data);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($item, $request->file('images'));
            }

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'create');

            return response()->json([
                'success' => true,
                'data' => $item->load(['user', 'category', 'images']),
                'message' => 'Item created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'contact_method' => 'nullable|string|in:chat,phone,email',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
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
                'category',
                'price',
                'condition',
                'location',
                'contact_method',
                'tags'
            ]);

            $item->update($updateData);

            // Add to history
            HistoryService::addItemHistory(Auth::id(), $item->id, $item->title, 'update');

            return response()->json([
                'success' => true,
                'data' => $item->load(['user', 'category', 'images']),
                'message' => 'Item updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item',
                'error' => $e->getMessage()
            ], 500);
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

        foreach ($images as $index => $image) {
            $extension = $image->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '_' . $index . '.' . $extension;

            // Store in storage/app/public/items/user_{userId}/item_{itemId}/
            $directory = "items/user_{$userId}/item_{$itemId}";
            $path = $image->storeAs($directory, $filename, 'public');

            ItemImage::create([
                'item_id' => $item->id,
                'image_path' => $path,
                'filename' => $filename,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
                'order' => $index + 1,
                'is_primary' => $index === 0,
            ]);
        }
    }
}
