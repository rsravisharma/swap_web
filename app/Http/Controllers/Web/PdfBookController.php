<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PdfBook;
use App\Models\PdfCategory;
use App\Models\PdfBookPurchase;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class PdfBookController extends Controller
{
    private $razorpayApi;

    public function __construct()
    {
        $this->razorpayApi = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    public function category(PdfCategory $pdfCategory)
    {
        $books = PdfBook::with('category', 'seller')
            ->where('category_id', $pdfCategory->id)
            ->available()
            ->paginate(16);

        $breadcrumb = $pdfCategory->breadcrumb;

        return view('frontend.pdf-books.category', compact('books', 'pdfCategory', 'breadcrumb'));
    }

    /**
     * Display all categories (category listing page)
     */
    public function categories()
    {
        $mainCategories = PdfCategory::mainCategories()
            ->with(['children' => function ($query) {
                $query->with('children');
            }])
            ->get();

        return view('frontend.pdf-books.categories', compact('mainCategories'));
    }


    public function index(Request $request)
    {
        $query = PdfBook::with(['category', 'seller']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // ✅ NEW: Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by language
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('original_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('original_price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $books = $query->paginate(12);
        $languages = PdfBook::available()->distinct()->pluck('language')->filter();

        // ✅ NEW: Pass categories to index view
        $categories = PdfCategory::mainCategories()
            ->with(['children' => function ($q) {
                $q->with('children');
            }])
            ->withCount(['pdfBooks' => function ($q) {
                $q->available();
            }])
            ->get();

        return view('frontend.pdf-books.index', compact('books', 'languages', 'categories'));
    }

    public function show(PdfBook $pdfBook)
    {
        if (!$pdfBook->is_available) {
            abort(404, 'This book is not available');
        }

        $pdfBook->load('seller');

        $hasPurchased = false;
        $userPurchase = null;

        if (Auth::check()) {
            $userPurchase = PdfBookPurchase::where('user_id', Auth::id())
                ->where('pdf_book_id', $pdfBook->id)
                ->where('status', 'active')
                ->first();
            $hasPurchased = $userPurchase !== null;
        }

        // Similar books
        $similarBooks = PdfBook::available()
            ->where('id', '!=', $pdfBook->id)
            ->where(function ($query) use ($pdfBook) {
                $query->where('author', $pdfBook->author)
                    ->orWhere('publisher', $pdfBook->publisher)
                    ->orWhere('language', $pdfBook->language);
            })
            ->limit(4)
            ->get();

        return view('frontend.pdf-books.show', compact('pdfBook', 'hasPurchased', 'userPurchase', 'similarBooks'));
    }

    public function initiatePayment(Request $request, PdfBook $pdfBook)
    {
        Log::info('Payment initiation started', [
            'pdf_book_id' => $pdfBook->id,
            'user_id' => Auth::id()
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to purchase',
                'redirect' => route('user.login')
            ], 401);
        }

        // Check if already purchased
        $existingPurchase = PdfBookPurchase::where('user_id', Auth::id())
            ->where('pdf_book_id', $pdfBook->id)
            ->where('status', 'active')
            ->first();

        if ($existingPurchase) {
            return response()->json([
                'success' => false,
                'message' => 'You have already purchased this book'
            ], 400);
        }

        try {
            Log::info('Creating Razorpay order', [
                'amount' => $pdfBook->original_price,
                'book_id' => $pdfBook->id
            ]);

            $amount = $pdfBook->original_price * 100; // Convert to paisa

            // Check if Razorpay credentials exist
            if (!config('services.razorpay.key') || !config('services.razorpay.secret')) {
                Log::error('Razorpay credentials missing');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured. Please contact support.'
                ], 500);
            }

            // Create Razorpay Order
            $razorpayOrder = $this->razorpayApi->order->create([
                'receipt' => 'pdf_book_' . $pdfBook->id . '_' . time(),
                'amount' => $amount,
                'currency' => config('razorpay.currency', 'INR'),
                'payment_capture' => 1
            ]);

            Log::info('Razorpay order created', [
                'razorpay_order_id' => $razorpayOrder->id
            ]);

            // Create Order in database
            $order = Order::create([
                'user_id' => Auth::id(),
                'pdf_book_id' => $pdfBook->id,
                'order_type' => 'pdf_book',
                'total_amount' => $pdfBook->original_price,
                'payment_status' => 'pending',
                'razorpay_order_id' => $razorpayOrder->id,
                'status' => Order::STATUS_PENDING
            ]);

            Log::info('Database order created', [
                'order_id' => $order->id
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder->id,
                'amount' => $amount,
                'currency' => config('razorpay.currency', 'INR'),
                'key' => config('services.razorpay.key'),
                'name' => config('app.name'),
                'description' => $pdfBook->title,
                'image' => $pdfBook->cover_image_url,
                'prefill' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'contact' => Auth::user()->phone ?? ''
                ],
                'db_order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pdf_book_id' => $pdfBook->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.'
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        Log::info('Payment verification started', $request->all()); // ✅ Debug log

        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string'
        ]);

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            $this->razorpayApi->utility->verifyPaymentSignature($attributes);

            DB::beginTransaction();

            // ✅ Find order by razorpay_order_id (RELIABLE)
            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            Log::info('Order found for verification', ['order_id' => $order->id]);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'paid_at' => now()
            ]);

            $pdfBook = PdfBook::findOrFail($order->pdf_book_id);

            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'currency' => 'INR',
                'status' => 'completed',
                'gateway_transaction_id' => $request->razorpay_payment_id,
                'gateway_response' => json_encode($request->all()),
                'description' => 'Payment for PDF Book: ' . $pdfBook->title,
                'processed_at' => now()
            ]);

            $purchase = PdfBookPurchase::create([
                'user_id' => $order->user_id,
                'seller_id' => $pdfBook->seller_id,
                'pdf_book_id' => $pdfBook->id,
                'order_id' => $order->id,
                'payment_transaction_id' => $paymentTransaction->id,
                'purchase_price' => $pdfBook->original_price,
                'download_token' => \Illuminate\Support\Str::random(64),
                'download_count' => 0,
                'max_downloads' => 5,
                'access_expires_at' => now()->addYear(),
                'status' => 'active'
            ]);

            DB::commit();

            Log::info('Payment COMPLETELY successful', [
                'purchase_id' => $purchase->id,
                'pdf_book_id' => $pdfBook->id,
                'redirect_url' => route('pdf-books.my-library')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Book added to your library.',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'book_id' => $pdfBook->id,
                    'redirect' => route('pdf-books.my-library') // ✅ SERVER-SIDE REDIRECT
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment verification FAILED', [
                'error' => $e->getMessage(),
                'razorpay_order_id' => $request->razorpay_order_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myLibrary()
    {
        $purchases = PdfBookPurchase::with(['pdfBook', 'order'])
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('frontend.pdf-books.my-library', compact('purchases'));
    }

    public function download(PdfBookPurchase $purchase)
    {
        if ($purchase->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        if (!$purchase->canDownload()) {
            return back()->with('error', 'Download limit exceeded or access expired');
        }

        // Log download activity
        Log::info('PDF Book downloaded', [
            'user_id' => Auth::id(),
            'pdf_book_id' => $purchase->pdf_book_id,
            'purchase_id' => $purchase->id,
            'download_count' => $purchase->download_count + 1
        ]);

        $purchase->incrementDownloadCount();

        return redirect($purchase->pdfBook->getDirectDownloadLink());
    }
}
