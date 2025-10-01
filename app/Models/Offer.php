<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'item_id',
        'parent_offer_id',
        'amount',
        'message',
        'status',
        'offer_type',
        'accepted_at',
        'rejected_at',
        'cancelled_at',
        'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function parentOffer()
    {
        return $this->belongsTo(Offer::class, 'parent_offer_id');
    }

    public function counterOffers()
    {
        return $this->hasMany(Offer::class, 'parent_offer_id')->orderBy('created_at', 'desc');
    }

    public function latestCounterOffer()
    {
        return $this->hasOne(Offer::class, 'parent_offer_id')->latest();
    }

    // Get the entire offer chain (original + all counters)
    public function offerChain()
    {
        if ($this->parent_offer_id) {
            return $this->parentOffer->offerChain();
        }
        
        return $this->counterOffers()->with('counterOffers');
    }

    // Get the root/original offer
    public function rootOffer()
    {
        if ($this->parent_offer_id) {
            return $this->parentOffer->rootOffer();
        }
        
        return $this;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        });
    }

    // NEW: Counter offer specific scopes
    public function scopeInitialOffers($query)
    {
        return $query->where('offer_type', 'initial')->whereNull('parent_offer_id');
    }

    public function scopeCounterOffers($query)
    {
        return $query->where('offer_type', 'counter')->whereNotNull('parent_offer_id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeAccepted($userId)
    {
        return $this->isPending() && $this->receiver_id === $userId;
    }

    public function canBeCancelled($userId)
    {
        return $this->isPending() && $this->sender_id === $userId;
    }

    public function getFormattedAmount()
    {
        return '₹' . number_format($this->amount, 2);
    }
    
     public function getCounterOffersCount()
    {
        if ($this->isCounterOffer()) {
            return $this->rootOffer()->counterOffers()->count();
        }
        
        return $this->counterOffers()->count();
    }

    public function getOfferSequenceNumber()
    {
        if ($this->isInitialOffer()) {
            return 1;
        }
        
        $rootOffer = $this->rootOffer();
        $allOffers = collect([$rootOffer])->merge($rootOffer->counterOffers);
        
        return $allOffers->search(function ($offer) {
            return $offer->id === $this->id;
        }) + 1;
    }
}
