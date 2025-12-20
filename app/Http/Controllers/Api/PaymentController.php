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
use Razorpay\Api\Api;

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
     * Process payment
     * POST /payment/process
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id', // Your existing marketplace order
            'notes' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Get the existing order
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if already has Razorpay order
            if ($order->razorpay_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already initiated for this order'
                ], 400);
            }

            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_SECRET'));

            // Amount in paise
            $amountInPaise = $order->total_amount * 100;

            $orderData = [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'order_' . $order->id . '_' . time(),
                'notes' => array_merge([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ], $request->notes ?? [])
            ];

            $razorpayOrder = $api->order->create($orderData);

            // Update the order with Razorpay order ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
                'payment_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $razorpayOrder['id'], // Razorpay order ID for checkout
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                    'key_id' => env('RAZORPAY_KEY_ID'),
                    'app_order_id' => $order->id // Your order ID for reference
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order'
            ], 500);
        }
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_SECRET'));

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            // Verify signature
            $api->utility->verifyPaymentSignature($attributes);

            DB::beginTransaction();

            // Find order
            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Fetch payment details from Razorpay
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            // Update order
            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'paid_at' => now()
            ]);

            // Create payment transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'payment_method_id' => null, // Razorpay handles this
                'order_id' => $order->id,
                'amount' => $order->total_amount,
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
                'description' => 'Payment for Order #' . $order->id,
                'processed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'order' => $order,
                    'transaction' => $transaction
                ]
            ]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            DB::rollback();
            Log::error('Signature verification failed: ' . $e->getMessage());

            // Mark as failed
            if (isset($order)) {
                $order->update(['payment_status' => 'failed']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed - Invalid signature'
            ], 400);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
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
     * Handle payment failure
     * POST /payment/failed
     */
    public function handlePaymentFailure(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'error_code' => 'nullable|string',
            'error_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('user_id', $user->id)
                ->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'failed'
                ]);

                // Create failed transaction record
                PaymentTransaction::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'currency' => 'INR',
                    'status' => 'failed',
                    'failure_reason' => $request->error_description ?? 'Payment failed',
                    'gateway_response' => json_encode($request->all()),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment failure recorded'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record payment failure: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment failure'
            ], 500);
        }
    }


    private function processRefundWithGateway(PaymentTransaction $transaction, float $amount): array
    {
        try {
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_SECRET'));

            // Amount in paise
            $amountInPaise = $amount * 100;

            $refund = $api->payment->fetch($transaction->gateway_transaction_id)
                ->refund(['amount' => $amountInPaise]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'response' => json_encode($refund->toArray())
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
