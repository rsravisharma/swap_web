<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'action',
        'title',
        'description',
        'category',
        'details',
        'related_id',
        'related_type',
    ];

    protected $casts = [
        'details' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
              ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($search) . '%'])
              ->orWhereRaw('LOWER(action) LIKE ?', ['%' . strtolower($search) . '%']);
        });
    }

    // Helper methods
    public static function addHistory($userId, $type, $action, $data = [])
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'action' => $action,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'details' => $data['details'] ?? null,
            'related_id' => $data['related_id'] ?? null,
            'related_type' => $data['related_type'] ?? null,
        ]);
    }

    public function getFormattedDetailsAttribute()
    {
        if (!$this->details) {
            return null;
        }

        return collect($this->details)->map(function ($value, $key) {
            return [
                'key' => ucwords(str_replace('_', ' ', $key)),
                'value' => $value
            ];
        })->values();
    }
}
