<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\WialonService;
use App\Services\WialonVehicleSettingsSyncLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class WialonSettingsSyncController extends Controller
{
    public function push(WialonService $wialon, WialonVehicleSettingsSyncLogger $syncLogger): RedirectResponse
    {
        $vehicles = Vehicle::query()
            ->where('gps_provider', 'wialon')
            ->whereNotNull('gps_unit_id')
            ->orderBy('fleet_no')
            ->get();

        if ($vehicles->isEmpty()) {
            return back()->with('warning', 'No linked Wialon vehicles are available to sync.');
        }

        $settingsFields = [
            'wialon_movement_type',
            'wialon_gps_correction',
            'wialon_min_satellites',
            'min_moving_speed_kmh',
            'min_parking_seconds',
            'max_messages_distance_m',
            'min_trip_seconds',
            'min_trip_distance_m',
            'speed_limit_kmh',
            'speeding_tolerance_kmh',
            'speeding_min_duration_s',
            'speeding_mode',
        ];
        $synced = 0;
        $failures = [];

        foreach ($vehicles as $vehicle) {
            $unitIdText = (string) $vehicle->gps_unit_id;
            $unitId = filter_var($unitIdText, FILTER_VALIDATE_INT);
            $settings = $vehicle->only($settingsFields);

            if (!$unitId || $unitId <= 0 || strlen($unitIdText) >= 13) {
                $failures[] = $vehicle->fleet_no . ': invalid numeric Wialon unit ID';
                $syncLogger->record($vehicle, 'push', 'failed', $settings, 'Invalid numeric Wialon unit ID.');
                continue;
            }

            try {
                $wialon->pushUnitSettings((int) $unitId, $settings);
                $vehicle->forceFill(['wialon_settings_synced_at' => now()])->save();
                $syncLogger->record($vehicle, 'push', 'success', $settings);
                $synced++;
            } catch (\Throwable $exception) {
                $failures[] = $vehicle->fleet_no . ': ' . $exception->getMessage();
                $syncLogger->record($vehicle, 'push', 'failed', $settings, $exception->getMessage());
                Log::warning('Manual Wialon settings push failed', [
                    'vehicle_id' => $vehicle->id,
                    'wialon_unit_id' => $unitId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $message = sprintf(
            'Wialon settings sync finished: %d vehicle(s) synced, %d failed.',
            $synced,
            count($failures),
        );

        if ($failures !== []) {
            $message .= ' ' . implode(' | ', array_slice($failures, 0, 5));
            if (count($failures) > 5) {
                $message .= ' | and ' . (count($failures) - 5) . ' more failure(s).';
            }

            return back()->with($synced > 0 ? 'warning' : 'error', $message);
        }

        return back()->with('success', $message);
    }
}
