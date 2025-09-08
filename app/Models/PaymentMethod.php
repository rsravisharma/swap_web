<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
        'config',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'json',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function isActive()
    {
        return $this->is_active;
    }

    public function getDisplayName()
    {
        return $this->name;
    }
}
