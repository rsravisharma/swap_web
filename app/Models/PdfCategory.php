<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PdfCategory extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'level'
    ];

    protected $casts = [
        'level' => 'integer'
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(PdfCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PdfCategory::class, 'parent_id');
    }

    public function pdfBooks()
    {
        return $this->hasMany(PdfBook::class, 'category_id');
    }

    // Scopes
    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Helpers
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;

        while ($category) {
            array_unshift($breadcrumb, $category);
            $category = $category->parent;
        }

        return $breadcrumb;
    }

    public function getFullNameAttribute(): string
    {
        $names = [];
        $category = $this;

        while ($category) {
            $names[] = $category->name;
            $category = $category->parent;
        }

        return implode(' > ', array_reverse($names));
    }
}
