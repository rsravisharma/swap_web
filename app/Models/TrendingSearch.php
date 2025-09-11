<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrendingSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'term',
        'hits',
        'search_date'
    ];

    protected $casts = [
        'search_date' => 'date',
        'hits' => 'integer'
    ];

    /**
     * Record a search term
     */
    public static function recordSearch(string $term): void
    {
        if (empty(trim($term)) || strlen($term) < 2) {
            return; // Don't record very short or empty terms
        }

        $cleanTerm = strtolower(trim($term));
        $today = Carbon::now()->toDateString();

        // Update or create trending search record
        static::updateOrCreate(
            [
                'term' => $cleanTerm,
                'search_date' => $today
            ],
            ['hits' => 1]
        )->increment('hits');
    }

    /**
     * Get trending searches for today
     */
    public static function getTodaysTrending(int $limit = 10): array
    {
        return static::where('search_date', Carbon::now()->toDateString())
            ->orderBy('hits', 'desc')
            ->limit($limit)
            ->pluck('term')
            ->toArray();
    }

    /**
     * Get trending searches for the last week
     */
    public static function getWeeklyTrending(int $limit = 10): array
    {
        return static::where('search_date', '>=', Carbon::now()->subWeek()->toDateString())
            ->selectRaw('term, SUM(hits) as total_hits')
            ->groupBy('term')
            ->orderBy('total_hits', 'desc')
            ->limit($limit)
            ->pluck('term')
            ->toArray();
    }

    /**
     * Get trending searches for the last month
     */
    public static function getMonthlyTrending(int $limit = 10): array
    {
        return static::where('search_date', '>=', Carbon::now()->subMonth()->toDateString())
            ->selectRaw('term, SUM(hits) as total_hits')
            ->groupBy('term')
            ->orderBy('total_hits', 'desc')
            ->limit($limit)
            ->pluck('term')
            ->toArray();
    }

    /**
     * Clean up old trending search records
     */
    public static function cleanupOldRecords(int $daysToKeep = 30): int
    {
        return static::where('search_date', '<', Carbon::now()->subDays($daysToKeep)->toDateString())
            ->delete();
    }

    /**
     * Scope for recent searches
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('search_date', '>=', Carbon::now()->subDays($days)->toDateString());
    }

    /**
     * Scope for today's searches
     */
    public function scopeToday($query)
    {
        return $query->where('search_date', Carbon::now()->toDateString());
    }
}
