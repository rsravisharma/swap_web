<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'reason',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForBlocker($query, $blockerId)
    {
        return $query->where('blocker_id', $blockerId);
    }

    // Helper methods
    public static function isBlocked($blockerId, $blockedId)
    {
        return static::where('blocker_id', $blockerId)
                    ->where('blocked_id', $blockedId)
                    ->active()
                    ->exists();
    }

    public static function blockUser($blockerId, $blockedId, $reason = null, $expiresAt = null)
    {
        if ($blockerId === $blockedId) {
            return false; // Cannot block self
        }

        return static::create([
            'blocker_id' => $blockerId,
            'blocked_id' => $blockedId,
            'reason' => $reason,
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);
    }

    public static function unblockUser($blockerId, $blockedId)
    {
        return static::where('blocker_id', $blockerId)
                    ->where('blocked_id', $blockedId)
                    ->update(['status' => 'resolved']);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
