<?php

namespace App\Repositories;

use App\Interfaces\VehicleInterface;
use App\Dto\VehicleDto;
use App\Models\Vehicle;
use App\Models\GpsDeviceType;
use App\Services\WialonService;
use App\Services\WialonVehicleSettingsSyncLogger;
use App\Exceptions\WialonSettingsSyncException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleRepository extends BaseRepository implements VehicleInterface
{
    protected WialonService $wialon;

    public function __construct(Vehicle $model, WialonService $wialon)
    {
        $this->setModel($model);
        $this->wialon = $wialon;
    }

    /**
     * Store a newly created resource in storage.
     *
     * Strategy:
     * 1. Create vehicle in local database FIRST
     * 2. Then attempt Wialon synchronization
     * 3. If Wialon fails, vehicle remains in DB with failed status
     * 4. Admin can retry synchronization later
     */
    public function store(VehicleDto $data)
    {
        return DB::transaction(function () use ($data) {
            $dataArray = $data->toArray();

            // Set created_by to authenticated user ID
            $dataArray['created_by'] = auth()->id();

            // Never trust frontend-supplied Wialon fields
            unset($dataArray['gps_unit_id'], $dataArray['gps_sync_status'], $dataArray['gps_sync_error'], $dataArray['last_sync_at']);

            $gpsProvider = $dataArray['gps_provider'] ?? null;
            $gpsImei = $dataArray['gps_imei'] ?? null;
            $gpsDeviceTypeId = $dataArray['gps_device_type_id'] ?? null;
            unset($dataArray['gps_device_type_id']); // not a vehicles column

            // ALWAYS validate uniqueness of Fleet Number and Registration Number (regardless of GPS provider)
            $fleetNo = $dataArray['fleet_no'] ?? null;
            if (!empty($fleetNo) && Vehicle::where('fleet_no', $fleetNo)->exists()) {
                throw new \InvalidArgumentException("Fleet Number {$fleetNo} is already assigned to another vehicle in the system.");
            }

            $truckNo = $dataArray['truck_no'] ?? null;
            if (!empty($truckNo) && Vehicle::where('truck_no', $truckNo)->exists()) {
                throw new \InvalidArgumentException("Vehicle Registration Number {$truckNo} is already assigned to another vehicle in the system.");
            }

            // Prepare Wialon sync data if provider is Wialon
            $shouldSyncWialon = false;
            $settingsWarning = null;
            $hwTypeId = null;
            $unitName = null;

            if ($gpsProvider === 'wialon') {
                if (empty($gpsImei) || empty($gpsDeviceTypeId)) {
                    throw new \InvalidArgumentException('GPS IMEI and device type are required when GPS provider is Wialon.');
                }

                // Validate IMEI format (15 digits)
                if (!preg_match('/^\d{15}$/', $gpsImei)) {
                    throw new \InvalidArgumentException('GPS IMEI must be exactly 15 digits.');
                }

                // Duplicate IMEI check in local database
                if (Vehicle::where('gps_imei', $gpsImei)->exists()) {
                    throw new \InvalidArgumentException("GPS IMEI {$gpsImei} is already assigned to another vehicle in the system.");
                }

                $deviceType = GpsDeviceType::where('id', $gpsDeviceTypeId)->where('status', 1)->first();
                if (!$deviceType) {
                    throw new \InvalidArgumentException('Selected GPS device type was not found or is inactive.');
                }

                $hwTypeId = (int) $deviceType->wialon_hw_type_id;
                if (empty($hwTypeId) || $hwTypeId <= 0) {
                    throw new \InvalidArgumentException('Invalid Wialon hardware type ID for the selected GPS device type.');
                }

                $unitName = $dataArray['fleet_no'] ?? $dataArray['truck_no'] ?? ('vehicle-' . $gpsImei);
                $unitName = preg_replace('/[^\w\s\-]/', '', $unitName);
                $unitName = trim($unitName);
                if (empty($unitName)) {
                    $unitName = 'Vehicle-' . substr($gpsImei, -6);
                }

                // IMPORTANT: Check for duplicates in Wialon BEFORE creating database record
                try {
                    $existingUnitByImei = $this->wialon->findUnitByImei($gpsImei);
                    if ($existingUnitByImei !== null) {
                        $existingUnitId = $existingUnitByImei['id'] ?? 'unknown';
                        $existingUnitName = $existingUnitByImei['nm'] ?? 'unknown';

                        throw new \InvalidArgumentException(
                            "GPS IMEI {$gpsImei} already exists in Wialon tracking system " .
                            "(Unit ID: {$existingUnitId}, Name: {$existingUnitName}). " .
                            "Each vehicle must have a unique IMEI."
                        );
                    }

                    // Also check if fleet number exists in Wialon (by unit name)
                    if (!empty($unitName)) {
                        $existingUnitByName = $this->wialon->findUnitByName($unitName);
                        if ($existingUnitByName !== null) {
                            $existingNameUnitId = $existingUnitByName['id'] ?? 'unknown';

                            throw new \InvalidArgumentException(
                                "Fleet Number {$unitName} already exists in Wialon tracking system " .
                                "(Unit ID: {$existingNameUnitId}). " .
                                "Each vehicle must have a unique fleet number."
                            );
                        }
                    }
                } catch (\InvalidArgumentException $e) {
                    // Re-throw validation errors to stop the transaction
                    throw $e;
                } catch (\Throwable $th) {
                    // For API errors, log but continue (graceful degradation)
                    Log::warning('Wialon pre-validation check failed, continuing with creation', [
                        'error' => $th->getMessage(),
                    ]);
                }

                // Set initial pending status
                $dataArray['gps_hw_type_id'] = $hwTypeId;
                $dataArray['gps_unit_id'] = null;
                $dataArray['gps_sync_status'] = 'pending';
                $dataArray['gps_sync_error'] = null;
                $dataArray['last_sync_at'] = now();

                $shouldSyncWialon = true;

            } else {
                $dataArray['gps_sync_status'] = 'pending';
                $dataArray['gps_sync_error'] = null;
            }

            // STEP 1: Create vehicle in local database FIRST
            $vehicle = $this->add($this->_model, $dataArray);

            if (!$vehicle) {
                throw new \RuntimeException('Failed to create vehicle in database');
            }

            Log::info('Vehicle created in database', [
                'vehicle_id' => $vehicle->id,
                'fleet_no' => $vehicle->fleet_no,
                'truck_no' => $vehicle->truck_no,
                'gps_provider' => $gpsProvider,
            ]);

            // STEP 2: Attempt Wialon synchronization (if needed)
            if ($shouldSyncWialon) {
                Log::info('Starting Wialon synchronization', [
                    'vehicle_id' => $vehicle->id,
                    'fleet_no' => $vehicle->fleet_no,
                    'truck_no' => $vehicle->truck_no,
                    'gps_imei' => $gpsImei,
                    'hw_type_id' => $hwTypeId,
                    'unit_name' => $unitName,
                ]);

                try {
                    // Provision unit in Wialon using Fleet No as unitName and passing vehicle_type and phone_number
                    $result = $this->wialon->provisionUnit($unitName, $hwTypeId, $gpsImei, $dataArray['vehicle_type'] ?? null, $dataArray['phone_number'] ?? null);

                    if (!isset($result['unit_id']) || empty($result['unit_id'])) {
                        throw new \RuntimeException('Wialon provisioning returned empty unit ID');
                    }

                    $wialonUnitId = (int) $result['unit_id'];

                    // Reject fake/invalid IDs
                    if ($wialonUnitId === 999999 || $wialonUnitId <= 0) {
                        throw new \RuntimeException('Wialon returned invalid unit ID: ' . $wialonUnitId);
                    }

                    // Trip and speeding settings are deliberately non-fatal on create.
                    // Unit provisioning and its duplicate checks remain authoritative.
                    $vehicleSettings = $this->vehicleSettings($dataArray);
                    try {
                        $this->wialon->pushUnitSettings($wialonUnitId, $vehicleSettings);
                        $vehicle->update(['wialon_settings_synced_at' => now()]);
                        $this->recordSettingsSync($vehicle, 'push', 'success', $vehicleSettings);
                    } catch (\Throwable $settingsException) {
                        $settingsWarning = 'Vehicle created, but Wialon settings could not be saved: ' . $settingsException->getMessage();
                        $vehicle->update(['wialon_settings_synced_at' => null]);
                        $this->recordSettingsSync($vehicle, 'push', 'failed', $vehicleSettings, $settingsException->getMessage());
                        Log::warning('Non-fatal: Wialon vehicle settings update failed during creation', [
                            'vehicle_id' => $vehicle->id,
                            'wialon_unit_id' => $wialonUnitId,
                            'error' => $settingsException->getMessage(),
                        ]);
                    }

                    // SUCCESS - Update vehicle with real Wialon unit ID
                    $vehicle->update([
                        'gps_unit_id' => $wialonUnitId,
                        'gps_sync_status' => 'synced',
                        'gps_sync_error' => null,
                        'last_sync_at' => now(),
                    ]);

                    Log::info('Wialon synchronization completed successfully', [
                        'vehicle_id' => $vehicle->id,
                        'fleet_no' => $vehicle->fleet_no,
                        'wialon_unit_id' => $wialonUnitId,
                        'unit_name' => $unitName,
                        'imei' => $gpsImei,
                    ]);

                } catch (\InvalidArgumentException $e) {
                    // Validation errors (duplicate IMEI, etc.) - DELETE the vehicle and re-throw
                    Log::error('Wialon validation failed - deleting vehicle from database', [
                        'vehicle_id' => $vehicle->id,
                        'fleet_no' => $vehicle->fleet_no,
                        'error' => $e->getMessage(),
                    ]);

                    // Delete the vehicle we just created
                    $vehicle->delete();

                    // Re-throw to show error to user
                    throw $e;

                } catch (\Throwable $th) {
                    // Other Wialon synchronization errors - DELETE vehicle and throw user-friendly error
                    Log::error('Wialon synchronization failed - deleting vehicle from database', [
                        'vehicle_id' => $vehicle->id,
                        'fleet_no' => $vehicle->fleet_no,
                        'truck_no' => $vehicle->truck_no,
                        'gps_imei' => $gpsImei,
                        'hw_type_id' => $hwTypeId,
                        'unit_name' => $unitName,
                        'error' => $th->getMessage(),
                        'exception_class' => get_class($th),
                    ]);

                    // Delete the vehicle we just created
                    $vehicle->delete();

                    // Throw user-friendly error
                    throw new \RuntimeException(
                        'Failed to create vehicle in GPS tracking system: ' . $th->getMessage()
                    );
                }
            }

            return ['warning' => $settingsWarning];
        });
    }

    public function update($id, VehicleDto $data)
    {
        $result = $this->checkRecord($id);

        $dataArray = $data->toArray();

        // Never let a plain field edit re-trigger unit creation — Wialon
        // sync fields are backend-controlled here too.
        unset($dataArray['gps_unit_id'], $dataArray['gps_sync_status'], $dataArray['gps_sync_error'], $dataArray['last_sync_at']);
        $gpsDeviceTypeId = $dataArray['gps_device_type_id'] ?? null;
        unset($dataArray['gps_device_type_id']);

        $gpsProvider = $dataArray['gps_provider'] ?? $result->gps_provider;
        $newImei = $dataArray['gps_imei'] ?? $result->gps_imei;
        $newFleetNo = $dataArray['fleet_no'] ?? $result->fleet_no;
        $newVehicleType = $dataArray['vehicle_type'] ?? $result->vehicle_type;
        $newPhoneNumber = $dataArray['phone_number'] ?? $result->phone_number;
        $settingsPushed = false;
        $settingsPushedPayload = [];

        // ALWAYS validate uniqueness of Fleet Number if it's being changed
        if (array_key_exists('fleet_no', $dataArray) && $dataArray['fleet_no'] !== $result->fleet_no) {
            $checkFleetNo = $dataArray['fleet_no'];
            if (!empty($checkFleetNo) && Vehicle::where('fleet_no', $checkFleetNo)->where('id', '!=', $result->id)->exists()) {
                throw new \InvalidArgumentException("Fleet Number {$checkFleetNo} is already assigned to another vehicle in the system.");
            }
        }

        // ALWAYS validate uniqueness of Registration Number if it's being changed
        if (array_key_exists('truck_no', $dataArray) && $dataArray['truck_no'] !== $result->truck_no) {
            $checkTruckNo = $dataArray['truck_no'];
            if (!empty($checkTruckNo) && Vehicle::where('truck_no', $checkTruckNo)->where('id', '!=', $result->id)->exists()) {
                throw new \InvalidArgumentException("Vehicle Registration Number {$checkTruckNo} is already assigned to another vehicle in the system.");
            }
        }

        // Re-push settings on every edit. This also repairs Wialon when it has
        // drifted from the local values even though the form values did not change.
        if (!empty($result->gps_unit_id) && $gpsProvider === 'wialon') {
            $settings = $this->vehicleSettings($dataArray);
            $storedUnitId = (string) $result->gps_unit_id;
            $unitId = filter_var($storedUnitId, FILTER_VALIDATE_INT);
            if (!$unitId || $unitId <= 0 || strlen($storedUnitId) >= 13) {
                $message = 'Cannot sync Wialon settings because this vehicle does not have a valid numeric Wialon unit ID.';
                $this->recordSettingsSync($result, 'push', 'failed', $settings, $message);
                throw new WialonSettingsSyncException($message);
            }

            try {
                $this->wialon->pushUnitSettings((int) $unitId, $settings);
                $dataArray['wialon_settings_synced_at'] = now();
                $settingsPushed = true;
                $settingsPushedPayload = $settings;
            } catch (\Throwable $settingsException) {
                $this->recordSettingsSync($result, 'push', 'failed', $settings, $settingsException->getMessage());
                throw new WialonSettingsSyncException($settingsException->getMessage(), 0, $settingsException);
            }
        }

        // SCENARIO 1: Vehicle currently has NO Wialon unit ID (e.g. initial sync failed or provider changed)
        if (empty($result->gps_unit_id) && $gpsProvider === 'wialon') {
            if (!empty($newImei) && !empty($gpsDeviceTypeId)) {
                $deviceType = GpsDeviceType::where('id', $gpsDeviceTypeId)->where('status', 1)->first();
                if ($deviceType && !empty($deviceType->wialon_hw_type_id)) {
                    $hwTypeId = (int) $deviceType->wialon_hw_type_id;
                    $unitName = preg_replace('/[^\w\s\-]/', '', trim($newFleetNo));
                    if (empty($unitName)) {
                        $unitName = 'Vehicle-' . substr($newImei, -6);
                    }

                    try {
                        $wialonResult = $this->wialon->provisionUnit(
                            $unitName,
                            $hwTypeId,
                            $newImei,
                            $newVehicleType,
                            $newPhoneNumber
                        );

                        if (isset($wialonResult['unit_id']) && (int)$wialonResult['unit_id'] > 0) {
                            $dataArray['gps_unit_id'] = (int)$wialonResult['unit_id'];
                            $dataArray['gps_hw_type_id'] = $hwTypeId;
                            $dataArray['gps_sync_status'] = 'synced';
                            $dataArray['gps_sync_error'] = null;
                            $dataArray['last_sync_at'] = now();
                        }
                    } catch (\Throwable $th) {
                        Log::error('Wialon provisioning on edit failed: ' . $th->getMessage());
                        $dataArray['gps_sync_status'] = 'failed';
                        $dataArray['gps_sync_error'] = $th->getMessage();
                    }
                }
            }
        }
        // SCENARIO 2: Vehicle ALREADY HAS a Wialon unit ID
        elseif (!empty($result->gps_unit_id)) {
            $wialonUnitId = (int) $result->gps_unit_id;

            // A. Update Unit Name (Fleet No) in Wialon if changed
            if (array_key_exists('fleet_no', $dataArray) && $dataArray['fleet_no'] !== $result->fleet_no) {
                $unitName = preg_replace('/[^\w\s\-]/', '', trim($dataArray['fleet_no']));
                if (!empty($unitName)) {
                    // VALIDATION: check Wialon for another unit already using this name,
                    // excluding this vehicle's own unit (self-exclusion).
                    // This check was previously missing on the edit path, unlike create.
                    try {
                        $existingUnitByName = $this->wialon->findUnitByName($unitName);
                        if ($existingUnitByName !== null && (int) ($existingUnitByName['id'] ?? 0) !== $wialonUnitId) {
                            $existingNameUnitId = $existingUnitByName['id'] ?? 'unknown';

                            throw new \InvalidArgumentException(
                                "Fleet Number {$dataArray['fleet_no']} already exists in Wialon tracking system " .
                                "(Unit ID: {$existingNameUnitId}). Each vehicle must have a unique fleet number."
                            );
                        }
                    } catch (\InvalidArgumentException $e) {
                        // Real duplicate — stop the update and surface the error
                        throw $e;
                    } catch (\Throwable $th) {
                        // API/network error only — log and proceed (graceful degradation,
                        // same behaviour as the create path)
                        Log::warning('Wialon fleet-name pre-check failed, continuing with update', [
                            'vehicle_id' => $result->id,
                            'error' => $th->getMessage(),
                        ]);
                    }

                    try {
                        $this->wialon->updateUnitName($wialonUnitId, $unitName);
                    } catch (\Throwable $th) {
                        Log::warning('Wialon unit name update failed: ' . $th->getMessage());
                    }
                }
            }

            // B. Update IMEI or Hardware Device Type in Wialon if changed
            $imeiChanged = array_key_exists('gps_imei', $dataArray)
                && $dataArray['gps_imei'] !== $result->gps_imei;
            $deviceChanged = $gpsDeviceTypeId
                && (int) optional(GpsDeviceType::find($gpsDeviceTypeId))->wialon_hw_type_id !== (int) $result->gps_hw_type_id;

            if ($imeiChanged || $deviceChanged) {
                $checkImei = $dataArray['gps_imei'] ?? $result->gps_imei;

                // Check for duplicate IMEI in local database
                if (Vehicle::where('gps_imei', $checkImei)->where('id', '!=', $result->id)->exists()) {
                    throw new \InvalidArgumentException("GPS IMEI {$checkImei} is already assigned to another vehicle in the system.");
                }

                $hwTypeId = $deviceChanged
                    ? (int) GpsDeviceType::find($gpsDeviceTypeId)->wialon_hw_type_id
                    : (int) $result->gps_hw_type_id;

                try {
                    $this->wialon->updateDeviceType($wialonUnitId, $hwTypeId, $checkImei);

                    $dataArray['gps_hw_type_id'] = $hwTypeId;
                    $dataArray['gps_sync_status'] = 'synced';
                    $dataArray['gps_sync_error'] = null;
                    $dataArray['last_sync_at'] = now();

                    Log::info('Wialon unit device/IMEI updated', [
                        'vehicle_id' => $result->id,
                        'wialon_unit_id' => $result->gps_unit_id,
                    ]);
                } catch (\InvalidArgumentException $e) {
                    // Re-throw validation errors from Wialon (like duplicate IMEI)
                    throw $e;
                } catch (\Throwable $th) {
                    Log::error('Wialon device/IMEI update failed', [
                        'vehicle_id' => $result->id,
                        'wialon_unit_id' => $result->gps_unit_id,
                        'error' => $th->getMessage(),
                        'exception' => $th,
                    ]);

                    $dataArray['gps_sync_status'] = 'failed';
                    $dataArray['gps_sync_error'] = $th->getMessage();
                }
            }

            // C. Sync unit type (vehicle_class) to Wialon whenever a type is set
            if (!empty($newVehicleType)) {
                try {
                    $this->wialon->updateVehicleType($wialonUnitId, $newVehicleType);

                    Log::info('Wialon unit vehicle_type updated', [
                        'vehicle_id' => $result->id,
                        'wialon_unit_id' => $result->gps_unit_id,
                        'vehicle_type' => $newVehicleType,
                    ]);
                } catch (\Throwable $th) {
                    Log::warning('Wialon vehicle_type update failed (non-fatal)', [
                        'vehicle_id' => $result->id,
                        'wialon_unit_id' => $result->gps_unit_id,
                        'vehicle_type' => $newVehicleType,
                        'error' => $th->getMessage(),
                    ]);
                }
            }

            // D. Sync phone number to Wialon whenever it is present in the save payload
            if (array_key_exists('phone_number', $dataArray)) {
                try {
                    $this->wialon->updateUnitPhoneNumber($wialonUnitId, $newPhoneNumber);

                    Log::info('Wialon unit phone_number updated', [
                        'vehicle_id' => $result->id,
                        'wialon_unit_id' => $result->gps_unit_id,
                        'phone_number' => $newPhoneNumber,
                    ]);
                } catch (\Throwable $th) {
                    Log::warning('Wialon phone_number update failed (non-fatal)', [
                        'vehicle_id' => $result->id,
                        'wialon_unit_id' => $result->gps_unit_id,
                        'phone_number' => $newPhoneNumber,
                        'error' => $th->getMessage(),
                    ]);
                }
            }
        }

        // A previously unlinked vehicle may have just received a new unit above.
        // Push its settings before saving them locally; unlike create, an edit
        // must fail visibly if the settings could not be written.
        if (empty($result->gps_unit_id) && !empty($dataArray['gps_unit_id']) && $gpsProvider === 'wialon') {
            $settings = $this->vehicleSettings($dataArray);
            try {
                $this->wialon->pushUnitSettings((int) $dataArray['gps_unit_id'], $settings);
                $dataArray['wialon_settings_synced_at'] = now();
                $settingsPushed = true;
                $settingsPushedPayload = $settings;
            } catch (\Throwable $settingsException) {
                $this->recordSettingsSync($result, 'push', 'failed', $settings, $settingsException->getMessage());
                throw new WialonSettingsSyncException($settingsException->getMessage(), 0, $settingsException);
            }
        }

        $result->update($dataArray);

        if ($settingsPushed) {
            $this->recordSettingsSync($result, 'push', 'success', $settingsPushedPayload);
        }

        return $result;
    }

    /** @param array<string, mixed> $attributes */
    private function vehicleSettings(array $attributes): array
    {
        $fields = [
            'wialon_movement_type', 'wialon_gps_correction', 'wialon_min_satellites',
            'min_moving_speed_kmh', 'min_parking_seconds', 'max_messages_distance_m',
            'min_trip_seconds', 'min_trip_distance_m', 'speed_limit_kmh',
            'speeding_tolerance_kmh', 'speeding_min_duration_s', 'speeding_mode',
        ];

        return array_intersect_key($attributes, array_flip($fields));
    }

    private function recordSettingsSync(Vehicle $vehicle, string $direction, string $status, array $settings, ?string $error = null): void
    {
        app(WialonVehicleSettingsSyncLogger::class)->record($vehicle, $direction, $status, $settings, $error);
    }

    /**
     * Retry Wialon synchronization for a failed vehicle
     * This allows admin to retry sync without recreating the vehicle
     */
    public function retryWialonSync($vehicleId): bool
    {
        $vehicle = $this->checkRecord($vehicleId);

        if ($vehicle->gps_provider !== 'wialon') {
            throw new \InvalidArgumentException('Vehicle GPS provider is not Wialon');
        }

        if ($vehicle->gps_sync_status === 'synced' && !empty($vehicle->gps_unit_id)) {
            throw new \InvalidArgumentException('Vehicle is already synced with Wialon');
        }

        if (empty($vehicle->gps_imei)) {
            throw new \InvalidArgumentException('Vehicle has no GPS IMEI set');
        }

        if (empty($vehicle->gps_hw_type_id)) {
            throw new \InvalidArgumentException('Vehicle has no hardware type ID set');
        }

        $unitName = $vehicle->fleet_no ?? $vehicle->truck_no ?? ('vehicle-' . $vehicle->gps_imei);
        $unitName = preg_replace('/[^\w\s\-]/', '', $unitName);
        $unitName = trim($unitName);
        if (empty($unitName)) {
            $unitName = 'Vehicle-' . substr($vehicle->gps_imei, -6);
        }

        Log::info('Retrying Wialon synchronization', [
            'vehicle_id' => $vehicle->id,
            'fleet_no' => $vehicle->fleet_no,
            'truck_no' => $vehicle->truck_no,
            'gps_imei' => $vehicle->gps_imei,
            'hw_type_id' => $vehicle->gps_hw_type_id,
            'unit_name' => $unitName,
        ]);

        try {
            $result = $this->wialon->provisionUnit(
                $unitName,
                $vehicle->gps_hw_type_id,
                $vehicle->gps_imei,
                $vehicle->vehicle_type,
                $vehicle->phone_number
            );

            if (!isset($result['unit_id']) || empty($result['unit_id'])) {
                throw new \RuntimeException('Wialon provisioning returned empty unit ID');
            }

            $wialonUnitId = (int) $result['unit_id'];

            if ($wialonUnitId === 999999 || $wialonUnitId <= 0) {
                throw new \RuntimeException('Wialon returned invalid unit ID: ' . $wialonUnitId);
            }

            $vehicle->update([
                'gps_unit_id' => $wialonUnitId,
                'gps_sync_status' => 'synced',
                'gps_sync_error' => null,
                'last_sync_at' => now(),
            ]);

            Log::info('Wialon synchronization retry succeeded', [
                'vehicle_id' => $vehicle->id,
                'wialon_unit_id' => $wialonUnitId,
            ]);

            return true;

        } catch (\Throwable $th) {
            Log::error('Wialon synchronization retry failed', [
                'vehicle_id' => $vehicle->id,
                'error' => $th->getMessage(),
            ]);

            $vehicle->update([
                'gps_sync_status' => 'failed',
                'gps_sync_error' => $th->getMessage(),
                'last_sync_at' => now(),
            ]);

            throw new \RuntimeException('Failed to sync with Wialon: ' . $th->getMessage(), 0, $th);
        }
    }

    /**
     * Delete a vehicle from both database and Wialon.
     *
     * Strategy:
     * 1. Check if vehicle exists and has Wialon unit
     * 2. Delete from Wialon first (if unit exists)
     * 3. Then delete from database
     * 4. If Wialon deletion fails, log but still delete from database
     *
     * @param mixed $model The model (passed for compatibility with parent)
     * @param string $id Vehicle ID
     * @return bool
     * @throws \RuntimeException if database deletion fails
     */
    public function delete($model, $id)
    {
        $vehicle = $this->checkRecord($id);

        if (!$vehicle) {
            throw new \RuntimeException('Vehicle not found');
        }

        Log::info('Starting vehicle deletion', [
            'vehicle_id' => $vehicle->id,
            'fleet_no' => $vehicle->fleet_no,
            'gps_unit_id' => $vehicle->gps_unit_id,
            'gps_provider' => $vehicle->gps_provider,
        ]);

        // STEP 1: Delete from Wialon if unit exists
        if (!empty($vehicle->gps_unit_id) && $vehicle->gps_provider === 'wialon') {
            try {
                $result = $this->wialon->deleteUnit((int) $vehicle->gps_unit_id);

                Log::info('Wialon unit deleted successfully', [
                    'vehicle_id' => $vehicle->id,
                    'wialon_unit_id' => $vehicle->gps_unit_id,
                    'result' => $result,
                ]);
            } catch (\Throwable $th) {
                // Log error but continue with database deletion
                // We don't want to prevent database cleanup if Wialon fails
                Log::error('Wialon unit deletion failed (continuing with database deletion)', [
                    'vehicle_id' => $vehicle->id,
                    'wialon_unit_id' => $vehicle->gps_unit_id,
                    'error' => $th->getMessage(),
                ]);
            }
        } else {
            Log::info('No Wialon unit to delete', [
                'vehicle_id' => $vehicle->id,
                'gps_provider' => $vehicle->gps_provider,
            ]);
        }

        // STEP 2: Delete from database using the parent trait method
        try {
            // Use the model passed as parameter or fall back to $this->_model
            $modelToUse = $model ?? $this->_model;
            $deleted = $modelToUse->findOrFail($id)->delete();

            if ($deleted) {
                Log::info('Vehicle deleted successfully from database', [
                    'vehicle_id' => $vehicle->id,
                    'fleet_no' => $vehicle->fleet_no,
                ]);
            } else {
                throw new \RuntimeException('Database delete returned false');
            }

            return true;

        } catch (\Throwable $th) {
            Log::error('Failed to delete vehicle from database', [
                'vehicle_id' => $vehicle->id,
                'error' => $th->getMessage(),
            ]);

            throw new \RuntimeException(
                'Failed to delete vehicle from database: ' . $th->getMessage(),
                0,
                $th
            );
        }
    }
}
