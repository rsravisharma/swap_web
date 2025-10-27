<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meetup extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'item_id',
        'offer_id',
        'agreed_price',
        'original_price',
        'meetup_location',
        'meetup_location_type',
        'meetup_location_details',
        'preferred_meetup_time',
        'alternative_meetup_time',
        'payment_method',
        'buyer_notes',
        'acknowledged_safety',
        'status',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'meetup_location_details' => 'array',
        'agreed_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'acknowledged_safety' => 'boolean',
        'preferred_meetup_time' => 'datetime',
        'alternative_meetup_time' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
