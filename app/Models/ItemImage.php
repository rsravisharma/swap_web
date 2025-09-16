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
        return asset('storage/' . $this->image_path);
    }
}
