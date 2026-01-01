<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\UserPaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Order;
use App\Services\RazorpayService;
use App\Models\PdfBook;
use App\Models\PdfBookPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

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
        // 🔥 This validates order_id, NOT book_id
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id', // ✅ Correct
            'amount' => 'nullable|numeric|min:1',
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

            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if ($order->razorpay_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already initiated for this order'
                ], 400);
            }

            $paymentAmount = $request->input('amount') ?? $order->total_amount;

            if ($paymentAmount > $order->total_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed order total'
                ], 400);
            }

            Log::info('Creating Razorpay order', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'original_amount' => $order->total_amount,
                'payment_amount' => $paymentAmount,
                'notes' => $request->notes
            ]);

            $amountInPaise = (int) ($paymentAmount * 100);

            $orderData = [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'order_' . $order->id . '_' . time(),
                'notes' => array_merge([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'order_type' => $order->order_type,
                    'original_amount' => $order->total_amount,
                    'payment_amount' => $paymentAmount,
                ], $request->notes ?? [])
            ];

            $razorpayOrder = $this->razorpayService->createOrder($orderData);

            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
                'payment_status' => 'pending'
            ]);

            Log::info('Razorpay order created successfully', [
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount_in_paise' => $amountInPaise,
                'order_type' => $order->order_type
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $razorpayOrder['id'],
                    'amount' => $razorpayOrder['amount'],
                    'currency' => $razorpayOrder['currency'],
                    'key_id' => config('services.razorpay.key'),
                    'app_order_id' => $order->id
                ]
            ]);
        } catch (\Razorpay\Api\Errors\Error $e) {
            Log::error('Razorpay API Error', [
                'order_id' => $request->order_id,
                'error_class' => get_class($e),
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'order_id' => $request->order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            // 🔥 Use RazorpayService instead of new Api()
            $this->razorpayService->verifyPaymentSignature($attributes);

            DB::beginTransaction();

            // Find order
            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // 🔥 Fetch payment details using service
            $payment = $this->razorpayService->fetchPayment($request->razorpay_payment_id);

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
                'payment_method_id' => null,
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

            $bookPurchase = null;

            if ($order->order_type === 'pdf_book' && $order->pdf_book_id) {
                $book = PdfBook::find($order->pdf_book_id);

                if ($book) {
                    $bookPurchase = PdfBookPurchase::create([
                        'user_id' => $user->id,
                        'seller_id' => $book->seller_id,
                        'book_id' => $book->id,
                        'order_id' => $order->id,
                        'payment_transaction_id' => $transaction->id,
                        'purchase_price' => $book->price,
                        'download_token' => \Illuminate\Support\Str::random(64),
                        'download_count' => 0,
                        'max_downloads' => 5,
                        'status' => 'active',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'order' => $order,
                    'transaction' => $transaction,
                    'book_purchase' => $bookPurchase
                ]
            ]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            DB::rollback();
            Log::error('Signature verification failed', [
                'order_id' => $request->razorpay_order_id,
                'payment_id' => $request->razorpay_payment_id,
                'error' => $e->getMessage()
            ]);

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
            Log::error('Payment verification error', [
                'order_id' => $request->razorpay_order_id ?? null,
                'payment_id' => $request->razorpay_payment_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            $amountInPaise = (int) ($amount * 100);

            $refund = $this->razorpayService->refundPayment(
                $transaction->gateway_transaction_id,
                $amountInPaise
            );

            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'response' => json_encode($refund)
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed', [
                'transaction_id' => $transaction->id,
                'payment_id' => $transaction->gateway_transaction_id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
