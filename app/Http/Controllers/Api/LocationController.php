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

            $recentLocations = UserRecentLocation::with([
                'location.city:id,name,state',
                'location.country:id,name,code',
                'location.university:id,name'
            ])
                ->where('user_id', $userId)
                ->orderBy('visited_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $recentLocations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent locations',
                'error' => $e->getMessage()
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

            // Enhanced address with OSM data
            $finalAddress = $geocodedData['address'] ??
                "Lat: {$data['latitude']}, Lng: {$data['longitude']}";

            $location = Location::firstOrCreate([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ], [
                'name' => $data['name'],
                'address' => $finalAddress,
                'type' => $data['type'] ?? 'current',
                'is_active' => true,
                'created_by' => $userId,

                // ✅ Store enhanced metadata from best service
                'geocoding_source' => $geocodedData['source'] ?? 'none',
                'geocoding_confidence' => $geocodedData['confidence'] ?? 0.0,
                'osm_id' => $geocodedData['osm_id'] ?? null,
                'osm_type' => $geocodedData['osm_type'] ?? null,
                'place_type' => $geocodedData['place_type'] ?? null,
                'geocoded_at' => now(),
            ]);

            // Save to user recent locations
            UserRecentLocation::updateOrCreate([
                'user_id' => $userId,
                'location_id' => $location->id
            ], [
                'visited_at' => now()
            ]);

            // ✅ RETURN ENHANCED DATA: Include geocoded information
            return response()->json([
                'success' => true,
                'message' => 'Location saved successfully',
                'data' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'type' => $location->type,
                    'geocoding_source' => $geocodedData['source'] ?? 'none',
                    'confidence' => $geocodedData['confidence'] ?? 0.0,
                    'metadata' => [
                        'house_number' => $geocodedData['house_number'] ?? null,
                        'road' => $geocodedData['road'] ?? null,
                        'city' => $geocodedData['city'] ?? null,
                        'state' => $geocodedData['state'] ?? null,
                        'country' => $geocodedData['country'] ?? null,
                        'postcode' => $geocodedData['postcode'] ?? null,
                    ],
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error saving recent location', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save location',
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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'city_id' => 'nullable|integer|exists:cities,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'university_id' => 'nullable|integer|exists:universities,id',
            'type' => 'nullable|string|in:current,campus,custom,online,shipping',
            'description' => 'nullable|string|max:1000',
            'is_safe_meetup' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['created_by'] = Auth::id();
            $data['type'] = $data['type'] ?? 'custom';
            $data['is_active'] = true;

            $location = Location::create($data);

            // Load relationships for response
            $location->load([
                'city:id,name,state',
                'country:id,name,code',
                'university:id,name'
            ]);

            // Clear relevant caches
            Cache::forget('all_locations');
            Cache::forget("locations_by_type_{$data['type']}");

            if ($data['type'] === 'campus') {
                Cache::forget('campus_locations');
            }

            return response()->json([
                'success' => true,
                'data' => $location,
                'message' => 'Custom location added successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add custom location',
                'error' => $e->getMessage()
            ], 500);
        }
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
