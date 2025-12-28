<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PdfBookPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'book_id',
        'order_id',
        'payment_transaction_id',
        'purchase_price',
        'download_token',
        'download_count',
        'max_downloads',
        'first_downloaded_at',
        'last_downloaded_at',
        'access_expires_at',
        'status'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'download_count' => 'integer',
        'max_downloads' => 'integer',
        'first_downloaded_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'access_expires_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->download_token)) {
                $purchase->download_token = Str::random(64);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function book()
    {
        return $this->belongsTo(PdfBook::class, 'book_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    // Helper Methods
    public function canDownload(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->access_expires_at && $this->access_expires_at->isPast()) {
            return false;
        }

        if ($this->download_count >= $this->max_downloads) {
            return false;
        }

        return true;
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        
        if (is_null($this->first_downloaded_at)) {
            $this->update(['first_downloaded_at' => now()]);
        }
        
        $this->update(['last_downloaded_at' => now()]);
    }
}
