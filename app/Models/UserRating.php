<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rater_id',
        'rated_id',
        'transaction_id',
        'rating',
        'review',
        'tags',
        'type',
        'is_public'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_public' => 'boolean',
        'tags' => 'array',
    ];

    // Relationships
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function rated()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('rated_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    // Helper methods
    public static function getAverageRating($userId, $type = null)
    {
        $query = static::where('rated_id', $userId)->public();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->avg('rating') ?: 0.0;
    }

    public static function getTotalRatings($userId, $type = null)
    {
        $query = static::where('rated_id', $userId)->public();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->count();
    }

    public function getStarsAttribute()
    {
        return str_repeat('⭐', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

}
