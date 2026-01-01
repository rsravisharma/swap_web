<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CoinTransaction;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Exception;

class CoinsController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    public function getBalance(Request $request)
    {
        try {
            $user = Auth::user();

            // Get transaction history with pagination
            $limit = $request->input('limit', 20);
            $transactions = $user->coinTransactions()
                ->with('item:id,title')
                ->paginate($limit);

            // Calculate statistics
            $stats = [
                'total_earned' => CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '>', 0)
                    ->sum('amount'),
                'total_spent' => abs(CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '<', 0)
                    ->sum('amount')),
                'transactions_count' => $user->coinTransactions()->count(),
                'last_transaction' => $user->coinTransactions()->first(),
                'monthly_earned' => CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '>', 0)
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'monthly_spent' => abs(CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '<', 0)
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount')),
            ];

            // Get subscription info
            $subscription = null;
            if ($user->subscription_plan_id) {
                $subscription = [
                    'has_active' => true,
                    'monthly_coins' => $user->subscriptionPlan->coins_monthly ?? 0,
                    'plan_name' => $user->subscriptionPlan->name ?? null,
                    'plan_badge' => $user->subscriptionPlan->badge ?? 'normal',
                    'monthly_slots' => $user->subscriptionPlan->monthly_slots ?? 0,
                    'pdf_uploads_allowed' => $user->subscriptionPlan->allowed_pdf_uploads ?? false,
                ];
            } else {
                $subscription = [
                    'has_active' => false,
                    'monthly_coins' => 0,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $user->coins,
                    'statistics' => $stats,
                    'transactions' => $transactions->items(),
                    'subscription' => $subscription,
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                    ],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Get coin balance failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch coin balance',
            ], 500);
        }
    }

    /**
     * Purchase coins using Razorpay
     * POST /user/coins/purchase
     * 
     * Two-step process:
     * 1. Create Razorpay order (without payment details)
     * 2. Verify payment and credit coins (with payment_id, order_id, signature)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseCoins(Request $request)
    {
        // Validation rules change based on whether this is order creation or verification
        $isVerification = $request->has('razorpay_payment_id');

        $rules = [
            'coins' => 'required|integer|min:10|max:100000',
        ];

        if ($isVerification) {
            $rules = array_merge($rules, [
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $coins = $request->input('coins');
            $amount = $coins; // 1 Coin = ₹1 INR

            // STEP 2: Verify payment and credit coins
            if ($isVerification) {
                return $this->verifyCoinPurchase($request, $user, $coins, $amount);
            }

            // STEP 1: Create Razorpay order
            return $this->createCoinPurchaseOrder($user, $coins, $amount);
        } catch (Exception $e) {
            Log::error('Coin purchase failed', [
                'user_id' => Auth::id(),
                'coins' => $request->input('coins'),
                'is_verification' => $isVerification,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Coin purchase failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create Razorpay order for coin purchase
     * 
     * @param User $user
     * @param int $coins
     * @param float $amount
     * @return \Illuminate\Http\JsonResponse
     */
    private function createCoinPurchaseOrder(User $user, int $coins, float $amount)
    {
        try {
            $amountInPaise = $amount * 100;

            $orderData = [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'coin_purchase_' . time() . '_' . $user->id,
                'notes' => [
                    'user_id' => $user->id,
                    'coins' => $coins,
                    'type' => 'coin_purchase',
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                ]
            ];

            // 🔥 Use the service instead of creating Api directly
            $razorpayOrder = $this->razorpayService->createOrder($orderData);

            // Store pending transaction
            $pendingTransaction = CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => $coins,
                'type' => 'coin_purchase_pending',
                'description' => "Pending coin purchase - {$coins} coins",
                'balance_after' => $user->coins,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'amount_in_rupees' => $amount,
                    'currency' => $razorpayOrder['currency'],
                    'coins' => $coins,
                    'razorpay_key' => config('services.razorpay.key'),
                    'user' => [
                        'name' => $user->name ?? 'Customer',
                        'email' => $user->email ?? '',
                        'phone' => $user->phone ?? '',
                    ],
                    'transaction_id' => $pendingTransaction->id,
                ],
            ], 200);
        } catch (\Razorpay\Api\Errors\Error $e) {
            Log::error('Razorpay API Error', [
                'user_id' => $user->id,
                'error_class' => get_class($e),
                'error_message' => $e->getMessage(),
            ]);

            throw new Exception('Payment gateway error: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('Order creation failed', [
                'user_id' => $user->id,
                'coins' => $coins,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function verifyCoinPurchase(Request $request, User $user, int $coins, float $amount)
    {
        try {
            // 🔥 Use the service
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $this->razorpayService->verifyPaymentSignature($attributes);

            // Fetch payment details
            $payment = $this->razorpayService->fetchPayment($request->razorpay_payment_id);

            DB::beginTransaction();
            try {
                // Credit coins to user
                $user->addCoins($coins, 'coin_purchase');

                // Create successful coin transaction
                $coinTransaction = CoinTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $coins,
                    'type' => 'coin_purchase',
                    'description' => "Purchased {$coins} coins via Razorpay - Payment ID: {$request->razorpay_payment_id}",
                    'balance_after' => $user->coins,
                ]);

                // Create payment transaction record
                PaymentTransaction::create([
                    'user_id' => $user->id,
                    'payment_method_id' => null,
                    'order_id' => null,
                    'amount' => $amount,
                    'currency' => $payment['currency'] ?? 'INR',
                    'status' => 'completed',
                    'gateway_transaction_id' => $request->razorpay_payment_id,
                    'payment_method_type' => $payment['method'] ?? null,
                    'payment_method_details' => json_encode([
                        'card_id' => $payment['card_id'] ?? null,
                        'bank' => $payment['bank'] ?? null,
                        'wallet' => $payment['wallet'] ?? null,
                        'vpa' => $payment['vpa'] ?? null,
                        'email' => $payment['email'] ?? null,
                    ]),
                    'gateway_response' => json_encode($payment->toArray()),
                    'description' => "Coin Purchase - {$coins} coins",
                    'processed_at' => now(),
                ]);

                // Delete pending transaction
                CoinTransaction::where('user_id', $user->id)
                    ->where('type', 'coin_purchase_pending')
                    ->whereDate('created_at', today())
                    ->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Coins purchased successfully!',
                    'data' => [
                        'coins_added' => $coins,
                        'new_balance' => $user->coins,
                        'transaction' => [
                            'id' => $coinTransaction->id,
                            'payment_id' => $request->razorpay_payment_id,
                            'amount' => $amount,
                            'coins' => $coins,
                            'created_at' => $coinTransaction->created_at,
                        ],
                        'payment_details' => [
                            'method' => $payment['method'] ?? null,
                            'status' => $payment['status'] ?? null,
                        ],
                    ],
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Coin purchase signature verification failed', [
                'user_id' => $user->id,
                'order_id' => $request->razorpay_order_id,
                'payment_id' => $request->razorpay_payment_id,
                'error' => $e->getMessage(),
            ]);

            // Record failed transaction
            CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => 0,
                'type' => 'coin_purchase_failed',
                'description' => "Failed coin purchase - Signature verification failed",
                'balance_after' => $user->coins,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Invalid signature.',
            ], 400);
        }
    }

    /**
     * Deduct coins for various purposes
     * POST /user/coins/deduct
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deductCoins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coins' => 'required|integer|min:1',
            'reason' => 'required|string|in:item_listing,promotion,feature_listing,boost_listing,gift,purchase,subscription,other',
            'description' => 'sometimes|string|max:500',
            'item_id' => 'nullable|integer|exists:items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $coins = $request->input('coins');
            $reason = $request->input('reason');
            $description = $request->input('description', '');
            $itemId = $request->input('item_id');

            // Check if user has enough coins
            if (!$user->canAfford($coins)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient coin balance',
                    'data' => [
                        'current_balance' => $user->coins,
                        'required' => $coins,
                        'shortage' => $user->coinsNeeded($coins),
                    ],
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Deduct coins atomically
                $deducted = $user->deductCoins($coins, $reason);

                if (!$deducted) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to deduct coins. Please try again.',
                    ], 500);
                }

                // Record transaction
                $transaction = CoinTransaction::create([
                    'user_id' => $user->id,
                    'amount' => -$coins,
                    'type' => $reason,
                    'description' => $description ?: $this->getDeductionDescription($reason, $coins),
                    'item_id' => $itemId,
                    'balance_after' => $user->coins,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Coins deducted successfully',
                    'data' => [
                        'coins_deducted' => $coins,
                        'new_balance' => $user->coins,
                        'transaction' => [
                            'id' => $transaction->id,
                            'type' => $transaction->type_label,
                            'description' => $transaction->description,
                            'created_at' => $transaction->created_at,
                        ],
                    ],
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Coin deduction failed', [
                'user_id' => Auth::id(),
                'coins' => $request->input('coins'),
                'reason' => $request->input('reason'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Coin deduction failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get coin packages and pricing
     * GET /user/coins/packages
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPackages()
    {
        try {
            $packages = [
                [
                    'id' => 'pkg_100',
                    'coins' => 100,
                    'price' => 100.00,
                    'bonus' => 0,
                    'label' => '100 Coins',
                    'popular' => false,
                ],
                [
                    'id' => 'pkg_250',
                    'coins' => 250,
                    'price' => 240.00,
                    'bonus' => 10,
                    'label' => '250 Coins',
                    'popular' => false,
                    'discount' => '4% OFF',
                ],
                [
                    'id' => 'pkg_500',
                    'coins' => 500,
                    'price' => 475.00,
                    'bonus' => 25,
                    'label' => '500 Coins',
                    'popular' => true,
                    'discount' => '5% OFF',
                ],
                [
                    'id' => 'pkg_1000',
                    'coins' => 1000,
                    'price' => 900.00,
                    'bonus' => 100,
                    'label' => '1000 Coins',
                    'popular' => false,
                    'discount' => '10% OFF',
                ],
                [
                    'id' => 'pkg_2500',
                    'coins' => 2500,
                    'price' => 2125.00,
                    'bonus' => 375,
                    'label' => '2500 Coins',
                    'popular' => false,
                    'discount' => '15% OFF',
                ],
                [
                    'id' => 'pkg_5000',
                    'coins' => 5000,
                    'price' => 4000.00,
                    'bonus' => 1000,
                    'label' => '5000 Coins',
                    'popular' => false,
                    'discount' => '20% OFF',
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'packages' => $packages,
                    'custom' => [
                        'enabled' => true,
                        'min' => 10,
                        'max' => 100000,
                    ],
                    'conversion_rate' => '1 Coin = ₹1 INR',
                    'payment_methods' => ['UPI', 'Card', 'Net Banking', 'Wallet'],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to fetch coin packages', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch coin packages',
            ], 500);
        }
    }

    /**
     * Get coin transaction history with filters
     * GET /user/coins/transactions
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $type = $request->input('type'); // 'credit', 'debit', or 'all'
            $limit = $request->input('limit', 20);

            $query = $user->coinTransactions()->with('item:id,title,price,images');

            // Filter by transaction type
            if ($type === 'credit') {
                $query->where('amount', '>', 0);
            } elseif ($type === 'debit') {
                $query->where('amount', '<', 0);
            }

            // Filter by specific coin transaction type
            if ($request->filled('transaction_type')) {
                $query->where('type', $request->transaction_type);
            }

            // Date range filters
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($limit);

            // Calculate summary
            $summary = [
                'total_credits' => CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '>', 0)
                    ->sum('amount'),
                'total_debits' => abs(CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '<', 0)
                    ->sum('amount')),
                'transaction_count' => $user->coinTransactions()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions->items(),
                    'summary' => $summary,
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                    ],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Get transaction history failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction history',
            ], 500);
        }
    }

    public function addCoins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coins' => 'required|integer|min:1',
            'type' => 'required|string|in:refund,compensation,bonus,gift_received,other',
            'description' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $coins = $request->input('coins');
            $type = $request->input('type');
            $description = $request->input('description', '');

            DB::beginTransaction();
            try {
                // Add coins
                $user->addCoins($coins, $type);

                // Record transaction
                $transaction = CoinTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $coins, // Positive amount
                    'type' => $type,
                    'description' => $description ?: $this->getAddCoinsDescription($type, $coins),
                    'balance_after' => $user->coins,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Coins added successfully',
                    'data' => [
                        'coins_added' => $coins,
                        'new_balance' => $user->coins,
                        'transaction' => [
                            'id' => $transaction->id,
                            'type' => $transaction->type_label ?? $transaction->type,
                            'description' => $transaction->description,
                            'created_at' => $transaction->created_at,
                        ],
                    ],
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Add coins failed', [
                'user_id' => Auth::id(),
                'coins' => $request->input('coins'),
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add coins. Please try again.',
            ], 500);
        }
    }

    /**
     * Get add coins description
     */
    private function getAddCoinsDescription(string $type, int $coins): string
    {
        return match ($type) {
            'refund' => "Refund: {$coins} coins",
            'compensation' => "Compensation: {$coins} coins",
            'bonus' => "Bonus: {$coins} coins",
            'gift_received' => "Gift received: {$coins} coins",
            default => "Added {$coins} coins",
        };
    }

    /**
     * Get deduction description based on reason
     * 
     * @param string $reason
     * @param int $coins
     * @return string
     */
    private function getDeductionDescription(string $reason, int $coins): string
    {
        return match ($reason) {
            'item_listing' => "Deducted {$coins} coins for item listing",
            'promotion' => "Deducted {$coins} coins for item promotion",
            'feature_listing' => "Deducted {$coins} coins for featured listing",
            'boost_listing' => "Deducted {$coins} coins for boosting listing",
            'gift' => "Sent {$coins} coins as gift",
            'purchase' => "Deducted {$coins} coins for purchase",
            'subscription' => "Deducted {$coins} coins for subscription",
            default => "Deducted {$coins} coins",
        };
    }

    /**
     * Award bonus coins (admin/system use)
     * POST /user/coins/award-bonus (admin middleware required)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function awardBonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'coins' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $admin = Auth::user();
            $user = User::findOrFail($request->user_id);
            $coins = $request->coins;
            $reason = $request->reason;

            DB::beginTransaction();
            try {
                $user->addCoins($coins, 'admin_bonus');

                CoinTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $coins,
                    'type' => 'admin_bonus',
                    'description' => $reason . " (Awarded by Admin: {$admin->name})",
                    'balance_after' => $user->coins,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bonus coins awarded successfully',
                    'data' => [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'coins_awarded' => $coins,
                        'new_balance' => $user->coins,
                        'awarded_by' => $admin->name,
                    ],
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Award bonus coins failed', [
                'admin_id' => Auth::id(),
                'user_id' => $request->user_id,
                'coins' => $request->coins,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to award bonus coins',
            ], 500);
        }
    }
}
