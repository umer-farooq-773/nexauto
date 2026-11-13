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
            'https://api.tomtom.com/traffic/map/4/tile/flow/relative/*' => Http::response($fakePng, 200, ['Content-Type' => 'image/png'])
        ]);

        $response = $this->get('/driver-panel/tile/traffic/10/500/300.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertEquals($fakePng, $response->getContent());
    }

    public function test_traffic_tile_returns_fallback_png_when_key_is_missing(): void
    {
        Config::set('services.tomtom.key', null);

        $response = $this->get('/driver-panel/tile/traffic/10/501/300.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function test_traffic_tile_does_not_fall_back_to_another_provider(): void
    {
        Config::set('services.tomtom.key', 'test-tomtom-key');
        Http::fake(['*' => Http::response('', 503)]);

        $response = $this->get('/driver-panel/tile/traffic/10/502/300.png');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/traffic/map/4/tile/flow/relative/10/502/300.png')
            && str_contains($request->url(), 'key=test-tomtom-key'));
    }

    public function test_traffic_flow_checks_only_the_current_fix_and_reports_uncovered_roads(): void
    {
        Config::set('services.tomtom.key', 'test-tomtom-key');
        Config::set('services.tomtom.ttl', 0);
        Http::fake([
            'https://api.tomtom.com/traffic/services/4/flowSegmentData/relative/18/json*' => Http::response([
                'detailedError' => ['message' => 'Point too far from nearest existing segment.'],
            ], 400),
        ]);

        $response = $this->postJson('/driver-panel/journey/traffic/flow', [
            'lat' => 33.6844,
            'lon' => 73.0479,
            'heading' => 90,
        ]);

        $response->assertOk()->assertJson([
            'available' => false,
            'code' => 'no_coverage',
            'message' => 'Live traffic data is unavailable for this road section.',
        ]);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'point=33.6844%2C73.0479')
            && !str_contains($request->url(), 'calculateRoute'));
    }

    public function test_traffic_flow_rejects_invalid_coordinate_ranges(): void
    {
        Config::set('services.tomtom.key', 'test-tomtom-key');

        $this->postJson('/driver-panel/journey/traffic/flow', [
            'lat' => 91,
            'lon' => 73.0479,
            'heading' => 90,
        ])->assertStatus(422)->assertJson(['available' => false, 'message' => 'Live GPS position or course is invalid.']);
    }

    public function test_traffic_flow_handles_connection_failures_without_throwing(): void
    {
        Config::set('services.tomtom.key', 'test-tomtom-key');
        Config::set('services.tomtom.ttl', 0);
        Http::fake(['*' => Http::failedConnection('Connection timed out')]);

        $this->postJson('/driver-panel/journey/traffic/flow', [
            'lat' => 8.1955,
            'lon' => -12.6561,
            'heading' => 0,
        ])->assertOk()->assertJson([
            'available' => false,
            'code' => 'provider_unavailable',
            'message' => 'Unable to retrieve traffic information. Please try again.',
        ]);
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

