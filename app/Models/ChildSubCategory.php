<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChildSubCategory extends Model
{
    use HasFactory;

    protected $table = 'child_sub_categories'; // Add this line to match your table name

    protected $fillable = [
        'sub_category_id',
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
        'sub_category_id' => 'integer',
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
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'child_sub_category_id');
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

    public function scopeBySubCategory($query, $subCategoryId)
    {
        return $query->where('sub_category_id', $subCategoryId);
    }

    // Get category through subcategory
    public function getCategoryAttribute()
    {
        return $this->subCategory->category ?? null;
    }

    // Get total items count for this child subcategory
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }
}
