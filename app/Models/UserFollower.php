<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollower extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'following_id'
    ];

    // Relationships
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    // Helper methods
    public static function isFollowing($followerId, $followingId)
    {
        return static::where('follower_id', $followerId)
                    ->where('following_id', $followingId)
                    ->exists();
    }

    public static function followUser($followerId, $followingId)
    {
        if ($followerId === $followingId) {
            return false; // Cannot follow self
        }

        return static::firstOrCreate([
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ]);
    }

    public static function unfollowUser($followerId, $followingId)
    {
        return static::where('follower_id', $followerId)
                    ->where('following_id', $followingId)
                    ->delete();
    }

    // Scopes
    public function scopeFollowersOf($query, $userId)
    {
        return $query->where('following_id', $userId);
    }

    public function scopeFollowingOf($query, $userId)
    {
        return $query->where('follower_id', $userId);
    }
}
