<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Automatically generate slug when creating
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    // Relationships
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class)->orderBy('sort_order')->orderBy('name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // Get all items through subcategories and child subcategories
    public function allItems()
    {
        return Item::whereHas('category', function ($query) {
            $query->where('id', $this->id);
        })->orWhereHas('subCategory.category', function ($query) {
            $query->where('id', $this->id);
        })->orWhereHas('childSubCategory.subCategory.category', function ($query) {
            $query->where('id', $this->id);
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Get total items count for this category
    public function getItemsCountAttribute()
    {
        return $this->allItems()->count();
    }
}
