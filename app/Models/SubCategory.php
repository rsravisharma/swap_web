<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'sub_categories'; // Add this line to match your table name

    protected $fillable = [
        'category_id',
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
        'category_id' => 'integer',
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
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function childSubCategories(): HasMany
    {
        return $this->hasMany(ChildSubCategory::class, 'sub_category_id')->orderBy('sort_order')->orderBy('name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'sub_category_id');
    }

    // Get all items through child subcategories too
    public function allItems()
    {
        return Item::where('sub_category_id', $this->id)
            ->orWhereHas('childSubCategory', function ($query) {
                $query->where('sub_category_id', $this->id);
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

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Get total items count for this subcategory
    public function getItemsCountAttribute()
    {
        return $this->allItems()->count();
    }
}
