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
}
