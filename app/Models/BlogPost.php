<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'tags',
        'featured_image',
        'author_name',
        'author_title',
        'author_bio',
        'author_social',
        'reading_time',
        'published',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'author_social' => 'array',
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Scope for published posts
    public function scopePublished($query)
    {
        return $query->where('published', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    // Generate slug from title
    public static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = \Illuminate\Support\Str::slug($post->title);
            }
        });
    }
}
