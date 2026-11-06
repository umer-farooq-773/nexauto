<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\WialonService;
use App\Services\WialonVehicleSettingsSyncLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWialonVehicleSettings extends Command
{
    protected $signature = 'wialon:sync-vehicle-settings {--vehicle= : Limit to a vehicle ID} {--push : Push vehicle settings to Wialon} {--dry-run : Compare settings without changing anything}';

    protected $description = 'Pull Wialon trip and speeding settings to vehicles, or push vehicle settings to Wialon';

    public function handle(WialonService $wialon, WialonVehicleSettingsSyncLogger $syncLogger): int
    {
        $vehicles = Vehicle::query()
            ->whereNotNull('gps_unit_id')
            ->when($this->option('vehicle'), fn ($query, $id) => $query->whereKey($id))
            ->orderBy('fleet_no')
            ->get();

        if ($vehicles->isEmpty()) {
            $this->warn('No vehicles with a Wialon unit were found.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $push = (bool) $this->option('push');
        $hasFailures = false;

        foreach ($vehicles as $vehicle) {
            $unitId = filter_var($vehicle->gps_unit_id, FILTER_VALIDATE_INT);
            $settingsFields = [
                'wialon_movement_type', 'wialon_gps_correction', 'wialon_min_satellites',
                'min_moving_speed_kmh', 'min_parking_seconds', 'max_messages_distance_m',
                'min_trip_seconds', 'min_trip_distance_m', 'speed_limit_kmh',
                'speeding_tolerance_kmh', 'speeding_min_duration_s', 'speeding_mode',
            ];
            $localSettings = $vehicle->only($settingsFields);
            if (!$unitId || $unitId <= 0 || strlen((string) $vehicle->gps_unit_id) >= 13) {
                $hasFailures = true;
                $this->line(sprintf('FAILED %s: invalid Wialon unit ID (%s)', $vehicle->fleet_no, (string) $vehicle->gps_unit_id));
                $syncLogger->record($vehicle, $push ? 'push' : 'pull', 'failed', $localSettings, 'Invalid numeric Wialon unit ID.');
                continue;
            }

            try {
                $remoteSettings = $wialon->pullUnitSettings((int) $unitId);
                // Null optional values are omitted from a push, so Wialon's
                // existing optional report values remain valid in push mode.
                $different = !$this->settingsMatch($localSettings, $remoteSettings, $push);

                if ($push) {
                    if (!$dryRun) {
                        $wialon->pushUnitSettings((int) $unitId, $localSettings);
                        $vehicle->forceFill(['wialon_settings_synced_at' => now()])->save();
                        $syncLogger->record($vehicle, 'push', 'success', $localSettings);
                    }

                    $label = $different ? 'DIFFERENT' : 'OK';
                    $detail = $dryRun
                        ? ($different ? 'would push vehicle settings' : 'settings match; no changes made')
                        : 'pushed vehicle settings and verified Wialon read-back';
                } else {
                    if ($different && !$dryRun) {
                        $vehicle->forceFill($remoteSettings + ['wialon_settings_synced_at' => now()])->save();
                        $syncLogger->record($vehicle, 'pull', 'success', $remoteSettings);
                    } elseif (!$dryRun) {
                        $vehicle->forceFill(['wialon_settings_synced_at' => now()])->save();
                        $syncLogger->record($vehicle, 'pull', 'success', $remoteSettings);
                    }

                    $label = $different ? 'DIFFERENT' : 'OK';
                    $detail = $different ? ($dryRun ? 'Wialon values differ from vehicle' : 'pulled Wialon values to vehicle') : 'settings match';
                }

                $this->line(sprintf('%s %s (unit %d): %s', $label, $vehicle->fleet_no, $unitId, $detail));
            } catch (\Throwable $exception) {
                $hasFailures = true;
                $this->line(sprintf('FAILED %s (unit %d): %s', $vehicle->fleet_no, $unitId, $exception->getMessage()));
                $syncLogger->record($vehicle, $push ? 'push' : 'pull', 'failed', $localSettings, $exception->getMessage());
                Log::warning('Wialon vehicle settings sync failed', [
                    'vehicle_id' => $vehicle->id,
                    'wialon_unit_id' => $unitId,
                    'direction' => $push ? 'push' : 'pull',
                    'dry_run' => $dryRun,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }

    private function settingsMatch(array $local, array $remote, bool $ignoreUnsetLocal = false): bool
    {
        foreach ($remote as $key => $remoteValue) {
            $localValue = $local[$key] ?? null;
            if ($ignoreUnsetLocal && $localValue === null) {
                continue;
            }
            if ($key === 'wialon_gps_correction') {
                if ((bool) $localValue !== (bool) $remoteValue) {
                    return false;
                }
            } elseif ($localValue === null || $remoteValue === null) {
                if ($localValue !== $remoteValue) {
                    return false;
                }
            } elseif ((int) $localValue !== (int) $remoteValue) {
                return false;
            }
        }

        return true;
    }
}
