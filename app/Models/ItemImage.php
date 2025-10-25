<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'image_path',
        'filename',
        'file_size',
        'mime_type',
        'order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $appends = ['url'];

    // Relationship back to Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Helper methods
    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->image_path);
    }

    // For private storage (served via route)
    public function getPrivateUrlAttribute()
    {
        return route('item.image', ['id' => $this->id]);
    }

    public function getUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // If already a full URL, return as is
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Otherwise, prepend storage path
        return asset('storage/' . $this->image_path);
    }
}
