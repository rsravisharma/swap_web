<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'subcategory_id',
        'child_subcategory_id',
        'price',
        'condition',
        'status',
        'location',
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

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function childSubcategory(): BelongsTo
    {
        return $this->belongsTo(ChildSubcategory::class);
    }

      // Get the most specific category path
    public function getCategoryPathAttribute()
    {
        $path = [];
        
        if ($this->category) {
            $path[] = $this->category->name;
        }
        
        if ($this->subcategory) {
            $path[] = $this->subcategory->name;
        }
        
        if ($this->childSubcategory) {
            $path[] = $this->childSubcategory->name;
        }
        
        return implode(' > ', $path);
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ItemImage::class)->where('is_primary', true);
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
}
