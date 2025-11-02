<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Meetup;
use App\Models\Item;
use App\Models\User;
use App\Models\Offer;

class MeetupController extends Controller
{
    /**
     * Get all meetups for authenticated user (both as buyer and seller)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $role = $request->query('role', 'all');
            $status = $request->query('status');

            Log::info('📋 Fetching meetups', [
                'user_id' => $user->id,
                'role' => $role,
                'status' => $status,
            ]);

            $query = Meetup::query();

            // Filter by role
            if ($role === 'buyer') {
                $query->where('buyer_id', $user->id);
            } elseif ($role === 'seller') {
                $query->where('seller_id', $user->id);
            } else {
                // Get both buyer and seller meetups
                $query->where(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                });
            }

            // Filter by status
            if ($status) {
                $query->where('status', $status);
            }

            // Load relationships
            $meetups = $query->with([
                'buyer:id,name,email,phone',
                'seller:id,name,email,phone',
                'item:id,title,description,price,category_name,location,images',
                'offer:id,amount,message,status'
            ])
                ->orderBy('preferred_meetup_time', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('✅ Meetups fetched', [
                'count' => $meetups->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $meetups,
                'meta' => [
                    'total' => $meetups->count(),
                    'role' => $role,
                    'status' => $status,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch meetups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch meetups',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get single meetup details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $meetup = Meetup::with([
                'buyer:id,name,email,phone',
                'seller:id,name,email,phone',
                'item:id,title,description,price,category_name,condition,location,images,tags',
                'offer:id,amount,message,status,offer_type'
            ])->find($id);

            if (!$meetup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meetup not found'
                ], 404);
            }

            // Check authorization (must be either buyer or seller)
            if ($meetup->buyer_id !== $user->id && $meetup->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            Log::info('✅ Meetup details fetched', [
                'meetup_id' => $meetup->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $meetup
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch meetup details', [
                'meetup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch meetup details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Confirm meetup (seller confirms the meetup details)
     */
    public function confirmMeetup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:single,basket',
            'isOfferCheckout' => 'required|boolean',
            'items' => 'required|array',
            'items.*.itemId' => 'required|integer|exists:items,id',
            'items.*.sellerId' => 'required|integer|exists:users,id',
            'items.*.agreedPrice' => 'required|numeric|min:0',
            'meetupLocation' => 'required|string|max:500',
            'meetupLocationType' => 'required|string|in:public,campus,doorstep',
            'preferredMeetupTime' => 'required|date',
            'alternativeMeetupTime' => 'nullable|date',
            'agreedPaymentMethod' => 'required|string|in:cash,upi,card',
            'agreedAmount' => 'required|numeric|min:0',
            'buyerNotes' => 'nullable|string|max:500',
            'acknowledgedSafety' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            Log::info('📦 Meetup confirmation request', [
                'authenticated_user_id' => $user->id,
                'authenticated_user_name' => $user->name,
                'type' => $request->type,
                'items_count' => count($request->items),
                'meetup_location' => $request->meetupLocation,
            ]);

            DB::beginTransaction();

            // Create meetup records for each item
            $meetups = [];
            $itemsWithDetails = [];

            foreach ($request->items as $itemData) {
                $item = Item::with(['images', 'category', 'user'])->find($itemData['itemId']);

                if (!$item || $item->status !== 'active') {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Item is no longer available'
                    ], 400);
                }

                // Determine buyer and seller
                $sellerId = $item->user_id; // Item owner is always the seller
                $buyerId = $user->id; // Authenticated user is the buyer

                // Verify the authenticated user is not trying to buy their own item
                if ($user->id === $sellerId) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot create a meetup for your own item'
                    ], 400);
                }

                // Create meetup coordination record
                $meetup = Meetup::create([
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                    'item_id' => $itemData['itemId'],
                    'offer_id' => $itemData['offerId'] ?? null,
                    'agreed_price' => $itemData['agreedPrice'],
                    'original_price' => $itemData['originalPrice'] ?? $item->price,
                    'meetup_location' => $request->meetupLocation,
                    'meetup_location_type' => $request->meetupLocationType,
                    'meetup_location_details' => $request->meetupLocationDetails ?? null,
                    'preferred_meetup_time' => $request->preferredMeetupTime,
                    'alternative_meetup_time' => $request->alternativeMeetupTime,
                    'payment_method' => $request->agreedPaymentMethod,
                    'buyer_notes' => $request->buyerNotes,
                    'acknowledged_safety' => $request->acknowledgedSafety,
                    'status' => 'pending',
                    'created_at' => now(),
                ]);

                // Update item status to 'reserved'
                $item->update(['status' => 'reserved']);

                // If this was from an accepted offer, update offer status
                if ($request->isOfferCheckout && isset($itemData['offerId'])) {
                    $offer = Offer::find($itemData['offerId']);
                    if ($offer) {
                        $offer->update(['status' => 'meetup_scheduled']);
                    }
                }

                // Prepare item details for response
                $itemDetails = [
                    'itemId' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'condition' => $item->condition,
                    'category' => $item->category_name,
                    'itemLocation' => $item->location,
                    'contact_method' => $item->contact_method,
                    'images' => $item->images ? $item->images->pluck('image_url') : [],
                    'tags' => $item->tags ?? [],
                    'agreedPrice' => (float)$itemData['agreedPrice'],
                    'originalPrice' => (float)($itemData['originalPrice'] ?? $item->price),
                    'isOffer' => $itemData['isOffer'] ?? false,
                    'offerId' => $itemData['offerId'] ?? null,
                    'offerType' => $itemData['offerType'] ?? null,
                    'offerAmount' => isset($itemData['offerAmount']) ? (float)$itemData['offerAmount'] : null,
                ];

                $itemsWithDetails[] = $itemDetails;
                $meetups[] = $meetup;

                Log::info('✅ Meetup created', [
                    'meetup_id' => $meetup->id,
                    'item_id' => $item->id,
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                ]);
            }

            DB::commit();

            // Get buyer and seller details
            $buyer = User::select('id', 'name', 'phone', 'email', 'profile_image')
                ->find($meetups[0]->buyer_id);
            $seller = User::select('id', 'name', 'phone', 'email', 'profile_image')
                ->find($meetups[0]->seller_id);

            // Calculate savings if offer checkout
            $savings = 0.0;
            $discountPercentage = 0.0;
            if ($request->isOfferCheckout && isset($itemsWithDetails[0])) {
                $originalPrice = $itemsWithDetails[0]['originalPrice'];
                $agreedPrice = $itemsWithDetails[0]['agreedPrice'];
                $savings = $originalPrice - $agreedPrice;
                $discountPercentage = $originalPrice > 0 ? (($savings / $originalPrice) * 100) : 0.0;
            }

            // Prepare comprehensive response
            $responseData = [
                'meetups' => $meetups,
                'items' => $itemsWithDetails,
                'buyer' => [
                    'id' => $buyer->id,
                    'name' => $buyer->name,
                    'phone' => $buyer->phone,
                    'email' => $buyer->email,
                    'profile_image' => $buyer->full_profile_image_url,
                ],
                'seller' => [
                    'id' => $seller->id,
                    'name' => $seller->name,
                    'phone' => $seller->phone,
                    'email' => $seller->email,
                    'profile_image' => $seller->full_profile_image_url,
                ],
                'meetup_details' => [
                    'location' => $request->meetupLocation,
                    'location_type' => $request->meetupLocationType,
                    'location_details' => $request->meetupLocationDetails,
                    'preferred_time' => $request->preferredMeetupTime,
                    'alternative_time' => $request->alternativeMeetupTime,
                    'payment_method' => $request->agreedPaymentMethod,
                    'agreed_amount' => (float)$request->agreedAmount,
                    'buyer_notes' => $request->buyerNotes,
                ],
                'offer_details' => $request->isOfferCheckout ? [
                    'is_offer_checkout' => true,
                    'savings' => $savings,
                    'discount_percentage' => round($discountPercentage, 2),
                ] : null,
                'is_current_user_seller' => false, // Always false since authenticated user is buyer
                'message' => 'Meetup confirmed successfully! The seller will be notified.',
            ];

            // TODO: Send notifications to the seller about meetup coordination
            // NotificationService::sendMeetupNotification($meetups, $buyer, $seller);

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Meetup confirmed successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('❌ Failed to confirm meetup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm meetup',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }


    /**
     * Mark meetup as completed (after successful exchange)
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $meetup = Meetup::find($id);

            if (!$meetup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meetup not found'
                ], 404);
            }

            // Check authorization (must be either buyer or seller)
            if ($meetup->buyer_id !== $user->id && $meetup->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Can only complete pending or confirmed meetups
            if (!in_array($meetup->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meetup cannot be completed in current status'
                ], 400);
            }

            DB::beginTransaction();

            // Update meetup status
            $meetup->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Update item status to 'sold'
            $item = Item::find($meetup->item_id);
            if ($item) {
                $item->update(['status' => 'sold']);
            }

            // Update offer status if exists
            if ($meetup->offer_id) {
                $offer = Offer::find($meetup->offer_id);
                if ($offer) {
                    $offer->update(['status' => 'completed']);
                }
            }

            DB::commit();

            Log::info('✅ Meetup completed', [
                'meetup_id' => $meetup->id,
                'completed_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $meetup->fresh(),
                'message' => 'Meetup marked as completed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('❌ Failed to complete meetup', [
                'meetup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete meetup',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Cancel meetup
     */
    public function cancel(Request $request, int $id): JsonResponse
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
            $meetup = Meetup::find($id);

            if (!$meetup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meetup not found'
                ], 404);
            }

            // Check authorization (must be either buyer or seller)
            if ($meetup->buyer_id !== $user->id && $meetup->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Can only cancel pending or confirmed meetups
            if (!in_array($meetup->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meetup cannot be cancelled in current status'
                ], 400);
            }

            DB::beginTransaction();

            // Update meetup status
            $meetup->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
            ]);

            // Revert item status back to 'active'
            $item = Item::find($meetup->item_id);
            if ($item && $item->status === 'reserved') {
                $item->update(['status' => 'active']);
            }

            // Update offer status if exists
            if ($meetup->offer_id) {
                $offer = Offer::find($meetup->offer_id);
                if ($offer && $offer->status === 'meetup_scheduled') {
                    $offer->update(['status' => 'accepted']); // Revert to accepted
                }
            }

            DB::commit();

            Log::info('✅ Meetup cancelled', [
                'meetup_id' => $meetup->id,
                'cancelled_by' => $user->id,
                'reason' => $request->reason,
            ]);

            // TODO: Send notification to the other party
            // NotificationService::sendMeetupCancellationNotification($meetup);

            return response()->json([
                'success' => true,
                'data' => $meetup->fresh(),
                'message' => 'Meetup cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('❌ Failed to cancel meetup', [
                'meetup_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel meetup',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
