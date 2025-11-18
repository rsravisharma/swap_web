<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    // Specify which fields are mass assignable
    protected $fillable = [
        'name',
        'badge',
        'monthly_price',
        'annual_price',
        'monthly_slots',
        'allowed_pdf_uploads',
        'coins_monthly',
        'description',
    ];

    // Cast fields to appropriate data types
    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'monthly_slots' => 'integer',
        'allowed_pdf_uploads' => 'boolean',
        'coins_monthly' => 'integer',
    ];

    // Relationship: one plan has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
