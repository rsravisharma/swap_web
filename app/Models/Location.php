<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'type',
        'description',
        'is_active',
        'is_popular',
        'is_safe_meetup',
        'popularity_score',
        'created_by'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_safe_meetup' => 'boolean',
        'popularity_score' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recentUsers()
    {
        return $this->hasMany(UserRecentLocation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeSafeMeetup($query)
    {
        return $query->where('is_safe_meetup', true);
    }
}
