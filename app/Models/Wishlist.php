<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this wishlist entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item in this wishlist entry
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope to get wishlist items for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get wishlist entries for a specific item
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Check if a user has wishlisted a specific item
     */
    public static function isWishlisted($userId, $itemId): bool
    {
        return self::where('user_id', $userId)
            ->where('item_id', $itemId)
            ->exists();
    }

    /**
     * Add item to wishlist
     */
    public static function addItem($userId, $itemId): ?self
    {
        return self::firstOrCreate([
            'user_id' => $userId,
            'item_id' => $itemId,
        ]);
    }

    /**
     * Remove item from wishlist
     */
    public static function removeItem($userId, $itemId): bool
    {
        return self::where('user_id', $userId)
            ->where('item_id', $itemId)
            ->delete();
    }

    /**
     * Get count of users who wishlisted this item
     */
    public static function getItemWishlistCount($itemId): int
    {
        return self::where('item_id', $itemId)->count();
    }
}
