<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sequence',
        'duration',
        'start_month',
        'end_month',
        'academic_year',
        'status'
    ];

    protected $casts = [
        'sequence' => 'integer',
        'academic_year' => 'integer',
        'status' => 'string',
    ];

    // Relationships
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_semester')
                    ->withTimestamps()
                    ->withPivot('is_mandatory');
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

    public function scopeBySequence($query)
    {
        return $query->orderBy('sequence');
    }

    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFormattedNameAttribute()
    {
        return $this->name . ' (Year ' . $this->academic_year . ')';
    }
}
