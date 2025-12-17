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
        'buyer_confirmed',
        'seller_confirmed',
        'buyer_confirmed_at',
        'seller_confirmed_at',
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
        'buyer_confirmed' => 'boolean',
        'seller_confirmed' => 'boolean',
        'buyer_confirmed_at' => 'datetime',
        'seller_confirmed_at' => 'datetime',
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

    // OPTIONAL: Add helper methods
    public function isPending()
    {
        return $this->status === 'pending_meetup';
    }

    public function isScheduled()
    {
        return $this->status === 'meetup_scheduled';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function bothPartiesConfirmed()
    {
        return $this->buyer_confirmed && $this->seller_confirmed;
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'item_id', 'item_id')
            ->where('buyer_id', $this->buyer_id)
            ->where('seller_id', $this->seller_id);
    }
}
