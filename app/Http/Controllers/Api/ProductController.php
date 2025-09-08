<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\Favorite;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get all products
     * GET /products
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $query = Product::with(['user:id,name,profile_image', 'images'])
                ->where('status', 'active');

            // Apply filters
            if ($request->filled('category')) {
                $query->where('category', $request->category);
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

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('created_at', 'desc')->get();

            // Add favorite status for authenticated user
            if (Auth::check()) {
                $userId = Auth::id();
                $favoriteIds = Favorite::where('user_id', $userId)
                    ->pluck('product_id')
                    ->toArray();

                $products->each(function ($product) use ($favoriteIds) {
                    $product->is_favorited = in_array($product->id, $favoriteIds);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get listing details
     * GET /products/listings/{listingId}
     */
    public function getListingDetails(string $listingId): JsonResponse
    {
        try {
            $listing = Product::with(['user', 'images'])
                ->where('id', $listingId)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found'
                ], 404);
            }

            // Add favorite status for authenticated user
            if (Auth::check()) {
                $listing->is_favorited = Favorite::where('user_id', Auth::id())
                    ->where('product_id', $listing->id)
                    ->exists();
            }

            return response()->json([
                'success' => true,
                'data' => $listing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listing details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update listing
     * PUT /products/listings/{listingId}
     */
    public function updateListing(Request $request, string $listingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|string|max:100',
            'condition' => 'sometimes|required|string|max:50',
            'price' => 'sometimes|required|numeric|min:0',
            'images' => 'sometimes|array',
            'images.*' => 'image|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $listing = Product::where('id', $listingId)
                ->where('user_id', $user->id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or not authorized'
                ], 404);
            }

            DB::beginTransaction();

            // Update listing data
            $updateData = $request->only(['title', 'description', 'category', 'condition', 'price']);
            $listing->update($updateData);

            // Handle image uploads if provided
            if ($request->hasFile('images')) {
                $this->handleImageUploads($listing, $request->file('images'));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $listing->load(['user', 'images']),
                'message' => 'Listing updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get favorite products
     * GET /products/favorites
     */
    public function getFavoriteProducts(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $favorites = Product::whereHas('favorites', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['user:id,name,profile_image', 'images'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

            $favorites->each(function ($product) {
                $product->is_favorited = true;
            });

            return response()->json([
                'success' => true,
                'data' => $favorites
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorite status
     * POST /products/{productId}/toggle-favorite
     */
    public function toggleFavorite(string $productId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $favorite = Favorite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($favorite) {
                $favorite->delete();
                $isFavorited = false;
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'product_id' => $productId
                ]);
                $isFavorited = true;
            }

            return response()->json([
                'success' => true,
                'is_favorited' => $isFavorited,
                'message' => $isFavorited ? 'Added to favorites' : 'Removed from favorites'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all favorites
     * DELETE /products/favorites/clear
     */
    public function clearAllFavorites(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            Favorite::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'All favorites cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's listings
     * GET /products/my-listings
     */
    public function getMyListings(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $listings = Product::where('user_id', $user->id)
                ->with(['images'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $listings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete listing
     * DELETE /products/listings/{itemId}
     */
    public function deleteListing(string $itemId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $listing = Product::where('id', $itemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or not authorized'
                ], 404);
            }

            // Delete associated images
            if ($listing->images) {
                foreach ($listing->images as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            $listing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete listing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update listing status
     * PATCH /products/listings/{itemId}/status
     */
    public function updateListingStatus(Request $request, string $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,sold,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            $listing = Product::where('id', $itemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or not authorized'
                ], 404);
            }

            $listing->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'data' => $listing,
                'message' => 'Listing status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's purchases
     * GET /products/my-purchases
     */
    public function getMyPurchases(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $purchases = Purchase::where('user_id', $user->id)
                ->with(['product.user', 'product.images'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $purchases
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchases',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order/purchase
     * POST /products/purchases/{purchaseId}/cancel
     */
    public function cancelOrder(string $purchaseId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $purchase = Purchase::where('id', $purchaseId)
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found or cannot be cancelled'
                ], 404);
            }

            $purchase->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'data' => $purchase,
                'message' => 'Order cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get related products
     * GET /products/{productId}/related
     */
    public function getRelatedProducts(string $productId): JsonResponse
    {
        try {
            $product = Product::find($productId);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $relatedProducts = Product::where('category', $product->category)
                ->where('id', '!=', $productId)
                ->where('status', 'active')
                ->with(['user:id,name,profile_image', 'images'])
                ->limit(10)
                ->get();

            // Add favorite status for authenticated user
            if (Auth::check()) {
                $userId = Auth::id();
                $favoriteIds = Favorite::where('user_id', $userId)
                    ->pluck('product_id')
                    ->toArray();

                $relatedProducts->each(function ($product) use ($favoriteIds) {
                    $product->is_favorited = in_array($product->id, $favoriteIds);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $relatedProducts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch related products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's items
     * GET /users/{userId}/items
     */
    public function getUserItems(string $userId): JsonResponse
    {
        try {
            $products = Product::where('user_id', $userId)
                ->where('status', 'active')
                ->with(['user:id,name,profile_image', 'images'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Add favorite status for authenticated user
            if (Auth::check()) {
                $authUserId = Auth::id();
                $favoriteIds = Favorite::where('user_id', $authUserId)
                    ->pluck('product_id')
                    ->toArray();

                $products->each(function ($product) use ($favoriteIds) {
                    $product->is_favorited = in_array($product->id, $favoriteIds);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user info
     * GET /users/{userId}
     */
    public function getUserInfo(string $userId): JsonResponse
    {
        try {
            $user = User::select(['id', 'name', 'email', 'profile_image', 'university', 'course', 'created_at'])
                ->find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Add additional user stats
            $user->total_listings = Product::where('user_id', $userId)->count();
            $user->active_listings = Product::where('user_id', $userId)->where('status', 'active')->count();

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to handle image uploads
     */
    private function handleImageUploads($product, $images)
    {
        foreach ($images as $index => $image) {
            $path = $image->store('products/' . $product->id, 'public');
            
            $product->images()->create([
                'path' => $path,
                'url' => Storage::url($path),
                'is_primary' => $index === 0,
                'order' => $index,
            ]);
        }
    }
}