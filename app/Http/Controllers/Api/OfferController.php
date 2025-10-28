<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Order;
use App\Models\BasketItem;
use App\Models\Wishlist;
use App\Models\StudyMaterialRequest;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\DeliveryOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{

    /**
     * Get offers
     * GET /offers
     */
    public function getOffers(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $tab = $request->query('tab', 'all');

            // Start with base query
            $offersQuery = Offer::query()
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                });

            switch ($tab) {
                case 'received':
                    $offersQuery->where('receiver_id', $user->id)
                        ->where('status', 'pending');
                    break;

                case 'sent':
                    $offersQuery->where('sender_id', $user->id)
                        ->where('status', 'pending');
                    break;

                case 'accepted':
                    $offersQuery->where('status', 'accepted');
                    break;

                case 'rejected':
                    $offersQuery->whereIn('status', ['rejected', 'cancelled']);
                    break;
            }

            // Apply latestInChain AFTER status filtering
            $offers = $offersQuery
                ->latestInChain()
                ->with([
                    'item.user',
                    'item.images',
                    'sender',
                    'receiver',
                    'parentOffer.sender',
                    'parentOffer.receiver'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $offers,
                'meta' => [
                    'total' => $offers->count(),
                    'tab' => $tab
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching offers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getOfferStatistics(): JsonResponse
    {
        try {
            $user = Auth::user();
            $allOffers = Offer::latestInChain()
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                })
                ->get();

            $statistics = [
                'total' => $allOffers->count(),
                'received' => $allOffers->where('receiver_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'sent' => $allOffers->where('sender_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'accepted' => $allOffers->where('status', 'accepted')->count(),
                'rejected' => $allOffers->whereIn('status', ['rejected', 'cancelled'])->count(),
                'pending' => $allOffers->where('status', 'pending')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offer statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOfferChain(string $offerId): JsonResponse
    {
        try {
            $offer = Offer::with([
                'parentOffer.sender',
                'parentOffer.receiver',
                'counterOffers.sender',
                'counterOffers.receiver',
            ])->findOrFail($offerId);

            // Find root offer
            $rootOffer = $offer->rootOffer();

            // Load all offers in this chain: root + counter offers ordered by created_at
            $offerChain = Offer::where('id', $rootOffer->id)
                ->orWhere('parent_offer_id', $rootOffer->id)
                ->with(['item.user','item.images','sender', 'receiver', 'parentOffer'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $offerChain
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offer chain',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Send offer
     * POST /offers
     */
    public function sendOffer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:items,id',
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
            'parent_offer_id' => 'nullable|integer|exists:offers,id', // NEW: For counter offers
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $item = Item::find($request->item_id);

            // Check if user is trying to offer on their own item
            if ($item->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot make offer on your own item'
                ], 400);
            }

            // NEW: Determine if this is a counter offer
            $isCounterOffer = $request->filled('parent_offer_id');
            $receiverId = $item->user_id;

            // NEW: If it's a counter offer, validate parent offer and swap sender/receiver
            if ($isCounterOffer) {
                $parentOffer = Offer::find($request->parent_offer_id);
                if (!$parentOffer || !$parentOffer->isPending()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent offer for counter offer'
                    ], 400);
                }

                // For counter offers, the receiver becomes the original sender
                $receiverId = $parentOffer->sender_id;
            }

            $offer = Offer::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'item_id' => $request->item_id,
                'parent_offer_id' => $request->parent_offer_id, // NEW
                'amount' => $request->amount,
                'message' => $request->message,
                'status' => 'pending',
                'offer_type' => $isCounterOffer ? 'counter' : 'initial' // NEW
            ]);

            return response()->json([
                'success' => true,
                'data' => $offer->load(['item', 'sender', 'parentOffer']),
                'message' => $isCounterOffer ? 'Counter offer sent successfully' : 'Offer sent successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function sendCounterOffer(Request $request, string $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Find the original offer
            $originalOffer = Offer::with(['item', 'sender', 'receiver'])
                ->findOrFail($offerId);

            // Validate that user can send counter offer
            if ($originalOffer->receiver_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only counter offers made to you'
                ], 403);
            }

            if (!$originalOffer->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only counter pending offers'
                ], 400);
            }

            // Get the root offer (in case this is already a counter offer)
            $rootOffer = $originalOffer->rootOffer();

            DB::beginTransaction();

            try {
                // Mark the original offer as rejected (since we're countering)
                $originalOffer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejection_reason' => 'Counter offer made'
                ]);

                // Create the counter offer
                // Note: sender and receiver are swapped for counter offers
                $counterOffer = Offer::create([
                    'sender_id' => $user->id,  // Current user becomes sender
                    'receiver_id' => $originalOffer->sender_id,  // Original sender becomes receiver
                    'item_id' => $rootOffer->item_id,
                    'parent_offer_id' => $rootOffer->id,  // Link to root offer
                    'amount' => $request->amount,
                    'message' => $request->message,
                    'status' => 'pending',
                    'offer_type' => 'counter'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => $counterOffer->load(['item', 'sender', 'receiver', 'parentOffer']),
                    'message' => 'Counter offer sent successfully'
                ], 201);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send counter offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept offer
     * PUT /offers/{offerId}/accept
     */
    public function acceptOffer(string $offerId): JsonResponse
    {
        try {
            $user = Auth::user();
            $offer = Offer::with(['parentOffer', 'counterOffers'])
                ->where('id', $offerId)
                ->where('receiver_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or cannot be accepted'
                ], 404);
            }

            // NEW: Use database transaction for offer chain management
            DB::beginTransaction();

            try {
                // Accept the current offer
                $offer->update([
                    'status' => 'accepted',
                    'accepted_at' => now()
                ]);

                // NEW: If this is a counter offer, reject all other pending offers in the chain
                $rootOffer = $offer->isCounterOffer() ? $offer->parentOffer : $offer;

                if ($rootOffer) {
                    // Reject all other pending offers for this item from the same chain
                    Offer::where('item_id', $offer->item_id)
                        ->where('id', '!=', $offer->id)
                        ->where(function ($query) use ($rootOffer) {
                            $query->where('id', $rootOffer->id)
                                ->orWhere('parent_offer_id', $rootOffer->id);
                        })
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'rejected',
                            'rejected_at' => now(),
                            'rejection_reason' => 'Another offer was accepted'
                        ]);
                }

                // NEW: Mark the item as sold/reserved (optional)
                // $offer->item->update(['status' => 'sold']);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Offer accepted successfully',
                    'data' => $offer->fresh(['item', 'sender', 'receiver'])
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Reject offer
     * PUT /offers/{offerId}/reject
     */
    public function rejectOffer(Request $request, string $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500', // CHANGED: Made optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $offer = Offer::with('parentOffer')
                ->where('id', $offerId)
                ->where('receiver_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or cannot be rejected'
                ], 404);
            }

            $offer->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $request->reason ?? 'Offer rejected' // NEW: Default reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Offer rejected successfully',
                'data' => $offer->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Cancel offer
     * DELETE /offers/{offerId}
     */
    public function cancelOffer(string $offerId): JsonResponse
    {
        try {
            $user = Auth::user();
            $offer = Offer::with(['counterOffers'])
                ->where('id', $offerId)
                ->where('sender_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or cannot be cancelled'
                ], 404);
            }

            // NEW: Use transaction to handle cascading cancellations
            DB::beginTransaction();

            try {
                // Cancel the current offer
                $offer->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now()
                ]);

                // NEW: If this is an initial offer, cancel all pending counter offers
                if ($offer->isInitialOffer() && $offer->counterOffers()->exists()) {
                    $offer->counterOffers()
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'rejection_reason' => 'Original offer was cancelled'
                        ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Offer cancelled successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get basket items
     * GET /basket/items
     */
    public function getBasketItems(): JsonResponse
    {
        try {
            $user = Auth::user();
            $basketItems = BasketItem::where('user_id', $user->id)
                ->with(['item', 'item.images'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $basketItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch basket items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove single item from basket
     * DELETE /basket/items/{basketItemId}
     */
    public function removeBasketItem(string $basketItemId): JsonResponse
    {
        try {
            $user = Auth::user();
            $basketItem = BasketItem::where('id', $basketItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$basketItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Basket item not found'
                ], 404);
            }

            $basketItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from basket successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from basket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove multiple items from basket
     * POST /basket/remove-multiple
     */
    public function removeMultipleBasketItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array',
            'item_ids.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $itemIds = $request->input('item_ids');

            $deletedCount = BasketItem::where('user_id', $user->id)
                ->whereIn('id', $itemIds)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} items removed from basket"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove items from basket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear entire basket
     * DELETE /basket/clear
     */
    public function clearBasket(): JsonResponse
    {
        try {
            $user = Auth::user();
            $deletedCount = BasketItem::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Basket cleared successfully',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear basket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move basket item to wishlist
     * POST /wishlist/add
     */
    public function moveToWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'basket_item_id' => 'required|string',
            'item_id' => 'required|exists:items,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $basketItemId = $request->input('basket_item_id');
            $itemId = $request->input('item_id');

            // Find and delete the basket item
            $basketItem = BasketItem::where('id', $basketItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$basketItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Basket item not found'
                ], 404);
            }

            // Add to wishlist using firstOrCreate to prevent duplicates
            $wishlist = Wishlist::firstOrCreate([
                'user_id' => $user->id,
                'item_id' => $itemId
            ]);

            // Delete from basket
            $basketItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item moved to wishlist successfully',
                'data' => [
                    'wishlist_id' => $wishlist->id,
                    'item_id' => $itemId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move item to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get payment methods
     * GET /payment/methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $paymentMethods = PaymentMethod::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get delivery options
     * GET /delivery/options
     */
    public function getDeliveryOptions(): JsonResponse
    {
        try {
            $deliveryOptions = DeliveryOption::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => $deliveryOptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process checkout
     * POST /checkout
     */
    public function processCheckout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'payment_method_id' => 'required|string',
            'delivery_option_id' => 'required|string',
            'delivery_address' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'delivery_option_id' => $request->delivery_option_id,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes,
                'status' => 'pending',
                'total_amount' => 0 // Will be calculated
            ]);

            $totalAmount = 0;

            // Process order items
            foreach ($request->items as $itemData) {
                $item = Item::find($itemData['item_id']);
                if ($item) {
                    $order->items()->create([
                        'item_id' => $item->id,
                        'quantity' => $itemData['quantity'],
                        'price' => $item->price,
                        'total' => $item->price * $itemData['quantity']
                    ]);

                    $totalAmount += $item->price * $itemData['quantity'];
                }
            }

            $order->update(['total_amount' => $totalAmount]);

            // Clear basket items
            BasketItem::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order placed successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user orders
     * GET /orders
     */
    public function getOrders(): JsonResponse
    {
        try {
            $user = Auth::user();
            $orders = Order::where('user_id', $user->id)
                ->with(['items.item', 'paymentMethod', 'deliveryOption'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details
     * GET /orders/{orderId}
     */
    public function getOrderDetails(string $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->with(['items.item', 'paymentMethod', 'deliveryOption', 'trackingUpdates'])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order tracking
     * GET /orders/{orderId}/tracking
     */
    public function getOrderTracking(string $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $trackingUpdates = $order->trackingUpdates()->orderBy('created_at')->get();

            return response()->json([
                'success' => true,
                'data' => $trackingUpdates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tracking updates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order
     * PUT /orders/{orderId}/cancel
     */
    public function cancelOrder(string $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if (!in_array($order->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled'
                ], 400);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
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
     * Get study material requests
     * GET /study-material-requests
     */
    public function getStudyMaterialRequests(): JsonResponse
    {
        try {
            $user = Auth::user();
            $requests = StudyMaterialRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch study material requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create study material request
     * POST /study-material-requests
     */
    public function createStudyMaterialRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'subject' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'desired_price_min' => 'nullable|numeric|min:0',
            'desired_price_max' => 'nullable|numeric|min:0',
            'urgency' => 'nullable|string|in:low,medium,high',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            $studyRequest = StudyMaterialRequest::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'subject' => $request->subject,
                'category' => $request->category,
                'desired_price_min' => $request->desired_price_min,
                'desired_price_max' => $request->desired_price_max,
                'urgency' => $request->urgency ?? 'medium',
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'data' => $studyRequest,
                'message' => 'Study material request created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create study material request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete study material request
     * DELETE /study-material-requests/{requestId}
     */
    public function deleteStudyMaterialRequest(string $requestId): JsonResponse
    {
        try {
            $user = Auth::user();
            $request = StudyMaterialRequest::where('id', $requestId)
                ->where('user_id', $user->id)
                ->first();

            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Study material request not found'
                ], 404);
            }

            $request->delete();

            return response()->json([
                'success' => true,
                'message' => 'Study material request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete study material request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark request as fulfilled
     * PUT /study-material-requests/{requestId}/fulfill
     */
    public function markRequestFulfilled(string $requestId): JsonResponse
    {
        try {
            $user = Auth::user();
            $request = StudyMaterialRequest::where('id', $requestId)
                ->where('user_id', $user->id)
                ->first();

            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Study material request not found'
                ], 404);
            }

            $request->update([
                'status' => 'fulfilled',
                'fulfilled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request marked as fulfilled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark request as fulfilled',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user preferences
     * GET /user/preferences
     */
    public function getUserPreferences(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => [
                    'university' => $user->university ?? '',
                    'course' => $user->course ?? '',
                    'year' => $user->year ?? '',
                    'location' => $user->location ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
