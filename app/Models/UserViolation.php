<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reported_by',
        'admin_id',
        'violation_type',
        'severity',
        'status',
        'description',
        'admin_notes',
        'evidence',
        'action_taken',
        'action_taken_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'action_taken_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }
}
