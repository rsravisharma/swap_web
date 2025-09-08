<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price',
        'total'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Helper method to calculate total
    public function calculateTotal()
    {
        return $this->price * $this->quantity;
    }

    // Automatically calculate total before saving
    protected static function booted()
    {
        static::saving(function ($orderItem) {
            $orderItem->total = $orderItem->price * $orderItem->quantity;
        });
    }
}
