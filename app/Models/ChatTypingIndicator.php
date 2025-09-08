<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatTypingIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'is_typing',
        'last_activity_at',
    ];

    protected $casts = [
        'is_typing' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeTyping($query)
    {
        return $query->where('is_typing', true)
                    ->where('last_activity_at', '>', now()->subMinutes(2));
    }

    public function scopeInSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('last_activity_at', '>', now()->subMinutes(2));
    }

    // Helper methods
    public function startTyping()
    {
        return $this->update([
            'is_typing' => true,
            'last_activity_at' => now()
        ]);
    }

    public function stopTyping()
    {
        return $this->update([
            'is_typing' => false,
            'last_activity_at' => now()
        ]);
    }

    public function isActive()
    {
        return $this->is_typing && $this->last_activity_at->gt(now()->subMinutes(2));
    }

    public static function setTypingStatus($sessionId, $userId, $isTyping)
    {
        return static::updateOrCreate(
            [
                'session_id' => $sessionId,
                'user_id' => $userId
            ],
            [
                'is_typing' => $isTyping,
                'last_activity_at' => now()
            ]
        );
    }

    public static function getTypingUsers($sessionId, $excludeUserId = null)
    {
        $query = static::where('session_id', $sessionId)
            ->typing()
            ->with('user:id,name,profile_image');

        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        return $query->get();
    }

    public static function cleanupInactive()
    {
        return static::where('last_activity_at', '<', now()->subMinutes(5))
            ->delete();
    }
}
