<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'gateway_transaction_id',
        'gateway_response',
        'description',
        'refund_amount',
        'refund_reason',
        'processed_at',
        'refunded_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'gateway_response' => 'json',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(UserPaymentMethod::class, 'payment_method_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function isRefundable()
    {
        return $this->status === 'completed' &&
            ($this->refund_amount === null || $this->refund_amount < $this->amount);
    }

    public function bookPurchases()
    {
        return $this->hasMany(PdfBookPurchase::class);
    }
}
