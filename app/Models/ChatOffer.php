<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'message_id',
        'sender_id',
        'amount',
        'currency',
        'message',
        'expires_at',
        'status',
        'accepted_by',
        'accepted_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canAccept($userId)
    {
        return $this->isPending() && $this->sender_id !== $userId;
    }

    public function canReject($userId)
    {
        return $this->isPending() && $this->sender_id !== $userId;
    }

    public function accept($userId)
    {
        return $this->update([
            'status' => 'accepted',
            'accepted_by' => $userId,
            'accepted_at' => now()
        ]);
    }

    public function reject($userId)
    {
        return $this->update([
            'status' => 'rejected',
            'rejected_by' => $userId,
            'rejected_at' => now()
        ]);
    }

    public function markAsExpired()
    {
        if ($this->isPending() && $this->isExpired()) {
            $this->update(['status' => 'expired']);
        }
    }

    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }
}
