<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to query OpenWeather API.
 * Uses 2.5 Current Weather (supported across all standard/free keys)
 * with graceful fallback to 3.0 OneCall.
 */
class WeatherService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.openweather.base_url', 'https://api.openweathermap.org'), '/');
        $this->apiKey = config('services.openweather.key');
    }

    /**
     * Retrieve weather data for latitude / longitude.
     *
     * @param float $lat
     * @param float $lon
     * @return array Decoded JSON payload (or empty array on failure).
     */
    public function fetchCurrent(float $lat, float $lon): array
    {
        if (!$this->apiKey) {
            Log::warning('WeatherService: OpenWeather API key is not configured.');
            return ['error' => 'OpenWeather API key is not configured'];
        }

        try {
            // 1. Try OpenWeather 2.5 Current Weather (standard, reliable, all tiers)
            $response = Http::timeout(6)->get("{$this->baseUrl}/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'current' => [
                        'temp' => $data['main']['temp'] ?? null,
                        'feels_like' => $data['main']['feels_like'] ?? null,
                        'humidity' => $data['main']['humidity'] ?? null,
                        'pressure' => $data['main']['pressure'] ?? null,
                        'wind_speed' => $data['wind']['speed'] ?? null,
                        'wind_deg' => $data['wind']['deg'] ?? null,
                        'weather' => $data['weather'] ?? [],
                        'name' => $data['name'] ?? null,
                    ],
                    'coord' => $data['coord'] ?? ['lat' => $lat, 'lon' => $lon],
                    'raw' => $data,
                ];
            }

            Log::warning('WeatherService non-successful response', [
                'status_25' => $response->status(),
                'provider_message' => data_get($response->json(), 'message'),
                'lat' => $lat,
                'lon' => $lon,
            ]);
            return ['error' => 'OpenWeather request failed', 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error('WeatherService exception: ' . $e->getMessage(), ['lat' => $lat, 'lon' => $lon]);
            return ['error' => 'OpenWeather request failed'];
        }
    }
}
