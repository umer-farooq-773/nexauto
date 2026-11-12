<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DriverJourneyOverlayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_traffic_tile_proxy_returns_png_response(): void
    {
        Config::set('services.tomtom.key', 'test-tomtom-key');

        $fakePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        Http::fake([
            'https://api.tomtom.com/traffic/map/4/tile/flow/relative0/*' => Http::response($fakePng, 200, ['Content-Type' => 'image/png'])
        ]);

        $response = $this->get('/driver-panel/tile/traffic/10/500/300.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertEquals($fakePng, $response->getContent());
    }

    public function test_traffic_tile_returns_fallback_png_when_key_is_missing(): void
    {
        Config::set('services.tomtom.key', null);

        $response = $this->get('/driver-panel/tile/traffic/10/500/300.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function test_weather_tile_proxy_returns_png_response(): void
    {
        Config::set('services.openweather.key', 'test-openweather-key');

        $fakePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        Http::fake([
            'https://tile.openweathermap.org/map/precipitation_new/*' => Http::response($fakePng, 200, ['Content-Type' => 'image/png'])
        ]);

        $response = $this->get('/driver-panel/tile/weather/precipitation_new/6/30/20.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertEquals($fakePng, $response->getContent());
    }

    public function test_weather_tile_returns_fallback_png_for_invalid_layer(): void
    {
        Config::set('services.openweather.key', 'test-openweather-key');

        $response = $this->get('/driver-panel/tile/weather/invalid_layer/6/30/20.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function test_journey_weather_endpoint_returns_json(): void
    {
        Config::set('services.openweather.key', 'test-openweather-key');
        Config::set('services.openweather.base_url', 'https://api.openweathermap.org');

        Http::fake([
            'https://api.openweathermap.org/data/3.0/onecall*' => Http::response([
                'current' => [
                    'temp' => 24.5,
                    'humidity' => 60,
                    'wind_speed' => 3.5,
                    'weather' => [
                        ['id' => 800, 'main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']
                    ]
                ]
            ], 200)
        ]);

        $response = $this->get('/driver-panel/journey/weather?lat=33.6844&lon=73.0479');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current' => [
                'temp',
                'humidity',
                'wind_speed',
                'weather'
            ]
        ]);
    }

    public function test_journey_traffic_incidents_endpoint_returns_geojson(): void
    {
        Config::set('services.tomtom.key', 'test-tomtom-key');
        Config::set('services.tomtom.base_url', 'https://api.tomtom.com');

        Http::fake([
            'https://api.tomtom.com/traffic/services/4/incidentDetails/geojson*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => [
                            'iconCategory' => 1,
                            'events' => [
                                ['description' => 'Accident on Motorway M-2']
                            ],
                            'delay' => 600
                        ],
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [73.0479, 33.6844]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->get('/driver-panel/journey/traffic/incidents?bbox=73.0,33.0,73.5,33.5');

        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'FeatureCollection'
        ]);
    }
}

