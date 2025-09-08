<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    // Helper method to get boolean values
    public function getBooleanValue(): bool
    {
        return $this->value === 'true';
    }

    // Helper method to set boolean values
    public function setBooleanValue(bool $value): void
    {
        $this->value = $value ? 'true' : 'false';
    }
}
