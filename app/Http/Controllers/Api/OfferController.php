<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Order;
use App\Models\BasketItem;
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
            'basket_item_id' => 'required|string'
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

            $basketItem = BasketItem::where('id', $basketItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$basketItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Basket item not found'
                ], 404);
            }

            // Add to wishlist (implement Wishlist model logic)
            // For now, just removing from basket
            $basketItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item moved to wishlist successfully'
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
     * Get offers
     * GET /offers
     */
    public function getOffers(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get both sent and received offers
            $offers = Offer::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->with(['item', 'sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $offers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offers',
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

            $offer = Offer::create([
                'sender_id' => $user->id,
                'receiver_id' => $item->user_id,
                'item_id' => $request->item_id,
                'amount' => $request->amount,
                'message' => $request->message,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'data' => $offer->load(['item', 'sender']),
                'message' => 'Offer sent successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send offer',
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
            $offer = Offer::where('id', $offerId)
                ->where('receiver_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or cannot be accepted'
                ], 404);
            }

            $offer->update([
                'status' => 'accepted',
                'accepted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Offer accepted successfully'
            ]);

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
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $offer = Offer::where('id', $offerId)
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
                'rejection_reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Offer rejected successfully'
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
            $offer = Offer::where('id', $offerId)
                ->where('sender_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or cannot be cancelled'
                ], 404);
            }

            $offer->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Offer cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel offer',
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