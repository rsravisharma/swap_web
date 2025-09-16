<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Earning;
use App\Models\StudentVerification;
use App\Models\UserFollow;
use App\Models\UserBlock;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            $profile = $user->profile;

            return response()->json([
                'success' => true,
                'data' => array_merge($user->toArray(), $profile ? $profile->toArray() : [])
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
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'sometimes|string|max:20',
            'university' => 'sometimes|string|max:255',
            'course' => 'sometimes|string|max:255',
            'semester' => 'sometimes|string|max:50',
            'bio' => 'sometimes|string|max:1000',
            'avatar' => 'sometimes|image|max:5120'
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

            // Update user table fields
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            $user->save();

            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->profile && $user->profile->avatar) {
                    Storage::disk('public')->delete($user->profile->avatar);
                }
                
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // Update or create profile
            $profileData = $request->only(['phone', 'university', 'course', 'semester', 'bio']);
            $profileData['name'] = $user->name;
            $profileData['email'] = $user->email;
            
            if ($avatarPath) {
                $profileData['avatar'] = $avatarPath;
            }

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => array_merge($user->fresh()->toArray(), $profile->toArray()),
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

    /**
     * Get user statistics
     * GET /profile/stats or GET /profile/stats/{userId}
     */
    public function getUserStats(string $userId = null): JsonResponse
    {
        try {
            $targetUserId = $userId ?: Auth::id();

            $listings = Product::where('user_id', $targetUserId)->count();
            $sold = Product::where('user_id', $targetUserId)->where('status', 'sold')->count();
            $purchases = Purchase::where('user_id', $targetUserId)->count();
            $followers = UserFollow::where('followed_id', $targetUserId)->count();
            $following = UserFollow::where('follower_id', $targetUserId)->count();
            
            // Calculate average rating from reviews (if you have a reviews table)
            $rating = 0.0; // Placeholder - implement based on your rating system

            return response()->json([
                'success' => true,
                'data' => [
                    'listings' => $listings,
                    'sold' => $sold,
                    'purchases' => $purchases,
                    'followers' => $followers,
                    'following' => $following,
                    'rating' => $rating
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user stats',
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
            $verification = StudentVerification::where('user_id', $user->id)->first();

            return response()->json([
                'success' => true,
                'data' => $verification
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
            
            $path = $request->file('file')->store('student-ids', 'public');

            // Update or create student verification record
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
            $user = User::with('profile')->find($userId);
            
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
                $isFollowing = UserFollow::where('follower_id', $authUser->id)
                    ->where('followed_id', $userId)
                    ->exists();
                
                $isBlocked = UserBlock::where('blocker_id', $authUser->id)
                    ->where('blocked_id', $userId)
                    ->exists();
            }

            $userData = array_merge($user->toArray(), $user->profile ? $user->profile->toArray() : []);
            $userData['isFollowing'] = $isFollowing;
            $userData['isBlocked'] = $isBlocked;

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
     * Toggle follow status
     * POST /users/{userId}/follow
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

            $follow = UserFollow::where('follower_id', $authUser->id)
                ->where('followed_id', $userId)
                ->first();

            if ($follow) {
                $follow->delete();
                $isFollowing = false;
                $message = 'User unfollowed successfully';
            } else {
                UserFollow::create([
                    'follower_id' => $authUser->id,
                    'followed_id' => $userId
                ]);
                $isFollowing = true;
                $message = 'User followed successfully';
            }

            return response()->json([
                'success' => true,
                'is_following' => $isFollowing,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update follow status',
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
                
                // Also unfollow if following
                UserFollow::where('follower_id', $authUser->id)
                    ->where('followed_id', $userId)
                    ->delete();
            }

            return response()->json([
                'success' => true,
                'is_blocked' => $isBlocked,
                'message' => $message
            ]);

        } catch (\Exception $e) {
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

            StudentVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id' => $request->studentId,
                    'university' => $request->university,
                    'graduation_year' => $request->graduationYear,
                    'status' => 'pending'
                ]
            );

            // Also update profile with university info
            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'university' => $request->university,
                    'course' => $request->course
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

    /**
     * Get wishlist
     * GET /profile/wishlist
     */
    public function getWishlist(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $wishlistItems = Product::whereHas('favorites', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['user:id,name,profile_image', 'images'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $wishlistItems
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
            
            $favorite = Favorite::where('user_id', $user->id)
                ->where('item_id', $itemId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in wishlist'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}