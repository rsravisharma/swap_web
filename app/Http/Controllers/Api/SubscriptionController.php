<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use App\Models\CoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Carbon\Carbon;
use Exception;

class SubscriptionController extends Controller
{
    /**
     * Get user's subscription status
     * GET /user/subscription/status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus()
    {
        try {
            $user = Auth::user();

            // Get current subscription
            $currentSubscription = null;
            $isActive = false;
            $daysRemaining = 0;
            $autoRenewal = false;

            if ($user->subscription_plan_id) {
                $subscription = UserSubscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->with('plan')
                    ->first();

                if ($subscription && $subscription->expires_at && $subscription->expires_at->isFuture()) {
                    $isActive = true;
                    $daysRemaining = now()->diffInDays($subscription->expires_at, false);
                    $autoRenewal = $subscription->auto_renewal ?? false;

                    $currentSubscription = [
                        'id' => $subscription->id,
                        'plan' => [
                            'id' => $subscription->plan->id,
                            'name' => $subscription->plan->name,
                            'badge' => $subscription->plan->badge,
                            'monthly_price' => $subscription->plan->monthly_price,
                            'annual_price' => $subscription->plan->annual_price,
                            'monthly_slots' => $subscription->plan->monthly_slots,
                            'coins_monthly' => $subscription->plan->coins_monthly,
                            'pdf_uploads_allowed' => $subscription->plan->allowed_pdf_uploads,
                            'description' => $subscription->plan->description,
                        ],
                        'billing_cycle' => $subscription->billing_cycle,
                        'started_at' => $subscription->started_at,
                        'expires_at' => $subscription->expires_at,
                        'days_remaining' => max(0, $daysRemaining),
                        'auto_renewal' => $autoRenewal,
                        'status' => $subscription->status,
                        'slots_used' => $user->active_listings ?? 0,
                        'slots_remaining' => max(0, $subscription->plan->monthly_slots - ($user->active_listings ?? 0)),
                    ];
                }
            }

            // Get available plans
            $availablePlans = SubscriptionPlan::orderBy('monthly_price', 'asc')->get()->map(function ($plan) use ($user) {
                // 🔥 FIX: Safe division check
                $annualDiscount = 0;
                if ($plan->annual_price && $plan->monthly_price > 0) {
                    $annualDiscount = round((1 - ($plan->annual_price / ($plan->monthly_price * 12))) * 100);
                }

                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'badge' => $plan->badge,
                    'monthly_price' => $plan->monthly_price,
                    'annual_price' => $plan->annual_price,
                    'annual_discount' => $annualDiscount,
                    'monthly_slots' => $plan->monthly_slots,
                    'coins_monthly' => $plan->coins_monthly,
                    'pdf_uploads_allowed' => $plan->allowed_pdf_uploads,
                    'description' => $plan->description,
                    'is_current' => $user->subscription_plan_id == $plan->id,
                    'features' => $this->getPlanFeatures($plan),
                ];
            });

            // Subscription history
            $history = UserSubscription::where('user_id', $user->id)
                ->with('plan:id,name,badge')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'plan_name' => $sub->plan->name ?? 'Unknown',
                        'badge' => $sub->plan->badge ?? 'normal',
                        'billing_cycle' => $sub->billing_cycle,
                        'amount' => $sub->amount,
                        'started_at' => $sub->started_at,
                        'expires_at' => $sub->expires_at,
                        'status' => $sub->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'has_active_subscription' => $isActive,
                    'current_subscription' => $currentSubscription,
                    'available_plans' => $availablePlans,
                    'subscription_history' => $history,
                    'user_benefits' => [
                        'current_coins' => $user->coins,
                        'total_listings' => $user->total_listings ?? 0,
                        'active_listings' => $user->active_listings ?? 0,
                        'can_upload_pdf' => $user->canUploadPdf(),
                    ],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Get subscription status failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription status',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    /**
     * Purchase subscription
     * POST /user/subscription/purchase
     * 
     * Two-step process:
     * 1. Create Razorpay order (without payment details)
     * 2. Verify payment and activate subscription (with payment details)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseSubscription(Request $request)
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'billing_cycle' => 'required|in:monthly,annual',
            ]);

            $user = Auth::user();
            $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

            // Calculate amount based on billing cycle
            $amount = $validated['billing_cycle'] === 'annual'
                ? $plan->annual_price
                : $plan->monthly_price;

            // 🔥 FIX: Check if amount is valid
            if (!$amount || $amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subscription price',
                ], 400);
            }

            // TODO: Create Razorpay order here
            // For now, return mock response
            return response()->json([
                'success' => true,
                'message' => 'Subscription order created',
                'data' => [
                    'razorpay_order_id' => 'order_' . uniqid(),
                    'amount' => $amount,
                    'currency' => 'INR',
                    'plan_name' => $plan->name,
                    'billing_cycle' => $validated['billing_cycle'],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Purchase subscription failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription order',
            ], 500);
        }
    }

    /**
     * Create Razorpay order for subscription
     * 
     * @param User $user
     * @param SubscriptionPlan $plan
     * @param string $billingCycle
     * @param float $amount
     * @return \Illuminate\Http\JsonResponse
     */
    private function createSubscriptionOrder(User $user, SubscriptionPlan $plan, string $billingCycle, float $amount)
    {
        try {
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_SECRET'));

            $amountInPaise = $amount * 100;

            $orderData = [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'subscription_' . $plan->id . '_' . time() . '_' . $user->id,
                'notes' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'billing_cycle' => $billingCycle,
                    'type' => 'subscription_purchase',
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                ]
            ];

            $razorpayOrder = $api->order->create($orderData);

            return response()->json([
                'success' => true,
                'message' => 'Subscription order created successfully',
                'data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'amount_in_rupees' => $amount,
                    'currency' => $razorpayOrder['currency'],
                    'razorpay_key' => env('RAZORPAY_KEY_ID'),
                    'plan' => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'badge' => $plan->badge,
                        'billing_cycle' => $billingCycle,
                        'monthly_slots' => $plan->monthly_slots,
                        'coins_monthly' => $plan->coins_monthly,
                    ],
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ],
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Razorpay subscription order creation failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify payment and activate subscription
     * 
     * @param Request $request
     * @param User $user
     * @param SubscriptionPlan $plan
     * @param string $billingCycle
     * @param float $amount
     * @return \Illuminate\Http\JsonResponse
     */
    private function verifySubscriptionPurchase(Request $request, User $user, SubscriptionPlan $plan, string $billingCycle, float $amount)
    {
        try {
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_SECRET'));

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Fetch payment details
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            DB::beginTransaction();
            try {
                // Calculate subscription period
                $startDate = now();
                $endDate = $billingCycle === 'annual'
                    ? $startDate->copy()->addYear()
                    : $startDate->copy()->addMonth();

                // Deactivate any existing active subscriptions
                UserSubscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->update(['status' => 'replaced', 'auto_renewal' => false]);

                // Create new subscription record
                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'billing_cycle' => $billingCycle,
                    'amount' => $amount,
                    'started_at' => $startDate,
                    'expires_at' => $endDate,
                    'status' => 'active',
                    'auto_renewal' => false,
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                ]);

                // Update user's subscription plan
                $user->update([
                    'subscription_plan_id' => $plan->id,
                ]);

                // Award subscription coins if applicable
                if ($plan->coins_monthly > 0) {
                    $user->addCoins($plan->coins_monthly, 'subscription_bonus');

                    CoinTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $plan->coins_monthly,
                        'type' => 'subscription_bonus',
                        'description' => "Subscription bonus - {$plan->coins_monthly} coins for {$plan->name} plan",
                        'balance_after' => $user->coins,
                    ]);
                }

                // Create payment transaction record
                $paymentTransaction = PaymentTransaction::create([
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
                    'description' => "Subscription Purchase - {$plan->name} ({$billingCycle})",
                    'processed_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription activated successfully!',
                    'data' => [
                        'subscription' => [
                            'id' => $subscription->id,
                            'plan_name' => $plan->name,
                            'badge' => $plan->badge,
                            'billing_cycle' => $billingCycle,
                            'amount' => $amount,
                            'started_at' => $subscription->started_at,
                            'expires_at' => $subscription->expires_at,
                            'status' => $subscription->status,
                        ],
                        'benefits' => [
                            'monthly_slots' => $plan->monthly_slots,
                            'coins_awarded' => $plan->coins_monthly,
                            'pdf_uploads_allowed' => $plan->allowed_pdf_uploads,
                            'new_coin_balance' => $user->coins,
                        ],
                        'payment' => [
                            'transaction_id' => $paymentTransaction->id,
                            'payment_id' => $request->razorpay_payment_id,
                            'method' => $payment['method'] ?? null,
                        ],
                    ],
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Subscription payment signature verification failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $request->razorpay_order_id,
                'payment_id' => $request->razorpay_payment_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Invalid signature.',
            ], 400);
        }
    }

    /**
     * Cancel subscription
     * POST /user/subscription/cancel
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSubscription(Request $request)
    {
        try {
            $user = Auth::user();

            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found',
                ], 404);
            }

            $subscription->update([
                'auto_renewal' => false,
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error('Cancel subscription failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription',
            ], 500);
        }
    }


    /**
     * Toggle auto-renewal
     * POST /user/subscription/toggle-renewal
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAutoRenewal(Request $request)
    {
        try {
            $user = Auth::user();

            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found',
                ], 404);
            }

            $subscription->update([
                'auto_renewal' => !$subscription->auto_renewal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Auto-renewal ' . ($subscription->auto_renewal ? 'enabled' : 'disabled'),
                'data' => [
                    'auto_renewal' => $subscription->auto_renewal,
                ],
            ], 200);
        } catch (Exception $e) {
            Log::error('Toggle auto-renewal failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle auto-renewal',
            ], 500);
        }
    }

    /**
     * Get plan features for display
     * 
     * @param SubscriptionPlan $plan
     * @return array
     */
    private function getPlanFeatures(SubscriptionPlan $plan): array
    {
        $features = [];

        // Monthly slots feature
        if ($plan->monthly_slots > 0) {
            $features[] = $plan->monthly_slots . ' listings per month';
        }

        // Coins feature
        if ($plan->coins_monthly > 0) {
            $features[] = $plan->coins_monthly . ' coins monthly';
        }

        // PDF upload feature
        if ($plan->allowed_pdf_uploads) {
            $features[] = 'PDF uploads allowed';
        }

        // Annual savings - 🔥 FIX: Safe division
        if ($plan->annual_price && $plan->monthly_price > 0) {
            $monthlyCost = $plan->monthly_price * 12;
            if ($monthlyCost > 0) {
                $savings = $monthlyCost - $plan->annual_price;
                if ($savings > 0) {
                    $features[] = 'Save ₹' . number_format($savings, 0) . ' annually';
                }
            }
        }

        // Priority support for premium plans
        if ($plan->monthly_price >= 500) {
            $features[] = 'Priority support';
        }

        // Badge feature
        if ($plan->badge && $plan->badge !== 'normal') {
            $features[] = ucfirst($plan->badge) . ' badge';
        }

        return $features;
    }
}
