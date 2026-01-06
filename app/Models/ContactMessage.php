<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
        'user_id',
        'admin_notes',
        'read_at',
        'resolved_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user associated with the message (if logged in)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for resolved messages
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'read_at' => now(),
            'status' => 'in_progress'
        ]);
    }

    /**
     * Mark message as resolved
     */
    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);
    }

    /**
     * Get subject label
     */
    public function getSubjectLabelAttribute()
    {
        $subjects = [
            'general' => 'General Inquiry',
            'support' => 'Technical Support',
            'bug' => 'Report a Bug',
            'feature' => 'Feature Request',
            'account' => 'Account Issues',
            'payment' => 'Payment Issues',
            'safety' => 'Safety Concerns',
            'other' => 'Other',
        ];

        return $subjects[$this->subject] ?? $this->subject;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }
}
