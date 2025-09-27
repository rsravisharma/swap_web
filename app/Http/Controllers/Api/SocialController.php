<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserRating;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SocialController extends Controller
{
    /**
     * Get user's followers
     * GET /users/{userId}/followers
     */
    public function getFollowers(string $userId): JsonResponse
    {
        try {
            $followers = UserFollow::where('followed_id', $userId)
                ->with(['follower:id,name,email,profile_image'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($follow) {
                    $follower = $follow->follower;
                    return [
                        'id' => $follower->id,
                        'name' => $follower->name,
                        'email' => $follower->email,
                        'profile_image' => $follower->profile_image,
                        'followed_at' => $follow->created_at->toDateTimeString(),
                        'is_verified' => $follower->is_verified ?? false,
                        'university' => $follower->university ?? null,
                        'total_listings' => $follower->items()->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $followers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get followers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve followers'
            ], 500);
        }
    }

    /**
     * Get users that the user is following
     * GET /users/{userId}/following
     */
    public function getFollowing(string $userId): JsonResponse
    {
        try {
            $following = UserFollow::where('follower_id', $userId)
                ->with(['followed:id,name,email,profile_image'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($follow) {
                    $followed = $follow->followed;
                    return [
                        'id' => $followed->id,
                        'name' => $followed->name,
                        'email' => $followed->email,
                        'profile_image' => $followed->profile_image,
                        'followed_at' => $follow->created_at->toDateTimeString(),
                        'is_verified' => $followed->is_verified ?? false,
                        'university' => $followed->university ?? null,
                        'total_listings' => $followed->items()->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $following
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get following: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve following users'
            ], 500);
        }
    }

    /**
     * Toggle follow user
     * POST /users/{userId}/toggle-follow
     */
    public function toggleFollow(string $userId): JsonResponse
    {
        try {
            $authUser = Auth::user();

            if ($authUser->id == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot follow yourself'
                ], 400);
            }

            $targetUser = User::find($userId);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            DB::beginTransaction(); // ✅ ADD: Transaction for consistency

            $existingFollow = UserFollow::where('follower_id', $authUser->id)
                ->where('followed_id', $userId)
                ->first();

            if ($existingFollow) {
                $existingFollow->delete();
                // ✅ ADD: Update cached counters
                $authUser->decrement('following_count');
                $targetUser->decrement('followers_count');
                $isFollowing = false;
                $message = 'User unfollowed successfully';
            } else {
                UserFollow::create([
                    'follower_id' => $authUser->id,
                    'followed_id' => $userId
                ]);
                // ✅ ADD: Update cached counters
                $authUser->increment('following_count');
                $targetUser->increment('followers_count');
                $isFollowing = true;
                $message = 'User followed successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_following' => $isFollowing
                ],
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to toggle follow: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update follow status'
            ], 500);
        }
    }

    /**
     * Remove follower
     * DELETE /users/followers/{userId}
     */
    public function removeFollower(string $userId): JsonResponse
    {
        try {
            $authUser = Auth::user();

            $follow = UserFollow::where('follower_id', $userId)
                ->where('followed_id', $authUser->id)
                ->first();

            if (!$follow) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not following you'
                ], 400);
            }

            $follow->delete();

            return response()->json([
                'success' => true,
                'message' => 'Follower removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove follower: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove follower'
            ], 500);
        }
    }

    /**
     * Get user ratings
     * GET /users/{userId}/ratings
     */
    public function getUserRatings(string $userId, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|string|in:buyer,seller',
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->input('type');
            $perPage = $request->input('per_page', 20);

            $query = UserRating::where('rated_id', $userId)
                ->public() // Only public ratings
                ->with(['rater:id,name,profile_image', 'transaction']);

            if ($type) {
                $query->byType($type);
            }

            $ratings = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $ratingsData = $ratings->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'review' => $rating->review, // ✅ CHANGED: review instead of comment
                    'type' => $rating->type,
                    'created_at' => $rating->created_at->toDateTimeString(),
                    'transaction_id' => $rating->transaction_id,
                    'rater' => [
                        'id' => $rating->rater->id,
                        'name' => $rating->rater->name,
                        'profile_image' => $rating->rater->profile_image
                    ]
                ];
            });

            // ✅ CHANGED: Use UserRating helper methods
            $averageRating = UserRating::getAverageRating($userId, $type);
            $totalRatings = UserRating::getTotalRatings($userId, $type);

            return response()->json([
                'success' => true,
                'data' => [
                    'ratings' => $ratingsData,
                    'average_rating' => round($averageRating, 2),
                    'total_ratings' => $totalRatings,
                    'pagination' => [
                        'current_page' => $ratings->currentPage(),
                        'total_pages' => $ratings->lastPage(),
                        'total_items' => $ratings->total(),
                        'per_page' => $ratings->perPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user ratings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ratings'
            ], 500);
        }
    }

    /**
     * Submit rating
     * POST /ratings
     */
    public function submitRating(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rated_id' => 'required|integer|exists:users,id', // ✅ CHANGED: rated_id
            'transaction_id' => 'required|integer|exists:transactions,id',
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:1000', // ✅ CHANGED: review
            'type' => 'required|string|in:buyer,seller',
            'is_public' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $authUser = Auth::user();
            $ratedId = $request->rated_id;
            $transactionId = $request->transaction_id;

            // Prevent self-rating
            if ($authUser->id == $ratedId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot rate yourself'
                ], 400);
            }

            // Check if transaction exists and user is part of it
            $transaction = Transaction::where('id', $transactionId)
                ->where(function ($query) use ($authUser) {
                    $query->where('buyer_id', $authUser->id)
                        ->orWhere('seller_id', $authUser->id);
                })
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or unauthorized'
                ], 404);
            }

            // Check if already rated
            $existingRating = UserRating::where('rater_id', $authUser->id)
                ->where('rated_id', $ratedId)
                ->where('transaction_id', $transactionId)
                ->first();

            if ($existingRating) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already rated this transaction'
                ], 400);
            }

            DB::beginTransaction();

            $rating = UserRating::create([
                'rater_id' => $authUser->id,
                'rated_id' => $ratedId,
                'transaction_id' => $transactionId,
                'rating' => $request->rating,
                'review' => $request->review,
                'type' => $request->type,
                'is_public' => $request->input('is_public', true)
            ]);

            // ✅ ADD: Update user's cached rating stats
            $ratedUser = User::find($ratedId);
            if ($ratedUser) {
                $newAverage = UserRating::getAverageRating($ratedId);
                $totalReviews = UserRating::getTotalRatings($ratedId);

                $ratedUser->update([
                    'seller_rating' => $newAverage,
                    'total_reviews' => $totalReviews
                ]);
            }

            DB::commit();

            $ratingData = [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
                'type' => $rating->type,
                'created_at' => $rating->created_at->toDateTimeString(),
                'transaction_id' => $rating->transaction_id,
                'rater' => [
                    'id' => $authUser->id,
                    'name' => $authUser->name,
                    'profile_image' => $authUser->profile_image
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $ratingData,
                'message' => 'Rating submitted successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to submit rating: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating'
            ], 500);
        }
    }

    /**
     * Mark rating as helpful
     * POST /ratings/{ratingId}/helpful
     */
    public function markRatingHelpful(string $ratingId): JsonResponse
    {
        try {
            $authUser = Auth::user();
            $rating = Rating::find($ratingId);

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found'
                ], 404);
            }

            // Check if already marked as helpful
            $existingMark = $rating->userHelpfulMarks()
                ->where('user_id', $authUser->id)
                ->first();

            if ($existingMark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already marked as helpful'
                ], 400);
            }

            $rating->userHelpfulMarks()->create([
                'user_id' => $authUser->id
            ]);

            $rating->increment('helpful_count');

            return response()->json([
                'success' => true,
                'message' => 'Rating marked as helpful'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark rating helpful: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark rating as helpful'
            ], 500);
        }
    }

    /**
     * Report rating
     * POST /ratings/{ratingId}/report
     */
    public function reportRating(Request $request, string $ratingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $authUser = Auth::user();
            $rating = Rating::find($ratingId);

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found'
                ], 404);
            }

            // Create report (assuming you have a RatingReport model)
            $rating->reports()->create([
                'reporter_id' => $authUser->id,
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating reported successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to report rating: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to report rating'
            ], 500);
        }
    }

    /**
     * Get transaction details
     * GET /transactions/{transactionId}
     */
    public function getTransactionDetails(string $transactionId): JsonResponse
    {
        try {
            $authUser = Auth::user();

            $transaction = Transaction::where('id', $transactionId)
                ->where(function ($query) use ($authUser) {
                    $query->where('buyer_id', $authUser->id)
                        ->orWhere('seller_id', $authUser->id);
                })
                ->with(['buyer:id,name,profile_image', 'seller:id,name,profile_image', 'item'])
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or unauthorized'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get transaction details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction details'
            ], 500);
        }
    }

    /**
     * Get top sellers
     * GET /users/top-sellers
     */
    public function getTopSellers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'sometimes|string|in:week,month,year,all',
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $period = $request->input('period', 'month');
            $limit = $request->input('limit', 50);

            $query = User::select([
                'users.id',
                'users.name',
                'users.profile_image',
                DB::raw('COUNT(transactions.id) as total_sales'),
                DB::raw('AVG(ratings.rating) as average_rating'),
                DB::raw('SUM(transactions.amount) as total_earnings')
            ])
                ->join('transactions', 'users.id', '=', 'transactions.seller_id')
                ->leftJoin('ratings', 'users.id', '=', 'ratings.rated_user_id')
                ->where('transactions.status', 'completed');

            // Apply period filter
            switch ($period) {
                case 'week':
                    $query->where('transactions.created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('transactions.created_at', '>=', now()->subMonth());
                    break;
                case 'year':
                    $query->where('transactions.created_at', '>=', now()->subYear());
                    break;
                    // 'all' - no date filter
            }

            $topSellers = $query->groupBy('users.id', 'users.name', 'users.profile_image')
                ->orderBy('total_sales', 'desc')
                ->orderBy('average_rating', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($seller, $index) {
                    return [
                        'id' => $seller->id,
                        'name' => $seller->name,
                        'profile_image' => $seller->profile_image,
                        'rank' => $index + 1,
                        'total_sales' => $seller->total_sales,
                        'average_rating' => round($seller->average_rating ?? 0, 2),
                        'total_earnings' => $seller->total_earnings ?? 0,
                        'is_verified' => $seller->is_verified ?? false
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $topSellers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get top sellers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top sellers'
            ], 500);
        }
    }
}
