<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Earning;
use App\Models\StudentVerification;
use App\Models\UserFollow;
use App\Models\UserBlock;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Purchase;
use App\Models\UserRating;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Get earnings history
     * GET /profile/earnings
     */
    public function getEarningsHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'sometimes|string|in:daily,weekly,monthly,yearly'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $period = $request->input('period', 'monthly');

            $earnings = Earning::where('user_id', $user->id)
                ->where('period', $period)
                ->orderBy('date', 'desc')
                ->get();

            $totalEarnings = $earnings->sum('amount');
            $monthlyEarnings = Earning::where('user_id', $user->id)
                ->where('period', 'monthly')
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('amount');

            return response()->json([
                'success' => true,
                'transactions' => $earnings,
                'totalEarnings' => $totalEarnings,
                'monthlyEarnings' => $monthlyEarnings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch earnings history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     * GET /profile
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     * PUT /profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // Handle method override if sent from frontend
        if ($request->has('_method') && $request->input('_method') === 'PUT') {
            // This allows POST request to be treated as PUT for REST compliance
        }

        // Convert empty strings to null for proper validation
        $data = $request->all();

        // Remove _method if it exists
        unset($data['_method']);

        // Handle empty strings for optional fields
        $optionalFields = ['phone', 'bio', 'university', 'course', 'semester'];
        foreach ($optionalFields as $field) {
            if (isset($data[$field]) && trim($data[$field]) === '') {
                unset($data[$field]); // Remove empty fields completely
            }
        }

        // Handle boolean conversion
        if (isset($data['is_student'])) {
            if (is_string($data['is_student'])) {
                $data['is_student'] = filter_var($data['is_student'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|min:2',
            'phone' => 'sometimes|nullable|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'bio' => 'sometimes|nullable|string|max:500',
            'avatar' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:5120',
            'profile_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:5120',
            'university' => 'sometimes|nullable|string|max:255',
            'course' => 'sometimes|nullable|string|max:255',
            'semester' => 'sometimes|nullable|string|max:50',
            'is_student' => 'sometimes|boolean',
        ], [
            'name.required' => 'Name is required',
            'name.min' => 'Name must be at least 2 characters long',
            'phone.regex' => 'Please enter a valid phone number',
            'bio.max' => 'Bio cannot exceed 500 characters',
            'avatar.image' => 'Avatar must be an image file',
            'avatar.mimes' => 'Avatar must be a JPG, JPEG, PNG, or WebP file',
            'profile_image.image' => 'Profile image must be an image file',
            'profile_image.mimes' => 'Profile image must be a JPG, JPEG, PNG, or WebP file',
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

            // Handle profile image upload
            if ($request->hasFile('profile_image') || $request->hasFile('avatar')) {
                $file = $request->file('profile_image') ?? $request->file('avatar');

                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                $imagePath = $file->store('profile-images', 'public');
                $user->profile_image = $imagePath;
            }

            // Update user fields - only update fields that are present in the request
            $fillableFields = [
                'name',
                'phone',
                'university',
                'course',
                'semester',
                'bio',
                'is_student'
            ];

            foreach ($fillableFields as $field) {
                if (array_key_exists($field, $data)) {
                    $user->$field = $data[$field];
                }
            }

            // Special handling for academic fields when not a student
            if (isset($data['is_student']) && !$data['is_student']) {
                $user->university = null;
                $user->course = null;
                $user->semester = null;
            }

            $user->save();
            DB::commit();

            // Prepare response data with full image URL
            $userData = $user->fresh()->toArray();
            if ($userData['profile_image']) {
                $userData['profile_image'] = Storage::disk('public')->url($userData['profile_image']);
            }

            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getCurrentUserStats(): JsonResponse
    {
        return $this->getUserStats(null);
    }

    /**
     * Get user statistics
     * GET /profile/stats or GET /profile/stats/{userId}
     */
    public function getUserStats(?string $userId = null, bool $forceRefresh = false): JsonResponse
    {
        try {
            $targetUserId = $userId ?: Auth::id();
            Log::info('Getting stats for user ID: ' . $targetUserId);

            $user = User::find($targetUserId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // ✅ OPTION 1: Use cached counters (fastest)
            if ($forceRefresh) {
                $stats = $this->calculateAndCacheUserStats($user);
            } elseif ($this->shouldUseCachedStats($user)) {
                $stats = $this->getCachedUserStats($user);
            } else {
                $stats = $this->calculateAndCacheUserStats($user);
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function shouldUseCachedStats(User $user): bool
    {
        // If stats were updated recently (within last hour), use cache
        if (
            $user->stats_last_updated &&
            $user->stats_last_updated->gt(now()->subHour())
        ) {
            return true;
        }

        // For high-activity users, use cache more aggressively
        if ($user->total_listings > 100) {
            return $user->stats_last_updated &&
                $user->stats_last_updated->gt(now()->subMinutes(30));
        }

        return false;
    }

    private function getCachedUserStats(User $user): array
    {
        return [
            'listings' => $user->total_listings,
            'active_listings' => $user->active_listings,
            'sold' => $user->items_sold,
            'purchases' => $user->items_bought,
            'followers' => $user->followers_count,
            'following' => $user->following_count,
            'rating' => (float) $user->seller_rating,
            'total_earnings' => (float) $user->total_earnings,
            'total_spent' => (float) $user->total_spent,
            'items_sold' => $user->items_sold,
            'items_bought' => $user->items_bought,
            'total_reviews' => $user->total_reviews,
            'last_updated' => $user->stats_last_updated,
            'is_cached' => true, // For debugging
        ];
    }

    private function calculateAndCacheUserStats(User $user): array
    {
        // Calculate real-time values
        $totalListings = Item::where('user_id', $user->id)->count();
        $activeListings = Item::where('user_id', $user->id)
            ->where('status', 'active')->count();
        $sold = Item::where('user_id', $user->id)
            ->where('status', 'sold')->count();
        $purchases = Purchase::where('user_id', $user->id)->count();
        $followers = UserFollow::where('followed_id', $user->id)->count();
        $following = UserFollow::where('follower_id', $user->id)->count();

        // Update user model with fresh counts
        $user->update([
            'total_listings' => $totalListings,
            'active_listings' => $activeListings,
            'items_sold' => $sold, // Update if different
            'items_bought' => $purchases, // Update if different
            'followers_count' => $followers,
            'following_count' => $following,
            'stats_last_updated' => now(),
        ]);

        return [
            'listings' => $totalListings,
            'active_listings' => $activeListings,
            'sold' => $sold,
            'purchases' => $purchases,
            'followers' => $followers,
            'following' => $following,
            'rating' => (float) $user->seller_rating,
            'total_earnings' => (float) $user->total_earnings,
            'total_spent' => (float) $user->total_spent,
            'items_sold' => $sold,
            'items_bought' => $purchases,
            'total_reviews' => $user->total_reviews,
            'last_updated' => now(),
            'is_cached' => false, // For debugging
        ];
    }

    // ✅ NEW: Always get fresh data from database (never use cache)
    public function getUserStatsRealtime(?string $userId = null): JsonResponse
    {
        try {
            $targetUserId = $userId ?: Auth::id();
            Log::info('Getting REALTIME stats for user ID: ' . $targetUserId);

            $user = User::find($targetUserId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Always calculate from database - never use cache
            $stats = $this->calculateRealtimeUserStats($user);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch realtime user stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch realtime user stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Calculate stats without updating cache (pure database query)
    private function calculateRealtimeUserStats(User $user): array
    {
        Log::info('Calculating REALTIME stats from database for user: ' . $user->id);

        // Calculate real-time values - pure database queries
        $totalListings = Item::where('user_id', $user->id)->count();
        $activeListings = Item::where('user_id', $user->id)
            ->where('status', 'active')->count();
        $sold = Item::where('user_id', $user->id)
            ->where('status', 'sold')->count();
        $purchases = Purchase::where('user_id', $user->id)->count();
        $followers = UserFollow::where('followed_id', $user->id)->count();
        $following = UserFollow::where('follower_id', $user->id)->count();

        // Get total earnings from actual sold items
        $totalEarnings = Item::where('user_id', $user->id)
            ->where('status', 'sold')
            ->sum('price');

        // Get total spent from actual purchases
        $totalSpent = Purchase::where('user_id', $user->id)
            ->sum('price');

        // ✅ FIXED: Use UserRating model with correct methods
        $averageRating = UserRating::getAverageRating($user->id);
        $totalReviews = UserRating::getTotalRatings($user->id);

        // ✅ OPTIONAL: Get separate buyer and seller ratings
        $sellerRating = UserRating::getAverageRating($user->id, 'seller');
        $buyerRating = UserRating::getAverageRating($user->id, 'buyer');
        $sellerReviews = UserRating::getTotalRatings($user->id, 'seller');
        $buyerReviews = UserRating::getTotalRatings($user->id, 'buyer');

        // Return fresh data without updating cache
        return [
            'listings' => $totalListings,
            'active_listings' => $activeListings,
            'sold' => $sold,
            'purchases' => $purchases,
            'followers' => $followers,
            'following' => $following,
            'rating' => (float) $averageRating,
            'seller_rating' => (float) $sellerRating,
            'buyer_rating' => (float) $buyerRating,
            'total_earnings' => (float) $totalEarnings,
            'total_spent' => (float) $totalSpent,
            'items_sold' => $sold,
            'items_bought' => $purchases,
            'total_reviews' => $totalReviews,
            'seller_reviews' => $sellerReviews,
            'buyer_reviews' => $buyerReviews,
            'last_updated' => now(),
            'is_cached' => false,
            'is_realtime' => true,
            'data_source' => 'database_realtime'
        ];
    }

    // ✅ ENHANCED: Option to compare cache vs database
    public function compareUserStats(?string $userId = null): JsonResponse
    {
        try {
            $targetUserId = $userId ?: Auth::id();
            $user = User::find($targetUserId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get cached stats
            $cachedStats = $this->getCachedUserStats($user);

            // Get realtime stats
            $realtimeStats = $this->calculateRealtimeUserStats($user);

            // Calculate differences
            $differences = [];
            $fields = ['listings', 'active_listings', 'sold', 'purchases', 'followers', 'following'];

            foreach ($fields as $field) {
                $cached = $cachedStats[$field] ?? 0;
                $realtime = $realtimeStats[$field] ?? 0;
                $diff = $realtime - $cached;

                if ($diff !== 0) {
                    $differences[$field] = [
                        'cached' => $cached,
                        'realtime' => $realtime,
                        'difference' => $diff
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cached_stats' => $cachedStats,
                    'realtime_stats' => $realtimeStats,
                    'differences' => $differences,
                    'cache_is_accurate' => empty($differences),
                    'cache_age' => $user->stats_last_updated ?
                        now()->diffInMinutes($user->stats_last_updated) . ' minutes' : 'never updated'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to compare user stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to compare user stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student profile
     * GET /profile/student
     */
    public function getStudentProfile(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Return student-related fields from user model
            $studentData = [
                'university' => $user->university,
                'course' => $user->course,
                'semester' => $user->semester,
                'student_id' => $user->student_id,
                'student_verified' => $user->student_verified,
                'student_id_document' => $user->student_id_document,
            ];

            // Also get verification record if exists
            $verification = StudentVerification::where('user_id', $user->id)->first();
            if ($verification) {
                $studentData['verification'] = $verification;
            }

            return response()->json([
                'success' => true,
                'data' => $studentData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload student ID document
     * POST /profile/upload-student-id
     */
    public function uploadStudentId(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Delete old document if exists
            if ($user->student_id_document) {
                Storage::disk('public')->delete($user->student_id_document);
            }

            $path = $request->file('file')->store('student-ids', 'public');

            // Update user model with document path
            $user->student_id_document = $path;
            $user->save();

            // Also update or create student verification record
            StudentVerification::updateOrCreate(
                ['user_id' => $user->id],
                ['document_path' => $path]
            );

            return response()->json([
                'success' => true,
                'message' => 'Student ID uploaded successfully',
                'data' => ['document_path' => $path]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload student ID',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user details (public profile)
     * GET /users/{userId}
     */
    public function getUserDetails(string $userId): JsonResponse
    {
        try {
            $user = User::where('id', $userId)
                ->where('is_active', true) // ✅ ADD: Only active users
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $authUser = Auth::user();
            $isFollowing = false;
            $isBlocked = false;

            if ($authUser) {
                // ✅ FIX: Check if current user is blocked by the target user
                $blockedByTarget = UserBlock::where('blocker_id', $userId)
                    ->where('blocked_id', $authUser->id)
                    ->exists();

                if ($blockedByTarget) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }

                $isFollowing = UserFollow::where('follower_id', $authUser->id)
                    ->where('followed_id', $userId)
                    ->exists();

                $isBlocked = UserBlock::where('blocker_id', $authUser->id)
                    ->where('blocked_id', $userId)
                    ->exists();
            }

            $userData = $user->toArray();
            $userData['isFollowing'] = $isFollowing;
            $userData['isBlocked'] = $isBlocked;

            // ✅ ADD: Remove sensitive data from public profile
            unset(
                $userData['email'],
                $userData['phone'],
                $userData['device_id'],
                $userData['fcm_token'],
                $userData['student_id']
            );

            return response()->json([
                'success' => true,
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle block status
     * POST /users/{userId}/block
     */
    public function toggleBlock(string $userId): JsonResponse
    {
        try {
            $authUser = Auth::user();

            if ($authUser->id == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot block yourself'
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

            $block = UserBlock::where('blocker_id', $authUser->id)
                ->where('blocked_id', $userId)
                ->first();

            if ($block) {
                $block->delete();
                $isBlocked = false;
                $message = 'User unblocked successfully';
            } else {
                UserBlock::create([
                    'blocker_id' => $authUser->id,
                    'blocked_id' => $userId
                ]);
                $isBlocked = true;
                $message = 'User blocked successfully';

                // Also unfollow if following and update counters
                $existingFollow = UserFollow::where('follower_id', $authUser->id)
                    ->where('followed_id', $userId)
                    ->first();

                if ($existingFollow) {
                    $existingFollow->delete();
                    // ✅ FIX: Update cached counters when unfollowing due to block
                    $authUser->decrement('following_count');
                    $targetUser->decrement('followers_count');
                }
            }

            DB::commit(); // ✅ ADD: Commit transaction

            return response()->json([
                'success' => true,
                'is_blocked' => $isBlocked,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollback(); // ✅ ADD: Rollback on error
            return response()->json([
                'success' => false,
                'message' => 'Failed to update block status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit student verification
     * POST /profile/verification
     */
    public function submitStudentVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|string|max:50',
            'university' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'graduationYear' => 'required|integer|min:2020|max:2030'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Update user model with student info
            $user->student_id = $request->studentId;
            $user->university = $request->university;
            $user->course = $request->course;
            $user->save();

            // Create/update student verification record
            StudentVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id' => $request->studentId,
                    'university' => $request->university,
                    'course' => $request->course,
                    'graduation_year' => $request->graduationYear,
                    'status' => 'pending'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Student verification submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addToWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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
            $itemId = $request->input('item_id');

            // Check if item is active
            $item = Item::where('id', $itemId)
                ->where('status', 'active')
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found or not available'
                ], 404);
            }

            // Check if user is trying to wishlist their own item
            if ($item->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot add your own item to wishlist'
                ], 400);
            }

            // Add to wishlist using firstOrCreate (prevents duplicates)
            $wishlist = Wishlist::firstOrCreate([
                'user_id' => $user->id,
                'item_id' => $itemId
            ]);

            // Check if it was just created or already existed
            $wasRecentlyCreated = $wishlist->wasRecentlyCreated;

            return response()->json([
                'success' => true,
                'message' => $wasRecentlyCreated
                    ? 'Item added to wishlist successfully'
                    : 'Item already in wishlist',
                'data' => [
                    'wishlist_id' => $wishlist->id,
                    'item_id' => $itemId,
                    'added_at' => $wishlist->created_at
                ]
            ], $wasRecentlyCreated ? 201 : 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wishlist
     * GET /profile/wishlist
     */
    public function getWishlist(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $perPage = $request->input('per_page', 20);

            // Fetch wishlist items with optimized query
            $wishlistItems = Item::whereHas('wishlists', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with([
                    'user:id,name,profile_image',
                    'primaryImage:id,item_id,url,is_primary',
                    'category:id,name,icon'
                ])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data to include wishlist timestamp
            $items = $wishlistItems->getCollection()->map(function ($item) use ($user) {
                $wishlistEntry = Wishlist::where('user_id', $user->id)
                    ->where('item_id', $item->id)
                    ->first();

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'price' => $item->price,
                    'condition' => $item->condition,
                    'status' => $item->status,
                    'location' => $item->location_display,
                    'category' => $item->category_display,
                    'images' => $item->images,
                    'primary_image' => $item->primaryImage,
                    'user' => $item->user,
                    'added_to_wishlist_at' => $wishlistEntry ? $wishlistEntry->created_at : null,
                    'is_promoted' => $item->is_promoted,
                    'wishlist_count' => $item->wishlists()->count()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'current_page' => $wishlistItems->currentPage(),
                    'total_pages' => $wishlistItems->lastPage(),
                    'total_items' => $wishlistItems->total(),
                    'per_page' => $wishlistItems->perPage(),
                    'has_more_pages' => $wishlistItems->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove item from wishlist
     * DELETE /profile/wishlist/{itemId}
     */
    public function removeFromWishlist(string $itemId): JsonResponse
    {
        try {
            $user = Auth::user();

            // Find wishlist entry
            $wishlist = Wishlist::where('user_id', $user->id)
                ->where('item_id', $itemId)
                ->first();

            if (!$wishlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in wishlist'
                ], 404);
            }

            // Delete wishlist entry
            $wishlist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist successfully',
                'data' => [
                    'item_id' => $itemId,
                    'removed_at' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear entire wishlist
     * DELETE /profile/wishlist/clear
     */
    public function clearWishlist(): JsonResponse
    {
        try {
            $user = Auth::user();

            $deletedCount = Wishlist::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Wishlist cleared successfully',
                'data' => [
                    'items_removed' => $deletedCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wishlist count
     * GET /profile/wishlist/count
     */
    public function getWishlistCount(): JsonResponse
    {
        try {
            $user = Auth::user();

            $count = Wishlist::where('user_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get wishlist count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
