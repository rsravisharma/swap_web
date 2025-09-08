<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_holder_name',
        'card_type',
        'last_four',
        'expiry_month',
        'expiry_year',
        'billing_address',
        'is_default',
        'token',
        'is_active'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'expiry_month' => 'integer',
        'expiry_year' => 'integer'
    ];

    protected $hidden = [
        'token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_method_id');
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->card_type} ending in {$this->last_four}";
    }

    public function isExpired()
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        
        return $this->expiry_year < $currentYear || 
               ($this->expiry_year == $currentYear && $this->expiry_month < $currentMonth);
    }
}
