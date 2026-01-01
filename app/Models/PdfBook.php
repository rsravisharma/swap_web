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

    protected $appends = ['cover_image_url', 'formatted_file_size'];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (empty($this->file_size)) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
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

        // Get the base URL
        $appUrl = config('app.url') ?: request()->getSchemeAndHttpHost();
        $appUrl = rtrim($appUrl, '/');

        // Use Storage::url() but ensure it's absolute
        $storageUrl = Storage::url($this->cover_image);

        // If Storage::url() returned a relative path, make it absolute
        if (strpos($storageUrl, 'http') !== 0) {
            return $appUrl . $storageUrl;
        }

        return $storageUrl;
    }

    // Helper Methods
    public function getDirectDownloadLink(): string
    {
        // If file_id is available, use it
        if (!empty($this->google_drive_file_id)) {
            return "https://drive.google.com/uc?export=download&id=" . $this->google_drive_file_id;
        }

        // Otherwise, try to extract from shareable link
        if (!empty($this->google_drive_shareable_link)) {
            if (preg_match('/\/file\/d\/([^\/]+)/', $this->google_drive_shareable_link, $matches)) {
                return "https://drive.google.com/uc?export=download&id=" . $matches[1];
            }
        }

        // Fallback to shareable link if available
        return $this->google_drive_shareable_link ?? '';
    }

    public function getPreviewLink(): string
    {
        // If file_id is available, use it
        if (!empty($this->google_drive_file_id)) {
            return "https://drive.google.com/file/d/" . $this->google_drive_file_id . "/preview";
        }

        // Otherwise, return shareable link for preview
        return $this->google_drive_shareable_link ?? '';
    }

    public function isPurchasedBy($userId): bool
    {
        return $this->purchases()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }
}
