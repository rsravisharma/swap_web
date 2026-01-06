<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfBook;
use App\Models\PdfBookPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PdfBookAnalyticsController extends Controller
{
    /**
     * Show PDF books dashboard
     */
    public function index()
    {
        $stats = [
            'total_books' => PdfBook::count(),
            'available_books' => PdfBook::available()->count(),
            'total_purchases' => PdfBookPurchase::count(),
            'active_purchases' => PdfBookPurchase::where('status', 'active')->count(),
            'total_revenue' => PdfBookPurchase::sum('purchase_price'),
            'books_today' => PdfBook::whereDate('created_at', today())->count(),
            'purchases_today' => PdfBookPurchase::whereDate('created_at', today())->count(),
            'revenue_today' => PdfBookPurchase::whereDate('created_at', today())->sum('purchase_price'),
            'total_downloads' => PdfBookPurchase::sum('download_count'),
            'avg_book_price' => PdfBook::avg('price'),
        ];

        // Top selling books
        $topSellingBooks = PdfBook::withCount('purchases')
            ->having('purchases_count', '>', 0)
            ->orderBy('purchases_count', 'desc')
            ->limit(10)
            ->get();

        // Top sellers
        $topSellers = User::withCount(['pdfBooksForSale as books_count', 'pdfBookSales as sales_count'])
            ->having('sales_count', '>', 0)
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();

        // Recent books
        $recentBooks = PdfBook::with(['seller'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent purchases
        $recentPurchases = PdfBookPurchase::with(['user', 'pdfBook', 'seller'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Books growth (last 30 days)
        $booksGrowth = PdfBook::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue growth (last 30 days)
        $revenueGrowth = PdfBookPurchase::selectRaw('DATE(created_at) as date, SUM(purchase_price) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.pdf-books.index', compact(
            'stats',
            'topSellingBooks',
            'topSellers',
            'recentBooks',
            'recentPurchases',
            'booksGrowth',
            'revenueGrowth'
        ));
    }

    /**
     * Show books list with filters
     */
    /**
 * Show books list with filters
 */
public function booksList(Request $request)
{
    $query = PdfBook::with(['seller'])
        ->withCount('purchases'); 

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('author', 'LIKE', "%{$search}%")
              ->orWhere('isbn', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    // Availability filter
    if ($request->filled('availability')) {
        $query->where('is_available', $request->availability === 'available');
    }

    // Seller filter
    if ($request->filled('seller_id')) {
        $query->where('seller_id', $request->seller_id);
    }

    // Price range
    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }
    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    // Language filter
    if ($request->filled('language')) {
        $query->where('language', $request->language);
    }

    // Date range
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    // Sorting
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    // Paginate with 24 items per page (better for grid layout)
    $perPage = $request->get('per_page', 24);
    $books = $query->paginate($perPage)->withQueryString();
    
    $sellers = User::has('pdfBooksForSale')->orderBy('name')->get();

    return view('admin.pdf-books.list', compact('books', 'sellers'));
}


    /**
 * Show book details
 */
public function bookDetails($bookId)
{
    $book = PdfBook::with([
        'seller' => function($query) {
            $query->withCount(['pdfBooksForSale', 'pdfBookSales']);
        },
        'purchases.user'
    ])->findOrFail($bookId);

    // Purchase statistics
    $purchaseStats = [
        'total_purchases' => $book->purchases()->count(),
        'active_purchases' => $book->purchases()->where('status', 'active')->count(),
        'total_revenue' => $book->purchases()->sum('purchase_price'),
        'total_downloads' => $book->purchases()->sum('download_count'),
        'avg_downloads' => $book->purchases()->avg('download_count'),
        'purchases_today' => $book->purchases()->whereDate('created_at', today())->count(),
        'purchases_this_week' => $book->purchases()->where('created_at', '>=', now()->startOfWeek())->count(),
        'purchases_this_month' => $book->purchases()->whereMonth('created_at', now()->month)->count(),
    ];

    // Recent purchases
    $recentPurchases = $book->purchases()
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    // Purchase trend (last 30 days)
    $purchaseTrend = $book->purchases()
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(purchase_price) as revenue')
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    return view('admin.pdf-books.details', compact(
        'book',
        'purchaseStats',
        'recentPurchases',
        'purchaseTrend'
    ));
}


    /**
     * Show purchases list
     */
    public function purchasesList(Request $request)
    {
        $query = PdfBookPurchase::with(['user', 'seller', 'pdfBook']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhereHas('pdfBook', function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $purchases = $query->paginate(30);

        return view('admin.pdf-books.purchases', compact('purchases'));
    }

    /**
     * Update book availability
     */
    public function updateAvailability(Request $request, $bookId)
    {
        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $book = PdfBook::findOrFail($bookId);
        $book->update(['is_available' => $request->is_available]);

        return back()->with('success', 'Book availability updated successfully.');
    }

    /**
     * Delete book
     */
    public function destroy($bookId)
    {
        $book = PdfBook::findOrFail($bookId);
        $book->delete();

        return back()->with('success', 'Book deleted successfully.');
    }

    /**
     * Revoke purchase access
     */
    public function revokePurchase($purchaseId)
    {
        $purchase = PdfBookPurchase::findOrFail($purchaseId);
        $purchase->update(['status' => 'revoked']);

        return back()->with('success', 'Purchase access revoked successfully.');
    }

    /**
     * Extend purchase access
     */
    public function extendPurchase(Request $request, $purchaseId)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $purchase = PdfBookPurchase::findOrFail($purchaseId);
        
        $newExpiry = $purchase->access_expires_at 
            ? $purchase->access_expires_at->addDays($request->days)
            : now()->addDays($request->days);

        $purchase->update([
            'access_expires_at' => $newExpiry,
            'status' => 'active'
        ]);

        return back()->with('success', "Purchase access extended by {$request->days} days.");
    }

    /**
     * Export books data
     */
    public function exportBooks(Request $request)
    {
        $books = PdfBook::with(['seller'])->get();

        $csv = "ID,Title,Author,ISBN,Price,Seller,Sales,Revenue,Available,Created At\n";

        foreach ($books as $book) {
            $salesCount = $book->purchases()->count();
            $revenue = $book->purchases()->sum('purchase_price');

            $csv .= implode(',', [
                $book->id,
                '"' . str_replace('"', '""', $book->title) . '"',
                '"' . str_replace('"', '""', $book->author ?? 'N/A') . '"',
                $book->isbn ?? 'N/A',
                $book->price,
                '"' . str_replace('"', '""', $book->seller->name) . '"',
                $salesCount,
                $revenue,
                $book->is_available ? 'Yes' : 'No',
                $book->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pdf_books_export_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Export purchases data
     */
    public function exportPurchases(Request $request)
    {
        $purchases = PdfBookPurchase::with(['user', 'seller', 'pdfBook'])->get();

        $csv = "ID,Book Title,Buyer,Seller,Price,Downloads,Status,Purchased At\n";

        foreach ($purchases as $purchase) {
            $csv .= implode(',', [
                $purchase->id,
                '"' . str_replace('"', '""', $purchase->pdfBook->title) . '"',
                '"' . str_replace('"', '""', $purchase->user->name) . '"',
                '"' . str_replace('"', '""', $purchase->seller->name) . '"',
                $purchase->purchase_price,
                $purchase->download_count,
                $purchase->status,
                $purchase->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pdf_purchases_export_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
