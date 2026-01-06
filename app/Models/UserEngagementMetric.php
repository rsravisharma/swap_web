<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEngagementMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'dau_score',
        'engagement_score',
        'quality_score',
        'response_time_avg',
        'completion_rate',
        'reported_count',
        'reports_made',
        'is_suspicious',
    ];

    protected $casts = [
        'date' => 'date',
        'dau_score' => 'integer',
        'engagement_score' => 'integer',
        'quality_score' => 'integer',
        'response_time_avg' => 'integer',
        'completion_rate' => 'decimal:2',
        'reported_count' => 'integer',
        'reports_made' => 'integer',
        'is_suspicious' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and update engagement metrics for a user
     */
    public static function calculateMetrics($userId, $date = null)
    {
        $date = $date ?? today();
        
        $metric = self::firstOrCreate([
            'user_id' => $userId,
            'date' => $date,
        ]);

        // Calculate DAU score (0-100)
        $dauScore = self::calculateDAUScore($userId, $date);
        
        // Calculate engagement score (0-100)
        $engagementScore = self::calculateEngagementScore($userId, $date);
        
        // Calculate quality score (0-100)
        $qualityScore = self::calculateQualityScore($userId, $date);
        
        // Calculate average response time
        $responseTime = self::calculateResponseTime($userId, $date);
        
        // Calculate completion rate
        $completionRate = self::calculateCompletionRate($userId, $date);
        
        // Count reports
        $reportedCount = UserViolation::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->count();
            
        $reportsMade = UserViolation::where('reported_by', $userId)
            ->whereDate('created_at', $date)
            ->count();

        // Check if suspicious
        $isSuspicious = self::checkSuspiciousActivity($userId, $date);

        $metric->update([
            'dau_score' => $dauScore,
            'engagement_score' => $engagementScore,
            'quality_score' => $qualityScore,
            'response_time_avg' => $responseTime,
            'completion_rate' => $completionRate,
            'reported_count' => $reportedCount,
            'reports_made' => $reportsMade,
            'is_suspicious' => $isSuspicious,
        ]);

        return $metric;
    }

    /**
     * Calculate DAU (Daily Active User) score
     */
    protected static function calculateDAUScore($userId, $date)
    {
        $user = User::find($userId);
        $score = 0;

        // Login bonus: 20 points
        if ($user->last_login_at && $user->last_login_at->isToday()) {
            $score += 20;
        }

        // Activity bonus: 30 points
        $activities = UserActivityLog::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->count();
        $score += min(30, $activities * 2);

        // Session duration bonus: 30 points
        $sessionDuration = UserSessionLog::where('user_id', $userId)
            ->whereDate('started_at', $date)
            ->sum('duration_seconds');
        $score += min(30, ($sessionDuration / 60) / 10); // 10 minutes = 30 points

        // Interaction bonus: 20 points
        $dailyStat = DailyUserStat::where('user_id', $userId)
            ->where('date', $date)
            ->first();
        
        if ($dailyStat) {
            $interactions = $dailyStat->messages_sent + $dailyStat->offers_made;
            $score += min(20, $interactions * 2);
        }

        return min(100, round($score));
    }

    /**
     * Calculate engagement score
     */
    protected static function calculateEngagementScore($userId, $date)
    {
        $dailyStat = DailyUserStat::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$dailyStat) {
            return 0;
        }

        $score = 0;

        // Item views: 15 points
        $score += min(15, $dailyStat->items_viewed * 1.5);

        // Searches: 15 points
        $score += min(15, $dailyStat->searches_made * 3);

        // Messages: 25 points
        $score += min(25, ($dailyStat->messages_sent + $dailyStat->messages_received) * 2);

        // Offers: 20 points
        $score += min(20, ($dailyStat->offers_made + $dailyStat->offers_received) * 4);

        // Transactions: 25 points
        $score += min(25, ($dailyStat->items_listed + $dailyStat->items_sold + $dailyStat->items_bought) * 5);

        return min(100, round($score));
    }

    /**
     * Calculate quality score
     */
    protected static function calculateQualityScore($userId, $date)
    {
        $user = User::find($userId);
        $score = 50; // Start with neutral

        // Seller rating bonus: 30 points
        if ($user->seller_rating > 0) {
            $score += ($user->seller_rating / 5) * 30;
        }

        // Completion rate bonus: 20 points
        if ($user->items_sold > 0) {
            $completionRate = $user->items_sold / max(1, $user->items_sold + $user->total_listings);
            $score += $completionRate * 20;
        }

        // Penalties
        $violations = UserViolation::where('user_id', $userId)
            ->where('created_at', '>=', $date->copy()->subDays(30))
            ->count();
        $score -= $violations * 10;

        return max(0, min(100, round($score)));
    }

    /**
     * Calculate average response time
     */
    protected static function calculateResponseTime($userId, $date)
    {
        // This would require message timestamps - implement based on your chat system
        // For now, return a default value
        return null;
    }

    /**
     * Calculate completion rate
     */
    protected static function calculateCompletionRate($userId, $date)
    {
        $user = User::find($userId);
        
        if ($user->total_listings == 0) {
            return 0;
        }

        return round(($user->items_sold / $user->total_listings) * 100, 2);
    }

    /**
     * Check for suspicious activity
     */
    protected static function checkSuspiciousActivity($userId, $date)
    {
        $flags = 0;

        // Check for multiple reports
        $recentReports = UserViolation::where('user_id', $userId)
            ->where('created_at', '>=', $date->copy()->subDays(7))
            ->count();
        if ($recentReports >= 3) $flags++;

        // Check for rapid listing activity (potential spam)
        $dailyStat = DailyUserStat::where('user_id', $userId)
            ->where('date', $date)
            ->first();
        if ($dailyStat && $dailyStat->items_listed > 10) $flags++;

        // Check for low quality score
        $metric = self::where('user_id', $userId)->where('date', $date)->first();
        if ($metric && $metric->quality_score < 30) $flags++;

        return $flags >= 2;
    }

    /**
     * Scopes
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeHighEngagement($query, $threshold = 70)
    {
        return $query->where('engagement_score', '>=', $threshold);
    }

    public function scopeLowQuality($query, $threshold = 40)
    {
        return $query->where('quality_score', '<', $threshold);
    }

    /**
     * Get engagement trend for user
     */
    public static function getEngagementTrend($userId, $days = 7)
    {
        return self::where('user_id', $userId)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get(['date', 'engagement_score', 'dau_score']);
    }

    /**
     * Get top engaged users
     */
    public static function getTopEngagedUsers($date = null, $limit = 10)
    {
        $date = $date ?? today();
        
        return self::with('user')
            ->where('date', $date)
            ->orderBy('engagement_score', 'desc')
            ->limit($limit)
            ->get();
    }
}
