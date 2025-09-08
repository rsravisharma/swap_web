<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'university_id',
        'head_of_department',
        'email',
        'phone',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUniversity($query, $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getTotalCoursesAttribute()
    {
        return $this->courses()->count();
    }
}
