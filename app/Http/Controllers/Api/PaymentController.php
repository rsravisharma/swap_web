<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\UserPaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Get available payment methods
     * GET /payment/methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $methods = PaymentMethod::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $methods
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch payment methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods'
            ], 500);
        }
    }

    /**
     * Get saved payment methods (user cards)
     * GET /payment/saved-cards
     */
    public function getSavedPaymentMethods(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cards = UserPaymentMethod::where('user_id', $user->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cards
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch saved cards: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch saved cards'
            ], 500);
        }
    }

    /**
     * Add new payment method
     * POST /payment/add-method
     */
    public function addPaymentMethod(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string|min:13|max:19',
            'card_holder_name' => 'required|string|max:255',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y') . '|max:' . (date('Y') + 20),
            'cvv' => 'required|string|min:3|max:4',
            'card_type' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string|max:500',
            'is_default' => 'boolean',
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

            // In production, integrate with payment gateway for tokenization
            // For now, we'll mask the card number and store basic info
            $cardNumber = $request->card_number;
            $lastFour = substr($cardNumber, -4);
            $cardType = $this->detectCardType($cardNumber);

            $cardData = [
                'user_id' => $user->id,
                'card_holder_name' => $request->card_holder_name,
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'expiry_month' => $request->expiry_month,
                'expiry_year' => $request->expiry_year,
                'billing_address' => $request->billing_address,
                'is_default' => $request->boolean('is_default'),
                // In production, store tokenized card info from payment gateway
                'token' => 'tok_' . uniqid(), // Placeholder token
            ];

            // If this is set as default, make others non-default
            if ($cardData['is_default']) {
                UserPaymentMethod::where('user_id', $user->id)
                    ->update(['is_default' => false]);
            }

            $card = UserPaymentMethod::create($cardData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $card,
                'message' => 'Payment method added successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to add payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment method'
            ], 500);
        }
    }

    /**
     * Update payment method
     * PUT /payment/methods/{cardId}
     */
    public function updatePaymentMethod(Request $request, string $cardId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_holder_name' => 'nullable|string|max:255',
            'expiry_month' => 'nullable|integer|between:1,12',
            'expiry_year' => 'nullable|integer|min:' . date('Y') . '|max:' . (date('Y') + 20),
            'billing_address' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $card = UserPaymentMethod::where('user_id', $user->id)
                ->where('id', $cardId)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found'
                ], 404);
            }

            DB::beginTransaction();

            $updateData = array_filter($request->only([
                'card_holder_name',
                'expiry_month', 
                'expiry_year',
                'billing_address',
                'is_default'
            ]));

            // If setting as default, make others non-default
            if (isset($updateData['is_default']) && $updateData['is_default']) {
                UserPaymentMethod::where('user_id', $user->id)
                    ->where('id', '!=', $cardId)
                    ->update(['is_default' => false]);
            }

            $card->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $card,
                'message' => 'Payment method updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method'
            ], 500);
        }
    }

    /**
     * Delete payment method
     * DELETE /payment/methods/{cardId}
     */
    public function deletePaymentMethod(string $cardId): JsonResponse
    {
        try {
            $user = Auth::user();
            $card = UserPaymentMethod::where('user_id', $user->id)
                ->where('id', $cardId)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found'
                ], 404);
            }

            // If deleting default card, make another card default if available
            if ($card->is_default) {
                $nextCard = UserPaymentMethod::where('user_id', $user->id)
                    ->where('id', '!=', $cardId)
                    ->first();
                
                if ($nextCard) {
                    $nextCard->update(['is_default' => true]);
                }
            }

            $card->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method'
            ], 500);
        }
    }

    /**
     * Process payment
     * POST /payment/process
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|integer|exists:user_payment_methods,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:255',
            'payment_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            // Verify payment method belongs to user
            $paymentMethod = UserPaymentMethod::where('user_id', $user->id)
                ->where('id', $request->payment_method_id)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment method'
                ], 400);
            }

            DB::beginTransaction();

            // In production, integrate with payment gateway here
            // For now, we'll simulate a successful payment
            $paymentResult = $this->processPaymentWithGateway($paymentMethod, $request->all());

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            // Create payment transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'USD',
                'status' => 'completed',
                'gateway_transaction_id' => $paymentResult['transaction_id'],
                'gateway_response' => $paymentResult['response'],
                'description' => $request->description,
                'processed_at' => now(),
            ]);

            // Update order status if applicable
            if ($request->order_id) {
                $order = Order::find($request->order_id);
                if ($order && $order->user_id === $user->id) {
                    $order->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Payment processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed'
            ], 500);
        }
    }

    /**
     * Get payment history
     * GET /payment/history
     */
    public function getPaymentHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = PaymentTransaction::where('user_id', $user->id)
                ->with(['order', 'paymentMethod']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $limit = $request->input('limit', 20);
            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch payment history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment history'
            ], 500);
        }
    }

    /**
     * Get payment details
     * GET /payment/{paymentId}
     */
    public function getPaymentDetails(string $paymentId): JsonResponse
    {
        try {
            $user = Auth::user();
            $transaction = PaymentTransaction::where('user_id', $user->id)
                ->where('id', $paymentId)
                ->with(['order', 'paymentMethod'])
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch payment details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment details'
            ], 500);
        }
    }

    /**
     * Refund payment
     * POST /payment/{paymentId}/refund
     */
    public function refundPayment(Request $request, string $paymentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $transaction = PaymentTransaction::where('user_id', $user->id)
                ->where('id', $paymentId)
                ->where('status', 'completed')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or not eligible for refund'
                ], 404);
            }

            $refundAmount = $request->amount ?? $transaction->amount;

            if ($refundAmount > $transaction->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund amount cannot exceed original payment amount'
                ], 400);
            }

            DB::beginTransaction();

            // Process refund with payment gateway
            $refundResult = $this->processRefundWithGateway($transaction, $refundAmount);

            if (!$refundResult['success']) {
                throw new \Exception($refundResult['message']);
            }

            // Update transaction status
            $transaction->update([
                'status' => $refundAmount == $transaction->amount ? 'refunded' : 'partially_refunded',
                'refund_amount' => ($transaction->refund_amount ?? 0) + $refundAmount,
                'refund_reason' => $request->reason,
                'refunded_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Refund processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Refund processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed'
            ], 500);
        }
    }

    /**
     * Validate card details
     * POST /payment/validate-card
     */
    public function validateCard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string|min:13|max:19',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|min:3|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cardNumber = preg_replace('/\D/', '', $request->card_number);
            
            // Basic validation
            $isValid = $this->validateLuhnAlgorithm($cardNumber) &&
                      $this->validateExpiryDate($request->expiry_month, $request->expiry_year) &&
                      $this->validateCVV($request->cvv);

            $cardType = $this->detectCardType($cardNumber);

            return response()->json([
                'success' => true,
                'valid' => $isValid,
                'card_type' => $cardType,
                'data' => [
                    'last_four' => substr($cardNumber, -4),
                    'card_type' => $cardType,
                    'is_valid' => $isValid,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Card validation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Card validation failed'
            ], 500);
        }
    }

    /**
     * Helper methods
     */
    private function detectCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $cardNumber)) {
            return 'MasterCard';
        } elseif (preg_match('/^3[47][0-9]{13}$/', $cardNumber)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cardNumber)) {
            return 'Discover';
        }
        
        return 'Unknown';
    }

    private function validateLuhnAlgorithm(string $cardNumber): bool
    {
        $sum = 0;
        $numDigits = strlen($cardNumber);
        $oddEven = $numDigits & 1;

        for ($count = 0; $count < $numDigits; $count++) {
            $digit = (int) $cardNumber[$count];

            if (!(($count & 1) ^ $oddEven)) {
                $digit *= 2;
            }
            if ($digit > 9) {
                $digit -= 9;
            }

            $sum += $digit;
        }

        return ($sum % 10) == 0;
    }

    private function validateExpiryDate(int $month, int $year): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        return !($year < $currentYear || ($year == $currentYear && $month < $currentMonth));
    }

    private function validateCVV(string $cvv): bool
    {
        return preg_match('/^[0-9]{3,4}$/', $cvv);
    }

    private function processPaymentWithGateway(UserPaymentMethod $paymentMethod, array $paymentData): array
    {
        // Simulate payment gateway integration
        // In production, integrate with Stripe, PayPal, Razorpay, etc.
        
        return [
            'success' => true,
            'transaction_id' => 'txn_' . uniqid(),
            'response' => json_encode([
                'status' => 'success',
                'gateway' => 'simulation',
                'timestamp' => now()->toISOString(),
            ])
        ];
    }

    private function processRefundWithGateway(PaymentTransaction $transaction, float $amount): array
    {
        // Simulate refund processing with payment gateway
        // In production, integrate with actual payment gateway refund API
        
        return [
            'success' => true,
            'refund_id' => 'ref_' . uniqid(),
            'response' => json_encode([
                'status' => 'success',
                'refund_amount' => $amount,
                'gateway' => 'simulation',
                'timestamp' => now()->toISOString(),
            ])
        ];
    }
}
