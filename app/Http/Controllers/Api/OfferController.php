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
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserNotificationPreference;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{

    protected $fcmService;

    public function __construct()
    {
        $this->fcmService = app(FCMService::class);
    }

    public function getOffers(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $tab  = $request->query('tab', 'all');

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

                case 'inactive':
                    $offersQuery->whereIn('status', ['rejected', 'cancelled'])
                        ->where('updated_at', '>=', now()->subDays(7));
                    break;
            }

            $offers = $offersQuery
                ->latestInChain()
                ->with([
                    'item.user',
                    'item.images',
                    'sender',
                    'receiver',
                    'parentOffer.sender',
                    'parentOffer.receiver',
                    'meetup',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $offers,
                'meta'    => [
                    'total' => $offers->count(),
                    'tab'   => $tab,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching offers', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offers',
                'error'   => $e->getMessage(),
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
                'inactive' => $allOffers->whereIn('status', ['rejected', 'cancelled'])->count(),
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

            $rootOffer = $offer->rootOffer();

            $offerChain = Offer::where('id', $rootOffer->id)
                ->orWhere('parent_offer_id', $rootOffer->id)
                ->with(['item.user', 'item.images', 'sender', 'receiver', 'parentOffer'])
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


    public function sendOffer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:items,id',
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
            'parent_offer_id' => 'nullable|integer|exists:offers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $item = Item::with('user')->find($request->item_id);

            if ($item->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot make offer on your own item'
                ], 400);
            }

            $isCounterOffer = $request->filled('parent_offer_id');
            $receiverId = $item->user_id;

            if ($isCounterOffer) {
                $parentOffer = Offer::find($request->parent_offer_id);
                if (!$parentOffer || !$parentOffer->isPending()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent offer for counter offer'
                    ], 400);
                }

                $receiverId = $parentOffer->sender_id;
            }

            $offer = Offer::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'item_id' => $request->item_id,
                'parent_offer_id' => $request->parent_offer_id,
                'amount' => $request->amount,
                'message' => $request->message,
                'status' => 'pending',
                'offer_type' => $isCounterOffer ? 'counter' : 'initial'
            ]);

            // Load relationships
            $offer->load(['item', 'sender', 'receiver', 'parentOffer']);

            // 🔥 SEND NOTIFICATION TO RECEIVER
            if ($isCounterOffer) {
                $this->sendCounterOfferNotification($offer, $item);
            } else {
                $this->sendNewOfferNotification($offer, $item);
            }

            return response()->json([
                'success' => true,
                'data' => $offer,
                'message' => $isCounterOffer ? 'Counter offer sent successfully' : 'Offer sent successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to send offer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

            $originalOffer = Offer::with(['item.user', 'sender', 'receiver'])
                ->findOrFail($offerId);

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

            $rootOffer = $originalOffer->rootOffer();

            DB::beginTransaction();

            try {
                $originalOffer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejection_reason' => 'Counter offer made'
                ]);

                $counterOffer = Offer::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $originalOffer->sender_id,
                    'item_id' => $rootOffer->item_id,
                    'parent_offer_id' => $rootOffer->id,
                    'amount' => $request->amount,
                    'message' => $request->message,
                    'status' => 'pending',
                    'offer_type' => 'counter'
                ]);

                // Load relationships for notification
                $counterOffer->load(['item.user', 'sender', 'receiver', 'parentOffer']);

                // 🔥 SEND NOTIFICATION TO ORIGINAL SENDER
                $this->sendCounterOfferNotification($counterOffer, $counterOffer->item);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => $counterOffer,
                    'message' => 'Counter offer sent successfully'
                ], 201);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send counter offer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send counter offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function acceptOffer(string $offerId): JsonResponse
    {
        try {
            $user = Auth::user();
            $offer = Offer::with(['parentOffer', 'counterOffers', 'item', 'sender'])
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

            DB::beginTransaction();

            try {
                $offer->update([
                    'status' => 'accepted',
                    'accepted_at' => now()
                ]);

                $rootOffer = $offer->isCounterOffer() ? $offer->parentOffer : $offer;

                if ($rootOffer) {
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

                // 🔥 SEND NOTIFICATION TO SENDER
                $this->sendOfferAcceptedNotification($offer);

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

    private function sendOfferAcceptedNotification(Offer $offer)
    {
        try {
            $sender = User::find($offer->sender_id);
            $accepter = $offer->receiver;

            if (!$sender || !$sender->fcm_token) {
                return;
            }

            $preferences = UserNotificationPreference::where('user_id', $sender->id)->first();
            if ($preferences && !$preferences->offer_notifications) {
                return;
            }

            $title = "Offer Accepted!";
            $body = "{$accepter->name} accepted your ₹{$offer->amount} offer on {$offer->item->title}";

            $result = $this->fcmService->sendToDevice(
                $sender->fcm_token,
                [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default'
                ],
                [
                    'type' => 'offer_accepted',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'offer_id' => (string) $offer->id,
                    'item_id' => (string) $offer->item_id,
                    'timestamp' => now()->toISOString(),
                ]
            );

            if ($result['success']) {
                UserNotification::create([
                    'user_id' => $sender->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'offer',
                    'data' => json_encode(['offer_id' => $offer->id]),
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Offer accepted notification failed', [
                'error' => $e->getMessage()
            ]);
        }
    }


    public function rejectOffer(Request $request, string $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $offer = Offer::with(['parentOffer', 'item', 'sender', 'receiver']) // Load relationships
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
                'rejection_reason' => $request->reason ?? 'Offer rejected'
            ]);

            $this->sendOfferRejectedNotification($offer, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Offer rejected successfully',
                'data' => $offer->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject offer', [
                'offer_id' => $offerId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function cancelOffer(string $offerId): JsonResponse
    {
        try {
            $user = Auth::user();
            $offer = Offer::with(['counterOffers', 'item', 'sender', 'receiver']) // Load relationships
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

            DB::beginTransaction();

            try {
                $offer->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now()
                ]);

                // Store counter offers that need notifications before cancelling
                $activeCounterOffers = [];
                if ($offer->isInitialOffer() && $offer->counterOffers()->exists()) {
                    $activeCounterOffers = $offer->counterOffers()
                        ->where('status', 'pending')
                        ->with(['sender', 'receiver', 'item'])
                        ->get()
                        ->toArray();

                    $offer->counterOffers()
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'rejection_reason' => 'Original offer was cancelled'
                        ]);
                }

                // 🔥 SEND NOTIFICATION TO RECEIVER
                $this->sendOfferCancelledNotification($offer);

                // 🔥 SEND NOTIFICATIONS TO COUNTER OFFER SENDERS
                if (!empty($activeCounterOffers)) {
                    foreach ($activeCounterOffers as $counterOfferData) {
                        $this->sendCounterOfferCancelledNotification($counterOfferData, $offer);
                    }
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
            Log::error('Failed to cancel offer', [
                'offer_id' => $offerId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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

            $basketItem = BasketItem::where('id', $basketItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$basketItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Basket item not found'
                ], 404);
            }

            $wishlist = Wishlist::firstOrCreate([
                'user_id' => $user->id,
                'item_id' => $itemId
            ]);

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

            $order = Order::create([
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'delivery_option_id' => $request->delivery_option_id,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes,
                'status' => 'pending',
                'total_amount' => 0
            ]);

            $totalAmount = 0;

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

    private function sendNewOfferNotification(Offer $offer, Item $item)
    {
        try {
            $receiver = User::find($offer->receiver_id);
            $sender = $offer->sender;

            if (!$receiver || !$receiver->fcm_token) {
                Log::info('Receiver has no FCM token', [
                    'receiver_id' => $offer->receiver_id
                ]);
                return;
            }

            // Check notification preferences
            $preferences = UserNotificationPreference::where('user_id', $receiver->id)->first();
            if ($preferences && !$preferences->offer_notifications) {
                Log::info('Offer notifications disabled for receiver', [
                    'receiver_id' => $receiver->id
                ]);
                return;
            }

            // Check if user has push notifications enabled
            if (!$receiver->push_notifications && !$receiver->notifications_enabled) {
                Log::info('Push notifications disabled for user', [
                    'receiver_id' => $receiver->id
                ]);
                return;
            }

            // Format notification
            $title = "New Offer from {$sender->name}";
            $body = "₹{$offer->amount} offer on {$item->title}";

            if ($offer->message) {
                $messagePreview = substr($offer->message, 0, 50);
                if (strlen($offer->message) > 50) {
                    $messagePreview .= "...";
                }
                $body .= " - {$messagePreview}";
            }

            // Prepare FCM data
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ];

            $data = [
                'type' => 'offer_received',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'offer_id' => (string) $offer->id,
                'item_id' => (string) $item->id,
                'item_title' => $item->title,
                'sender_id' => (string) $sender->id,
                'sender_name' => $sender->name,
                'amount' => (string) $offer->amount,
                'offer_type' => 'initial',
                'timestamp' => now()->toISOString(),
            ];

            // Add sender profile image if available
            if ($sender->profile_image) {
                $data['sender_image'] = $sender->profile_image;
            }

            // Add item image if available
            if ($item->images && is_array($item->images) && count($item->images) > 0) {
                $data['item_image'] = $item->images[0];
            }

            // Send FCM notification
            $result = $this->fcmService->sendToDevice(
                $receiver->fcm_token,
                $notificationData,
                $data
            );

            if ($result['success']) {
                Log::info('Offer notification sent successfully', [
                    'receiver_id' => $receiver->id,
                    'offer_id' => $offer->id,
                    'fcm_message_id' => $result['message_id']
                ]);

                // Save notification to database
                UserNotification::create([
                    'user_id' => $receiver->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'offer',
                    'data' => json_encode($data),
                    'is_read' => false,
                ]);
            } else {
                Log::error('Failed to send offer notification', [
                    'receiver_id' => $receiver->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Offer notification failed', [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send push notification for counter offer
     */
    private function sendCounterOfferNotification(Offer $counterOffer, Item $item)
    {
        try {
            $receiver = User::find($counterOffer->receiver_id);
            $sender = $counterOffer->sender;

            if (!$receiver || !$receiver->fcm_token) {
                Log::info('Receiver has no FCM token for counter offer', [
                    'receiver_id' => $counterOffer->receiver_id
                ]);
                return;
            }

            // Check notification preferences
            $preferences = UserNotificationPreference::where('user_id', $receiver->id)->first();
            if ($preferences && !$preferences->offer_notifications) {
                Log::info('Offer notifications disabled for receiver', [
                    'receiver_id' => $receiver->id
                ]);
                return;
            }

            // Check if user has push notifications enabled
            if (!$receiver->push_notifications && !$receiver->notifications_enabled) {
                return;
            }

            // Format notification
            $title = "Counter Offer from {$sender->name}";
            $body = "₹{$counterOffer->amount} counter offer on {$item->title}";

            if ($counterOffer->message) {
                $messagePreview = substr($counterOffer->message, 0, 50);
                if (strlen($counterOffer->message) > 50) {
                    $messagePreview .= "...";
                }
                $body .= " - {$messagePreview}";
            }

            // Prepare FCM data
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ];

            $data = [
                'type' => 'counter_offer_received',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'offer_id' => (string) $counterOffer->id,
                'item_id' => (string) $item->id,
                'item_title' => $item->title,
                'sender_id' => (string) $sender->id,
                'sender_name' => $sender->name,
                'amount' => (string) $counterOffer->amount,
                'offer_type' => 'counter',
                'parent_offer_id' => (string) $counterOffer->parent_offer_id,
                'timestamp' => now()->toISOString(),
            ];

            // Add sender profile image if available
            if ($sender->profile_image) {
                $data['sender_image'] = $sender->profile_image;
            }

            // Add item image if available
            if ($item->images && is_array($item->images) && count($item->images) > 0) {
                $data['item_image'] = $item->images[0];
            }

            // Send FCM notification
            $result = $this->fcmService->sendToDevice(
                $receiver->fcm_token,
                $notificationData,
                $data
            );

            if ($result['success']) {
                Log::info('Counter offer notification sent successfully', [
                    'receiver_id' => $receiver->id,
                    'offer_id' => $counterOffer->id,
                    'fcm_message_id' => $result['message_id']
                ]);

                // Save notification to database
                UserNotification::create([
                    'user_id' => $receiver->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'offer',
                    'data' => json_encode($data),
                    'is_read' => false,
                ]);
            } else {
                Log::error('Failed to send counter offer notification', [
                    'receiver_id' => $receiver->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Counter offer notification failed', [
                'offer_id' => $counterOffer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function sendOfferRejectedNotification(Offer $offer, $reason = null)
    {
        try {
            $sender = User::find($offer->sender_id);
            $rejecter = $offer->receiver;

            if (!$sender || !$sender->fcm_token) {
                Log::info('Sender has no FCM token for rejection notification', [
                    'sender_id' => $offer->sender_id
                ]);
                return;
            }

            // Check notification preferences
            $preferences = UserNotificationPreference::where('user_id', $sender->id)->first();
            if ($preferences && !$preferences->offer_notifications) {
                Log::info('Offer notifications disabled for sender', [
                    'sender_id' => $sender->id
                ]);
                return;
            }

            // Check if user has push notifications enabled
            if (!$sender->push_notifications && !$sender->notifications_enabled) {
                return;
            }

            // Format notification
            $offerType = $offer->offer_type === 'counter' ? 'counter offer' : 'offer';
            $title = "Offer Declined";
            $body = "{$rejecter->name} declined your ₹{$offer->amount} {$offerType} on {$offer->item->title}";

            // Add reason if provided
            if ($reason && strlen(trim($reason)) > 0) {
                $reasonPreview = substr($reason, 0, 40);
                if (strlen($reason) > 40) {
                    $reasonPreview .= "...";
                }
                $body .= " - Reason: {$reasonPreview}";
            }

            // Prepare FCM data
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ];

            $data = [
                'type' => 'offer_rejected',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'offer_id' => (string) $offer->id,
                'item_id' => (string) $offer->item_id,
                'item_title' => $offer->item->title,
                'rejecter_id' => (string) $rejecter->id,
                'rejecter_name' => $rejecter->name,
                'amount' => (string) $offer->amount,
                'offer_type' => $offer->offer_type,
                'timestamp' => now()->toISOString(),
            ];

            // Add rejection reason if available
            if ($reason) {
                $data['rejection_reason'] = $reason;
            }

            // Add rejecter profile image if available
            if ($rejecter->profile_image) {
                $data['rejecter_image'] = $rejecter->profile_image;
            }

            // Add item image if available
            if ($offer->item->images && is_array($offer->item->images) && count($offer->item->images) > 0) {
                $data['item_image'] = $offer->item->images[0];
            }

            // Send FCM notification
            $result = $this->fcmService->sendToDevice(
                $sender->fcm_token,
                $notificationData,
                $data
            );

            if ($result['success']) {
                Log::info('Offer rejection notification sent successfully', [
                    'sender_id' => $sender->id,
                    'offer_id' => $offer->id,
                    'fcm_message_id' => $result['message_id']
                ]);

                // Save notification to database
                UserNotification::create([
                    'user_id' => $sender->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'offer',
                    'data' => json_encode($data),
                    'is_read' => false,
                ]);
            } else {
                Log::error('Failed to send offer rejection notification', [
                    'sender_id' => $sender->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Offer rejection notification failed', [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send notification when offer is cancelled by sender
     */
    private function sendOfferCancelledNotification(Offer $offer)
    {
        try {
            $receiver = User::find($offer->receiver_id);
            $canceller = $offer->sender;

            if (!$receiver || !$receiver->fcm_token) {
                Log::info('Receiver has no FCM token for cancellation notification', [
                    'receiver_id' => $offer->receiver_id
                ]);
                return;
            }

            // Check notification preferences
            $preferences = UserNotificationPreference::where('user_id', $receiver->id)->first();
            if ($preferences && !$preferences->offer_notifications) {
                Log::info('Offer notifications disabled for receiver', [
                    'receiver_id' => $receiver->id
                ]);
                return;
            }

            // Check if user has push notifications enabled
            if (!$receiver->push_notifications && !$receiver->notifications_enabled) {
                return;
            }

            // Format notification
            $offerType = $offer->offer_type === 'counter' ? 'counter offer' : 'offer';
            $title = "Offer Cancelled";
            $body = "{$canceller->name} cancelled their ₹{$offer->amount} {$offerType} on {$offer->item->title}";

            // Prepare FCM data
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ];

            $data = [
                'type' => 'offer_cancelled',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'offer_id' => (string) $offer->id,
                'item_id' => (string) $offer->item_id,
                'item_title' => $offer->item->title,
                'canceller_id' => (string) $canceller->id,
                'canceller_name' => $canceller->name,
                'amount' => (string) $offer->amount,
                'offer_type' => $offer->offer_type,
                'timestamp' => now()->toISOString(),
            ];

            // Add canceller profile image if available
            if ($canceller->profile_image) {
                $data['canceller_image'] = $canceller->profile_image;
            }

            // Add item image if available
            if ($offer->item->images && is_array($offer->item->images) && count($offer->item->images) > 0) {
                $data['item_image'] = $offer->item->images[0];
            }

            // Send FCM notification
            $result = $this->fcmService->sendToDevice(
                $receiver->fcm_token,
                $notificationData,
                $data
            );

            if ($result['success']) {
                Log::info('Offer cancellation notification sent successfully', [
                    'receiver_id' => $receiver->id,
                    'offer_id' => $offer->id,
                    'fcm_message_id' => $result['message_id']
                ]);

                // Save notification to database
                UserNotification::create([
                    'user_id' => $receiver->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'offer',
                    'data' => json_encode($data),
                    'is_read' => false,
                ]);
            } else {
                Log::error('Failed to send offer cancellation notification', [
                    'receiver_id' => $receiver->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Offer cancellation notification failed', [
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send notification to counter offer sender when original offer is cancelled
     */
    private function sendCounterOfferCancelledNotification(array $counterOfferData, Offer $originalOffer)
    {
        try {
            $counterOfferSender = User::find($counterOfferData['sender_id']);

            if (!$counterOfferSender || !$counterOfferSender->fcm_token) {
                Log::info('Counter offer sender has no FCM token', [
                    'sender_id' => $counterOfferData['sender_id']
                ]);
                return;
            }

            // Check notification preferences
            $preferences = UserNotificationPreference::where('user_id', $counterOfferSender->id)->first();
            if ($preferences && !$preferences->offer_notifications) {
                return;
            }

            // Check if user has push notifications enabled
            if (!$counterOfferSender->push_notifications && !$counterOfferSender->notifications_enabled) {
                return;
            }

            $originalSender = $originalOffer->sender;
            $itemTitle = $counterOfferData['item']['title'] ?? 'item';

            // Format notification
            $title = "Offer Cancelled";
            $body = "{$originalSender->name} cancelled their original offer on {$itemTitle}. Your counter offer has been automatically cancelled.";

            // Prepare FCM data
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ];

            $data = [
                'type' => 'counter_offer_cancelled',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'offer_id' => (string) $counterOfferData['id'],
                'original_offer_id' => (string) $originalOffer->id,
                'item_id' => (string) $counterOfferData['item_id'],
                'item_title' => $itemTitle,
                'canceller_id' => (string) $originalSender->id,
                'canceller_name' => $originalSender->name,
                'timestamp' => now()->toISOString(),
            ];

            // Add item image if available
            if (
                isset($counterOfferData['item']['images']) &&
                is_array($counterOfferData['item']['images']) &&
                count($counterOfferData['item']['images']) > 0
            ) {
                $data['item_image'] = $counterOfferData['item']['images'][0];
            }

            // Send FCM notification
            $result = $this->fcmService->sendToDevice(
                $counterOfferSender->fcm_token,
                $notificationData,
                $data
            );

            if ($result['success']) {
                Log::info('Counter offer cancellation notification sent successfully', [
                    'sender_id' => $counterOfferSender->id,
                    'counter_offer_id' => $counterOfferData['id'],
                    'fcm_message_id' => $result['message_id']
                ]);

                // Save notification to database
                UserNotification::create([
                    'user_id' => $counterOfferSender->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'offer',
                    'data' => json_encode($data),
                    'is_read' => false,
                ]);
            } else {
                Log::error('Failed to send counter offer cancellation notification', [
                    'sender_id' => $counterOfferSender->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Counter offer cancellation notification failed', [
                'counter_offer_id' => $counterOfferData['id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
