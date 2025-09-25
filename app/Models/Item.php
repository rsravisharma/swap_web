<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_name',        // Added for hybrid approach
        'category_id',
        'sub_category_id',      // Fixed column name
        'child_sub_category_id', // Fixed column name
        'price',
        'condition',
        'status',
        'location_id',
        'location',             // Added for hybrid approach
        'contact_method',
        'tags',
        'is_sold',
        'is_archived',
        'is_promoted',
        'promotion_type',
        'promoted_until',
        'sold_at',
        'archived_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tags' => 'array',
        'is_sold' => 'boolean',
        'is_archived' => 'boolean',
        'is_promoted' => 'boolean',
        'promoted_until' => 'datetime',
        'sold_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    // Auto-populate cached fields when saving
    protected static function booted()
    {
        static::saving(function ($item) {
            // Cache category name for fast display
            if ($item->category_id && $item->isDirty('category_id')) {
                $item->category_name = $item->category->name ?? null;
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function childSubCategory(): BelongsTo
    {
        return $this->belongsTo(ChildSubCategory::class, 'child_sub_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ItemImage::class)->where('is_primary', true);
    }

    // Accessors
    public function getCategoryPathAttribute()
    {
        $path = [];

        if ($this->category_name) {
            $path[] = $this->category_name;
        } elseif ($this->category) {
            $path[] = $this->category->name;
        }

        if ($this->subCategory) {
            $path[] = $this->subCategory->name;
        }

        if ($this->childSubCategory) {
            $path[] = $this->childSubCategory->name;
        }

        return implode(' > ', $path);
    }

    public function getLocationDisplayAttribute()
    {
        // Priority: location string first, then relationship
        if ($this->location) {
            return $this->location;
        }

        if ($this->location_id && $this->location) {
            return $this->location->display_name ?? $this->location->name ?? $this->location->full_address;
        }

        return 'No location specified';
    }

    public function getCategoryDisplayAttribute()
    {
        return $this->category_name ?? $this->category?->name ?? 'Uncategorized';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopePromoted($query)
    {
        return $query->where('is_promoted', true)
            ->where('promoted_until', '>', now());
    }

    public function scopeByCategory($query, $categoryName)
    {
        return $query->where('category_name', $categoryName)
            ->orWhereHas('category', function ($q) use ($categoryName) {
                $q->where('name', $categoryName);
            });
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%")
            ->orWhereHas('location', function ($q) use ($location) {
                $q->where('name', 'like', "%{$location}%")
                    ->orWhere('full_address', 'like', "%{$location}%");
            });
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSold()
    {
        return $this->status === 'sold' || $this->is_sold;
    }

    public function isArchived()
    {
        return $this->status === 'archived' || $this->is_archived;
    }

    public function isPromoted()
    {
        return $this->is_promoted &&
            $this->promoted_until &&
            $this->promoted_until->isFuture();
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites', 'item_id', 'user_id')->withTimestamps();
    }

    public function views()
    {
        return $this->hasMany(ItemView::class);
    }

    // Add this scope for better performance
    public function scopeWithOptimizedRelations($query)
    {
        return $query->with([
            'user:id,name,profile_image',
            'primaryImage:id,item_id,url,is_primary',
            'category:id,name,icon'
        ]);
    }
}
