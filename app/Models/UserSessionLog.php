<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSessionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'device_type',
        'device_model',
        'os_version',
        'app_version',
        'ip_address',
        'location',
        'actions_count',
        'pages_visited',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'pages_visited' => 'array',
        'actions_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Start a new session
     */
    public static function startSession($userId, $request)
    {
        return self::create([
            'user_id' => $userId,
            'session_id' => session()->getId(),
            'started_at' => now(),
            'device_type' => $request->header('X-Device-Type') ?? self::detectDeviceType($request),
            'device_model' => $request->header('X-Device-Model'),
            'os_version' => $request->header('X-OS-Version'),
            'app_version' => $request->header('X-App-Version'),
            'ip_address' => $request->ip(),
            'location' => self::getLocationFromIp($request->ip()),
            'pages_visited' => [],
            'actions_count' => 0,
        ]);
    }

    /**
     * End the session
     */
    public function endSession()
    {
        $this->update([
            'ended_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }

    /**
     * Log a page visit
     */
    public function logPageVisit($page)
    {
        $pages = $this->pages_visited ?? [];
        $pages[] = [
            'page' => $page,
            'timestamp' => now()->toDateTimeString(),
        ];

        $this->update([
            'pages_visited' => $pages,
        ]);
    }

    /**
     * Increment action count
     */
    public function incrementActions($count = 1)
    {
        $this->increment('actions_count', $count);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('started_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Get sessions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Get average session duration
     */
    public static function getAverageSessionDuration($userId = null, $days = 30)
    {
        $query = self::whereNotNull('duration_seconds')
            ->where('started_at', '>=', now()->subDays($days));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->avg('duration_seconds') ?? 0;
    }

    /**
     * Get total sessions count
     */
    public static function getTotalSessions($userId, $days = 30)
    {
        return self::where('user_id', $userId)
            ->where('started_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Get user's most active time
     */
    public static function getMostActiveTime($userId)
    {
        return self::where('user_id', $userId)
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();
    }

    /**
     * Helper: Detect device type from user agent
     */
    protected static function detectDeviceType($request)
    {
        $userAgent = strtolower($request->userAgent());
        
        if (str_contains($userAgent, 'android')) {
            return 'android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'ios';
        } else {
            return 'web';
        }
    }

    /**
     * Helper: Get location from IP (you can integrate with IP geolocation service)
     */
    protected static function getLocationFromIp($ip)
    {
        // Basic implementation - you can use services like ipapi.co, ipinfo.io
        // For now, return null or implement your preferred geolocation service
        try {
            // Example with ipapi.co (free tier)
            // $response = file_get_contents("https://ipapi.co/{$ip}/json/");
            // $data = json_decode($response);
            // return "{$data->city}, {$data->region}";
            
            return null; // Replace with actual implementation
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        } else {
            return "{$seconds}s";
        }
    }
}
