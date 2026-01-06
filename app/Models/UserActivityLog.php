<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'action_type',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'performed_at',
        'device_type',
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public static function log($userId, $action, $actionType, $description = null, $metadata = null)
    {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'action_type' => $actionType,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => request()->header('X-Device-Type'),
            'performed_at' => now(),
        ]);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }
}
