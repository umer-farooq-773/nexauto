<?php

namespace Tests\Unit;

use App\Services\WialonService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WialonVehicleSettingsSyncTest extends TestCase
{
    public function test_push_updates_all_trip_and_speeding_settings_and_verifies_them(): void
    {
        Cache::flush();
        config([
            'services.wialon.host' => 'https://wialon.test',
            'services.wialon.token' => 'unit-test-token',
        ]);

        $settings = [
            'wialon_movement_type' => 1,
            'wialon_gps_correction' => true,
            'wialon_min_satellites' => 2,
            'min_moving_speed_kmh' => 1,
            'min_parking_seconds' => 720,
            'max_messages_distance_m' => 10000,
            'min_trip_seconds' => 60,
            'min_trip_distance_m' => 100,
            'speed_limit_kmh' => 85,
            'speeding_tolerance_kmh' => 5,
            'speeding_min_duration_s' => 30,
            'speeding_mode' => 0,
        ];
        $sent = [];

        Http::fake(function ($request) use (&$sent, $settings) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $service = $query['svc'] ?? '';
            $params = json_decode($query['params'] ?? '{}', true) ?: [];
            $sent[$service] = $params;

            return match ($service) {
                'token/login' => Http::response(['eid' => 'test-session'], 200),
                'unit/update_trip_detector', 'unit/update_report_settings' => Http::response([], 200),
                'unit/get_trip_detector' => Http::response([
                    'type' => $settings['wialon_movement_type'],
                    'gpsCorrection' => $settings['wialon_gps_correction'],
                    'minSat' => $settings['wialon_min_satellites'],
                    'minMovingSpeed' => $settings['min_moving_speed_kmh'],
                    'minStayTime' => $settings['min_parking_seconds'],
                    'maxMessagesDistance' => $settings['max_messages_distance_m'],
                    'minTripTime' => $settings['min_trip_seconds'],
                    'minTripDistance' => $settings['min_trip_distance_m'],
                ], 200),
                'unit/get_report_settings' => Http::response([
                    'speedLimit' => $settings['speed_limit_kmh'],
                    'speedingTolerance' => $settings['speeding_tolerance_kmh'],
                    'speedingMinDuration' => $settings['speeding_min_duration_s'],
                    'speedingMode' => $settings['speeding_mode'],
                ], 200),
                default => Http::response(['error' => 2], 200),
            };
        });

        $confirmed = app(WialonService::class)->pushUnitSettings(12345, $settings);

        $this->assertSame(720, $sent['unit/update_trip_detector']['minStayTime']);
        $this->assertSame(12345, $sent['unit/update_trip_detector']['itemId']);
        $this->assertSame(85, $sent['unit/update_report_settings']['params']['speedLimit']);
        $this->assertSame(5, $sent['unit/update_report_settings']['params']['speedingTolerance']);
        $this->assertSame(30, $sent['unit/update_report_settings']['params']['speedingMinDuration']);
        $this->assertSame(720, $confirmed['min_parking_seconds']);
        $this->assertSame(85, $confirmed['speed_limit_kmh']);
    }

    public function test_push_fails_when_wialon_does_not_confirm_a_setting(): void
    {
        Cache::flush();
        config([
            'services.wialon.host' => 'https://wialon.test',
            'services.wialon.token' => 'unit-test-token',
        ]);

        Http::fake(function ($request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            return match ($query['svc'] ?? '') {
                'token/login' => Http::response(['eid' => 'test-session'], 200),
                'unit/update_trip_detector', 'unit/update_report_settings' => Http::response([], 200),
                'unit/get_trip_detector' => Http::response([
                    'type' => 1, 'gpsCorrection' => 1, 'minSat' => 2,
                    'minMovingSpeed' => 1, 'minStayTime' => 300,
                    'maxMessagesDistance' => 10000, 'minTripTime' => 60,
                    'minTripDistance' => 100,
                ], 200),
                'unit/get_report_settings' => Http::response(['speedLimit' => 80, 'speedingMode' => 0], 200),
                default => Http::response(['error' => 2], 200),
            };
        });

        try {
            app(WialonService::class)->pushUnitSettings(12345, [
                'wialon_movement_type' => 1,
                'wialon_gps_correction' => true,
                'wialon_min_satellites' => 2,
                'min_moving_speed_kmh' => 1,
                'min_parking_seconds' => 720,
                'max_messages_distance_m' => 10000,
                'min_trip_seconds' => 60,
                'min_trip_distance_m' => 100,
                'speed_limit_kmh' => 85,
                'speeding_mode' => 0,
            ]);

            $this->fail('Expected Wialon read-back mismatch to be reported.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('did not confirm saving the min_parking_seconds', $exception->getMessage());
        }
    }
}
