<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'is_archived',
        'archived_at',
        'is_muted',
        'muted_until',
        'last_read_at',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'is_muted' => 'boolean',
        'archived_at' => 'datetime',
        'muted_until' => 'datetime',
        'last_read_at' => 'datetime',
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
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeMuted($query)
    {
        return $query->where('is_muted', true)
                    ->where(function ($q) {
                        $q->whereNull('muted_until')
                          ->orWhere('muted_until', '>', now());
                    });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function archive()
    {
        return $this->update([
            'is_archived' => true,
            'archived_at' => now()
        ]);
    }

    public function unarchive()
    {
        return $this->update([
            'is_archived' => false,
            'archived_at' => null
        ]);
    }

    public function mute($hours = null)
    {
        $muteUntil = $hours ? now()->addHours($hours) : null;
        
        return $this->update([
            'is_muted' => true,
            'muted_until' => $muteUntil
        ]);
    }

    public function unmute()
    {
        return $this->update([
            'is_muted' => false,
            'muted_until' => null
        ]);
    }

    public function isMuted()
    {
        if (!$this->is_muted) {
            return false;
        }

        // Check if mute has expired
        if ($this->muted_until && $this->muted_until->isPast()) {
            $this->unmute();
            return false;
        }

        return true;
    }

    public function updateLastRead()
    {
        return $this->update(['last_read_at' => now()]);
    }

    public static function getOrCreate($sessionId, $userId)
    {
        return static::firstOrCreate([
            'session_id' => $sessionId,
            'user_id' => $userId
        ]);
    }
    
}
