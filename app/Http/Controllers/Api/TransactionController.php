<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Get user's transaction history with filters
     * GET /transactions
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:all,sales,purchases',
            'status' => 'nullable|string|in:pending,completed,cancelled,refunded',
            'period' => 'nullable|string|in:today,week,month,year,all',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $type = $request->query('type', 'all');
            $status = $request->query('status');
            $period = $request->query('period', 'all');
            $limit = $request->query('limit', 20);

            // Base query
            $query = Transaction::query();

            // Filter by user type
            if ($type === 'sales') {
                $query->where('seller_id', $user->id);
            } elseif ($type === 'purchases') {
                $query->where('buyer_id', $user->id);
            } else {
                // All transactions (both as buyer and seller)
                $query->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                      ->orWhere('buyer_id', $user->id);
                });
            }

            // Filter by status
            if ($status) {
                $query->where('status', $status);
            }

            // Filter by period
            if ($period !== 'all') {
                $startDate = $this->getPeriodStartDate($period);
                if ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }
            }

            // Custom date range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Get transactions with relationships
            $transactions = $query->with([
                'buyer:id,name,email,profile_image',
                'seller:id,name,email,profile_image',
                'item:id,title,price,category_name,status',
                'item.primaryImage:id,item_id,image_path'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

            // Format response
            $formattedTransactions = $transactions->map(function ($transaction) use ($user) {
                $isSeller = $transaction->seller_id === $user->id;
                
                return [
                    'id' => $transaction->id,
                    'type' => $isSeller ? 'sale' : 'purchase',
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at,
                    'completed_at' => $transaction->completed_at,
                    
                    // Other party info
                    'other_party' => [
                        'id' => $isSeller ? $transaction->buyer->id : $transaction->seller->id,
                        'name' => $isSeller ? $transaction->buyer->name : $transaction->seller->name,
                        'profile_image' => $isSeller 
                            ? $transaction->buyer->full_profile_image_url 
                            : $transaction->seller->full_profile_image_url,
                    ],
                    
                    // Item info
                    'item' => [
                        'id' => $transaction->item->id,
                        'title' => $transaction->item->title,
                        'price' => (float) $transaction->item->price,
                        'category' => $transaction->item->category_name,
                        'image' => $transaction->item->primaryImage 
                            ? asset('storage/' . $transaction->item->primaryImage->image_path)
                            : null,
                    ],
                    
                    // Display helpers
                    'display' => [
                        'title' => $isSeller 
                            ? "Sold to {$transaction->buyer->name}" 
                            : "Purchased from {$transaction->seller->name}",
                        'subtitle' => $transaction->item->title,
                        'amount_text' => $isSeller ? '+₹' . $transaction->amount : '-₹' . $transaction->amount,
                        'amount_color' => $isSeller ? 'green' : 'red',
                        'status_text' => ucfirst($transaction->status),
                        'date_text' => $transaction->created_at->format('d M Y, h:i A'),
                    ]
                ];
            });

            // Calculate summary
            $summary = $this->calculateSummary($user, $type, $period, $request);

            return response()->json([
                'success' => true,
                'data' => $formattedTransactions,
                'summary' => $summary,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'has_more' => $transactions->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch transactions', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get transaction details
     * GET /transactions/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $transaction = Transaction::with([
                'buyer:id,name,email,phone,profile_image',
                'seller:id,name,email,phone,profile_image',
                'item:id,title,description,price,category_name,condition,status,location',
                'item.images:id,item_id,image_path,is_primary'
            ])->find($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Check authorization
            if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $isSeller = $transaction->seller_id === $user->id;

            // Format detailed response
            $response = [
                'id' => $transaction->id,
                'type' => $isSeller ? 'sale' : 'purchase',
                'amount' => (float) $transaction->amount,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at,
                'completed_at' => $transaction->completed_at,
                
                'buyer' => [
                    'id' => $transaction->buyer->id,
                    'name' => $transaction->buyer->name,
                    'email' => $transaction->buyer->email,
                    'phone' => $transaction->buyer->phone,
                    'profile_image' => $transaction->buyer->full_profile_image_url,
                ],
                
                'seller' => [
                    'id' => $transaction->seller->id,
                    'name' => $transaction->seller->name,
                    'email' => $transaction->seller->email,
                    'phone' => $transaction->seller->phone,
                    'profile_image' => $transaction->seller->full_profile_image_url,
                ],
                
                'item' => [
                    'id' => $transaction->item->id,
                    'title' => $transaction->item->title,
                    'description' => $transaction->item->description,
                    'price' => (float) $transaction->item->price,
                    'category' => $transaction->item->category_name,
                    'condition' => $transaction->item->condition,
                    'location' => $transaction->item->location,
                    'images' => $transaction->item->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => asset('storage/' . $image->image_path),
                            'is_primary' => $image->is_primary,
                        ];
                    }),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch transaction details', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get monthly transaction summary (like Google Pay)
     * GET /transactions/monthly-summary
     */
    public function monthlySummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $year = $request->query('year', date('Y'));
            $month = $request->query('month', date('m'));

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // Get all transactions for the month
            $transactions = Transaction::where(function ($q) use ($user) {
                $q->where('seller_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

            // Calculate totals
            $sales = $transactions->where('seller_id', $user->id);
            $purchases = $transactions->where('buyer_id', $user->id);

            $totalEarned = $sales->sum('amount');
            $totalSpent = $purchases->sum('amount');
            $netAmount = $totalEarned - $totalSpent;

            // Group by day for chart data
            $dailyData = [];
            for ($day = 1; $day <= $endDate->day; $day++) {
                $date = Carbon::create($year, $month, $day);
                $dayTransactions = $transactions->filter(function ($t) use ($date) {
                    return $t->created_at->isSameDay($date);
                });

                $dailySales = $dayTransactions->where('seller_id', $user->id)->sum('amount');
                $dailyPurchases = $dayTransactions->where('buyer_id', $user->id)->sum('amount');

                $dailyData[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $day,
                    'sales' => (float) $dailySales,
                    'purchases' => (float) $dailyPurchases,
                    'net' => (float) ($dailySales - $dailyPurchases),
                ];
            }

            // Category breakdown
            $categoryBreakdown = $this->getCategoryBreakdown($transactions, $user);

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'year' => $year,
                        'month' => $month,
                        'month_name' => $startDate->format('F'),
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                    ],
                    'summary' => [
                        'total_earned' => (float) $totalEarned,
                        'total_spent' => (float) $totalSpent,
                        'net_amount' => (float) $netAmount,
                        'total_sales' => $sales->count(),
                        'total_purchases' => $purchases->count(),
                        'total_transactions' => $transactions->count(),
                    ],
                    'daily_data' => $dailyData,
                    'category_breakdown' => $categoryBreakdown,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch monthly summary', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch monthly summary',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get yearly overview
     * GET /transactions/yearly-overview
     */
    public function yearlyOverview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $year = $request->query('year', date('Y'));

            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();

            $transactions = Transaction::where(function ($q) use ($user) {
                $q->where('seller_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

            // Monthly breakdown
            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
                $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

                $monthTransactions = $transactions->filter(function ($t) use ($monthStart, $monthEnd) {
                    return $t->created_at->between($monthStart, $monthEnd);
                });

                $monthlySales = $monthTransactions->where('seller_id', $user->id)->sum('amount');
                $monthlyPurchases = $monthTransactions->where('buyer_id', $user->id)->sum('amount');

                $monthlyData[] = [
                    'month' => $month,
                    'month_name' => $monthStart->format('M'),
                    'sales' => (float) $monthlySales,
                    'purchases' => (float) $monthlyPurchases,
                    'net' => (float) ($monthlySales - $monthlyPurchases),
                    'transaction_count' => $monthTransactions->count(),
                ];
            }

            $totalSales = $transactions->where('seller_id', $user->id);
            $totalPurchases = $transactions->where('buyer_id', $user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $year,
                    'summary' => [
                        'total_earned' => (float) $totalSales->sum('amount'),
                        'total_spent' => (float) $totalPurchases->sum('amount'),
                        'net_amount' => (float) ($totalSales->sum('amount') - $totalPurchases->sum('amount')),
                        'total_sales' => $totalSales->count(),
                        'total_purchases' => $totalPurchases->count(),
                    ],
                    'monthly_data' => $monthlyData,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch yearly overview', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch yearly overview',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Helper: Get period start date
     */
    private function getPeriodStartDate(string $period): ?Carbon
    {
        return match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => null,
        };
    }

    /**
     * Helper: Calculate summary
     */
    private function calculateSummary(User $user, string $type, string $period, Request $request): array
    {
        $query = Transaction::where('status', 'completed');

        // Apply same filters as main query
        if ($type === 'sales') {
            $query->where('seller_id', $user->id);
        } elseif ($type === 'purchases') {
            $query->where('buyer_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('seller_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
            });
        }

        if ($period !== 'all') {
            $startDate = $this->getPeriodStartDate($period);
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date . ' 23:59:59'
            ]);
        }

        $transactions = $query->get();
        $sales = $transactions->where('seller_id', $user->id);
        $purchases = $transactions->where('buyer_id', $user->id);

        return [
            'total_earned' => (float) $sales->sum('amount'),
            'total_spent' => (float) $purchases->sum('amount'),
            'net_amount' => (float) ($sales->sum('amount') - $purchases->sum('amount')),
            'total_sales' => $sales->count(),
            'total_purchases' => $purchases->count(),
            'total_transactions' => $transactions->count(),
        ];
    }

    /**
     * Helper: Get category breakdown
     */
    private function getCategoryBreakdown($transactions, User $user): array
    {
        $sales = $transactions->where('seller_id', $user->id);
        $purchases = $transactions->where('buyer_id', $user->id);

        $salesByCategory = $sales->groupBy('item.category_name')->map(function ($items, $category) {
            return [
                'category' => $category ?? 'Uncategorized',
                'count' => $items->count(),
                'amount' => (float) $items->sum('amount'),
            ];
        })->values();

        $purchasesByCategory = $purchases->groupBy('item.category_name')->map(function ($items, $category) {
            return [
                'category' => $category ?? 'Uncategorized',
                'count' => $items->count(),
                'amount' => (float) $items->sum('amount'),
            ];
        })->values();

        return [
            'sales' => $salesByCategory,
            'purchases' => $purchasesByCategory,
        ];
    }
}
