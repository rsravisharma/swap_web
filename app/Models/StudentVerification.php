<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'university',
        'graduation_year',
        'verified',
        'status',
        'notes',
        'document_path'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'graduation_year' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDocumentUrlAttribute()
    {
        return $this->document_path ? asset('storage/' . $this->document_path) : null;
    }
}
