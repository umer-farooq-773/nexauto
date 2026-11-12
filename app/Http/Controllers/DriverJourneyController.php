<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WeatherService;
use App\Services\TrafficService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DriverJourneyController extends Controller
{
    protected $weather;
    protected $traffic;

    // 1x1 transparent PNG fallback
    private const TRANSPARENT_PNG = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=";

    public function __construct(WeatherService $weather, TrafficService $traffic)
    {
        $this->weather = $weather;
        $this->traffic = $traffic;
    }

    /**
     * Proxy traffic raster tiles.
     * URL pattern: /driver-panel/tile/traffic/{z}/{x}/{y}.png
     * Delivers real-time road traffic colors (green: clear, orange: moderate, red: congestion).
     * Automatically uses TomTom Flow Raster Tiles when available, and falls back to
     * global live traffic overlay for regions where TomTom flow raster is not licensed (such as Pakistan).
     */
    public function trafficTile($z, $x, $y)
    {
        $z = (int)$z;
        $x = (int)$x;
        $y = (int)$y;
        if ($z < 0 || $z > 22 || $x < 0 || $y < 0) {
            return response(base64_decode(self::TRANSPARENT_PNG), 200)->header('Content-Type', 'image/png');
        }

        $cacheKey = "tile_traffic_{$z}_{$x}_{$y}";
        $ttl = (int) config('services.tomtom.tile_ttl', 60);

        // Encode as base64 to prevent MySQL 1366 binary character set exception when using database cache driver
        $pngBase64 = $this->rememberSafely($cacheKey, $ttl, function () use ($z, $x, $y) {
            $key = config('services.tomtom.key');

            // 1. Try TomTom traffic flow tile
            if ($key) {
                try {
                    $url = rtrim(config('services.tomtom.base_url', 'https://api.tomtom.com'), '/') . "/traffic/map/4/tile/flow/relative/{$z}/{$x}/{$y}.png?key={$key}";
                    $response = Http::timeout(5)->get($url);
                    // TomTom returns exactly 1241 bytes for blank/uncovered tiles (e.g. Pakistan)
                    if ($response->successful() && strlen($response->body()) > 1500) {
                        return base64_encode($response->body());
                    }
                    if (!$response->successful()) {
                        Log::warning('TomTom traffic tile unavailable', ['status' => $response->status(), 'z' => $z, 'x' => $x, 'y' => $y]);
                    }
                } catch (\Exception $e) {
                    Log::warning('TomTom traffic tile exception: ' . $e->getMessage());
                }
            }

            // 2. High-precision live road traffic overlay for global coverage (including Pakistan)
            try {
                $gtUrl = "https://mt0.google.com/vt?lyrs=h@159000000,traffic|seconds_into_week:-1&style=15&x={$x}&y={$y}&z={$z}";
                $gResponse = Http::timeout(5)->get($gtUrl);
                if ($gResponse->successful() && strlen($gResponse->body()) > 500) {
                    return base64_encode($gResponse->body());
                }
                Log::warning('Traffic tile fallback unavailable', ['status' => $gResponse->status(), 'z' => $z, 'x' => $x, 'y' => $y]);
            } catch (\Exception $e) {
                Log::warning('Road traffic overlay exception: ' . $e->getMessage());
            }

            return null;
        });

        if ($pngBase64) {
            $binary = base64_decode($pngBase64);
            return response($binary, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', "public, max-age={$ttl}");
        }

        return response(base64_decode(self::TRANSPARENT_PNG), 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=30');
    }

    /**
     * Proxy OpenWeather map tiles.
     * layer: precipitation_new, clouds_new, etc.
     */
    public function weatherTile($layer, $z, $x, $y)
    {
        $z = (int)$z;
        $x = (int)$x;
        $y = (int)$y;
        $allowedLayers = ['precipitation_new', 'clouds_new', 'temp_new', 'wind_new', 'pressure_new'];
        if (!in_array($layer, $allowedLayers) || $z < 0 || $z > 19 || $x < 0 || $y < 0) {
            return response(base64_decode(self::TRANSPARENT_PNG), 200)->header('Content-Type', 'image/png');
        }

        $key = config('services.openweather.key');
        if (!$key) {
            Log::warning('Weather tile: OpenWeather API key not configured.');
            return response(base64_decode(self::TRANSPARENT_PNG), 200)->header('Content-Type', 'image/png');
        }

        $cacheKey = "tile_weather_{$layer}_{$z}_{$x}_{$y}";
        $ttl = (int) config('services.openweather.tile_ttl', 300);

        // Encode as base64 to prevent MySQL 1366 binary character set exception when using database cache driver
        $pngBase64 = $this->rememberSafely($cacheKey, $ttl, function () use ($layer, $z, $x, $y, $key) {
            $tileBaseUrl = rtrim(config('services.openweather.tile_base_url', 'https://tile.openweathermap.org'), '/');
            $url = "{$tileBaseUrl}/map/{$layer}/{$z}/{$x}/{$y}.png?appid={$key}";
            try {
                $response = Http::timeout(6)->get($url);
                if ($response->successful() && strlen($response->body()) > 0) {
                    return base64_encode($response->body());
                }
                Log::warning('OpenWeather tile fetch failed', ['status' => $response->status(), 'layer' => $layer, 'z' => $z, 'x' => $x, 'y' => $y]);
            } catch (\Exception $e) {
                Log::error('OpenWeather tile exception: ' . $e->getMessage());
            }
            return null;
        });

        if ($pngBase64) {
            $binary = base64_decode($pngBase64);
            return response($binary, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', "public, max-age={$ttl}");
        }

        return response(base64_decode(self::TRANSPARENT_PNG), 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=60');
    }

    /**
     * Return weather data for given lat/lon (cached 10 minutes).
     */
    public function weather(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        if (!$lat || !$lon) {
            return response()->json(['error' => 'Missing lat/lon'], 400);
        }
        if (!is_numeric($lat) || !is_numeric($lon) || (float) $lat < -90 || (float) $lat > 90 || (float) $lon < -180 || (float) $lon > 180) {
            return response()->json(['error' => 'Latitude/longitude out of range'], 422);
        }
        $cacheKey = "weather_data_" . round((float)$lat, 3) . "_" . round((float)$lon, 3);
        $data = $this->rememberSafely($cacheKey, config('services.openweather.ttl', 600), function () use ($lat, $lon) {
            return $this->weather->fetchCurrent((float)$lat, (float)$lon);
        });
        $status = isset($data['error']) ? (int) ($data['status'] ?? 502) : 200;
        return response()->json($data, $status);
    }

    /**
     * Return traffic flow for a list of points (cached 5 minutes).
     */
    public function trafficFlow(Request $request)
    {
        if ($request->has(['lat', 'lon', 'heading'])) {
            $lat = $request->json('lat');
            $lon = $request->json('lon');
            $heading = $request->json('heading');
            if (!is_numeric($lat) || !is_numeric($lon) || !is_numeric($heading)
                || (float) $lat < -90 || (float) $lat > 90
                || (float) $lon < -180 || (float) $lon > 180
                || (float) $heading < 0 || (float) $heading >= 360) {
                return response()->json(['available' => false, 'message' => 'Live GPS position or course is invalid.'], 422);
            }

            $cacheKey = 'traffic_forward_100_' . md5(json_encode([
                round((float) $lat, 3),
                round((float) $lon, 3),
                (int) (round((float) $heading / 15) * 15) % 360,
            ]));
            $data = $this->rememberSafely($cacheKey, config('services.tomtom.ttl', 300), function () use ($lat, $lon, $heading) {
                return $this->traffic->fetchNext100KmFlow((float) $lat, (float) $lon, (float) $heading);
            });
            return response()->json($data);
        }

        $points = $request->json('points', []);
        $pointPairs = [];
        foreach ($points as $p) {
            if (isset($p['lat'], $p['lon'])) {
                $pointPairs[] = [(float)$p['lat'], (float)$p['lon']];
            }
        }
        $cacheKey = 'traffic_flow_' . md5(json_encode($pointPairs));
        $data = $this->rememberSafely($cacheKey, config('services.tomtom.ttl', 300), function () use ($pointPairs) {
            return $this->traffic->fetchFlow($pointPairs);
        });
        return response()->json($data);
    }

    /**
     * Return traffic incidents for a bounding box (cached 5 minutes).
     */
    public function trafficIncidents(Request $request)
    {
        $bbox = $request->query('bbox');
        if (!$bbox) {
            return response()->json(['error' => 'Missing bbox'], 400);
        }
        $cacheKey = "traffic_incidents_" . md5($bbox);
        $data = $this->rememberSafely($cacheKey, config('services.tomtom.ttl', 300), function () use ($bbox) {
            return $this->traffic->fetchIncidents($bbox);
        });
        return response()->json($data);
    }

    /** The map APIs should remain usable when the configured cache backend is temporarily down. */
    private function rememberSafely(string $key, int $ttl, \Closure $callback)
    {
        try {
            // Keep map calls independent from the app's database cache store;
            // a database outage must not stall a weather or traffic request.
            $mapCache = Cache::store('file');
            $cached = $mapCache->get($key);
            if ($cached !== null) return $cached;
        } catch (\Throwable $e) {
            Log::warning('Journey cache read failed; fetching provider data directly', ['error' => $e->getMessage()]);
        }

        $data = $callback();
        try {
            Cache::store('file')->put($key, $data, $ttl);
        } catch (\Throwable $e) {
            Log::warning('Journey cache write failed; provider response returned without caching', ['error' => $e->getMessage()]);
        }
        return $data;
    }
}
