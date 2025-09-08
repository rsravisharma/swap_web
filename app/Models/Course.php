<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'duration',
        'degree_type',
        'university_id',
        'department_id',
        'total_semesters',
        'fees_per_semester',
        'eligibility_criteria',
        'status'
    ];

    protected $casts = [
        'total_semesters' => 'integer',
        'fees_per_semester' => 'decimal:2',
        'status' => 'string',
        'degree_type' => 'string',
    ];

    // Relationships
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class, 'course_semester')
                    ->withTimestamps()
                    ->withPivot('is_mandatory')
                    ->orderBy('sequence');
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

    public function scopeByDegreeType($query, $degreeType)
    {
        return $query->where('degree_type', $degreeType);
    }

    public function scopeByUniversity($query, $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . ucfirst($this->degree_type) . ')';
    }

    public function getTotalSubjectsAttribute()
    {
        return $this->subjects()->count();
    }

    public function getTotalCreditsAttribute()
    {
        return $this->subjects()->sum('credits');
    }
}
