<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use App\Models\ItemReport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SafetyController extends Controller
{
    /**
     * Get blocked users
     * GET /safety/blocked-users
     */
    public function getBlockedUsers(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $blockedUsers = UserBlock::with('blockedUser:id,name,email,profile_image')
                ->where('blocker_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($block) {
                    $blocked = $block->blockedUser;
                    return [
                        'id' => $blocked->id,
                        'name' => $blocked->name,
                        'email' => $blocked->email,
                        'profile_image' => $blocked->profile_image,
                        'blocked_at' => $block->created_at->toDateTimeString(),
                        'reason' => $block->reason,
                        'block_id' => $block->id
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $blockedUsers
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get blocked users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blocked users'
            ], 500);
        }
    }

    /**
     * Block a user
     * POST /safety/block-user
     */
    public function blockUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $blocker = Auth::user();
            $blockedUserId = $request->user_id;

            // Cannot block yourself
            if ($blocker->id == $blockedUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot block yourself'
                ], 400);
            }

            // Check if user exists
            $userToBlock = User::find($blockedUserId);
            if (!$userToBlock) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if already blocked
            $existingBlock = UserBlock::where('blocker_id', $blocker->id)
                ->where('blocked_id', $blockedUserId)
                ->first();

            if ($existingBlock) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already blocked'
                ], 400);
            }

            DB::beginTransaction();

            // Create block record
            UserBlock::create([
                'blocker_id' => $blocker->id,
                'blocked_id' => $blockedUserId,
                'reason' => $request->reason
            ]);

            // Remove any existing follows between users
            DB::table('user_follows')
                ->where(function ($query) use ($blocker, $blockedUserId) {
                    $query->where('follower_id', $blocker->id)
                          ->where('followed_id', $blockedUserId);
                })
                ->orWhere(function ($query) use ($blocker, $blockedUserId) {
                    $query->where('follower_id', $blockedUserId)
                          ->where('followed_id', $blocker->id);
                })
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User blocked successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to block user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to block user'
            ], 500);
        }
    }

    /**
     * Unblock a user
     * POST /safety/unblock-user
     */
    public function unblockUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $unblockedUserId = $request->user_id;

            $block = UserBlock::where('blocker_id', $user->id)
                ->where('blocked_id', $unblockedUserId)
                ->first();

            if (!$block) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not blocked'
                ], 400);
            }

            $block->delete();

            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to unblock user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user'
            ], 500);
        }
    }

    /**
     * Report a user
     * POST /safety/report-user
     */
    public function reportUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|max:500',
            'category' => 'sometimes|string|in:harassment,spam,inappropriate_content,fake_profile,scam,other',
            'description' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reporter = Auth::user();
            $reportedUserId = $request->user_id;

            // Cannot report yourself
            if ($reporter->id == $reportedUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot report yourself'
                ], 400);
            }

            // Check if user exists
            $reportedUser = User::find($reportedUserId);
            if (!$reportedUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check for duplicate reports (optional - allow multiple reports)
            $existingReport = UserReport::where('reporter_id', $reporter->id)
                ->where('reported_id', $reportedUserId)
                ->where('created_at', '>=', now()->subDays(7)) // Within last 7 days
                ->first();

            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reported this user recently'
                ], 400);
            }

            UserReport::create([
                'reporter_id' => $reporter->id,
                'reported_id' => $reportedUserId,
                'reason' => $request->reason,
                'category' => $request->category ?? 'other',
                'description' => $request->description,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User reported successfully. Our team will review this report.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to report user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to report user'
            ], 500);
        }
    }

    /**
     * Report an item/product
     * POST /safety/report-item
     */
    public function reportItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:products,id',
            'reason' => 'required|string|max:500',
            'category' => 'sometimes|string|in:inappropriate_content,fake_listing,overpriced,spam,copyright,other',
            'description' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reporter = Auth::user();
            $itemId = $request->item_id;

            // Check if item exists
            $item = Product::find($itemId);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Cannot report your own item
            if ($item->user_id == $reporter->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot report your own item'
                ], 400);
            }

            // Check for duplicate reports
            $existingReport = ItemReport::where('reporter_id', $reporter->id)
                ->where('item_id', $itemId)
                ->where('created_at', '>=', now()->subDays(7))
                ->first();

            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reported this item recently'
                ], 400);
            }

            ItemReport::create([
                'reporter_id' => $reporter->id,
                'item_id' => $itemId,
                'item_owner_id' => $item->user_id,
                'reason' => $request->reason,
                'category' => $request->category ?? 'other',
                'description' => $request->description,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item reported successfully. Our team will review this report.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to report item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to report item'
            ], 500);
        }
    }

    /**
     * Get safety stats (optional - for admin dashboard)
     * GET /safety/stats
     */
    public function getSafetyStats(): JsonResponse
    {
        try {
            $user = Auth::user();

            $stats = [
                'blocked_users_count' => UserBlock::where('blocker_id', $user->id)->count(),
                'user_reports_made' => UserReport::where('reporter_id', $user->id)->count(),
                'item_reports_made' => ItemReport::where('reporter_id', $user->id)->count(),
                'reports_against_me' => [
                    'user_reports' => UserReport::where('reported_id', $user->id)->count(),
                    'item_reports' => ItemReport::where('item_owner_id', $user->id)->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get safety stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve safety statistics'
            ], 500);
        }
    }

    /**
     * Check if user is blocked
     * GET /safety/is-blocked/{userId}
     */
    public function isUserBlocked(string $userId): JsonResponse
    {
        try {
            $user = Auth::user();

            $isBlocked = UserBlock::where('blocker_id', $user->id)
                ->where('blocked_id', $userId)
                ->exists();

            $isBlockedBy = UserBlock::where('blocker_id', $userId)
                ->where('blocked_id', $user->id)
                ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_blocked' => $isBlocked,
                    'is_blocked_by' => $isBlockedBy
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check block status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check block status'
            ], 500);
        }
    }
}