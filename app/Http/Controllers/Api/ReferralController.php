<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\ReferralTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CoinTransaction;


class ReferralController extends Controller
{
    /**
     * User registration
     */

    public function getStats(Request $request)
    {
        try {
            $user = auth()->user();

            // Count total referrals
            $totalReferrals = User::where('referred_by', $user->id)->count();

            // Calculate coins earned from referrals (5 coins per referral)
            $coinsEarned = $totalReferrals * 5;

            // Get additional stats
            $activeReferrals = User::where('referred_by', $user->id)
                ->where('is_active', true)
                ->count();

            $recentReferrals = User::where('referred_by', $user->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_referrals' => $totalReferrals,
                    'coins_earned' => $coinsEarned,
                    'active_referrals' => $activeReferrals,
                    'recent_referrals' => $recentReferrals,
                    'my_referral_code' => $user->referral_code,
                ],
                'message' => 'Referral statistics retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching referral stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve referral statistics',
            ], 500);
        }
    }

    /**
     * Get list of users referred by the authenticated user
     */
    public function getReferredUsers(Request $request)
    {
        try {
            $user = auth()->user();

            // Get pagination parameters
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            // Fetch referred users with selected fields
            $referredUsers = User::where('referred_by', $user->id)
                ->select([
                    'id',
                    'name',
                    'email',
                    'profile_image',
                    'is_active',
                    'created_at',
                    'items_sold',
                    'items_bought',
                    'seller_rating',
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform data for Flutter app
            $transformedData = $referredUsers->map(function ($referredUser) {
                return [
                    'id' => $referredUser->id,
                    'name' => $referredUser->name,
                    'email' => $referredUser->email,
                    'profile_image' => $referredUser->profile_image,
                    'is_active' => $referredUser->is_active,
                    'joined_date' => $referredUser->created_at->format('Y-m-d'),
                    'joined_days_ago' => $referredUser->created_at->diffForHumans(),
                    'items_sold' => $referredUser->items_sold,
                    'items_bought' => $referredUser->items_bought,
                    'seller_rating' => number_format($referredUser->seller_rating, 1),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'pagination' => [
                    'total' => $referredUsers->total(),
                    'per_page' => $referredUsers->perPage(),
                    'current_page' => $referredUsers->currentPage(),
                    'last_page' => $referredUsers->lastPage(),
                    'from' => $referredUsers->firstItem(),
                    'to' => $referredUsers->lastItem(),
                ],
                'message' => 'Referred users retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching referred users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve referred users',
            ], 500);
        }
    }

    /**
     * Validate referral code
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:10',
        ]);

        $referralCode = strtoupper(trim($request->referral_code));
        $currentUser = auth()->user();

        // Find user with this referral code
        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code. Please check and try again.',
            ], 200);
        }

        // Check if user is trying to use their own code
        if ($referrer->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot use your own referral code.',
            ], 200);
        }

        // Check if user already used a referral code
        if ($currentUser->referred_by) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used a referral code.',
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valid referral code! Both you and ' . $referrer->name . ' will receive 5 coins.',
            'data' => [
                'referrer_id' => $referrer->id,
                'referrer_name' => $referrer->name,
                'reward_coins' => 5,
            ],
        ]);
    }

    /**
     * Apply referral code
     */
    public function applyCode(Request $request)
    {
        Log::info('=== APPLY REFERRAL CODE STARTED ===', [
            'user_id' => auth()->id(),
            'referral_code' => $request->referral_code,
        ]);

        $request->validate([
            'referral_code' => 'required|string|max:10',
        ]);

        $referralCode = strtoupper(trim($request->referral_code));
        $currentUser = auth()->user();

        Log::info('Current user data', [
            'user_id' => $currentUser->id,
            'current_coins' => $currentUser->coins,
            'referred_by' => $currentUser->referred_by,
        ]);

        // Check if user already used a referral code
        if ($currentUser->referred_by) {
            Log::warning('User already used a referral code', [
                'user_id' => $currentUser->id,
                'referred_by' => $currentUser->referred_by,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You have already used a referral code.',
            ], 200);
        }

        // Find referrer
        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer) {
            Log::warning('Invalid referral code', ['code' => $referralCode]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code.',
            ], 200);
        }

        Log::info('Referrer found', [
            'referrer_id' => $referrer->id,
            'referrer_name' => $referrer->name,
            'referrer_coins' => $referrer->coins,
        ]);

        if ($referrer->id === $currentUser->id) {
            Log::warning('User trying to use own code');

            return response()->json([
                'success' => false,
                'message' => 'You cannot use your own referral code.',
            ], 200);
        }

        // Use database transaction for data consistency
        DB::beginTransaction();
        try {
            Log::info('Starting transaction');

            // ✅ Update current user and log transaction
            $currentUser->referred_by = $referrer->id;
            $oldBalance = $currentUser->coins;
            $currentUser->coins += 5;
            $currentUser->save();

            // ✅ Log coin transaction for new user
            CoinTransaction::create([
                'user_id' => $currentUser->id,
                'amount' => 5,
                'type' => 'referral_signup_reward',
                'description' => "Referral reward for using code: {$referralCode}",
                'balance_after' => $currentUser->coins,
            ]);

            Log::info('Current user updated and transaction logged', [
                'user_id' => $currentUser->id,
                'old_balance' => $oldBalance,
                'new_balance' => $currentUser->coins,
                'coins_added' => 5,
            ]);

            // ✅ Reward referrer and log transaction
            $referrerOldBalance = $referrer->coins;
            $referrer->coins += 5;
            $referrer->save();

            // ✅ Log coin transaction for referrer
            CoinTransaction::create([
                'user_id' => $referrer->id,
                'amount' => 5,
                'type' => 'referral_commission',
                'description' => "Referral commission for referring {$currentUser->name} (Code: {$referralCode})",
                'balance_after' => $referrer->coins,
            ]);

            Log::info('Referrer updated and transaction logged', [
                'referrer_id' => $referrer->id,
                'old_balance' => $referrerOldBalance,
                'new_balance' => $referrer->coins,
                'coins_added' => 5,
            ]);

            // ✅ Log the referral transaction (for referral tracking)
            $transaction = ReferralTransaction::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $currentUser->id,
                'referral_code' => $referralCode,
                'coins_awarded' => 5,
                'awarded_at' => now(),
            ]);

            Log::info('Referral transaction created', [
                'transaction_id' => $transaction->id,
            ]);

            DB::commit();
            Log::info('Transaction committed successfully');

            // Refresh users to get latest data
            $currentUser->refresh();
            $referrer->refresh();

            Log::info('=== APPLY REFERRAL CODE COMPLETED ===', [
                'success' => true,
                'current_user_coins' => $currentUser->coins,
                'referrer_coins' => $referrer->coins,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Referral code applied successfully! You received 5 coins.',
                'user' => $currentUser->fresh(),
                'data' => [
                    'coins_received' => 5,
                    'new_coin_balance' => $currentUser->coins,
                    'referred_by' => [
                        'id' => $referrer->id,
                        'name' => $referrer->name,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying referral code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply referral code. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getTransactionHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->input('per_page', 20);

            // Get all transactions where user is the referrer
            $transactions = ReferralTransaction::where('referrer_id', $user->id)
                ->with('referredUser:id,name,email,profile_image')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Transaction history retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction history: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction history',
            ], 500);
        }
    }

    public function getReferralTransactionHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->input('per_page', 20);

            $transactions = CoinTransaction::where('user_id', $user->id)
                ->referrals() // Use the scope
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'formatted_amount' => $transaction->formatted_amount,
                        'type' => $transaction->type,
                        'type_label' => $transaction->type_label,
                        'description' => $transaction->description,
                        'balance_after' => $transaction->balance_after,
                        'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $transaction->created_at->diffForHumans(),
                    ];
                }),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching referral transactions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transactions',
            ], 500);
        }
    }
}
