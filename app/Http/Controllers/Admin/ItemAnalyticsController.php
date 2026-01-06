<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemAnalyticsController extends Controller
{
    /**
     * Show items dashboard
     */
    public function index()
    {
        $stats = [
            'total_items' => Item::count(),
            'active_items' => Item::active()->count(),
            'sold_items' => Item::sold()->count(),
            'archived_items' => Item::archived()->count(),
            'promoted_items' => Item::promoted()->count(),
            'items_today' => Item::whereDate('created_at', today())->count(),
            'items_this_week' => Item::where('created_at', '>=', now()->startOfWeek())->count(),
            'items_this_month' => Item::whereMonth('created_at', now()->month)->count(),
            'total_value' => Item::active()->sum('price'),
        ];

        // Items by category
        $itemsByCategory = Category::withCount(['items' => function($query) {
            $query->active();
        }])
        ->having('items_count', '>', 0)
        ->orderBy('items_count', 'desc')
        ->limit(10)
        ->get();

        // Items by condition
        $itemsByCondition = Item::select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')
            ->get();

        // Recent items
        $recentItems = Item::with(['user', 'category', 'primaryImage'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top sellers
        $topSellers = User::withCount(['items as sold_count' => function($query) {
            $query->sold();
        }])
        ->having('sold_count', '>', 0)
        ->orderBy('sold_count', 'desc')
        ->limit(10)
        ->get();

        // Items growth (last 30 days)
        $itemsGrowth = Item::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.items.index', compact(
            'stats',
            'itemsByCategory',
            'itemsByCondition',
            'recentItems',
            'topSellers',
            'itemsGrowth'
        ));
    }

    /**
     * Show items list with filters
     */
    public function itemList(Request $request)
    {
        $query = Item::with(['user', 'category', 'primaryImage']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Promoted filter
        if ($request->filled('promoted')) {
            if ($request->promoted === 'yes') {
                $query->promoted();
            }
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

        $items = $query->paginate(30);
        $categories = Category::orderBy('name')->get();

        return view('admin.items.list', compact('items', 'categories'));
    }

    /**
     * Show item details
     */
    public function itemDetails($itemId)
    {
        $item = Item::with([
            'user',
            'category',
            'subCategory',
            'childSubCategory',
            'location',
            'images',
            'views',
            'wishlists'
        ])->findOrFail($itemId);

        // View statistics
        $viewStats = [
            'total_views' => $item->views()->count(),
            'unique_views' => $item->views()->distinct('user_id')->count('user_id'),
            'views_today' => $item->views()->whereDate('created_at', today())->count(),
            'views_this_week' => $item->views()->where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        // Wishlist statistics
        $wishlistStats = [
            'total_wishlists' => $item->wishlists()->count(),
            'wishlists_today' => $item->wishlists()->whereDate('created_at', today())->count(),
        ];

        // Similar items
        $similarItems = Item::where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->active()
            ->limit(5)
            ->get();

        return view('admin.items.details', compact(
            'item',
            'viewStats',
            'wishlistStats',
            'similarItems'
        ));
    }

    /**
     * Update item status
     */
    public function updateStatus(Request $request, $itemId)
    {
        $request->validate([
            'status' => 'required|in:active,sold,archived,inactive,reserved',
        ]);

        $item = Item::findOrFail($itemId);
        $oldStatus = $item->status;
        
        $item->update([
            'status' => $request->status,
            'is_sold' => $request->status === 'sold',
            'is_archived' => $request->status === 'archived',
            'sold_at' => $request->status === 'sold' ? now() : null,
            'archived_at' => $request->status === 'archived' ? now() : null,
        ]);

        // Log activity
        UserActivityLog::create([
            'user_id' => $item->user_id,
            'action' => 'item_status_changed',
            'action_type' => 'item',
            'description' => "Item status changed from {$oldStatus} to {$request->status} by admin",
            'metadata' => ['item_id' => $item->id, 'admin_id' => auth('admin')->id()],
        ]);

        return back()->with('success', 'Item status updated successfully.');
    }

    /**
     * Delete item
     */
    public function destroy($itemId)
    {
        $item = Item::findOrFail($itemId);
        $item->delete();

        return back()->with('success', 'Item deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,archive,delete',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
        ]);

        $items = Item::whereIn('id', $request->item_ids);

        switch ($request->action) {
            case 'activate':
                $items->update(['status' => 'active']);
                $message = 'Items activated successfully';
                break;
            case 'deactivate':
                $items->update(['status' => 'inactive']);
                $message = 'Items deactivated successfully';
                break;
            case 'archive':
                $items->update([
                    'status' => 'archived',
                    'is_archived' => true,
                    'archived_at' => now()
                ]);
                $message = 'Items archived successfully';
                break;
            case 'delete':
                $items->delete();
                $message = 'Items deleted successfully';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Export items
     */
    public function export(Request $request)
    {
        $items = Item::with(['user', 'category'])->get();

        $csv = "ID,Title,Seller,Category,Price,Condition,Status,Views,Wishlists,Created At\n";

        foreach ($items as $item) {
            $csv .= implode(',', [
                $item->id,
                '"' . str_replace('"', '""', $item->title) . '"',
                '"' . str_replace('"', '""', $item->user->name) . '"',
                '"' . str_replace('"', '""', $item->category_display) . '"',
                $item->price,
                $item->condition,
                $item->status,
                $item->views()->count(),
                $item->wishlists()->count(),
                $item->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="items_export_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
