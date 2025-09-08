<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'item_id',
        'session_type',
        'status',
        'last_message',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'session_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'session_id')->latest();
    }

    public function offers()
    {
        return $this->hasMany(ChatOffer::class, 'session_id');
    }

    public function reports()
    {
        return $this->hasMany(ChatReport::class, 'session_id');
    }

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'session_id');
    }

    public function typingIndicators()
    {
        return $this->hasMany(ChatTypingIndicator::class, 'session_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)
              ->orWhere('user_two_id', $userId);
        });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('session_type', $type);
    }

    // Helper methods
    public function getOtherUser($currentUserId)
    {
        return $this->user_one_id === $currentUserId ? $this->userTwo : $this->userOne;
    }

    public function isParticipant($userId)
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    public function getUnreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('status', '!=', 'read')
            ->count();
    }

    public function isArchived($userId)
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->where('is_archived', true)
            ->exists();
    }

    public function isMuted($userId)
    {
        $participant = $this->participants()
            ->where('user_id', $userId)
            ->first();

        if (!$participant || !$participant->is_muted) {
            return false;
        }

        // Check if mute has expired
        if ($participant->muted_until && $participant->muted_until->isPast()) {
            $participant->update(['is_muted' => false, 'muted_until' => null]);
            return false;
        }

        return true;
    }
}
