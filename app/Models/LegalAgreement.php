<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'accepted_at',
        'version',
        'signature',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByDocumentType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('accepted_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isExpired($validityDays = 365)
    {
        return $this->accepted_at->addDays($validityDays)->isPast();
    }
}
