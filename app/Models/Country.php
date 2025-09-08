<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'flag_emoji',
        'currency',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'country_code', 'code');
    }

    public function scopeByPhoneCode($query, $phoneCode)
    {
        return $query->where('phone_code', $phoneCode);
    }

    // Helper methods
    public static function getByCode($code)
    {
        return static::where('code', strtoupper($code))->first();
    }

    public static function getActiveCountries()
    {
        return static::active()->orderBy('name')->get();
    }
}
