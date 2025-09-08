<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'session_id',
        'message_id',
        'reason',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByReporter($query, $reporterId)
    {
        return $query->where('reporter_id', $reporterId);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isReviewed()
    {
        return $this->status === 'reviewed';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function markAsReviewed($adminId, $notes = null)
    {
        return $this->update([
            'status' => 'reviewed',
            'resolved_by' => $adminId,
            'admin_notes' => $notes,
            'resolved_at' => now()
        ]);
    }

    public function markAsClosed($adminId, $notes = null)
    {
        return $this->update([
            'status' => 'closed',
            'resolved_by' => $adminId,
            'admin_notes' => $notes,
            'resolved_at' => now()
        ]);
    }

    public function isResolved()
    {
        return in_array($this->status, ['reviewed', 'closed']);
    }
}
