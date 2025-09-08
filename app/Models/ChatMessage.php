<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'sender_id',
        'reply_to_id',
        'message',
        'message_type',
        'metadata',
        'status',
        'read_at',
        'is_edited',
        'is_deleted',
        'edited_at',
        'deleted_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'reply_to_id');
    }

    public function offers()
    {
        return $this->hasMany(ChatOffer::class, 'message_id');
    }

    public function reports()
    {
        return $this->hasMany(ChatReport::class, 'message_id');
    }

    // Scopes
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeUnread($query, $userId)
    {
        return $query->where('sender_id', '!=', $userId)
                    ->where('status', '!=', 'read');
    }

    public function scopeInSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    // Helper methods
    public function isRead()
    {
        return $this->status === 'read';
    }

    public function isOwnMessage($userId)
    {
        return $this->sender_id === $userId;
    }

    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }

    public function softDelete()
    {
        $this->update([
            'message' => '[Message deleted]',
            'is_deleted' => true,
            'deleted_at' => now()
        ]);
    }

    public function editMessage($newMessage)
    {
        $this->update([
            'message' => $newMessage,
            'is_edited' => true,
            'edited_at' => now()
        ]);
    }

    public function hasReplies()
    {
        return $this->replies()->exists();
    }

    public function isReply()
    {
        return !is_null($this->reply_to_id);
    }

    public function getFormattedMessageAttribute()
    {
        if ($this->is_deleted) {
            return '[Message deleted]';
        }

        return $this->message;
    }
}
