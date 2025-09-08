<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'city',
        'state',
        'country_id',
        'website',
        'logo',
        'type',
        'established_year',
        'ranking',
        'status'
    ];

    protected $casts = [
        'established_year' => 'integer',
        'ranking' => 'integer',
        'status' => 'string',
        'type' => 'string',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'LIKE', "%{$city}%");
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', 'LIKE', "%{$state}%");
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFullLocationAttribute()
    {
        return trim($this->city . ', ' . $this->state);
    }
}
