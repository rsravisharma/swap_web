<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'category',
        'priority',
        'status',
        'rating',
        'rating_feedback',
        'rated_at'
    ];

    protected $casts = [
        'rated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(SupportAttachment::class);
    }

    public function responses()
    {
        return $this->hasMany(SupportResponse::class);
    }
}
