<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\SearchHistory;
use App\Models\PopularSearch;
use App\Models\TrendingSearch;
use App\Models\QuickSuggestion;
use App\Models\Location;
use App\Models\University;
use App\Models\Favorite;
use App\Models\UserFollow;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Global search with comprehensive results
     * GET /search/all
     */
    public function getAllSearchResults(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1',
            'type' => 'sometimes|string|in:all,items,users',
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('query');
            $type = $request->input('type', 'all');
            $limit = $request->input('limit', 20);

            $results = [];

            if ($type === 'all' || $type === 'items') {
                $items = Product::where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->where('status', 'active')
                    ->with(['user:id,name,profile_image', 'images'])
                    ->limit($limit)
                    ->get();

                $results['items'] = $items;
            }

            if ($type === 'all' || $type === 'users') {
                $users = User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->with('profile')
                    ->limit($limit)
                    ->get();

                $results['users'] = $users;
            }

            // Track search
            $this->trackSearch($query, 'all');

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items/products
     * GET /search/items
     */
    public function searchItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1',
            'category' => 'sometimes|string',
            'location' => 'sometimes|string',
            'university' => 'sometimes|string',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'condition' => 'sometimes|string',
            'sort' => 'sometimes|string|in:newest,oldest,price_low,price_high,relevance',
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 20);

            $items = Product::where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%");
                });

            // Apply filters
            if ($request->filled('category')) {
                $items->where('category', $request->category);
            }

            if ($request->filled('location')) {
                $items->where('location', $request->location);
            }

            if ($request->filled('university')) {
                $items->whereHas('user.profile', function ($q) use ($request) {
                    $q->where('university', $request->university);
                });
            }

            if ($request->filled('min_price')) {
                $items->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $items->where('price', '<=', $request->max_price);
            }

            if ($request->filled('condition')) {
                $items->where('condition', $request->condition);
            }

            // Apply sorting
            switch ($request->input('sort', 'relevance')) {
                case 'newest':
                    $items->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $items->orderBy('created_at', 'asc');
                    break;
                case 'price_low':
                    $items->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $items->orderBy('price', 'desc');
                    break;
                default:
                    $items->orderBy('created_at', 'desc');
            }

            $results = $items->with(['user:id,name,profile_image', 'images'])
                ->limit($limit)
                ->get();

            // Add favorite status for authenticated users
            if (Auth::check()) {
                $userId = Auth::id();
                $favoriteIds = Favorite::where('user_id', $userId)
                    ->pluck('item_id')
                    ->toArray();

                $results->each(function ($item) use ($favoriteIds) {
                    $item->is_favorited = in_array($item->id, $favoriteIds);
                });
            }

            // Track search
            $this->trackSearch($query, 'items');

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users
     * GET /search/users
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1',
            'university' => 'sometimes|string',
            'location' => 'sometimes|string',
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 20);

            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");

            // Apply filters
            if ($request->filled('university')) {
                $users->whereHas('profile', function ($q) use ($request) {
                    $q->where('university', $request->university);
                });
            }

            if ($request->filled('location')) {
                $users->whereHas('profile', function ($q) use ($request) {
                    $q->where('location', $request->location);
                });
            }

            $results = $users->with('profile')
                ->limit($limit)
                ->get();

            // Add follow status for authenticated users
            if (Auth::check()) {
                $userId = Auth::id();
                $followingIds = UserFollow::where('follower_id', $userId)
                    ->pluck('followed_id')
                    ->toArray();

                $results->each(function ($user) use ($followingIds) {
                    $user->is_following = in_array($user->id, $followingIds);
                });
            }

            // Track search
            $this->trackSearch($query, 'users');

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filtered products
     * POST /search/filtered
     */
    public function getFilteredProducts(Request $request): JsonResponse
    {
        try {
            $query = Product::where('status', 'active');

            // Apply all possible filters
            if ($request->filled('query')) {
                $searchQuery = $request->input('query');
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('subcategory')) {
                $query->where('subcategory', $request->subcategory);
            }

            if ($request->filled('condition')) {
                $query->where('condition', $request->condition);
            }

            if ($request->filled('location')) {
                $query->where('location', $request->location);
            }

            if ($request->filled('university')) {
                $query->whereHas('user.profile', function ($q) use ($request) {
                    $q->where('university', $request->university);
                });
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->filled('sort')) {
                switch ($request->sort) {
                    case 'price_low':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_high':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $limit = $request->input('limit', 20);
            $results = $query->with(['user:id,name,profile_image', 'images'])
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Filtered search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories with product counts
     * GET /search/categories
     */
    public function getCategoriesWithCounts(): JsonResponse
    {
        try {
            $categories = Category::withCount(['products' => function ($query) {
                $query->where('status', 'active');
            }])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
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
     * Get subcategories for a category
     * GET /search/categories/{categoryId}/subcategories
     */
    public function getSubCategories(string $categoryId): JsonResponse
    {
        try {
            $subcategories = Category::where('parent_id', $categoryId)
                ->withCount(['products' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available locations
     * GET /search/filters/locations
     */
    public function getAvailableLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('search_locations', 3600, function () {
                return Location::orderBy('name')->pluck('name')->toArray();
            });

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available universities
     * GET /search/filters/universities
     */
    public function getAvailableUniversities(): JsonResponse
    {
        try {
            $universities = Cache::remember('search_universities', 3600, function () {
                return University::orderBy('name')->pluck('name')->toArray();
            });

            return response()->json([
                'success' => true,
                'data' => $universities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch universities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions
     * GET /search/suggestions
     */
    public function getSearchSuggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('query');

            $suggestions = PopularSearch::where('term', 'like', "%{$query}%")
                ->orderBy('hits', 'desc')
                ->limit(10)
                ->pluck('term')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quick suggestions
     * GET /search/quick-suggestions
     */
    public function getQuickSuggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1',
            'limit' => 'sometimes|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 10);

            $suggestions = QuickSuggestion::where('term', 'like', "%{$query}%")
                ->limit($limit)
                ->pluck('term')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quick suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending searches
     * GET /search/trending
     */
    public function getTrendingSearches(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'week'); // today, week, month
            $limit = min($request->get('limit', 10), 50); // Max 50 results

            $cacheKey = "trending_searches_{$period}_{$limit}";

            $trending = Cache::remember($cacheKey, 900, function () use ($period, $limit) {
                switch ($period) {
                    case 'today':
                        return TrendingSearch::getTodaysTrending($limit);
                    case 'month':
                        return TrendingSearch::getMonthlyTrending($limit);
                    case 'week':
                    default:
                        return TrendingSearch::getWeeklyTrending($limit);
                }
            });

            return response()->json([
                'success' => true,
                'data' => $trending,
                'period' => $period,
                'count' => count($trending)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching trending searches: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending searches',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function recordSearch(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'required|string|max:255'
        ]);

        try {
            TrendingSearch::recordSearch($request->term);

            return response()->json([
                'success' => true,
                'message' => 'Search recorded'
            ]);
        } catch (\Exception $e) {
            // Don't fail the search if recording fails
            \Log::warning('Failed to record search term: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'message' => 'Search completed'
            ]);
        }
    }

    /**
     * Get popular searches
     * GET /search/popular
     */
    public function getPopularSearches(): JsonResponse
    {
        try {
            $popular = Cache::remember('popular_searches', 1800, function () {
                return PopularSearch::orderBy('hits', 'desc')
                    ->limit(10)
                    ->pluck('term')
                    ->toArray();
            });

            return response()->json([
                'success' => true,
                'data' => $popular
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular searches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent searches
     * GET /search/history/recent
     */
    public function getRecentSearches(): JsonResponse
    {
        try {
            $user = Auth::user();

            $recent = SearchHistory::where('user_id', $user->id)
                ->where('type', 'item')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->pluck('query')
                ->unique()
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $recent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent searches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save search history
     * POST /search/history/save
     */
    public function saveSearchHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'searches' => 'required|array',
            'searches.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Clear existing history
            SearchHistory::where('user_id', $user->id)->delete();

            // Save new history
            foreach ($request->searches as $search) {
                SearchHistory::create([
                    'user_id' => $user->id,
                    'query' => $search,
                    'type' => 'item'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Search history saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save search history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear search history
     * DELETE /search/history/clear
     */
    public function clearSearchHistory(): JsonResponse
    {
        try {
            $user = Auth::user();

            SearchHistory::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Search history cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear search history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorite
     * POST /search/favorites/toggle
     */
    public function toggleFavorite(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:items,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $itemId = $request->item_id;

            $favorite = Favorite::where('user_id', $user->id)
                ->where('item_id', $itemId)
                ->first();

            if ($favorite) {
                $favorite->delete();
                $message = 'Removed from favorites';
                $isFavorited = false;
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'item_id' => $itemId
                ]);
                $message = 'Added to favorites';
                $isFavorited = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_favorited' => $isFavorited
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle follow user
     * POST /search/users/follow/toggle
     */
    public function toggleFollowUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $followUserId = $request->user_id;

            if ($user->id == $followUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot follow yourself'
                ], 400);
            }

            $follow = UserFollow::where('follower_id', $user->id)
                ->where('followed_id', $followUserId)
                ->first();

            if ($follow) {
                $follow->delete();
                $message = 'Unfollowed user';
                $isFollowing = false;
            } else {
                UserFollow::create([
                    'follower_id' => $user->id,
                    'followed_id' => $followUserId
                ]);
                $message = 'Following user';
                $isFollowing = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_following' => $isFollowing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle follow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to track searches
     */
    private function trackSearch(string $query, string $type = 'all'): void
    {
        // Update popular searches
        PopularSearch::updateOrCreate(
            ['term' => $query],
            ['hits' => DB::raw('hits + 1')]
        );

        // Update trending searches (with time-based logic)
        TrendingSearch::updateOrCreate(
            ['term' => $query],
            ['hits' => DB::raw('hits + 1')]
        );

        // Save to user search history if authenticated
        if (Auth::check()) {
            SearchHistory::create([
                'user_id' => Auth::id(),
                'query' => $query,
                'type' => $type
            ]);
        }
    }

    public function getSuggestedUsers(Request $request): JsonResponse
    {
        try {
            $user = $request->user(); // Get authenticated user via Sanctum

            // Get suggested users (exclude current user and already followed users)
            $suggestedUsers = User::where('id', '!=', $user->id)
                ->whereNotIn('id', function ($query) use ($user) {
                    $query->select('following_id')
                        ->from('user_followers')
                        ->where('follower_id', $user->id);
                })
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->select(['id', 'name', 'email', 'profile_image', 'created_at'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->profile_image,
                        'created_at' => $user->created_at,
                    ];
                });


            return response()->json([
                'success' => true,
                'message' => 'Suggested users retrieved successfully',
                'data' => $suggestedUsers
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error getting suggested users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggested users',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
