<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'item_id',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function scopeCredits($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeDebits($query)
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeReferrals($query)
    {
        return $query->whereIn('type', [
            'referral_signup_reward',
            'referral_commission',
        ]);
    }

    public function scopeReferralSignupRewards($query)
    {
        return $query->where('type', 'referral_signup_reward');
    }

    public function scopeReferralCommissions($query)
    {
        return $query->where('type', 'referral_commission');
    }

    public function getFormattedAmountAttribute()
    {
        return ($this->amount >= 0 ? '+' : '') . $this->amount;
    }

    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'referral_signup_reward' => 'Referral Signup Bonus',
            'referral_commission' => 'Referral Commission',
            'item_listing' => 'Item Listing Fee',
            'purchase' => 'Purchase',
            'reward' => 'Reward',
            'sale_completed' => 'Sale Completed Reward', 
            'purchase_completed' => 'Purchase Completed Reward', 
            default => ucwords(str_replace('_', ' ', $this->type)),
        };
    }
}
