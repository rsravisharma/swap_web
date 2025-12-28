<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PdfBook;
use App\Models\PdfBookPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PdfBookController extends Controller
{
    /**
     * Get all available PDF books
     * GET /api/pdf-books
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PdfBook::available()
                ->with('seller:id,name,email');

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            }

            if ($request->filled('language')) {
                $query->where('language', $request->language);
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            $limit = $request->input('limit', 20);
            $books = $query->orderBy('created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $books->items(),
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                    'per_page' => $books->perPage(),
                    'total' => $books->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch PDF books: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch PDF books'
            ], 500);
        }
    }

    /**
     * Get single PDF book details
     * GET /api/pdf-books/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $book = PdfBook::with('seller:id,name,email')
                ->findOrFail($id);

            if (!$book->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is not available'
                ], 404);
            }

            $user = Auth::user();
            $isPurchased = $user ? $book->isPurchasedBy($user->id) : false;

            return response()->json([
                'success' => true,
                'data' => array_merge($book->toArray(), [
                    'is_purchased' => $isPurchased
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch PDF book details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }
    }

    /**
     * Get user's purchased books
     * GET /api/pdf-books/my-purchases
     */
    public function myPurchases(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = PdfBookPurchase::where('user_id', $user->id)
                ->with(['book', 'seller:id,name', 'order'])
                ->where('status', 'active');

            $limit = $request->input('limit', 20);
            $purchases = $query->orderBy('created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $purchases->items(),
                'pagination' => [
                    'current_page' => $purchases->currentPage(),
                    'last_page' => $purchases->lastPage(),
                    'per_page' => $purchases->perPage(),
                    'total' => $purchases->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch purchased books: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchased books'
            ], 500);
        }
    }

    /**
     * Get seller's books
     * GET /api/pdf-books/my-books
     */
    public function myBooks(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = PdfBook::where('seller_id', $user->id)
                ->withCount('purchases');

            $limit = $request->input('limit', 20);
            $books = $query->orderBy('created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $books->items(),
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                    'per_page' => $books->perPage(),
                    'total' => $books->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch seller books: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch seller books'
            ], 500);
        }
    }

    /**
     * Deliver PDF book after payment verification
     * POST /api/pdf-books/deliver
     */
    public function deliverBook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer|exists:pdf_books,id',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            // Verify the purchase exists and belongs to user
            $purchase = PdfBookPurchase::where('user_id', $user->id)
                ->where('book_id', $request->book_id)
                ->where('order_id', $request->order_id)
                ->where('status', 'active')
                ->with('book')
                ->first();

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book purchase not found or invalid'
                ], 404);
            }

            // Check if user can download
            if (!$purchase->canDownload()) {
                $reason = $purchase->download_count >= $purchase->max_downloads 
                    ? 'Download limit reached' 
                    : 'Access expired or revoked';
                
                return response()->json([
                    'success' => false,
                    'message' => $reason
                ], 403);
            }

            $book = $purchase->book;
            
            // Generate direct download link
            $downloadLink = $book->getDirectDownloadLink();

            // Increment download count
            $purchase->incrementDownloadCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadLink,
                    'preview_url' => $book->getPreviewLink(),
                    'download_token' => $purchase->download_token,
                    'downloads_remaining' => $purchase->max_downloads - $purchase->download_count,
                    'expires_at' => $purchase->access_expires_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to deliver book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to deliver book'
            ], 500);
        }
    }

    /**
     * Get download link using download token
     * GET /api/pdf-books/download/{token}
     */
    public function downloadByToken(string $token): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $purchase = PdfBookPurchase::where('download_token', $token)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->with('book')
                ->first();

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid download token'
                ], 404);
            }

            if (!$purchase->canDownload()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Download limit reached or access expired'
                ], 403);
            }

            $book = $purchase->book;
            $downloadLink = $book->getDirectDownloadLink();
            
            $purchase->incrementDownloadCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadLink,
                    'book_title' => $book->title,
                    'downloads_remaining' => $purchase->max_downloads - $purchase->download_count,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to download by token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process download'
            ], 500);
        }
    }
}
