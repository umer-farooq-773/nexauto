<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoapifyService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.geoapify.api_key');
    }

    /**
     * Search location autocomplete via Geoapify
     */
    public function autocomplete(string $text, int $limit = 8): array
    {
        if (trim($text) === '') {
            return [];
        }

        // Log::info('Geoapify autocomplete request initiated', [
        //     'search_text' => $text,
        //     'limit' => $limit,
        // ]);

        if (empty($this->apiKey)) {
            Log::warning('Geoapify API key not configured in environment (GEOAPIFY_API_KEY / GEO_APIFY)');
            return [];
        }

        // Log::debug('Geoapify API key configured', [
        //     'api_key_length' => strlen($this->apiKey),
        //     'api_key_prefix' => substr($this->apiKey, 0, 8) . '...',
        // ]);

        try {
            $params = [
                'text' => $text,
                'limit' => $limit,
                'apiKey' => $this->apiKey,
            ];

            // Log::debug('Geoapify autocomplete request params', [
            //     'url' => 'https://api.geoapify.com/v1/geocode/autocomplete',
            //     'params' => array_merge($params, ['apiKey' => substr($this->apiKey, 0, 8) . '...']),
            // ]);

            $response = Http::timeout(10)->get('https://api.geoapify.com/v1/geocode/autocomplete', $params);

            // Log::debug('Geoapify API raw response', [
            //     'status_code' => $response->status(),
            //     'response_body' => $response->body(),
            //     'headers' => $response->headers(),
            // ]);

            if (!$response->successful()) {
                Log::error('Geoapify autocomplete HTTP error', [
                    'status' => $response->status(),
                    'search_text' => $text,
                    'response_body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            $features = $data['features'] ?? [];

            // Log::debug('Geoapify API parsed response', [
            //     'features_count' => count($features),
            //     'raw_data' => $data,
            // ]);

            $results = [];
            foreach ($features as $feature) {
                $props = $feature['properties'] ?? [];
                $coords = $feature['geometry']['coordinates'] ?? [0, 0];

                $results[] = [
                    'formatted' => $props['formatted'] ?? ($props['name'] ?? ''),
                    'name' => $props['name'] ?? ($props['city'] ?? ($props['address_line1'] ?? '')),
                    'city' => $props['city'] ?? null,
                    'country' => $props['country'] ?? null,
                    'lat' => (float) ($props['lat'] ?? $coords[1] ?? 0),
                    'lng' => (float) ($props['lon'] ?? $coords[0] ?? 0),
                ];
            }

            // Log::info('Geoapify autocomplete request successful', [
            //     'search_text' => $text,
            //     'results_count' => count($results),
            // ]);

            return $results;
        } catch (\Throwable $e) {
            Log::error('Geoapify autocomplete exception', [
                'search_text' => $text,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Calculate route through waypoints via Geoapify Routing API
     * Waypoints format: array of [['lat' => 12.34, 'lng' => 56.78, 'name' => '...'], ...]
     */
    public function calculateRoute(array $waypoints): array
    {
        if (count($waypoints) < 2) {
            return [
                'success' => false,
                'message' => 'At least 2 waypoints (Origin and Destination) are required.',
            ];
        }

        $formattedWaypoints = [];
        $sanitizedLogWaypoints = [];

        foreach ($waypoints as $idx => $wp) {
            $lat = (float) ($wp['lat'] ?? $wp['latitude'] ?? 0);
            $lng = (float) ($wp['lng'] ?? $wp['longitude'] ?? 0);
            $name = $wp['name'] ?? "Point " . ($idx + 1);

            $formattedWaypoints[] = "{$lat},{$lng}";
            $sanitizedLogWaypoints[] = [
                'index' => $idx,
                'name' => $name,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        $waypointStr = implode('|', $formattedWaypoints);

        Log::info('Geoapify routing request initiated', [
            'waypoints_count' => count($waypoints),
            'waypoints' => $sanitizedLogWaypoints,
        ]);

        if (empty($this->apiKey)) {
            Log::warning('Geoapify API key missing during route calculation');
            return $this->fallbackDirectLineRoute($waypoints);
        }

        try {
            $response = Http::timeout(15)->get('https://api.geoapify.com/v1/routing', [
                'waypoints' => $waypointStr,
                'mode' => 'drive',
                'apiKey' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                // Log::error('Geoapify routing HTTP error', [
                //     'status' => $response->status(),
                //     'response_body' => $response->body(),
                // ]);
                return $this->fallbackDirectLineRoute($waypoints);
            }

            $data = $response->json();
            $feature = $data['features'][0] ?? null;

            if (!$feature) {
                // Log::warning('Geoapify routing returned no features');
                return $this->fallbackDirectLineRoute($waypoints);
            }

            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? [];

            $distanceMeters = (float) ($properties['distance'] ?? 0);
            $distanceKm = round($distanceMeters / 1000, 2);
            $timeSeconds = (float) ($properties['time'] ?? 0);

            $durationText = $this->formatDuration($timeSeconds);

            $rawCoords = $geometry['coordinates'] ?? [];
            $routeCoordinates = [];

            if (is_array($rawCoords)) {
                if (isset($rawCoords[0]) && is_array($rawCoords[0]) && is_array($rawCoords[0][0] ?? null)) {
                    foreach ($rawCoords as $leg) {
                        foreach ($leg as $pt) {
                            if (count($pt) >= 2) {
                                $routeCoordinates[] = [(float) $pt[1], (float) $pt[0]];
                            }
                        }
                    }
                } else {
                    foreach ($rawCoords as $pt) {
                        if (count($pt) >= 2) {
                            $routeCoordinates[] = [(float) $pt[1], (float) $pt[0]];
                        }
                    }
                }
            }

            // Log::info('Geoapify routing calculation successful', [
            //     'distance_km' => $distanceKm,
            //     'estimated_duration' => $durationText,
            //     'route_coordinates_count' => count($routeCoordinates),
            // ]);

            return [
                'success' => true,
                'distance_km' => $distanceKm,
                'estimated_duration' => $durationText,
                'time_seconds' => $timeSeconds,
                'route_coordinates' => $routeCoordinates,
            ];
        } catch (\Throwable $e) {
            Log::error('Geoapify routing exception', [
                'error' => $e->getMessage(),
            ]);
            return $this->fallbackDirectLineRoute($waypoints);
        }
    }

    protected function formatDuration(float $seconds): string
    {
        if ($seconds <= 0) {
            return '0 Mins';
        }

        $hours = (int) floor($seconds / 3600);
        $minutes = (int) round(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours} Hour" . ($hours > 1 ? 's' : '') . ($minutes > 0 ? " {$minutes} Min" . ($minutes > 1 ? 's' : '') : '');
        }

        return "{$minutes} Min" . ($minutes > 1 ? 's' : '');
    }

    protected function fallbackDirectLineRoute(array $waypoints): array
    {
        $coords = [];
        $totalDistance = 0.0;

        foreach ($waypoints as $idx => $wp) {
            $lat = (float) ($wp['lat'] ?? $wp['latitude'] ?? 0);
            $lng = (float) ($wp['lng'] ?? $wp['longitude'] ?? 0);
            $coords[] = [$lat, $lng];

            if ($idx > 0) {
                $prevLat = (float) ($waypoints[$idx - 1]['lat'] ?? $waypoints[$idx - 1]['latitude'] ?? 0);
                $prevLng = (float) ($waypoints[$idx - 1]['lng'] ?? $waypoints[$idx - 1]['longitude'] ?? 0);
                $totalDistance += $this->haversineDistance($prevLat, $prevLng, $lat, $lng);
            }
        }

        $distKm = round($totalDistance, 2);
        $approxHours = $distKm > 0 ? ($distKm / 60) : 0;
        $hours = (int) floor($approxHours);
        $mins = (int) round(($approxHours - $hours) * 60);

        $duration = $hours > 0 ? "{$hours} Hours {$mins} Mins" : "{$mins} Mins";

        return [
            'success' => true,
            'distance_km' => $distKm,
            'estimated_duration' => $duration,
            'route_coordinates' => $coords,
            'is_fallback' => true,
        ];
    }

    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
