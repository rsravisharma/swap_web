<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'course_id',
        'semester_id',
        'university_id',
        'credits',
        'subject_type',
        'prerequisite_subjects',
        'syllabus_file',
        'status'
    ];

    protected $casts = [
        'credits' => 'integer',
        'prerequisite_subjects' => 'json',
        'status' => 'string',
        'subject_type' => 'string',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeBySemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('subject_type', $type);
    }

    public function scopeCore($query)
    {
        return $query->where('subject_type', 'core');
    }

    public function scopeElective($query)
    {
        return $query->where('subject_type', 'elective');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCore()
    {
        return $this->subject_type === 'core';
    }

    public function isElective()
    {
        return $this->subject_type === 'elective';
    }

    public function hasPrerequisites()
    {
        return !empty($this->prerequisite_subjects);
    }

    public function getPrerequisiteSubjects()
    {
        if (!$this->hasPrerequisites()) {
            return collect();
        }

        return Subject::whereIn('id', $this->prerequisite_subjects)->get();
    }

    public function getFullCodeAttribute()
    {
        return $this->course->code . '-' . $this->code;
    }
}
