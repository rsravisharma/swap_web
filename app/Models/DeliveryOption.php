<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'estimated_days',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'estimated_days' => 'integer',
        'is_active' => 'boolean',
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

    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function isActive()
    {
        return $this->is_active;
    }

    public function isFree()
    {
        return $this->price == 0;
    }

    public function getEstimatedDelivery()
    {
        if ($this->estimated_days) {
            return now()->addDays($this->estimated_days);
        }
        return null;
    }

    public function getFormattedPrice()
    {
        return $this->price == 0 ? 'Free' : '₹' . number_format($this->price, 2);
    }
}
