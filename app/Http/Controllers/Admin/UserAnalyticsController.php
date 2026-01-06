<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\DailyUserStat;
use App\Models\UserViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAnalyticsController extends Controller
{
    /**
     * Show analytics dashboard
     */
    public function index()
{
    $stats = [
        'total_users' => User::count(),
        'active_users_today' => User::whereDate('last_active_at', today())->count(),
        'verified_users' => User::whereNotNull('email_verified_at')->count(),
        'student_verified' => User::where('student_verified', true)->count(),
        'new_users_today' => User::whereDate('created_at', today())->count(),
        'new_users_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
        'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        'blocked_users' => User::where('is_blocked', true)->count(),
        'pending_violations' => UserViolation::where('status', 'pending')->count(),
        'high_priority_violations' => UserViolation::pending()->highPriority()->count(),
    ];

    // User growth data (last 30 days) - Handle empty case
    $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    // Top users by activity - Handle empty case
    $topActiveUsers = User::withCount(['items as total_items'])
        ->orderBy('last_active_at', 'desc')
        ->limit(10)
        ->get();

    // Top sellers - Handle empty case
    $topSellers = User::where('items_sold', '>', 0)
        ->orderBy('items_sold', 'desc')
        ->orderBy('seller_rating', 'desc')
        ->limit(10)
        ->get();

    // Recent activities - Handle empty case
    $recentActivities = UserActivityLog::with('user')
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    return view('admin.analytics.index', compact(
        'stats',
        'userGrowth',
        'topActiveUsers',
        'topSellers',
        'recentActivities'
    ));
}

    /**
     * Show detailed user analytics
     */
    public function userDetails($userId)
    {
        $user = User::with([
            'referrer',
            'referrals',
            'items',
            'coinTransactions',
            'subscriptionPlan'
        ])->findOrFail($userId);

        // Activity summary
        $activitySummary = [
            'total_logins' => UserActivityLog::where('user_id', $userId)
                ->where('action', 'login')
                ->count(),
            'total_activities' => UserActivityLog::where('user_id', $userId)->count(),
            'last_30_days_activities' => UserActivityLog::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ];

        // Daily stats (last 30 days)
        $dailyStats = DailyUserStat::where('user_id', $userId)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();

        // Violations
        $violations = UserViolation::where('user_id', $userId)
            ->with(['reporter', 'admin'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Recent activities
        $recentActivities = UserActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Coin history
        $coinHistory = $user->coinTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Engagement metrics
        $engagementMetrics = [
            'response_rate' => $this->calculateResponseRate($userId),
            'completion_rate' => $this->calculateCompletionRate($userId),
            'avg_session_duration' => $this->getAvgSessionDuration($userId),
            'items_per_week' => $this->getItemsPerWeek($userId),
        ];

        return view('admin.analytics.user-details', compact(
            'user',
            'activitySummary',
            'dailyStats',
            'violations',
            'recentActivities',
            'coinHistory',
            'engagementMetrics'
        ));
    }

    /**
     * Show user list with filters
     */
    public function userList(Request $request)
    {
        $query = User::query();

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('verified')) {
            if ($request->verified === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->filled('student_verified')) {
            $query->where('student_verified', $request->student_verified === 'yes');
        }

        if ($request->filled('university')) {
            $query->where('university', 'LIKE', "%{$request->university}%");
        }

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

        $users = $query->paginate(50);

        return view('admin.analytics.user-list', compact('users'));
    }

    /**
     * Show violations dashboard
     */
    public function violations(Request $request)
    {
        $query = UserViolation::with(['user', 'reporter', 'admin']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('type')) {
            $query->where('violation_type', $request->type);
        }

        $violations = $query->orderBy('created_at', 'desc')->paginate(30);

        $stats = [
            'total' => UserViolation::count(),
            'pending' => UserViolation::where('status', 'pending')->count(),
            'high_priority' => UserViolation::where('severity', 'high')->count(),
            'critical' => UserViolation::where('severity', 'critical')->count(),
        ];

        return view('admin.analytics.violations', compact('violations', 'stats'));
    }

    /**
     * Export user data
     */
    public function exportUsers(Request $request)
    {
        $users = User::with('subscriptionPlan')->get();

        $csv = "ID,Name,Email,Phone,University,Course,Verified,Student Verified,Items Sold,Seller Rating,Total Earnings,Coins,Created At\n";

        foreach ($users as $user) {
            $csv .= implode(',', [
                $user->id,
                '"' . $user->name . '"',
                $user->email ?? '',
                $user->phone ?? '',
                '"' . ($user->university ?? '') . '"',
                '"' . ($user->course ?? '') . '"',
                $user->email_verified_at ? 'Yes' : 'No',
                $user->student_verified ? 'Yes' : 'No',
                $user->items_sold,
                $user->seller_rating,
                $user->total_earnings,
                $user->coins,
                $user->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_export_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // Helper methods
    private function calculateResponseRate($userId)
    {
        // Implement your logic
        return 85.5; // Example
    }

    private function calculateCompletionRate($userId)
    {
        // Implement your logic
        return 92.3; // Example
    }

    private function getAvgSessionDuration($userId)
    {
        return DB::table('user_session_logs')
            ->where('user_id', $userId)
            ->avg('duration_seconds') ?? 0;
    }

    private function getItemsPerWeek($userId)
    {
        return User::find($userId)
            ->items()
            ->where('created_at', '>=', now()->subWeek())
            ->count();
    }
}
