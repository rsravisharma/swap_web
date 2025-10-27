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
                'user_id' => $user->id,
                'type' => $request->type,
                'items_count' => count($request->items),
                'meetup_location' => $request->meetupLocation,
            ]);

            DB::beginTransaction();

            // Create meetup records for each item
            $meetups = [];
            
            foreach ($request->items as $itemData) {
                $item = Item::find($itemData['itemId']);
                
                if (!$item || $item->status !== 'active') {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Item is no longer available'
                    ], 400);
                }

                // Create meetup coordination record
                $meetup = Meetup::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $itemData['sellerId'],
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
                    'status' => 'pending', // pending, confirmed, completed, cancelled
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

                $meetups[] = $meetup;

                Log::info('✅ Meetup created', [
                    'meetup_id' => $meetup->id,
                    'item_id' => $item->id,
                    'buyer_id' => $user->id,
                    'seller_id' => $itemData['sellerId'],
                ]);
            }

            DB::commit();

            // TODO: Send notifications to sellers about meetup coordination
            // NotificationService::sendMeetupNotification($meetups);

            return response()->json([
                'success' => true,
                'data' => [
                    'meetups' => $meetups,
                    'message' => 'Meetup confirmed successfully! The seller will be notified.',
                ],
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
