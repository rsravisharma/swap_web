<?php

namespace App\Http\Controllers\Api;

use App\Models\UserHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class HistoryController extends Controller
{
    /**
     * Get user history with filtering and pagination
     * GET /user/history
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'category' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'search' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $query = UserHistory::where('user_id', $user->id);

            // Apply category filter
            if ($request->filled('category') && $request->category !== 'All') {
                $query->where('category', $request->category);
            }

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(action) LIKE ?', ['%' . strtolower($search) . '%']);
                });
            }

            $limit = $request->input('limit', 20);
            $page = $request->input('page', 1);

            $histories = $query->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $histories->items(),
                'pagination' => [
                    'current_page' => $histories->currentPage(),
                    'last_page' => $histories->lastPage(),
                    'per_page' => $histories->perPage(),
                    'total' => $histories->total(),
                    'has_more' => $histories->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single history item
     * GET /user/history/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $history = UserHistory::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$history) {
                return response()->json([
                    'success' => false,
                    'message' => 'History item not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new history item
     * POST /user/history
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:50',
            'action' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'details' => 'nullable|array',
            'related_id' => 'nullable|integer',
            'related_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            $history = UserHistory::create([
                'user_id' => $user->id,
                'type' => $request->type,
                'action' => $request->action,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'details' => $request->details,
                'related_id' => $request->related_id,
                'related_type' => $request->related_type,
            ]);

            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => 'History item added successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add history item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete single history item
     * DELETE /user/history/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $history = UserHistory::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$history) {
                return response()->json([
                    'success' => false,
                    'message' => 'History item not found'
                ], 404);
            }

            $history->delete();

            return response()->json([
                'success' => true,
                'message' => 'History item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete history item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all user history
     * DELETE /user/history
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $deletedCount = UserHistory::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'History cleared successfully',
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get history statistics
     * GET /user/history/stats
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $stats = [
                'total_items' => UserHistory::where('user_id', $user->id)->count(),
                'categories' => UserHistory::where('user_id', $user->id)
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->selectRaw('category, count(*) as count')
                    ->pluck('count', 'category')
                    ->toArray(),
                'types' => UserHistory::where('user_id', $user->id)
                    ->groupBy('type')
                    ->selectRaw('type, count(*) as count')
                    ->pluck('count', 'type')
                    ->toArray(),
                'recent_activity' => UserHistory::where('user_id', $user->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available categories
     * GET /user/history/categories
     */
    public function getCategories(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $categories = UserHistory::where('user_id', $user->id)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => array_merge(['All'], $categories)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete history items
     * DELETE /user/history/bulk
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:user_histories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            $deletedCount = UserHistory::where('user_id', $user->id)
                ->whereIn('id', $request->ids)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} history items",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete history items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}