<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyMaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'subject',
        'category',
        'desired_price_min',
        'desired_price_max',
        'urgency',
        'status',
        'fulfilled_at'
    ];

    protected $casts = [
        'desired_price_min' => 'decimal:2',
        'desired_price_max' => 'decimal:2',
        'fulfilled_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeByUrgency($query, $urgency)
    {
        return $query->where('urgency', $urgency);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isFulfilled()
    {
        return $this->status === 'fulfilled';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isUrgent()
    {
        return $this->urgency === 'high';
    }

    public function getFormattedPriceRange()
    {
        if ($this->desired_price_min && $this->desired_price_max) {
            return "₹{$this->desired_price_min} - ₹{$this->desired_price_max}";
        } elseif ($this->desired_price_min) {
            return "From ₹{$this->desired_price_min}";
        } elseif ($this->desired_price_max) {
            return "Up to ₹{$this->desired_price_max}";
        }
        return 'Price negotiable';
    }

    public function getUrgencyBadgeColor()
    {
        return match ($this->urgency) {
            'high' => 'red',
            'medium' => 'orange',
            'low' => 'green',
            default => 'gray'
        };
    }

    // Constants
    const URGENCY_LOW = 'low';
    const URGENCY_MEDIUM = 'medium';
    const URGENCY_HIGH = 'high';

    const STATUS_ACTIVE = 'active';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_CANCELLED = 'cancelled';

    public static function getUrgencyLevels()
    {
        return [
            self::URGENCY_LOW => 'Low',
            self::URGENCY_MEDIUM => 'Medium',
            self::URGENCY_HIGH => 'High',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_FULFILLED => 'Fulfilled',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
