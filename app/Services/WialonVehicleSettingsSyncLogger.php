<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\WialonVehicleSettingsSyncLog;
use Illuminate\Support\Facades\Log;

class WialonVehicleSettingsSyncLogger
{
    public function record(Vehicle $vehicle, string $direction, string $status, array $settings, ?string $error = null): void
    {
        try {
            WialonVehicleSettingsSyncLog::create([
                'vehicle_id' => $vehicle->id,
                'user_id' => auth()->id(),
                'direction' => $direction,
                'status' => $status,
                'settings' => $settings,
                'error' => $error,
            ]);
        } catch (\Throwable $logException) {
            Log::error('Could not persist Wialon vehicle settings sync log', [
                'vehicle_id' => $vehicle->id,
                'direction' => $direction,
                'status' => $status,
                'error' => $logException->getMessage(),
            ]);
        }
    }
}
