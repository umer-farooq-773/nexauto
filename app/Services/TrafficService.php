<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to query TomTom Traffic APIs.
 * Provides flow data (road speed) and incident details.
 */
class TrafficService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.tomtom.base_url', 'https://api.tomtom.com'), '/');
        $this->apiKey = config('services.tomtom.key');
    }

    /**
     * Fetch traffic flow information for a point or list of points.
     */
    public function fetchFlow(array $points, int $maxSamples = 8): array
    {
        if (empty($points) || !$this->apiKey) {
            if (!$this->apiKey) Log::warning('TrafficService: TomTom API key is not configured.');
            return ['available' => false, 'message' => 'Traffic provider is not configured.'];
        }

        $validPoints = [];
        foreach ($points as $point) {
            $lat = $point[0] ?? $point['lat'] ?? null;
            $lon = $point[1] ?? $point['lon'] ?? null;
            if (is_numeric($lat) && is_numeric($lon) && abs((float) $lat) <= 90 && abs((float) $lon) <= 180) {
                $validPoints[] = [(float) $lat, (float) $lon];
            }
        }
        if (!$validPoints) return ['available' => false, 'message' => 'No valid vehicle or route GPS points are available.'];

        // Keep calls bounded while checking representative positions along the waybill route.
        $sampleCount = min(max(1, min(8, $maxSamples)), count($validPoints));
        $sampledPoints = [];
        for ($i = 0; $i < $sampleCount; $i++) {
            $index = $sampleCount === 1 ? 0 : (int) round($i * (count($validPoints) - 1) / ($sampleCount - 1));
            $sampledPoints[] = $validPoints[$index];
        }
        $sampledPoints = array_values(array_unique($sampledPoints, SORT_REGULAR));

        try {
            $responses = Http::pool(function ($pool) use ($sampledPoints) {
                foreach ($sampledPoints as $index => [$lat, $lon]) {
                    // TomTom documents unit values as case-sensitive (`kmph` or `mph`).
                    // Use a high zoom so lower-class roads can be matched as well.
                    $pool->as((string) $index)->connectTimeout(2)->timeout(4)->get("{$this->baseUrl}/traffic/services/4/flowSegmentData/relative/18/json", [
                        'key' => $this->apiKey,
                        'point' => "{$lat},{$lon}",
                        'unit' => 'kmph',
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('TrafficService fetchFlow exception: ' . $e->getMessage());
            return ['available' => false, 'message' => 'Traffic provider is unreachable.'];
        }

        $segments = [];
        $failures = [];
        foreach ($responses as $index => $response) {
            [$lat, $lon] = $sampledPoints[(int) $index];
            if ($response->successful()) {
                $flow = $response->json()['flowSegmentData'] ?? null;
                if ($flow) $segments[] = ['flowSegmentData' => $flow, 'samplePoint' => ['lat' => $lat, 'lon' => $lon]];
                continue;
            }

            $failures[] = [
                'status' => $response->status(),
                'message' => data_get($response->json(), 'detailedError.message')
                    ?? data_get($response->json(), 'error.description')
                    ?? data_get($response->json(), 'message'),
            ];
        }

        if (!$segments) {
            $failureSummary = collect($failures)->countBy('status')->all();
            $providerMessage = collect($failures)->pluck('message')->filter()->countBy()->sortDesc()->keys()->first();
            Log::warning('TrafficService found no flow segments for sampled road points', [
                'sample_count' => count($sampledPoints),
                'failure_statuses' => $failureSummary,
                'provider_message' => $providerMessage,
            ]);
            $detail = $providerMessage ? " Provider response: {$providerMessage}" : '';
            return ['available' => false, 'message' => "TomTom returned no live traffic flow for {$sampleCount} sampled road points in this area.{$detail}"];
        }
        return ['available' => true, 'segments' => $segments];
    }

    /** Route 100 road-kilometers ahead of the live fix, then sample flow on that route. */
    public function fetchNext100KmFlow(float $lat, float $lon, float $heading): array
    {
        if (!$this->apiKey) return ['available' => false, 'message' => 'TomTom API key is not configured.'];
        if (abs($lat) > 90 || abs($lon) > 180 || $heading < 0 || $heading >= 360) {
            return ['available' => false, 'message' => 'Live GPS position or course is invalid.'];
        }

        $bearing = deg2rad($heading);
        $targetLat = $lat + (100 * cos($bearing) / 111.32);
        $targetLon = $lon + (100 * sin($bearing) / (111.32 * max(0.05, cos(deg2rad($lat)))));
        $locations = sprintf('%.6f,%.6f:%.6f,%.6f', $lat, $lon, $targetLat, $targetLon);

        try {
            $routeResponse = Http::connectTimeout(3)->timeout(8)->get("{$this->baseUrl}/routing/1/calculateRoute/{$locations}/json", [
                'key' => $this->apiKey,
                'travelMode' => 'car',
                'routeType' => 'fastest',
                'traffic' => 'true',
                'computeTravelTimeFor' => 'all',
                'vehicleHeading' => (int) round($heading),
            ]);
        } catch (\Exception $e) {
            Log::error('TrafficService forward route request failed: ' . $e->getMessage());
            return ['available' => false, 'message' => 'TomTom could not calculate the next-road route.'];
        }

        $route = $routeResponse->json()['routes'][0] ?? null;
        if (!$routeResponse->successful() || !$route) {
            Log::warning('TrafficService forward route unavailable', [
                'status' => $routeResponse->status(),
                'provider_message' => data_get($routeResponse->json(), 'error.description')
                    ?? data_get($routeResponse->json(), 'detailedError.message'),
            ]);
            return ['available' => false, 'message' => 'TomTom could not find a routed road 100 km ahead.'];
        }

        $roadPoints = $this->firstRoadKilometers($route['legs'][0]['points'] ?? [], 100);
        if (count($roadPoints) < 2) {
            return ['available' => false, 'message' => 'TomTom returned no usable road geometry for the next 100 km.'];
        }

        $flow = $this->fetchFlow($roadPoints, 4);
        return $flow + [
            'lookaheadKm' => 100,
            'routeDistanceKm' => round(((float) data_get($route, 'summary.lengthInMeters', 0)) / 1000, 1),
            'sampledRoadPoints' => min(4, count($roadPoints)),
        ];
    }

    private function firstRoadKilometers(array $points, float $limitKm): array
    {
        $result = [];
        $distance = 0.0;
        $previous = null;
        $limitMeters = $limitKm * 1000;

        foreach ($points as $point) {
            if (!isset($point['latitude'], $point['longitude'])) continue;
            $current = [(float) $point['latitude'], (float) $point['longitude']];
            if ($previous === null) {
                $result[] = $current;
                $previous = $current;
                continue;
            }

            $segmentMeters = $this->distanceMeters($previous, $current);
            if ($distance + $segmentMeters > $limitMeters) {
                $fraction = $segmentMeters > 0 ? ($limitMeters - $distance) / $segmentMeters : 0;
                $result[] = [
                    $previous[0] + (($current[0] - $previous[0]) * $fraction),
                    $previous[1] + (($current[1] - $previous[1]) * $fraction),
                ];
                break;
            }

            $result[] = $current;
            $distance += $segmentMeters;
            $previous = $current;
        }
        return $result;
    }

    private function distanceMeters(array $a, array $b): float
    {
        $lat1 = deg2rad($a[0]);
        $lat2 = deg2rad($b[0]);
        $deltaLat = $lat2 - $lat1;
        $deltaLon = deg2rad($b[1] - $a[1]);
        $h = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * (sin($deltaLon / 2) ** 2);
        return 6371000 * 2 * atan2(sqrt($h), sqrt(max(0, 1 - $h)));
    }

    /**
     * Fetch traffic incidents for a bounding box.
     * $bbox format: "minLon,minLat,maxLon,maxLat"
     */
    public function fetchIncidents(string $bbox): array
    {
        if (!$this->apiKey) {
            Log::warning('TrafficService: TomTom API key is not configured.');
            return ['type' => 'FeatureCollection', 'features' => []];
        }

        $parts = $this->splitBoundingBox($bbox);
        $features = [];

        foreach ($parts as $part) {
            try {
                $response = Http::timeout(6)->get("{$this->baseUrl}/traffic/services/5/incidentDetails", [
                    'key' => $this->apiKey,
                    'bbox' => $part,
                    'language' => 'en-GB',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $features = array_merge($features, $data['incidents'] ?? []);
                    continue;
                }

                Log::warning('TrafficService fetchIncidents non-successful', [
                    'status' => $response->status(),
                    'provider_message' => data_get($response->json(), 'detailedError.message'),
                    'bbox' => $part,
                ]);
            } catch (\Exception $e) {
                Log::error('TrafficService fetchIncidents exception: ' . $e->getMessage(), ['bbox' => $part]);
            }
        }

        $unique = [];
        foreach ($features as $feature) {
            $id = $feature['id'] ?? md5(json_encode($feature['geometry'] ?? $feature));
            $unique[$id] = $feature;
        }
        return ['type' => 'FeatureCollection', 'features' => array_values($unique)];
    }

    /** Keep every TomTom incident request below its 10,000 km² bounding-box limit. */
    private function splitBoundingBox(string $bbox): array
    {
        $coordinates = array_map('floatval', explode(',', $bbox));
        if (count($coordinates) !== 4) return [];
        [$minLon, $minLat, $maxLon, $maxLat] = $coordinates;
        if ($minLon > $maxLon || $minLat > $maxLat) return [];

        $boxes = [[$minLon, $minLat, $maxLon, $maxLat]];
        for ($depth = 0; $depth < 5; $depth++) {
            $next = [];
            $didSplit = false;
            foreach ($boxes as [$west, $south, $east, $north]) {
                $widthKm = ($east - $west) * 111.32 * max(0.05, cos(deg2rad(($south + $north) / 2)));
                $heightKm = ($north - $south) * 110.57;
                if ($widthKm * $heightKm <= 8500) {
                    $next[] = [$west, $south, $east, $north];
                    continue;
                }

                $midLon = ($west + $east) / 2;
                $midLat = ($south + $north) / 2;
                $next[] = [$west, $south, $midLon, $midLat];
                $next[] = [$midLon, $south, $east, $midLat];
                $next[] = [$west, $midLat, $midLon, $north];
                $next[] = [$midLon, $midLat, $east, $north];
                $didSplit = true;
            }
            $boxes = $next;
            if (!$didSplit) break;
        }

        return array_map(fn ($box) => implode(',', array_map(fn ($value) => round($value, 6), $box)), $boxes);
    }
}
