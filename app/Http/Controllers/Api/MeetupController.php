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
use App\Models\CoinTransaction;
use App\Models\Transaction;

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

            // ✅ FIXED: Load images as relationship, not column
            $meetups = $query->with([
                'buyer:id,name,email,phone,profile_image',
                'seller:id,name,email,phone,profile_image',
                'item:id,title,description,price,category_name,location,tags',
                'item.images:id,item_id,image_path,is_primary,order',
                'item.primaryImage:id,item_id,image_path,is_primary',
                'offer:id,amount,message,status'
            ])
                ->orderBy('preferred_meetup_time', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            // ✅ Format images for each meetup
            $meetupsData = $meetups->map(function ($meetup) {
                $data = $meetup->toArray();

                if (isset($data['item'])) {
                    $data['item']['images'] = collect($data['item']['images'] ?? [])
                        ->map(function ($image) {
                            return [
                                'id' => $image['id'],
                                'url' => asset('storage/' . $image['image_path']),
                                'is_primary' => $image['is_primary'] ?? false,
                            ];
                        })
                        ->toArray();

                    // Add primary_image
                    if (isset($data['item']['primary_image']) && $data['item']['primary_image']) {
                        $data['item']['primary_image'] = asset('storage/' . $data['item']['primary_image']['image_path']);
                    } elseif (!empty($data['item']['images'])) {
                        $data['item']['primary_image'] = $data['item']['images'][0]['url'];
                    } else {
                        $data['item']['primary_image'] = null;
                    }
                }

                return $data;
            });

            Log::info('✅ Meetups fetched', [
                'count' => $meetups->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $meetupsData,
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

            // ✅ Load item images as relationship, not as column
            $meetup = Meetup::with([
                'buyer:id,name,email,phone,profile_image',
                'seller:id,name,email,phone,profile_image',
                'item:id,title,description,price,category_name,condition,location,tags',
                'item.images:id,item_id,image_path,is_primary,order',
                'item.primaryImage:id,item_id,image_path,is_primary',
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

            // ✅ Format response to match Flutter expectations
            $meetupData = $meetup->toArray();

            // ✅ Format item images to array of URLs
            if (isset($meetupData['item'])) {
                // Convert images relationship to simple array
                $meetupData['item']['images'] = collect($meetupData['item']['images'] ?? [])
                    ->map(function ($image) {
                        return [
                            'id' => $image['id'],
                            'url' => asset('storage/' . $image['image_path']),
                            'is_primary' => $image['is_primary'] ?? false,
                        ];
                    })
                    ->toArray();

                // Add primary_image field for convenience
                if (isset($meetupData['item']['primary_image']) && $meetupData['item']['primary_image']) {
                    $meetupData['item']['primary_image'] = asset('storage/' . $meetupData['item']['primary_image']['image_path']);
                } elseif (!empty($meetupData['item']['images'])) {
                    // Fallback to first image if no primary
                    $meetupData['item']['primary_image'] = $meetupData['item']['images'][0]['url'];
                } else {
                    $meetupData['item']['primary_image'] = null;
                }
            }

            Log::info('✅ Meetup details fetched', [
                'meetup_id' => $meetup->id,
                'user_id' => $user->id,
                'has_images' => count($meetupData['item']['images'] ?? []),
            ]);

            return response()->json([
                'success' => true,
                'data' => $meetupData
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch meetup details', [
                'meetup_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
                'is_offer_checkout' => $request->isOfferCheckout,
                'items_count' => count($request->items),
                'meetup_location' => $request->meetupLocation,
            ]);

            DB::beginTransaction();

            $meetups = [];
            $itemsWithDetails = [];

            foreach ($request->items as $itemData) {
                // Load item with images relationship (ordered by 'order' field, primary first)
                $item = Item::with([
                    'images' => function ($query) {
                        $query->orderBy('is_primary', 'desc')
                            ->orderBy('order', 'asc');
                    },
                    'category',
                    'user'
                ])->find($itemData['itemId']);

                if (!$item || $item->status !== 'active') {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Item is no longer available'
                    ], 400);
                }

                $sellerId = $item->user_id;
                $buyerId = null;
                $isCurrentUserSeller = $user->id === $sellerId;

                if ($request->isOfferCheckout && isset($itemData['offerId'])) {
                    $offer = Offer::find($itemData['offerId']);

                    if (!$offer) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Offer not found'
                        ], 404);
                    }

                    $buyerId = $offer->sender_id == $sellerId
                        ? $offer->receiver_id
                        : $offer->sender_id;

                    Log::info('📋 Offer details', [
                        'offer_id' => $offer->id,
                        'sender_id' => $offer->sender_id,
                        'receiver_id' => $offer->receiver_id,
                        'item_owner (seller_id)' => $sellerId,
                        'determined_buyer_id' => $buyerId,
                    ]);

                    if ($user->id !== $buyerId && $user->id !== $sellerId) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'You are not authorized to confirm this meetup'
                        ], 403);
                    }
                } else {
                    if ($isCurrentUserSeller) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'You cannot create a meetup for your own item without an accepted offer'
                        ], 400);
                    }
                    $buyerId = $user->id;
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
                    'status' => 'pending_meetup',
                    'created_at' => now(),
                ]);

                $item->update(['status' => 'reserved']);

                if ($request->isOfferCheckout && isset($itemData['offerId'])) {
                    $offer = Offer::find($itemData['offerId']);
                    if ($offer) {
                        $offer->update(['status' => 'meetup_scheduled']);
                    }
                }

                // ✅ Prepare images array with proper URLs using the ItemImage model
                $images = [];
                if ($item->images && $item->images->count() > 0) {
                    foreach ($item->images as $image) {
                        $images[] = [
                            'id' => $image->id,
                            'url' => $image->url, // Uses the getUrlAttribute accessor
                            'is_primary' => $image->is_primary,
                            'order' => $image->order,
                            'filename' => $image->filename,
                        ];
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
                    'images' => $images, // Full image objects with metadata
                    'primary_image' => !empty($images) ? $images[0]['url'] : null, // First image (primary)
                    'tags' => $item->tags ?? [],
                    'agreedPrice' => (float)$itemData['agreedPrice'],
                    'originalPrice' => (float)($itemData['originalPrice'] ?? $item->price),
                    'isOffer' => $itemData['isOffer'] ?? false,
                    'offerId' => $itemData['offerId'] ?? null,
                    'offerType' => $itemData['offerType'] ?? null,
                    'offerAmount' => isset($itemData['offerAmount']) ? (float)$itemData['offerAmount'] : null,
                    'offerMessage' => $itemData['offerMessage'] ?? null,
                ];

                $itemsWithDetails[] = $itemDetails;
                $meetups[] = $meetup;

                Log::info('✅ Meetup created', [
                    'meetup_id' => $meetup->id,
                    'item_id' => $item->id,
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                    'confirmed_by_user_id' => $user->id,
                    'confirmed_by_role' => $isCurrentUserSeller ? 'seller' : 'buyer',
                    'images_count' => count($images),
                ]);
            }

            DB::commit();

            $buyer = User::select('id', 'name', 'phone', 'email', 'profile_image')
                ->find($meetups[0]->buyer_id);
            $seller = User::select('id', 'name', 'phone', 'email', 'profile_image')
                ->find($meetups[0]->seller_id);

            $savings = 0.0;
            $discountPercentage = 0.0;
            if ($request->isOfferCheckout && isset($itemsWithDetails[0])) {
                $originalPrice = $itemsWithDetails[0]['originalPrice'];
                $agreedPrice = $itemsWithDetails[0]['agreedPrice'];
                $savings = $originalPrice - $agreedPrice;
                $discountPercentage = $originalPrice > 0 ? (($savings / $originalPrice) * 100) : 0.0;
            }

            $isCurrentUserSeller = $user->id === $seller->id;
            $message = $isCurrentUserSeller
                ? 'Meetup confirmed successfully! The buyer will be notified.'
                : 'Meetup confirmed successfully! The seller will be notified.';

            // ✅ FIXED: Use profile_image directly, not full_profile_image_url accessor
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
                'is_current_user_seller' => $isCurrentUserSeller,
                'message' => $message,
            ];

            // TODO: Send notifications to the other party about meetup coordination
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
     * Create or update meetup from an accepted offer.
     * POST /offers/{offerId}/meetup
     */
    public function createOrUpdate(Request $request, string $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meetup_location'         => 'required|string|max:255',
            'meetup_location_type'    => 'required|string|in:public,campus,doorstep',
            'meetup_location_details' => 'nullable|array',
            'preferred_meetup_time'   => 'required|date|after:now',
            'alternative_meetup_time' => 'nullable|date|after:now',
            'payment_method'          => 'required|string|max:100',
            'buyer_notes'             => 'nullable|string|max:500',
            'acknowledged_safety'     => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user  = Auth::user();
            $offer = Offer::with('item')->where('id', $offerId)
                ->where('status', 'accepted')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or not accepted',
                ], 404);
            }

            if (!in_array($user->id, [$offer->sender_id, $offer->receiver_id], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authorized to schedule meetup for this offer',
                ], 403);
            }

            $sellerId = $offer->item->user_id;
            $buyerId  = $offer->sender_id === $sellerId ? $offer->receiver_id : $offer->sender_id;

            // ✅ NEW: Determine who is creating the meetup
            $isSeller = $user->id === $sellerId;
            $isBuyer = $user->id === $buyerId;

            DB::beginTransaction();

            $meetupData = [
                'buyer_id'                => $buyerId,
                'seller_id'               => $sellerId,
                'item_id'                 => $offer->item_id,
                'agreed_price'            => $offer->amount,
                'original_price'          => $offer->item->price,
                'meetup_location'         => $request->meetup_location,
                'meetup_location_type'    => $request->meetup_location_type,
                'meetup_location_details' => $request->meetup_location_details,
                'preferred_meetup_time'   => $request->preferred_meetup_time,
                'alternative_meetup_time' => $request->alternative_meetup_time,
                'payment_method'          => $request->payment_method,
                'buyer_notes'             => $request->buyer_notes,
                'acknowledged_safety'     => $request->acknowledged_safety,
                'status'                  => 'pending_meetup', // ✅ FIXED: Use pending_meetup
            ];

            // ✅ NEW: Auto-confirm the party creating the meetup
            if ($isSeller) {
                $meetupData['seller_confirmed'] = true;
                $meetupData['seller_confirmed_at'] = now();
            } elseif ($isBuyer) {
                $meetupData['buyer_confirmed'] = true;
                $meetupData['buyer_confirmed_at'] = now();
            }

            $meetup = Meetup::updateOrCreate(
                ['offer_id' => $offer->id],
                $meetupData
            );

            // Mark item as reserved (not sold yet)
            $offer->item->update(['status' => 'reserved']);

            DB::commit();

            Log::info('✅ Meetup scheduled', [
                'meetup_id' => $meetup->id,
                'offer_id'  => $offer->id,
                'scheduled_by' => $user->id,
                'auto_confirmed' => $isSeller ? 'seller' : ($isBuyer ? 'buyer' : 'none'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meetup scheduled successfully',
                'data'    => $meetup->fresh(['buyer', 'seller', 'item', 'offer']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Failed to schedule meetup', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule meetup',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Update existing meetup (reschedule time/location).
     * PUT /meetups/{meetup}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meetup_location'         => 'nullable|string|max:255',
            'meetup_location_type'    => 'nullable|string|in:public,campus,doorstep',
            'preferred_meetup_time'   => 'nullable|date|after:now',
            'alternative_meetup_time' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user   = Auth::user();
            $meetup = Meetup::find($id);

            if (!$meetup) {
                return response()->json(['success' => false, 'message' => 'Meetup not found'], 404);
            }

            if (!in_array($user->id, [$meetup->buyer_id, $meetup->seller_id], true)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // ✅ FIXED: Include 'pending_meetup' status
            if (!in_array($meetup->status, ['meetup_scheduled', 'pending_meetup'])) {
                return response()->json(['success' => false, 'message' => 'Cannot update meetup in current status'], 400);
            }

            // ✅ NEW: Determine who is rescheduling
            $isSeller = $user->id === $meetup->seller_id;
            $isBuyer = $user->id === $meetup->buyer_id;

            // Update meetup fields
            $updateData = $request->only([
                'meetup_location',
                'meetup_location_type',
                'preferred_meetup_time',
                'alternative_meetup_time',
            ]);

            // ✅ NEW: Auto-confirm the party that rescheduled
            if ($isSeller) {
                $updateData['seller_confirmed'] = true;
                $updateData['seller_confirmed_at'] = now();
                Log::info('🔄 Seller rescheduled and auto-confirmed', [
                    'meetup_id' => $meetup->id,
                    'seller_id' => $user->id,
                ]);
            } elseif ($isBuyer) {
                $updateData['buyer_confirmed'] = true;
                $updateData['buyer_confirmed_at'] = now();
                Log::info('🔄 Buyer rescheduled and auto-confirmed', [
                    'meetup_id' => $meetup->id,
                    'buyer_id' => $user->id,
                ]);
            }

            $meetup->update($updateData);

            Log::info('✅ Meetup updated', [
                'meetup_id' => $meetup->id,
                'updated_by' => $user->id,
                'seller_confirmed' => $meetup->seller_confirmed,
                'buyer_confirmed' => $meetup->buyer_confirmed,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meetup updated successfully',
                'data'    => $meetup->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to update meetup', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update meetup',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Double confirmation: buyer/seller confirms transaction happened.
     * PUT /offers/{offerId}/meetup/confirm OR /meetups/{meetup}/confirm
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:buyer,seller',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user   = Auth::user();
            $meetup = Meetup::with(['item', 'offer'])->find($id);

            if (!$meetup) {
                return response()->json(['success' => false, 'message' => 'Meetup not found'], 404);
            }

            // Validate user matches the role
            if ($request->role === 'buyer' && $user->id !== $meetup->buyer_id) {
                return response()->json(['success' => false, 'message' => 'Only buyer can confirm as buyer'], 403);
            }

            if ($request->role === 'seller' && $user->id !== $meetup->seller_id) {
                return response()->json(['success' => false, 'message' => 'Only seller can confirm as seller'], 403);
            }

            if (!in_array($meetup->status, ['meetup_scheduled', 'pending_meetup'])) {
                return response()->json(['success' => false, 'message' => 'Meetup cannot be confirmed in current status'], 400);
            }

            DB::beginTransaction();

            $field       = $request->role === 'buyer' ? 'buyer_confirmed' : 'seller_confirmed';
            $timestampField = $request->role === 'buyer' ? 'buyer_confirmed_at' : 'seller_confirmed_at';

            $meetup->update([
                $field          => true,
                $timestampField => now(),
            ]);

            // Check if BOTH parties confirmed
            $bothConfirmed = $meetup->fresh()->buyer_confirmed && $meetup->fresh()->seller_confirmed;

            if ($bothConfirmed) {
                $meetup->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                // Mark item as sold
                $meetup->item->update([
                    'status'  => 'sold',
                    'is_sold' => true,
                    'sold_at' => now(),
                ]);

                $saleAmount = (float) $meetup->agreed_price;

                // ✅ CREATE TRANSACTION RECORD (Source of Truth)
                $transaction = Transaction::create([
                    'buyer_id' => $meetup->buyer_id,
                    'seller_id' => $meetup->seller_id,
                    'item_id' => $meetup->item_id,
                    'amount' => $saleAmount,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                Log::info('💰 Transaction record created', [
                    'transaction_id' => $transaction->id,
                    'amount' => $saleAmount,
                ]);

                // ✅ UPDATE USER CACHED STATS (For Performance)
                try {
                    // Update seller stats
                    $seller = User::find($meetup->seller_id);
                    if ($seller) {
                        DB::table('users')
                            ->where('id', $seller->id)
                            ->update([
                                'items_sold' => DB::raw('items_sold + 1'),
                                'total_earnings' => DB::raw("total_earnings + {$saleAmount}"),
                                'active_listings' => DB::raw('GREATEST(active_listings - 1, 0)'),
                                'coins' => DB::raw('coins + 5'),
                                'stats_last_updated' => now(),
                            ]);

                        $seller->refresh();

                        // Create coin transaction
                        CoinTransaction::create([
                            'user_id' => $seller->id,
                            'amount' => 5,
                            'type' => 'sale_completed',
                            'description' => 'Reward for completing sale of "' . $meetup->item->title . '"',
                            'item_id' => $meetup->item_id,
                            'balance_after' => $seller->coins,
                        ]);
                    }

                    // Update buyer stats
                    $buyer = User::find($meetup->buyer_id);
                    if ($buyer) {
                        DB::table('users')
                            ->where('id', $buyer->id)
                            ->update([
                                'items_bought' => DB::raw('items_bought + 1'),
                                'total_spent' => DB::raw("total_spent + {$saleAmount}"),
                                'coins' => DB::raw('coins + 2'),
                                'stats_last_updated' => now(),
                            ]);

                        $buyer->refresh();

                        // Create coin transaction
                        CoinTransaction::create([
                            'user_id' => $buyer->id,
                            'amount' => 2,
                            'type' => 'purchase_completed',
                            'description' => 'Reward for completing purchase of "' . $meetup->item->title . '"',
                            'item_id' => $meetup->item_id,
                            'balance_after' => $buyer->coins,
                        ]);
                    }

                    Log::info('🎉 Transaction completed and all stats updated', [
                        'transaction_id' => $transaction->id,
                        'meetup_id' => $meetup->id,
                        'buyer_id' => $meetup->buyer_id,
                        'seller_id' => $meetup->seller_id,
                        'sale_amount' => $saleAmount,
                    ]);
                } catch (\Exception $e) {
                    Log::error('⚠️ Failed to update user stats', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::info('⏳ Waiting for other party confirmation', [
                    'meetup_id'        => $meetup->id,
                    'confirmed_by'     => $request->role,
                    'buyer_confirmed'  => $meetup->buyer_confirmed,
                    'seller_confirmed' => $meetup->seller_confirmed,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $bothConfirmed
                    ? 'Transaction completed! You earned coins! 🎉'
                    : 'Confirmation recorded. Waiting for other party.',
                'data'    => [
                    'meetup'         => $meetup->fresh(),
                    'both_confirmed' => $bothConfirmed,
                    'coins_awarded'  => $bothConfirmed ? [
                        'seller' => 5,
                        'buyer' => 2,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Failed to confirm meetup', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm meetup',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Mark deal as failed (they met but didn't transact).
     * PUT /offers/{offerId}/meetup/fail OR /meetups/{meetup}/fail
     */
    public function markFailed(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
            'relist' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user   = Auth::user();
            $meetup = Meetup::with(['item', 'offer'])->find($id);

            if (!$meetup) {
                return response()->json(['success' => false, 'message' => 'Meetup not found'], 404);
            }

            if (!in_array($user->id, [$meetup->buyer_id, $meetup->seller_id], true)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if (!in_array($meetup->status, ['meetup_scheduled', 'pending_meetup'])) {
                return response()->json(['success' => false, 'message' => 'Cannot mark as failed in current status'], 400);
            }

            DB::beginTransaction();

            $meetup->update([
                'status'             => 'failed',
                'cancelled_at'       => now(),
                'cancellation_reason' => $request->reason ?? 'Deal failed after meetup',
            ]);

            // Move offer to cancelled (shows in Inactive tab)
            if ($meetup->offer) {
                $meetup->offer->update([
                    'status'           => 'cancelled',
                    'cancelled_at'     => now(),
                    'rejection_reason' => $request->reason ?? 'Deal failed',
                ]);
            }

            // Relist item if seller requests
            if ($request->boolean('relist') && $meetup->item->user_id === $user->id) {
                $meetup->item->update([
                    'status'      => 'active',
                    'is_sold'     => false,
                    'sold_at'     => null,
                    'is_archived' => false,
                    'archived_at' => null,
                ]);

                // ✅ No need to update active_listings - item is back to active

                Log::info('🔄 Item relisted', ['item_id' => $meetup->item->id]);
            } else {
                // Just unreserve it if not relisting (but keep it reserved/inactive)
                $meetup->item->update(['status' => 'active']);

                // ✅ No update needed - item goes back to active
            }

            DB::commit();

            Log::info('❌ Deal marked as failed', [
                'meetup_id'  => $meetup->id,
                'failed_by'  => $user->id,
                'reason'     => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal marked as failed',
                'data'    => $meetup->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Failed to mark deal as failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark deal as failed',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
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
            if (!in_array($meetup->status, ['meetup_scheduled', 'pending_meetup'])) {
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
            $meetup = Meetup::with(['item'])->find($id);

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
            if (!in_array($meetup->status, ['meetup_scheduled', 'pending_meetup'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel meetup in current status'
                ], 400);
            }

            DB::beginTransaction();

            $meetup->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
            ]);

            // Unreserve item (make it active again)
            $meetup->item->update([
                'status' => 'active',
            ]);

            // ✅ No need to update active_listings here because item goes back to active

            // Update offer status if exists
            if ($meetup->offer_id) {
                $offer = Offer::find($meetup->offer_id);
                if ($offer) {
                    $offer->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'rejection_reason' => $request->reason,
                    ]);
                }
            }

            DB::commit();

            Log::info('❌ Meetup cancelled', [
                'meetup_id' => $meetup->id,
                'cancelled_by' => $user->id,
                'reason' => $request->reason,
            ]);

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
