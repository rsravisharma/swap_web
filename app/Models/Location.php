<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic location info
        'type',
        'name',
        'address',
        'description',
        
        // Geographic data
        'latitude',
        'longitude',
        
        // Relationships
        'city_id',
        'country_id', 
        'university_id',
        'created_by',
        
        // Geocoding data
        'geocoding_source',
        'geocoding_confidence',
        'geocoded_at',
        'osm_id',
        'osm_type',
        'place_type',
        'osm_importance',
        
        // Status and metadata
        'metadata',
        'manually_edited',
        'is_active',
        'is_popular',
        'is_safe_meetup',
        'popularity_score',
        'last_edited_at',
    ];

    protected $casts = [
        // Geographic coordinates
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        
        // Geocoding data
        'geocoding_confidence' => 'decimal:2',
        'osm_importance' => 'decimal:6',
        
        // JSON and boolean fields
        'metadata' => 'array',
        'manually_edited' => 'boolean',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_safe_meetup' => 'boolean',
        
        // Integer fields
        'popularity_score' => 'integer',
        
        // Timestamps
        'geocoded_at' => 'datetime',
        'last_edited_at' => 'datetime',
    ];

    // Define location types as constants
    const TYPE_CURRENT = 'current';
    const TYPE_CAMPUS = 'campus';
    const TYPE_CUSTOM = 'custom';
    const TYPE_ONLINE = 'online';
    const TYPE_SHIPPING = 'shipping';

    // Define geocoding sources
    const GEOCODING_SOURCES = [
        'nominatim',
        'google',
        'opencage',
        'mapbox',
        'manual',
        'user_input'
    ];

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recentUsers()
    {
        return $this->hasMany(UserRecentLocation::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->where('is_popular', true);
    }

    public function scopeSafeMeetup(Builder $query): Builder
    {
        return $query->where('is_safe_meetup', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10): Builder
    {
        $haversine = "(6371 * acos(cos(radians($lat)) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) 
                        - radians($lng)) 
                        + sin(radians($lat)) 
                        * sin(radians(latitude))))";
                        
        return $query->selectRaw("*, $haversine AS distance")
                    ->whereRaw("$haversine < ?", [$radiusKm])
                    ->orderBy('distance');
    }

    public function scopeWithCoordinates(Builder $query): Builder
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
    }

    public function scopeManuallyEdited(Builder $query): Builder
    {
        return $query->where('manually_edited', true);
    }

    public function scopeGeocodedBy(Builder $query, string $source): Builder
    {
        return $query->where('geocoding_source', $source);
    }

    /**
     * Accessors
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->address ?? 'Unknown Location';
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city?->name,
            $this->country?->name,
        ]);
        
        return !empty($parts) ? implode(', ', $parts) : null;
    }

    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }
        
        return null;
    }

    public function getIsGeocodedAttribute(): bool
    {
        return !is_null($this->geocoded_at);
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CURRENT => 'Current Location',
            self::TYPE_CAMPUS => 'Campus Location',
            self::TYPE_CUSTOM => 'Custom Location',
            self::TYPE_ONLINE => 'Online Location',
            self::TYPE_SHIPPING => 'Shipping Address',
            default => ucfirst($this->type ?? 'Unknown'),
        };
    }

    /**
     * Mutators
     */
    public function setLastEditedAtAttribute($value)
    {
        $this->attributes['last_edited_at'] = $value ?? now();
    }

    /**
     * Helper Methods
     */
    public function updatePopularityScore(): void
    {
        $score = $this->recentUsers()->count();
        
        if ($this->is_popular) {
            $score += 10;
        }
        
        if ($this->is_safe_meetup) {
            $score += 5;
        }
        
        if ($this->university_id) {
            $score += 3;
        }
        
        $this->update(['popularity_score' => $score]);
    }

    public function markAsManuallyEdited(): void
    {
        $this->update([
            'manually_edited' => true,
            'last_edited_at' => now(),
        ]);
    }

    public function updateGeocodingData(array $data): void
    {
        $this->update([
            'geocoding_source' => $data['source'] ?? null,
            'geocoding_confidence' => $data['confidence'] ?? null,
            'geocoded_at' => $data['geocoded_at'] ?? now(),
            'osm_id' => $data['osm_id'] ?? null,
            'osm_type' => $data['osm_type'] ?? null,
            'place_type' => $data['place_type'] ?? null,
            'osm_importance' => $data['osm_importance'] ?? null,
        ]);
    }

    public function calculateDistance(float $lat, float $lng): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function isNearby(float $lat, float $lng, float $radiusKm = 1): bool
    {
        return $this->calculateDistance($lat, $lng) <= $radiusKm;
    }

    public function hasValidCoordinates(): bool
    {
        return !is_null($this->latitude) && 
               !is_null($this->longitude) && 
               $this->latitude >= -90 && 
               $this->latitude <= 90 && 
               $this->longitude >= -180 && 
               $this->longitude <= 180;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($location) {
            if (auth()->check()) {
                $location->created_by = auth()->id();
            }
        });

        static::updating(function ($location) {
            if ($location->isDirty(['address', 'latitude', 'longitude', 'metadata'])) {
                $location->last_edited_at = now();
            }
        });
    }
}
