<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'tags',
        'sort_order',
        'helpful_count',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'helpful_count' => 'integer'
    ];
}
