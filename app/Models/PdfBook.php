<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PdfBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'seller_id',
        'isbn',
        'description',
        'author',
        'publisher',
        'publication_year',
        'cover_image',
        'price',
        'google_drive_file_id',
        'google_drive_shareable_link',
        'file_size',
        'is_available',
        'total_pages',
        'language'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'publication_year' => 'integer',
        'total_pages' => 'integer',
        'file_size' => 'integer'
    ];

    protected $hidden = [
        'google_drive_file_id', // Don't expose in API responses
        'google_drive_shareable_link'
    ];

    protected $appends = ['cover_image_url'];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }


    // Relationships
    public function purchases()
    {
        return $this->hasMany(PdfBookPurchase::class, 'book_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'pdf_book_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }

        // Generate full URL using Storage facade
        return Storage::url($this->cover_image);
    }

    // Helper Methods
    public function getDirectDownloadLink(): string
    {
        return "https://drive.google.com/uc?export=download&id=" . $this->google_drive_file_id;
    }

    public function getPreviewLink(): string
    {
        return "https://drive.google.com/file/d/" . $this->google_drive_file_id . "/preview";
    }

    public function isPurchasedBy($userId): bool
    {
        return $this->purchases()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }
}
