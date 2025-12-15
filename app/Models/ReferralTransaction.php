<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'referral_transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'referral_code',
        'coins_awarded',
        'awarded_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'awarded_at' => 'datetime',
        'coins_awarded' => 'integer',
    ];

    /**
     * Get the user who referred (the referrer)
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
