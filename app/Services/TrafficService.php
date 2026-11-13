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
        } catch (\Throwable $e) {
            Log::error('TrafficService fetchFlow exception: ' . $e->getMessage());
            return ['available' => false, 'code' => 'provider_unavailable', 'message' => 'Unable to retrieve traffic information. Please try again.'];
        }

        $segments = [];
        $failures = [];
        foreach ($responses as $index => $response) {
            [$lat, $lon] = $sampledPoints[(int) $index];
            if ($response instanceof \Throwable) {
                $failures[] = ['status' => null, 'message' => null];
                Log::warning('TrafficService flow request failed', ['error' => $response->getMessage()]);
                continue;
            }
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
            $status = collect($failures)->pluck('status')->first();
            $code = !$failures ? 'no_coverage' : ($status === null
                ? 'provider_unavailable'
                : match ((int) $status) {
                    401, 403 => 'authentication_error',
                    429 => 'rate_limited',
                    default => (str_contains(strtolower((string) $providerMessage), 'point too far') ? 'no_coverage' : 'provider_error'),
                });
            $message = match ($code) {
                'no_coverage' => 'Live traffic data is unavailable for this road section.',
                'authentication_error', 'rate_limited', 'provider_error', 'provider_unavailable' => 'Unable to retrieve traffic information. Please try again.',
                default => 'Live traffic data is unavailable for this road section.',
            };
            return ['available' => false, 'code' => $code, 'httpStatus' => $status, 'message' => $message];
        }
        return ['available' => true, 'segments' => $segments];
    }

    /** Check flow only at the current GPS fix; map-wide visualization belongs to flow tiles. */
    public function fetchCurrentPointFlow(float $lat, float $lon, float $heading): array
    {
        if (!$this->apiKey) return ['available' => false, 'message' => 'TomTom API key is not configured.'];
        if (!is_finite($lat) || !is_finite($lon) || abs($lat) > 90 || abs($lon) > 180 || !is_finite($heading) || $heading < 0 || $heading >= 360) {
            return ['available' => false, 'code' => 'invalid_coordinates', 'message' => 'Live GPS position or course is invalid.'];
        }
        return $this->fetchFlow([[$lat, $lon]], 1);
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
