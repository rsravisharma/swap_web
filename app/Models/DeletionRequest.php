<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'phone',
        'reason',
        'status',
        'admin_notes',
        'processed_at',
        'verification_token',
        'verified',
        'verified_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'verified_at' => 'datetime',
        'verified' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->verification_token = Str::random(64);
        });
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'completed' => 'badge-success',
            'rejected' => 'badge-danger',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function markAsVerified()
    {
        $this->update([
            'verified' => true,
            'verified_at' => now(),
        ]);
    }
}
