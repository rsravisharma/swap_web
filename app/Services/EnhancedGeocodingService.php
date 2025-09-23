<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnhancedGeocodingService
{
    private const CACHE_TTL = 86400; // 24 hours
    private const USER_AGENT = 'SwapApp/1.0 (swap.cubebitz@gmail.com)';

    /**
     * Perform reverse geocoding with multiple service fallbacks
     * Returns the best result from all available services
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $cacheKey = "enhanced_geocode_{$latitude}_{$longitude}";

        // Check cache first
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        // Define services in order of preference/reliability
        $services = [
            'nominatim' => [
                'method' => [$this, 'nominatimGeocode'],
                'priority' => 1,
                'daily_limit' => 86400, // 1 per second = ~86400 per day
            ],
            'locationiq' => [
                'method' => [$this, 'locationiqGeocode'],
                'priority' => 2,
                'daily_limit' => 5000,
            ],
            'opencage' => [
                'method' => [$this, 'opencageGeocode'],
                'priority' => 3,
                'daily_limit' => 2500,
            ],
            'positionstack' => [
                'method' => [$this, 'positionstackGeocode'],
                'priority' => 4,
                'daily_limit' => 833, // 25000 per month ≈ 833 per day
            ],
        ];

        $bestResult = null;
        $bestConfidence = 0;
        $usedService = null;

        // Try services concurrently for speed
        $results = $this->tryServicesParallel($services, $latitude, $longitude);

        // Evaluate results and pick the best one
        foreach ($results as $serviceName => $result) {
            if ($result && $this->isValidResult($result)) {
                $confidence = $this->calculateConfidence($result, $serviceName);

                if ($confidence > $bestConfidence) {
                    $bestResult = $result;
                    $bestConfidence = $confidence;
                    $usedService = $serviceName;
                }
            }
        }

        if ($bestResult) {
            $bestResult['confidence'] = $bestConfidence;
            $bestResult['source'] = $usedService;
            $bestResult['processed_at'] = now()->toISOString();

            // Cache the result
            Cache::put($cacheKey, $bestResult, self::CACHE_TTL);

            Log::info("Enhanced geocoding successful", [
                'lat' => $latitude,
                'lng' => $longitude,
                'service' => $usedService,
                'confidence' => $bestConfidence,
                'address' => $bestResult['address']
            ]);

            return $bestResult;
        }

        // Fallback: return coordinate-based result
        $fallback = $this->createFallbackResult($latitude, $longitude);
        Cache::put($cacheKey, $fallback, self::CACHE_TTL);

        return $fallback;
    }

    /**
     * Try multiple services in parallel for better performance
     */
    private function tryServicesParallel(array $services, float $lat, float $lng): array
    {
        $results = [];

        foreach ($services as $serviceName => $config) {
            // Check if service has reached daily limit
            $usageKey = "geocoding_usage_{$serviceName}_" . date('Y-m-d');
            $dailyUsage = Cache::get($usageKey, 0);

            if ($dailyUsage >= $config['daily_limit']) {
                Log::info("Skipping {$serviceName} - daily limit reached");
                continue;
            }

            try {
                $result = call_user_func($config['method'], $lat, $lng);

                if ($result) {
                    $results[$serviceName] = $result;

                    // Increment usage counter
                    Cache::put($usageKey, $dailyUsage + 1, 86400);
                }

                // Small delay between requests
                usleep(50000); // 0.05 seconds

            } catch (\Exception $e) {
                Log::warning("Service {$serviceName} failed", [
                    'error' => $e->getMessage(),
                    'lat' => $lat,
                    'lng' => $lng
                ]);
                continue;
            }
        }

        return $results;
    }

    /**
     * Nominatim (OpenStreetMap) - FREE & UNLIMITED
     */
    private function nominatimGeocode(float $lat, float $lng): ?array
    {
        $response = Http::timeout(10)
            ->withUserAgent(self::USER_AGENT)
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'zoom' => 18,
                'accept-language' => 'en',
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['display_name'])) {
                return $this->processNominatimResult($data);
            }
        }

        return null;
    }

    /**
     * LocationIQ - 5,000 free requests/day
     */
    private function locationiqGeocode(float $lat, float $lng): ?array
    {
        $apiKey = config('services.locationiq.api_key');
        if (!$apiKey) return null;

        $response = Http::timeout(10)->get('https://eu1.locationiq.com/v1/reverse.php', [
            'key' => $apiKey,
            'lat' => $lat,
            'lon' => $lng,
            'format' => 'json',
            'addressdetails' => 1,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->processLocationIQResult($data);
        }

        return null;
    }

    /**
     * OpenCage - 2,500 free requests/day
     */
    private function opencageGeocode(float $lat, float $lng): ?array
    {
        $apiKey = config('services.opencage.api_key');
        if (!$apiKey) return null;

        $response = Http::timeout(10)->get('https://api.opencagedata.com/geocode/v1/json', [
            'key' => $apiKey,
            'q' => "{$lat},{$lng}",
            'no_annotations' => 0,
            'language' => 'en',
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['results'])) {
                return $this->processOpenCageResult($data['results'][0]);
            }
        }

        return null;
    }

    /**
     * PositionStack - 25,000 free requests/month
     */
    private function positionstackGeocode(float $lat, float $lng): ?array
    {
        $apiKey = config('services.positionstack.access_key');
        if (!$apiKey) return null;

        $response = Http::timeout(10)->get('http://api.positionstack.com/v1/reverse', [
            'access_key' => $apiKey,
            'query' => "{$lat},{$lng}",
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['data'])) {
                return $this->processPositionStackResult($data['data'][0]);
            }
        }

        return null;
    }

    // Result processing methods
    private function processNominatimResult(array $data): array
    {
        $address = $data['address'] ?? [];

        return [
            'address' => $this->formatAddress($address, 'nominatim'),
            'raw_address' => $data['display_name'],
            'house_number' => $address['house_number'] ?? null,
            'road' => $address['road'] ?? null,
            'neighbourhood' => $address['neighbourhood'] ?? $address['suburb'] ?? null,
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
            'state' => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'osm_id' => $data['osm_id'] ?? null,
            'osm_type' => $data['osm_type'] ?? null,
            'place_type' => $data['type'] ?? null,
            'importance' => $data['importance'] ?? null,
        ];
    }

    private function processLocationIQResult(array $data): array
    {
        $address = $data['address'] ?? [];

        return [
            'address' => $this->formatAddress($address, 'locationiq'),
            'raw_address' => $data['display_name'],
            'house_number' => $address['house_number'] ?? null,
            'road' => $address['road'] ?? null,
            'neighbourhood' => $address['neighbourhood'] ?? null,
            'city' => $address['city'] ?? $address['town'] ?? null,
            'state' => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'place_id' => $data['place_id'] ?? null,
        ];
    }

    private function processOpenCageResult(array $result): array
    {
        $components = $result['components'];

        return [
            'address' => $result['formatted'],
            'house_number' => $components['house_number'] ?? null,
            'road' => $components['road'] ?? null,
            'neighbourhood' => $components['neighbourhood'] ?? $components['suburb'] ?? null,
            'city' => $components['city'] ?? $components['town'] ?? $components['village'] ?? null,
            'state' => $components['state'] ?? null,
            'country' => $components['country'] ?? null,
            'postcode' => $components['postcode'] ?? null,
            'opencage_confidence' => $result['confidence'] ?? null,
        ];
    }

    private function processPositionStackResult(array $result): array
    {
        return [
            'address' => trim(implode(', ', array_filter([
                $result['name'] ?? null,
                $result['locality'] ?? null,
                $result['region'] ?? null,
                $result['country'] ?? null
            ]))),
            'name' => $result['name'] ?? null,
            'locality' => $result['locality'] ?? null,
            'region' => $result['region'] ?? null,
            'country' => $result['country'] ?? null,
            'positionstack_confidence' => $result['confidence'] ?? null,
        ];
    }

    /**
     * Smart address formatting based on service type
     */
    private function formatAddress(array $address, string $service): string
    {
        $parts = [];

        // Build address hierarchy
        if (!empty($address['house_number']) && !empty($address['road'])) {
            $parts[] = $address['house_number'] . ' ' . $address['road'];
        } elseif (!empty($address['road'])) {
            $parts[] = $address['road'];
        }

        // Add neighborhood
        $neighborhood = $address['neighbourhood'] ?? $address['suburb'] ?? $address['quarter'] ?? null;
        if ($neighborhood) {
            $parts[] = $neighborhood;
        }

        // Add city
        $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? null;
        if ($city) {
            $parts[] = $city;
        }

        // Add state
        if (!empty($address['state'])) {
            $parts[] = $address['state'];
        }

        // Add country for international addresses
        if (!empty($address['country']) && !in_array($address['country_code'] ?? '', ['IN', 'in'])) {
            $parts[] = $address['country'];
        }

        return implode(', ', array_filter($parts));
    }

    /**
     * Calculate confidence score based on service and data quality
     */
    private function calculateConfidence(array $result, string $service): float
    {
        $confidence = 0.5; // Base confidence

        // Service-specific confidence adjustments
        switch ($service) {
            case 'nominatim':
                $confidence += 0.2;
                if (!empty($result['importance']) && $result['importance'] > 0.5) {
                    $confidence += 0.1;
                }
                break;
            case 'locationiq':
                $confidence += 0.3; // LocationIQ generally very good
                break;
            case 'opencage':
                if (!empty($result['opencage_confidence'])) {
                    $confidence += ($result['opencage_confidence'] / 10) * 0.3;
                }
                break;
            case 'positionstack':
                if (!empty($result['positionstack_confidence'])) {
                    $confidence += $result['positionstack_confidence'] * 0.2;
                }
                break;
        }

        // Data completeness bonus
        if (!empty($result['house_number'])) $confidence += 0.15;
        if (!empty($result['road'])) $confidence += 0.15;
        if (!empty($result['city'])) $confidence += 0.1;
        if (!empty($result['postcode'])) $confidence += 0.05;

        return min($confidence, 1.0);
    }

    /**
     * Validate if result is usable
     */
    private function isValidResult(?array $result): bool
    {
        if (!$result) return false;

        $address = $result['address'] ?? '';
        return !empty($address) && strlen($address) > 10;
    }

    /**
     * Create fallback result when all services fail
     */
    private function createFallbackResult(float $lat, float $lng): array
    {
        return [
            'address' => "Lat: " . number_format($lat, 4) . ", Lng: " . number_format($lng, 4),
            'source' => 'fallback',
            'confidence' => 0.1,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Get daily usage statistics
     */
    public function getUsageStats(): array
    {
        $today = date('Y-m-d');

        return [
            'date' => $today,
            'nominatim' => Cache::get("geocoding_usage_nominatim_{$today}", 0),
            'locationiq' => Cache::get("geocoding_usage_locationiq_{$today}", 0),
            'opencage' => Cache::get("geocoding_usage_opencage_{$today}", 0),
            'positionstack' => Cache::get("geocoding_usage_positionstack_{$today}", 0),
        ];
    }

    /**
     * Perform forward geocoding (address to coordinates)
     * Returns coordinates and location data for a given address
     */
    public function forwardGeocode(string $address): ?array
    {
        $cacheKey = "forward_geocode_" . md5($address);

        // Check cache first
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        // Define services for forward geocoding
        $services = [
            'nominatim' => [
                'method' => [$this, 'nominatimForwardGeocode'],
                'priority' => 1,
                'daily_limit' => 86400,
            ],
            'locationiq' => [
                'method' => [$this, 'locationiqForwardGeocode'],
                'priority' => 2,
                'daily_limit' => 5000,
            ],
            'opencage' => [
                'method' => [$this, 'opencageForwardGeocode'],
                'priority' => 3,
                'daily_limit' => 2500,
            ],
            'positionstack' => [
                'method' => [$this, 'positionstackForwardGeocode'],
                'priority' => 4,
                'daily_limit' => 833,
            ],
        ];

        $bestResult = null;
        $bestConfidence = 0;
        $usedService = null;

        // Try services for forward geocoding
        foreach ($services as $serviceName => $config) {
            // Check daily limit
            $usageKey = "geocoding_usage_{$serviceName}_" . date('Y-m-d');
            $dailyUsage = Cache::get($usageKey, 0);

            if ($dailyUsage >= $config['daily_limit']) {
                Log::info("Skipping {$serviceName} for forward geocoding - daily limit reached");
                continue;
            }

            try {
                $result = call_user_func($config['method'], $address);

                if ($result && $this->isValidForwardResult($result)) {
                    $confidence = $this->calculateForwardConfidence($result, $serviceName);

                    if ($confidence > $bestConfidence) {
                        $bestResult = $result;
                        $bestConfidence = $confidence;
                        $usedService = $serviceName;
                    }

                    // Increment usage counter
                    Cache::put($usageKey, $dailyUsage + 1, 86400);

                    // If we get a high-confidence result, use it
                    if ($confidence > 0.8) {
                        break;
                    }
                }

                // Small delay between requests
                usleep(100000); // 0.1 second

            } catch (\Exception $e) {
                Log::warning("Forward geocoding service {$serviceName} failed", [
                    'error' => $e->getMessage(),
                    'address' => $address
                ]);
                continue;
            }
        }

        if ($bestResult) {
            $bestResult['confidence'] = $bestConfidence;
            $bestResult['source'] = $usedService;
            $bestResult['processed_at'] = now()->toISOString();
            $bestResult['type'] = 'forward_geocoded';

            // Cache the result
            Cache::put($cacheKey, $bestResult, self::CACHE_TTL);

            Log::info("Forward geocoding successful", [
                'address' => $address,
                'service' => $usedService,
                'confidence' => $bestConfidence,
                'coordinates' => ['lat' => $bestResult['latitude'], 'lng' => $bestResult['longitude']]
            ]);

            return $bestResult;
        }

        Log::warning("Forward geocoding failed for address", ['address' => $address]);
        return null;
    }

    /**
     * Nominatim Forward Geocoding
     */
    private function nominatimForwardGeocode(string $address): ?array
    {
        $response = Http::timeout(10)
            ->withUserAgent(self::USER_AGENT)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 1,
                'accept-language' => 'en',
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return $this->processNominatimForwardResult($data[0]);
            }
        }

        return null;
    }

    /**
     * LocationIQ Forward Geocoding
     */
    private function locationiqForwardGeocode(string $address): ?array
    {
        $apiKey = config('services.locationiq.api_key');
        if (!$apiKey) return null;

        $response = Http::timeout(10)->get('https://eu1.locationiq.com/v1/search.php', [
            'key' => $apiKey,
            'q' => $address,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => 1,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return $this->processLocationIQForwardResult($data[0]);
            }
        }

        return null;
    }

    /**
     * OpenCage Forward Geocoding
     */
    private function opencageForwardGeocode(string $address): ?array
    {
        $apiKey = config('services.opencage.api_key');
        if (!$apiKey) return null;

        $response = Http::timeout(10)->get('https://api.opencagedata.com/geocode/v1/json', [
            'key' => $apiKey,
            'q' => $address,
            'limit' => 1,
            'language' => 'en',
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['results']) && isset($data['results'][0]['geometry'])) {
                return $this->processOpenCageForwardResult($data['results'][0]);
            }
        }

        return null;
    }

    /**
     * PositionStack Forward Geocoding
     */
    private function positionstackForwardGeocode(string $address): ?array
    {
        $apiKey = config('services.positionstack.access_key');
        if (!$apiKey) return null;

        $response = Http::timeout(10)->get('http://api.positionstack.com/v1/forward', [
            'access_key' => $apiKey,
            'query' => $address,
            'limit' => 1,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['data']) && isset($data['data'][0]['latitude'])) {
                return $this->processPositionStackForwardResult($data['data'][0]);
            }
        }

        return null;
    }

    // Forward result processing methods
    private function processNominatimForwardResult(array $data): array
    {
        $address = $data['address'] ?? [];

        return [
            'latitude' => (float)$data['lat'],
            'longitude' => (float)$data['lon'],
            'address' => $data['display_name'],
            'house_number' => $address['house_number'] ?? null,
            'road' => $address['road'] ?? null,
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
            'state' => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'importance' => $data['importance'] ?? null,
            'osm_id' => $data['osm_id'] ?? null,
            'osm_type' => $data['osm_type'] ?? null,
        ];
    }

    private function processLocationIQForwardResult(array $data): array
    {
        $address = $data['address'] ?? [];

        return [
            'latitude' => (float)$data['lat'],
            'longitude' => (float)$data['lon'],
            'address' => $data['display_name'],
            'house_number' => $address['house_number'] ?? null,
            'road' => $address['road'] ?? null,
            'city' => $address['city'] ?? $address['town'] ?? null,
            'state' => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'place_id' => $data['place_id'] ?? null,
        ];
    }

    private function processOpenCageForwardResult(array $result): array
    {
        $geometry = $result['geometry'];
        $components = $result['components'];

        return [
            'latitude' => (float)$geometry['lat'],
            'longitude' => (float)$geometry['lng'],
            'address' => $result['formatted'],
            'house_number' => $components['house_number'] ?? null,
            'road' => $components['road'] ?? null,
            'city' => $components['city'] ?? $components['town'] ?? $components['village'] ?? null,
            'state' => $components['state'] ?? null,
            'country' => $components['country'] ?? null,
            'postcode' => $components['postcode'] ?? null,
            'opencage_confidence' => $result['confidence'] ?? null,
        ];
    }

    private function processPositionStackForwardResult(array $result): array
    {
        return [
            'latitude' => (float)$result['latitude'],
            'longitude' => (float)$result['longitude'],
            'address' => $result['label'] ?? $result['name'] ?? null,
            'name' => $result['name'] ?? null,
            'locality' => $result['locality'] ?? null,
            'region' => $result['region'] ?? null,
            'country' => $result['country'] ?? null,
            'positionstack_confidence' => $result['confidence'] ?? null,
        ];
    }

    /**
     * Validate if forward geocoding result is usable
     */
    private function isValidForwardResult(?array $result): bool
    {
        if (!$result) return false;

        return isset($result['latitude']) &&
            isset($result['longitude']) &&
            is_numeric($result['latitude']) &&
            is_numeric($result['longitude']) &&
            abs($result['latitude']) <= 90 &&
            abs($result['longitude']) <= 180;
    }

    /**
     * Calculate confidence score for forward geocoding results
     */
    private function calculateForwardConfidence(array $result, string $service): float
    {
        $confidence = 0.5; // Base confidence

        // Service-specific adjustments
        switch ($service) {
            case 'nominatim':
                $confidence += 0.2;
                if (!empty($result['importance']) && $result['importance'] > 0.5) {
                    $confidence += 0.1;
                }
                break;
            case 'locationiq':
                $confidence += 0.3;
                break;
            case 'opencage':
                if (!empty($result['opencage_confidence'])) {
                    $confidence += ($result['opencage_confidence'] / 10) * 0.3;
                }
                break;
            case 'positionstack':
                if (!empty($result['positionstack_confidence'])) {
                    $confidence += $result['positionstack_confidence'] * 0.2;
                }
                break;
        }

        // Data completeness bonus
        if (!empty($result['house_number'])) $confidence += 0.1;
        if (!empty($result['road'])) $confidence += 0.1;
        if (!empty($result['city'])) $confidence += 0.1;
        if (!empty($result['postcode'])) $confidence += 0.05;

        return min($confidence, 1.0);
    }
}
