<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhoneOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'otp',
        'expires_at',
        'used',
        'ip_address'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('used', false)
                    ->where('expires_at', '>', now());
    }

    public function scopeForPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isValid()
    {
        return !$this->used && !$this->isExpired();
    }

    public function markAsUsed()
    {
        $this->update(['used' => true]);
    }

    public static function generateOtp($phone, $length = 6)
    {
        $otp = str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        
        return static::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
        ]);
    }

    public static function verify($phone, $otp)
    {
        $record = static::where('phone', $phone)
                       ->where('otp', $otp)
                       ->valid()
                       ->first();

        if ($record) {
            $record->markAsUsed();
            return true;
        }

        return false;
    }
}
