<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'code',
        'expires_at',
        'used',
        'ip_address'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('used', false)
            ->where('expires_at', '>', now());
    }

    // Helper methods
    public static function generateCode($email)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'ip_address' => request()->ip(),
        ]);
    }

    public static function verify($email, $code)
    {
        $record = static::where('email', $email)
            ->where('code', $code)
            ->valid()
            ->first();

        if ($record) {
            $record->update(['used' => true]);
            return true;
        }

        return false;
    }
}
