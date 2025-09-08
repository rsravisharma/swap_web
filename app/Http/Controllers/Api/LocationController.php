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
use App\Models\UserRecentLocation;

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
                    'name' => $location->name,
                    'address' => $location->address,
                    'city' => $location->city,
                    'state' => $location->state,
                    'country' => $location->country,
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

            $recentLocations = UserRecentLocation::with('location')
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
        $validator = Validator::make($request->all(), [
            'location_id' => 'nullable|integer|exists:locations,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'type' => 'nullable|string|max:50',
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

            // If location_id not provided, create a temporary location record
            if (!isset($data['location_id'])) {
                $location = Location::firstOrCreate([
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ], [
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'type' => $data['type'] ?? 'custom',
                    'created_by' => $userId
                ]);
                $data['location_id'] = $location->id;
            }

            // Save or update recent location
            UserRecentLocation::updateOrCreate([
                'user_id' => $userId,
                'location_id' => $data['location_id']
            ], [
                'visited_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location saved to recent successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save recent location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all locations
     * GET /location/all
     */
    public function getAllLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('all_locations', self::CACHE_DURATION_DYNAMIC, function () {
                return Location::orderBy('name')->get();
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
     * Get campus locations
     * GET /location/campus
     */
    public function getCampusLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('campus_locations', self::CACHE_DURATION_DYNAMIC, function () {
                return Location::where('type', 'campus')
                    ->where('is_active', true)
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
                return Location::where('is_popular', true)
                    ->where('is_active', true)
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
     * Get meetup locations
     * GET /location/meetup
     */
    public function getMeetupLocations(): JsonResponse
    {
        try {
            $locations = Cache::remember('meetup_locations', self::CACHE_DURATION_DYNAMIC, function () {
                return Location::where('is_safe_meetup', true)
                    ->where('is_active', true)
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
     * Search locations
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
                  ->orWhere('city', 'like', "%{$query}%");
            })
            ->where('is_active', true)
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
            'radius' => 'nullable|numeric|min:0.1|max:100'
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

            $locations = Location::selectRaw('
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance
            ', [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->where('is_active', true)
            ->orderBy('distance')
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
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'type' => 'nullable|string|max:50',
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

            // Clear relevant caches
            Cache::forget('all_locations');
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
}