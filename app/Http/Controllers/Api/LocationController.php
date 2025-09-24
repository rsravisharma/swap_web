<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use App\Models\City;
use App\Models\Location;
use App\Models\University;
use App\Models\UserRecentLocation;
use App\Services\EnhancedGeocodingService;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    private const CACHE_DURATION_STATIC = 86400; // 24 hours
    private const CACHE_DURATION_DYNAMIC = 3600; // 1 hour

    /**
     * Get list of countries
     * GET /location/countries
     */
    public function getCountries(): JsonResponse
    {
        try {
            $countries = Cache::remember('countries_list', self::CACHE_DURATION_STATIC, function () {
                return Country::active()
                    ->orderBy('name')
                    ->get(['id', 'name', 'code', 'flag_emoji', 'phone_code']);
            });

            return response()->json([
                'success' => true,
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cities by country
     * GET /location/cities
     */
    public function getCities(Request $request): JsonResponse
    {
        $countryCode = $request->query('country_code');

        if (empty($countryCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Country code is required'
            ], 400);
        }

        try {
            $cacheKey = "cities_list_{$countryCode}";

            $cities = Cache::remember($cacheKey, self::CACHE_DURATION_DYNAMIC, function () use ($countryCode) {
                return City::where('country_code', $countryCode)
                    ->where('is_active', true)
                    ->with('country:id,name,code')
                    ->orderBy('name')
                    ->get(['id', 'name', 'state', 'country_code', 'latitude', 'longitude']);
            });

            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get universities
     * GET /location/universities
     */
    public function getUniversities(Request $request): JsonResponse
    {
        try {
            $countryId = $request->query('country_id');
            $city = $request->query('city');
            $type = $request->query('type');
            $search = $request->query('search');

            // Create cache key based on filters
            $cacheKey = 'universities_list_' . md5(serialize([
                'country_id' => $countryId,
                'city' => $city,
                'type' => $type,
                'search' => $search
            ]));

            $universities = Cache::remember($cacheKey, self::CACHE_DURATION_DYNAMIC, function () use ($countryId, $city, $type, $search) {
                $query = University::active()
                    ->with(['country:id,name,code']);

                if ($countryId) {
                    $query->where('country_id', $countryId);
                }

                if ($city) {
                    $query->byCity($city);
                }

                if ($type) {
                    $query->byType($type);
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%");
                    });
                }

                return $query->orderBy('name')
                    ->get(['id', 'name', 'slug', 'code', 'city', 'state', 'country_id', 'type', 'established_year', 'ranking']);
            });

            return response()->json([
                'success' => true,
                'data' => $universities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch universities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse geocode coordinates to location
     * GET /location/reverse-geocode
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            // Find nearest location using spatial queries or distance calculation
            $location = Location::selectRaw('
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance
            ', [$lat, $lng, $lat])
                ->with(['city:id,name,state,country_code', 'country:id,name,code', 'university:id,name'])
                ->where('is_active', true)
                ->orderBy('distance')
                ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'No location found for given coordinates'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address,
                    'city' => $location->city,
                    'country' => $location->country,
                    'university' => $location->university,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'type' => $location->type,
                    'distance' => round($location->distance, 2)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reverse geocode',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's recent locations
     * GET /user/recent-locations
     */
    public function getRecentLocations(): JsonResponse
    {
        try {
            $userId = Auth::id();

            // ✅ Enhanced query with better eager loading
            $recentLocations = UserRecentLocation::with([
                'location' => function ($query) {
                    $query->select([
                        'id',
                        'name',
                        'address',
                        'latitude',
                        'longitude',
                        'type',
                        'description',
                        'is_safe_meetup',
                        'metadata',
                        'manually_edited',
                        'last_edited_at',
                        'geocoding_source',
                        'geocoding_confidence',
                        'geocoded_at',
                        'city_id',
                        'country_id',
                        'university_id',
                        'created_at',
                        'updated_at'
                    ]);
                },
                'location.city:id,name,state',
                'location.country:id,name,code',
                'location.university:id,name'
            ])
                ->where('user_id', $userId)
                ->orderBy('visited_at', 'desc')
                ->limit(10)
                ->get();

            // ✅ Transform data with enhanced metadata handling
            $transformedLocations = $recentLocations->map(function ($recentLocation) {
                $location = $recentLocation->location;

                if (!$location) {
                    Log::warning('Recent location found but location deleted', [
                        'recent_location_id' => $recentLocation->id,
                        'location_id' => $recentLocation->location_id
                    ]);
                    return null;
                }

                // ✅ Extract metadata for Flutter compatibility
                $metadata = is_array($location->metadata) ? $location->metadata : [];

                return [
                    // Basic location info
                    'id' => $location->id,
                    'name' => $location->name,
                    'title' => $location->name ?? 'Unknown Location',
                    'address' => $location->address,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'type' => $location->type,
                    'description' => $location->description,
                    'is_safe_meetup' => (bool)$location->is_safe_meetup,

                    // ✅ Metadata handling
                    'metadata' => $metadata,

                    // ✅ Manual edit tracking
                    'manually_edited' => (bool)($location->manually_edited ?? ($location->geocoding_source === 'manual_edit')),
                    'last_edited' => $location->last_edited_at?->toISOString() ?? $location->updated_at?->toISOString(),

                    // ✅ Geocoding information
                    'geocoding_source' => $location->geocoding_source,
                    'confidence' => $location->geocoding_confidence,
                    'geocoded_at' => $location->geocoded_at?->toISOString(),

                    // ✅ Recent location tracking
                    'visited_at' => $recentLocation->visited_at->toISOString(),
                    'recent_location_id' => $recentLocation->id,

                    // ✅ Timestamps
                    'created_at' => $location->created_at?->toISOString(),
                    'updated_at' => $location->updated_at?->toISOString(),

                    // ✅ Flattened relationships
                    'city' => $location->city ? [
                        'id' => $location->city->id,
                        'name' => $location->city->name,
                        'state' => $location->city->state,
                    ] : null,

                    'country' => $location->country ? [
                        'id' => $location->country->id,
                        'name' => $location->country->name,
                        'code' => $location->country->code,
                    ] : null,

                    'university' => $location->university ? [
                        'id' => $location->university->id,
                        'name' => $location->university->name,
                    ] : null,
                ];
            })->filter()->values(); // Remove nulls and reset keys

            Log::info('Recent locations fetched successfully', [
                'user_id' => $userId,
                'total_count' => $transformedLocations->count(),
                'location_types' => $transformedLocations->groupBy('type')->map->count(),
                'manually_edited_count' => $transformedLocations->where('manually_edited', true)->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $transformedLocations,
                'count' => $transformedLocations->count(),
                'message' => 'Recent locations retrieved successfully',
                'meta' => [
                    'total_locations' => $transformedLocations->count(),
                    'manually_edited' => $transformedLocations->where('manually_edited', true)->count(),
                    'location_types' => $transformedLocations->groupBy('type')->map->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching recent locations', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent locations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * Save location to user's recent locations
     * POST /user/recent-locations
     */
    public function saveRecentLocation(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'type' => 'nullable|string|in:current,campus,custom,online,shipping',
            'address' => 'nullable|string|max:500',
            'accuracy' => 'nullable|numeric|min:0',
            'timestamp' => 'nullable|string',

            // ✅ ADD: Optional fields from frontend
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'house_number' => 'nullable|string|max:50',
            'road' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // ✅ ENHANCED: Perform geocoding on backend
            $osmGeocodingService = new EnhancedGeocodingService();
            $geocodedData = $osmGeocodingService->reverseGeocode(
                $data['latitude'],
                $data['longitude']
            );

            // ✅ Build comprehensive metadata from multiple sources
            $metadata = [
                // Frontend provided data (highest priority)
                'house_number' => $data['house_number'] ?? $geocodedData['house_number'] ?? null,
                'road' => $data['road'] ?? $geocodedData['road'] ?? null,
                'city' => $data['city'] ?? $geocodedData['city'] ?? null,
                'state' => $data['state'] ?? $geocodedData['state'] ?? null,
                'country' => $data['country'] ?? $geocodedData['country'] ?? null,
                'postcode' => $data['postcode'] ?? $geocodedData['postcode'] ?? null,

                // Technical metadata
                'original_geocoding_source' => $geocodedData['source'] ?? 'none',
                'original_confidence' => $geocodedData['confidence'] ?? 0.0,
                'user_provided' => !empty($data['city']) || !empty($data['address']),
                'frontend_accuracy' => $data['accuracy'] ?? null,
                'frontend_timestamp' => $data['timestamp'] ?? null,

                // Creation tracking
                'created_method' => 'save_recent_location',
                'created_at' => now()->toISOString(),
                'created_by' => $userId,

                // OSM specific data
                'osm_data' => [
                    'osm_id' => $geocodedData['osm_id'] ?? null,
                    'osm_type' => $geocodedData['osm_type'] ?? null,
                    'place_type' => $geocodedData['place_type'] ?? null,
                    'osm_importance' => $geocodedData['osm_importance'] ?? null,
                ],

                // Additional geocoding metadata
                'geocoding_details' => $geocodedData['details'] ?? [],
            ];

            // ✅ FIXED: Enhanced address building - ignore temporary addresses
            $finalAddress = null;

            // Don't use temporary addresses from frontend
            if (
                !empty($data['address']) &&
                !str_contains(strtolower($data['address']), 'finding') &&
                !str_contains(strtolower($data['address']), 'loading') &&
                !str_contains(strtolower($data['address']), 'getting')
            ) {
                $finalAddress = $data['address'];
            }

            // Try geocoded address next
            if (!$finalAddress && !empty($geocodedData['address'])) {
                $finalAddress = $geocodedData['address'];
            }

            // Build from components if we have them
            if (!$finalAddress) {
                $finalAddress = $this->buildAddressFromComponents($metadata);
            }

            // Last resort - coordinates
            if (!$finalAddress) {
                $finalAddress = "Lat: {$data['latitude']}, Lng: {$data['longitude']}";
            }

            \Log::info('Address building debug', [
                'frontend_address' => $data['address'] ?? null,
                'geocoded_address' => $geocodedData['address'] ?? null,
                'metadata_components' => [
                    'house_number' => $metadata['house_number'],
                    'road' => $metadata['road'],
                    'city' => $metadata['city'],
                    'state' => $metadata['state'],
                    'country' => $metadata['country'],
                    'postcode' => $metadata['postcode'],
                ],
                'final_address' => $finalAddress
            ]);

            // ✅ Try to resolve relationships before creating location
            $cityId = null;
            $countryId = null;

            if (!empty($metadata['country'])) {
                $countryId = $this->findOrCreateCountry($metadata['country']);
                if ($countryId) {
                    $metadata['resolved_country_id'] = $countryId;
                }
            }

            if (!empty($metadata['city']) && !empty($metadata['state']) && $countryId) {
                $cityId = $this->findOrCreateCity($metadata['city'], $metadata['state'], $countryId);
                if ($cityId) {
                    $metadata['resolved_city_id'] = $cityId;
                }
            }

            // ✅ Create or update location with all fields
            $location = Location::firstOrCreate([
                'latitude' => round($data['latitude'], 8),
                'longitude' => round($data['longitude'], 8),
            ], [
                // Basic fields
                'name' => $data['name'],
                'address' => $finalAddress,
                'type' => $data['type'] ?? 'current',
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'created_by' => $userId,

                // Relationships
                'city_id' => $cityId,
                'country_id' => $countryId,

                // Geocoding fields
                'geocoding_source' => $geocodedData['source'] ?? 'none',
                'geocoding_confidence' => $geocodedData['confidence'] ?? 0.0,
                'geocoded_at' => now(),
                'osm_id' => $geocodedData['osm_id'] ?? null,
                'osm_type' => $geocodedData['osm_type'] ?? null,
                'place_type' => $geocodedData['place_type'] ?? null,
                'osm_importance' => $geocodedData['osm_importance'] ?? null,

                // ✅ Store complete metadata as JSON
                'metadata' => $metadata,

                // Status fields
                'manually_edited' => false,
                'last_edited_at' => now(),
            ]);

            // ✅ If location exists, update it with new data if needed
            if (!$location->wasRecentlyCreated) {
                $shouldUpdate = false;
                $updates = [];

                // Update if we have better geocoding data
                if (($geocodedData['confidence'] ?? 0) > ($location->geocoding_confidence ?? 0)) {
                    $updates['geocoding_source'] = $geocodedData['source'];
                    $updates['geocoding_confidence'] = $geocodedData['confidence'];
                    $updates['geocoded_at'] = now();
                    $shouldUpdate = true;
                }

                // Update if user provided additional data
                if (!empty($data['address']) && $data['address'] !== $location->address) {
                    $updates['address'] = $finalAddress;
                    $shouldUpdate = true;
                }

                // Merge metadata
                $existingMetadata = $location->metadata ?? [];
                $mergedMetadata = array_merge($existingMetadata, [
                    'access_history' => array_merge(
                        $existingMetadata['access_history'] ?? [],
                        [[
                            'accessed_at' => now()->toISOString(),
                            'accessed_by' => $userId,
                            'method' => 'save_recent_location',
                            'frontend_data' => array_filter([
                                'accuracy' => $data['accuracy'] ?? null,
                                'timestamp' => $data['timestamp'] ?? null,
                            ])
                        ]]
                    ),

                    // Update user-provided fields
                    'house_number' => $data['house_number'] ?? $existingMetadata['house_number'] ?? $metadata['house_number'],
                    'road' => $data['road'] ?? $existingMetadata['road'] ?? $metadata['road'],
                    'city' => $data['city'] ?? $existingMetadata['city'] ?? $metadata['city'],
                    'state' => $data['state'] ?? $existingMetadata['state'] ?? $metadata['state'],
                    'country' => $data['country'] ?? $existingMetadata['country'] ?? $metadata['country'],
                    'postcode' => $data['postcode'] ?? $existingMetadata['postcode'] ?? $metadata['postcode'],

                    // Keep latest geocoding info
                    'latest_geocoding_source' => $geocodedData['source'] ?? $existingMetadata['latest_geocoding_source'],
                    'latest_confidence' => $geocodedData['confidence'] ?? $existingMetadata['latest_confidence'],
                    'last_accessed' => now()->toISOString(),
                ]);

                $updates['metadata'] = $mergedMetadata;
                $updates['last_edited_at'] = now();

                if ($shouldUpdate || $mergedMetadata !== $existingMetadata) {
                    $location->update($updates);
                }
            }

            // ✅ Save to user recent locations
            UserRecentLocation::updateOrCreate([
                'user_id' => $userId,
                'location_id' => $location->id
            ], [
                'visited_at' => now()
            ]);

            // ✅ Load relationships for complete response
            $location->load([
                'city:id,name,state',
                'country:id,name,code',
                'university:id,name'
            ]);

            // ✅ RETURN ENHANCED DATA with all metadata
            return response()->json([
                'success' => true,
                'message' => 'Location saved successfully',
                'data' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'title' => $location->name, // For frontend compatibility
                    'address' => $location->address,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                    'type' => $location->type,
                    'description' => $location->description,

                    // ✅ Complete metadata (this fixes your frontend casting issue)
                    'metadata' => $location->metadata,

                    // ✅ Geocoding information
                    'geocoding_source' => $location->geocoding_source,
                    'confidence' => (float) ($location->geocoding_confidence ?? 0),
                    'geocoded_at' => $location->geocoded_at?->toISOString(),

                    // ✅ Status fields
                    'manually_edited' => $location->manually_edited,
                    'last_edited' => $location->last_edited_at?->toISOString(),
                    'is_safe_meetup' => $location->is_safe_meetup,

                    // ✅ Relationships
                    'city' => $location->city?->name,
                    'country' => $location->country?->name,
                    'university' => $location->university?->name,

                    // ✅ Additional response data
                    'was_created' => $location->wasRecentlyCreated,
                    'added_to_recent' => true,
                    'recent_visited_at' => now()->toISOString(),
                ]
            ], $location->wasRecentlyCreated ? 201 : 200);
        } catch (\Exception $e) {
            Log::error('Error saving recent location', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save location',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Helper method to build address from components
     */
    private function buildAddressFromComponents(array $metadata): ?string
    {
        $addressParts = [];

        // Build street address part
        $streetParts = [];
        if (!empty($metadata['house_number'])) {
            $streetParts[] = $metadata['house_number'];
        }
        if (!empty($metadata['road'])) {
            $streetParts[] = $metadata['road'];
        }

        if (!empty($streetParts)) {
            $addressParts[] = implode(' ', $streetParts);
        }

        // Add city and area parts
        if (!empty($metadata['city'])) {
            $addressParts[] = $metadata['city'];
        }

        if (!empty($metadata['postcode'])) {
            $addressParts[] = $metadata['postcode'];
        }

        if (!empty($metadata['state'])) {
            $addressParts[] = $metadata['state'];
        }

        if (!empty($metadata['country'])) {
            $addressParts[] = $metadata['country'];
        }

        $result = !empty($addressParts) ? implode(', ', $addressParts) : null;

        \Log::info('Built address from components', [
            'components' => $metadata,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Helper method to find or create country
     */
    private function findOrCreateCountry(string $countryName): ?int
    {
        try {
            $country = Country::firstOrCreate([
                'name' => $countryName,
            ], [
                'code' => strtoupper(substr($countryName, 0, 2)), // Simple code generation
                'is_active' => true,
            ]);

            return $country->id;
        } catch (\Exception $e) {
            Log::warning('Could not create country', [
                'country' => $countryName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Helper method to find or create city
     */
    private function findOrCreateCity(string $cityName, string $stateName, int $countryId): ?int
    {
        try {
            $city = City::firstOrCreate([
                'name' => $cityName,
                'state' => $stateName,
                'country_id' => $countryId,
            ], [
                'is_active' => true,
            ]);

            return $city->id;
        } catch (\Exception $e) {
            Log::warning('Could not create city', [
                'city' => $cityName,
                'state' => $stateName,
                'country_id' => $countryId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function updateLocation(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'house_number' => 'nullable|string|max:50',
            'road' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'city_id' => 'nullable|integer|exists:cities,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'manually_edited' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $location = Location::find($id);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            if ($location->created_by !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this location'
                ], 403);
            }

            $data = $validator->validated();

            // ✅ Get existing metadata and merge with new data
            $currentMetadata = $location->metadata ?? [];

            // ✅ Build comprehensive metadata
            $newMetadata = array_merge($currentMetadata, [
                // 'house_number' => $data['house_number'] ?? $currentMetadata['house_number'] ?? null,
                'road' => $data['road'] ?? $currentMetadata['road'] ?? null,
                'city' => $data['city'] ?? $currentMetadata['city'] ?? null,
                'state' => $data['state'] ?? $currentMetadata['state'] ?? null,
                'country' => $data['country'] ?? $currentMetadata['country'] ?? null,
                'postcode' => $data['postcode'] ?? $currentMetadata['postcode'] ?? null,

                // ✅ Track edit history
                'edit_history' => array_merge(
                    $currentMetadata['edit_history'] ?? [],
                    [[
                        'edited_at' => now()->toISOString(),
                        'edited_by' => $userId,
                        'changes' => array_filter([
                            'address' => $data['address'] ?? null,
                            'city' => $data['city'] ?? null,
                            'state' => $data['state'] ?? null,
                            'country' => $data['country'] ?? null,
                            'postcode' => $data['postcode'] ?? null,
                        ])
                    ]]
                ),

                // ✅ Preserve original geocoding data
                'original_geocoding_source' => $currentMetadata['original_geocoding_source'] ?? $location->geocoding_source,
                'original_confidence' => $currentMetadata['original_confidence'] ?? $location->geocoding_confidence,
                'user_edited' => true,
                'last_edit_method' => 'manual_form',
            ]);

            // ✅ Update location fields
            if (isset($data['address'])) {
                $location->address = $data['address'];
            }

            // ✅ Try to resolve city/country relationships (optional)
            if (!empty($data['country'])) {
                $countryId = $this->findOrCreateCountry($data['country']);
                if ($countryId) {
                    $location->country_id = $countryId;
                    $newMetadata['resolved_country_id'] = $countryId;
                }
            }

            if (!empty($data['city']) && !empty($data['state']) && $location->country_id) {
                $cityId = $this->findOrCreateCity($data['city'], $data['state'], $location->country_id);
                if ($cityId) {
                    $location->city_id = $cityId;
                    $newMetadata['resolved_city_id'] = $cityId;
                }
            }

            if (isset($data['description'])) {
                $location->description = $data['description'];
            }

            // ✅ Store all metadata as JSON
            $location->metadata = $newMetadata;

            // ✅ Mark as manually edited
            $location->manually_edited = true;
            $location->last_edited_at = now();
            $location->geocoding_source = 'manual_edit';
            $location->geocoding_confidence = 1.0;
            $location->geocoded_at = now();

            $location->save();

            UserRecentLocation::updateOrCreate([
                'user_id' => $userId,
                'location_id' => $location->id
            ], [
                'visited_at' => now()
            ]);

            // Load relationships for response
            $location->load([
                'city:id,name,state',
                'country:id,name,code',
                'university:id,name'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'type' => $location->type,
                    'description' => $location->description,

                    // ✅ Full metadata for frontend
                    'metadata' => $location->metadata,

                    // ✅ Convenience fields for frontend
                    'manually_edited' => $location->manually_edited,
                    'last_edited' => $location->last_edited_at?->toISOString(),
                    'geocoding_source' => $location->geocoding_source,
                    'confidence' => $location->geocoding_confidence,

                    // Relationships
                    'city' => $location->city,
                    'country' => $location->country,
                    'university' => $location->university,

                    // ✅ ADD: Indicate it was added to recent locations
                    'added_to_recent' => true,
                    'recent_visited_at' => now()->toISOString(),
                ],
                'message' => 'Location updated and added to recent locations'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating location', [
                'location_id' => $id,
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getGeocodingStats(): JsonResponse
    {
        try {
            $geocodingService = new EnhancedGeocodingService();
            $stats = $geocodingService->getUsageStats();

            // Add limits for reference
            $limits = [
                'nominatim' => ['daily' => 86400, 'note' => '1 request per second'],
                'locationiq' => ['daily' => 5000, 'note' => 'Free tier'],
                'opencage' => ['daily' => 2500, 'note' => 'Free tier'],
                'positionstack' => ['daily' => 833, 'note' => '25,000/month'],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'usage' => $stats,
                    'limits' => $limits,
                    'percentage_used' => [
                        'locationiq' => round(($stats['locationiq'] / 5000) * 100, 1),
                        'opencage' => round(($stats['opencage'] / 2500) * 100, 1),
                        'positionstack' => round(($stats['positionstack'] / 833) * 100, 1),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get geocoding stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get all locations with relationships
     * GET /location/all
     */
    public function getAllLocations(Request $request): JsonResponse
    {
        try {
            $type = $request->query('type');

            $cacheKey = $type ? "locations_by_type_{$type}" : 'all_locations';

            $locations = Cache::remember($cacheKey, self::CACHE_DURATION_DYNAMIC, function () use ($type) {
                $query = Location::with([
                    'city:id,name,state,country_code',
                    'country:id,name,code',
                    'university:id,name,city,state'
                ])
                    ->where('is_active', true);

                if ($type) {
                    $query->where('type', $type);
                }

                return $query->orderBy('name')->get();
            });

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campus locations with university details
     * GET /location/campus
     */
    public function getCampusLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('campus_locations', self::CACHE_DURATION_DYNAMIC, function () {
                return Location::where('type', 'campus')
                    ->active()
                    ->with([
                        'city:id,name,state,country_code',
                        'country:id,name,code',
                        'university:id,name,code,type,city,state'
                    ])
                    ->orderBy('name')
                    ->get();
            });

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch campus locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular locations
     * GET /location/popular
     */
    public function getPopularLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('popular_locations', self::CACHE_DURATION_DYNAMIC, function () {
                return Location::popular()
                    ->active()
                    ->with([
                        'city:id,name,state,country_code',
                        'country:id,name,code'
                    ])
                    ->orderBy('popularity_score', 'desc')
                    ->limit(20)
                    ->get();
            });

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get safe meetup locations
     * GET /location/meetup
     */
    public function getMeetupLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('meetup_locations', self::CACHE_DURATION_DYNAMIC, function () {
                return Location::safeMeetup()
                    ->active()
                    ->with([
                        'city:id,name,state,country_code',
                        'country:id,name,code'
                    ])
                    ->orderBy('name')
                    ->get();
            });

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch meetup locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search locations with relationships
     * GET /location/search
     */
    public function searchLocations(Request $request): JsonResponse
    {
        $query = $request->query('q');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ], 422);
        }

        try {
            $locations = Location::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhereHas('city', function ($cityQuery) use ($query) {
                        $cityQuery->where('name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('university', function ($uniQuery) use ($query) {
                        $uniQuery->where('name', 'like', "%{$query}%");
                    });
            })
                ->active()
                ->with([
                    'city:id,name,state,country_code',
                    'country:id,name,code',
                    'university:id,name'
                ])
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get nearby locations
     * GET /location/nearby
     */
    public function getNearbyLocations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
            'type' => 'nullable|string|in:current,campus,custom,online,shipping'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lat = $request->input('lat');
            $lng = $request->input('lng');
            $radius = $request->input('radius', 10); // Default 10 km
            $type = $request->input('type');

            $query = Location::selectRaw('
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance
            ', [$lat, $lng, $lat])
                ->with([
                    'city:id,name,state,country_code',
                    'country:id,name,code',
                    'university:id,name'
                ])
                ->having('distance', '<=', $radius)
                ->active();

            if ($type) {
                $query->where('type', $type);
            }

            $locations = $query->orderBy('distance')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch nearby locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add custom location
     * POST /location/custom
     */
    public function addCustomLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90', // Made nullable for manual entry
            'longitude' => 'nullable|numeric|between:-180,180', // Made nullable for manual entry
            'city_id' => 'nullable|integer|exists:cities,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'university_id' => 'nullable|integer|exists:universities,id',
            'type' => 'nullable|string|in:current,campus,custom,online,shipping',
            'description' => 'nullable|string|max:1000',
            'is_safe_meetup' => 'boolean',

            // ✅ Add validation for manual address entry (from your Flutter dialog)
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'house_number' => 'nullable|string|max:50',
            'road' => 'nullable|string|max:200',
            'manually_edited' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $data = $validator->validated();

            // ✅ Set default values
            $data['created_by'] = $userId;
            $data['type'] = $data['type'] ?? 'custom';
            $data['is_active'] = true;

            // ✅ Handle manual address entry (from Flutter dialog)
            if (!empty($data['city']) || !empty($data['country']) || !empty($data['state'])) {
                // Try to find or create country
                if (!empty($data['country']) && empty($data['country_id'])) {
                    $countryId = $this->findOrCreateCountry($data['country']);
                    if ($countryId) {
                        $data['country_id'] = $countryId;
                    }
                }

                // Try to find or create city
                if (!empty($data['city']) && !empty($data['state']) && !empty($data['country_id'])) {
                    $cityId = $this->findOrCreateCity($data['city'], $data['state'], $data['country_id']);
                    if ($cityId) {
                        $data['city_id'] = $cityId;
                    }
                }

                // ✅ Build comprehensive metadata from manual entry
                $metadata = [
                    'house_number' => $data['house_number'] ?? null,
                    'road' => $data['road'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'country' => $data['country'] ?? null,
                    'postcode' => $data['postcode'] ?? null,
                    'manually_created' => true,
                    'created_method' => 'manual_form',
                    'created_at' => now()->toISOString(),
                    'created_by' => $userId,
                ];

                $data['metadata'] = $metadata;
            }

            // ✅ Set geocoding info for manually created locations
            if ($data['manually_edited'] ?? false) {
                $data['geocoding_source'] = 'manual_entry';
                $data['geocoding_confidence'] = 1.0;
                $data['geocoded_at'] = now();
            }

            // ✅ Remove validation-only fields before creating location
            $locationData = array_filter($data, function ($key) {
                return !in_array($key, ['city', 'state', 'country', 'postcode', 'house_number', 'road', 'manually_edited']);
            }, ARRAY_FILTER_USE_KEY);

            if (empty($data['latitude']) && empty($data['longitude']) && !empty($data['address'])) {
                // Try to get coordinates from the enhanced geocoding service
                $geocodedData = $this->geocodeAddress($data['address'], $data);

                if ($geocodedData) {
                    $data['latitude'] = $geocodedData['latitude'];
                    $data['longitude'] = $geocodedData['longitude'];
                    $data['geocoding_source'] = $geocodedData['source'];
                    $data['geocoding_confidence'] = $geocodedData['confidence'];
                    $data['geocoded_at'] = now();

                    // Enhance metadata with geocoded info
                    if (isset($data['metadata'])) {
                        $data['metadata'] = array_merge($data['metadata'], [
                            'geocoded_from_address' => true,
                            'original_address_input' => $data['address'],
                            'geocoded_address' => $geocodedData['geocoded_address'],
                            'geocoding_metadata' => $geocodedData['metadata'],
                        ]);
                    }

                    Log::info('Successfully geocoded custom location', [
                        'original_address' => $data['address'],
                        'coordinates' => [$geocodedData['latitude'], $geocodedData['longitude']],
                        'confidence' => $geocodedData['confidence']
                    ]);
                }
            }

            $location = Location::create($locationData);

            // ✅ ADD TO RECENT LOCATIONS: Add immediately after creation
            $recentLocation = UserRecentLocation::updateOrCreate([
                'user_id' => $userId,
                'location_id' => $location->id
            ], [
                'visited_at' => now()
            ]);

            Log::info('Custom location created and added to recent locations', [
                'user_id' => $userId,
                'location_id' => $location->id,
                'address' => $location->address,
                'type' => $location->type
            ]);

            // Load relationships for response
            $location->load([
                'city:id,name,state',
                'country:id,name,code',
                'university:id,name'
            ]);

            // Clear relevant caches
            Cache::forget('all_locations');
            Cache::forget("locations_by_type_{$location->type}");

            if ($location->type === 'campus') {
                Cache::forget('campus_locations');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'type' => $location->type,
                    'description' => $location->description,
                    'is_safe_meetup' => $location->is_safe_meetup,

                    // ✅ Include metadata for frontend
                    'metadata' => $location->metadata ?? [],

                    // ✅ Geocoding info
                    'geocoding_source' => $location->geocoding_source,
                    'confidence' => $location->geocoding_confidence,
                    'geocoded_at' => $location->geocoded_at,

                    // ✅ Recent location info
                    'added_to_recent' => true,
                    'recent_visited_at' => $recentLocation->visited_at->toISOString(),

                    // ✅ Frontend compatibility flags
                    'manually_edited' => $location->geocoding_source === 'manual_entry',
                    'last_edited' => $location->updated_at?->toISOString(),

                    // Relationships
                    'city' => $location->city,
                    'country' => $location->country,
                    'university' => $location->university,
                ],
                'message' => 'Custom location created and added to recent locations'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating custom location', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add custom location',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Helper method to geocode an address to get coordinates
     */
    private function geocodeAddress(string $address, array $additionalData = []): ?array
    {
        // Build a more complete address string
        $addressParts = [$address];

        if (!empty($additionalData['city'])) {
            $addressParts[] = $additionalData['city'];
        }
        if (!empty($additionalData['state'])) {
            $addressParts[] = $additionalData['state'];
        }
        if (!empty($additionalData['country'])) {
            $addressParts[] = $additionalData['country'];
        }

        $fullAddress = implode(', ', array_filter($addressParts));

        Log::info('Attempting to geocode address', [
            'original_address' => $address,
            'full_address' => $fullAddress,
            'additional_data' => $additionalData
        ]);

        try {
            // ✅ Use your enhanced geocoding service for forward geocoding
            $geocodingService = new EnhancedGeocodingService();
            $result = $geocodingService->forwardGeocode($fullAddress);

            if ($result) {
                Log::info('Forward geocoding successful', [
                    'address' => $fullAddress,
                    'coordinates' => [
                        'lat' => $result['latitude'],
                        'lng' => $result['longitude']
                    ],
                    'source' => $result['source'],
                    'confidence' => $result['confidence']
                ]);

                return [
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude'],
                    'source' => $result['source'],
                    'confidence' => $result['confidence'],
                    'geocoded_address' => $result['address'],
                    'metadata' => [
                        'house_number' => $result['house_number'] ?? null,
                        'road' => $result['road'] ?? null,
                        'city' => $result['city'] ?? null,
                        'state' => $result['state'] ?? null,
                        'country' => $result['country'] ?? null,
                        'postcode' => $result['postcode'] ?? null,
                    ]
                ];
            }

            Log::warning('Forward geocoding failed - no results', [
                'address' => $fullAddress
            ]);
        } catch (\Exception $e) {
            Log::error('Forward geocoding error', [
                'address' => $fullAddress,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }


    /**
     * Create a new university
     * POST /location/university
     */
    public function createUniversity(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:universities,code',
            'description' => 'nullable|string|max:5000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country_id' => 'nullable|integer|exists:countries,id',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|string|max:255', // Expecting file path or URL
            'type' => 'nullable|string|in:public,private,deemed,autonomous',
            'established_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'ranking' => 'nullable|integer|min:1|max:999999',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Generate unique slug from name
            $data['slug'] = $this->generateUniqueSlug($data['name'], University::class);

            // Set defaults
            $data['type'] = $data['type'] ?? 'public';
            $data['status'] = 'active';
            $data['created_by'] = Auth::id();

            // Create university
            $university = University::create($data);

            // Load relationships for response
            $university->load(['country:id,name,code', 'creator:id,name']);

            // Clear relevant caches
            Cache::forget('universities_list');
            if (isset($data['country_id'])) {
                Cache::forget("universities_by_country_{$data['country_id']}");
            }

            return response()->json([
                'success' => true,
                'data' => $university,
                'message' => 'University created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create university',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique slug for any model
     * Private helper method
     */
    private function generateUniqueSlug(string $title, string $modelClass, string $column = 'slug'): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        // Check if slug exists and make it unique
        while ($modelClass::where($column, $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update existing university
     * PUT /location/university/{id}
     */
    public function updateUniversity(Request $request, $id): JsonResponse
    {
        $university = University::find($id);

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' => 'University not found'
            ], 404);
        }

        // Check if user can update (creator or admin)
        if ($university->created_by !== Auth::id()) {
            // Add your admin check logic here if needed
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this university'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:universities,code,' . $id,
            'description' => 'nullable|string|max:5000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country_id' => 'nullable|integer|exists:countries,id',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:public,private,deemed,autonomous',
            'established_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'ranking' => 'nullable|integer|min:1|max:999999',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Update slug if name changed
            if (isset($data['name']) && $data['name'] !== $university->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], University::class);
            }

            $university->update($data);
            $university->load(['country:id,name,code', 'creator:id,name']);

            // Clear caches
            Cache::forget('universities_list');
            if (isset($data['country_id']) || $university->country_id) {
                Cache::forget("universities_by_country_{$university->country_id}");
                if (isset($data['country_id'])) {
                    Cache::forget("universities_by_country_{$data['country_id']}");
                }
            }

            return response()->json([
                'success' => true,
                'data' => $university,
                'message' => 'University updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update university',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get university by ID or slug
     * GET /location/university/{identifier}
     */
    public function getUniversity($identifier): JsonResponse
    {
        try {
            // Try to find by ID first, then by slug
            $university = University::where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->with(['country:id,name,code', 'creator:id,name'])
                ->first();

            if (!$university) {
                return response()->json([
                    'success' => false,
                    'message' => 'University not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $university
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch university',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete university (soft delete or status change)
     * DELETE /location/university/{id}
     */
    public function deleteUniversity($id): JsonResponse
    {
        try {
            $university = University::find($id);

            if (!$university) {
                return response()->json([
                    'success' => false,
                    'message' => 'University not found'
                ], 404);
            }

            // Check authorization
            if ($university->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this university'
                ], 403);
            }

            // Instead of deleting, set status to inactive
            $university->update(['status' => 'inactive']);

            // Clear caches
            Cache::forget('universities_list');
            if ($university->country_id) {
                Cache::forget("universities_by_country_{$university->country_id}");
            }

            return response()->json([
                'success' => true,
                'message' => 'University deactivated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete university',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
