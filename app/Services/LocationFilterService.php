<?php

namespace App\Services;

class LocationFilterService
{
    const DEFAULT_CITY  = 'Shimla';
    const DEFAULT_STATE = 'Himachal Pradesh';

    // Common abbreviations and aliases
    const STATE_ALIASES = [
        'hp'      => 'Himachal Pradesh',
        'himachal' => 'Himachal Pradesh',
        'up'      => 'Uttar Pradesh',
        'mp'      => 'Madhya Pradesh',
        'ap'      => 'Andhra Pradesh',
        'tn'      => 'Tamil Nadu',
        'wb'      => 'West Bengal',
        'jk'      => 'Jammu and Kashmir',
        'j&k'     => 'Jammu and Kashmir',
        'uk'      => 'Uttarakhand',
        'uttarakhand' => 'Uttarakhand',
        'uttrakhand'  => 'Uttarakhand',  // common misspelling
        'pb'      => 'Punjab',
        'mh'      => 'Maharashtra',
        'ka'      => 'Karnataka',
        'gj'      => 'Gujarat',
        'rj'      => 'Rajasthan',
        'hr'      => 'Haryana',
        'dl'      => 'Delhi',
        'delhi'   => 'Delhi',
    ];

    const CITY_ALIASES = [
        'bombay'    => 'Mumbai',
        'madras'    => 'Chennai',
        'calcutta'  => 'Kolkata',
        'bangalore' => 'Bengaluru',
        'bengalore' => 'Bengaluru',  // misspelling
        'banglore'  => 'Bengaluru',  // misspelling
        'new delhi' => 'Delhi',
        'simla'     => 'Shimla',     // old spelling
    ];

    public function resolve(array $params): array
    {
        $city  = $this->normalizeInput($params['city']  ?? '');
        $state = $this->normalizeInput($params['state'] ?? '');

        $city  = $this->resolveAlias($city,  self::CITY_ALIASES);
        $state = $this->resolveAlias($state, self::STATE_ALIASES);

        return [
            'city'  => $city  ?: self::DEFAULT_CITY,
            'state' => $state ?: self::DEFAULT_STATE,
        ];
    }

    /**
     * Normalize: trim, collapse multiple spaces, title-case
     */
    private function normalizeInput(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);   // collapse "Himachal  Pradesh"
        return $value;
    }

    /**
     * Check alias map (case-insensitive key lookup)
     */
    private function resolveAlias(string $value, array $aliases): string
    {
        if (empty($value)) return $value;

        $key = strtolower($value);
        return $aliases[$key] ?? $value;
    }

    public function cacheKey(string $base, string $city, string $state, int $limit, int $page): string
    {
        // Normalize cache key to avoid duplicates for same-but-different-case inputs
        $citySlug  = strtolower(str_replace(' ', '_', $city));
        $stateSlug = strtolower(str_replace(' ', '_', $state));
        return "{$base}_{$citySlug}_{$stateSlug}_{$limit}_page_{$page}";
    }
}
