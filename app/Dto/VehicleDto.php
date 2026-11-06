<?php

namespace App\Dto;

class VehicleDto
{

    // database columns
    public readonly string $fleet_no;
    public readonly ?string $phone_number;

    public readonly string $manager_first_name;
    public readonly string $manager_last_name;
    public readonly string $manager_email;

    public readonly string $owner_first_name;
    public readonly string $owner_last_name;

    public readonly string $vehicle_make_model_id;
    public readonly ?string $vehicle_type_id;
    public readonly ?string $vehicle_type;

    public readonly string $truck_no;

    public readonly ?string $gps_unit_id;
    public readonly ?string $gps_imei;
    public readonly ?string $gps_device_type_id;
    public readonly string $gps_provider;
    public readonly bool $gps_status;
    public readonly ?string $last_sync_at;

    public readonly string $last_service_date;
    public readonly string $next_service_due_date;

    public readonly string $insurance_expiry_date;
    public readonly string $vehicle_license_expiry_date;

    public readonly string $base_site;

    public readonly string $status;

    public $drivers;

    private const WIALON_SETTING_FIELDS = [
        'wialon_movement_type', 'wialon_gps_correction', 'wialon_min_satellites',
        'min_moving_speed_kmh', 'min_parking_seconds', 'max_messages_distance_m',
        'min_trip_seconds', 'min_trip_distance_m', 'speed_limit_kmh',
        'speeding_tolerance_kmh', 'speeding_min_duration_s', 'speeding_mode',
    ];

    private array $wialonSettings = [];


    /**
     * Create a new DTO instance.
     */
    public function __construct($request)
    {

        $this->fleet_no = $request['fleet_no'];
        $this->phone_number = $request['phone_number'] ?? null;

        $this->manager_first_name = $request['manager_first_name'];
        $this->manager_last_name = $request['manager_last_name'];
        $this->manager_email = $request['manager_email'];

        $this->owner_first_name = $request['owner_first_name'];
        $this->owner_last_name = $request['owner_last_name'];

        // mapping request -> database columns
        $this->vehicle_make_model_id = $request['make_model'];
        
        // Handle vehicle_type - can be UUID from database or string name from Wialon
        $vehicleTypeInput = $request['vehicle_type'] ?? null;
        
        // Debug logging
        \Log::info('VehicleDto: Processing vehicle_type', [
            'raw_input' => $vehicleTypeInput,
            'is_uuid' => $vehicleTypeInput ? preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/i', $vehicleTypeInput) : false,
        ]);
        
        // Process vehicle_type and determine both ID and text value
        $resolvedTypeId = null;
        $resolvedTypeName = null;
        
        if (!empty($vehicleTypeInput)) {
            // Check if it's a UUID (for database storage)
            if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/i', $vehicleTypeInput)) {
                // It's a UUID - store it as vehicle_type_id
                $resolvedTypeId = $vehicleTypeInput;
                
                // Try to resolve the text name for Wialon sync
                try {
                    // First try database
                    $dbType = \App\Models\VehicleType::find($vehicleTypeInput);
                    if ($dbType && !empty($dbType->name)) {
                        // Map database names to Wialon vehicle type IDs (simplified)
                        $nameMap = [
                            'truck' => 'truck',
                            'car' => 'car',
                            'bus' => 'bus',
                            'van' => 'van',
                            'motorcycle' => 'motorcycle',
                            'minibus' => 'minibus',
                            'pickup' => 'pickup',
                            'tractor' => 'tractor',
                            'trailer' => 'trailer',
                        ];
                        
                        $normalizedName = strtolower(trim($dbType->name));
                        $resolvedTypeName = $nameMap[$normalizedName] ?? 'truck';
                        
                        \Log::info('VehicleDto: Resolved vehicle_type from database', [
                            'vehicle_type_id' => $resolvedTypeId,
                            'db_name' => $dbType->name,
                            'normalized_name' => $normalizedName,
                            'resolved_type' => $resolvedTypeName,
                        ]);
                    } else {
                        // Try Wialon list by UUID match (unlikely but check anyway)
                        $wialonService = app()->make(\App\Services\WialonService::class);
                        $wialonTypes = $wialonService->getWialonVehicleTypes();
                        $found = false;
                        foreach ($wialonTypes as $type) {
                            if ($type['id'] === $vehicleTypeInput) {
                                $resolvedTypeName = $type['id'];
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            // Default fallback
                            $resolvedTypeName = 'truck';
                            \Log::warning('VehicleDto: UUID not found in DB or Wialon, using default', [
                                'vehicle_type_id' => $vehicleTypeInput,
                                'default' => 'truck',
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    // If we can't resolve, use a default
                    \Log::warning('VehicleDto: Could not resolve vehicle_type name from UUID', [
                        'vehicle_type_id' => $vehicleTypeInput,
                        'error' => $e->getMessage(),
                    ]);
                    $resolvedTypeName = 'truck';
                }
            } else {
                // It's already a string name (from Wialon types like 'truck', 'car', etc.)
                $resolvedTypeName = $vehicleTypeInput;
                
                \Log::info('VehicleDto: Using string vehicle_type as-is', [
                    'vehicle_type' => $resolvedTypeName,
                ]);
                
                // Try to find matching UUID from database or keep null
                try {
                    $wialonService = app()->make(\App\Services\WialonService::class);
                    $wialonTypes = $wialonService->getWialonVehicleTypes();
                    
                    // Find if this is a valid Wialon type ID
                    foreach ($wialonTypes as $type) {
                        if ($type['id'] === $vehicleTypeInput) {
                            // It's a valid Wialon type, try to find matching DB record
                            $dbType = \App\Models\VehicleType::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($vehicleTypeInput) . '%'])->first();
                            if ($dbType) {
                                $resolvedTypeId = $dbType->id;
                            }
                            break;
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore lookup errors
                }
            }
        }
        
        // Assign to readonly properties ONCE
        $this->vehicle_type_id = $resolvedTypeId;
        $this->vehicle_type = $resolvedTypeName;
        
        \Log::info('VehicleDto: Final vehicle_type values', [
            'vehicle_type_id' => $this->vehicle_type_id,
            'vehicle_type' => $this->vehicle_type,
        ]);

        $this->truck_no = $request['truck_no'];

        $this->gps_unit_id = $request['gps_unit_id'] ?? null;
        $this->gps_imei = $request['gps_imei'] ?? null;
        $this->gps_device_type_id = $request['gps_device_type_id'] ?? null;
        $this->gps_provider = $request['gps_provider'] ?? 'wialon';
        $this->gps_status = isset($request['gps_status']) ? (bool)$request['gps_status'] : true;
        $this->last_sync_at = $request['last_sync_at'] ?? null;

        $this->last_service_date = $request['last_service_date'];
        $this->next_service_due_date = $request['next_service_due_date'];

        $this->insurance_expiry_date = $request['insurance_expiry_date'];
        $this->vehicle_license_expiry_date = $request['vehicle_license_expiry_date'];

        $this->base_site = $request['base_site'];

        $this->status = $request['status'];

        $this->drivers = $request['drivers'] ?? [];

        $defaults = config('trips.defaults', []);
        foreach (self::WIALON_SETTING_FIELDS as $field) {
            $value = $request[$field] ?? null;
            if ($field === 'wialon_gps_correction') {
                $this->wialonSettings[$field] = array_key_exists($field, $request)
                    ? (bool) $request[$field]
                    : (bool) ($defaults[$field] ?? true);
            } elseif (in_array($field, ['speeding_tolerance_kmh', 'speeding_min_duration_s'], true)) {
                $default = $defaults[$field] ?? null;
                $this->wialonSettings[$field] = ($value === '' || $value === null)
                    ? (($default === '' || $default === null) ? null : (int) $default)
                    : (int) $value;
            } else {
                $this->wialonSettings[$field] = ($value === '' || $value === null)
                    ? ($defaults[$field] ?? null)
                    : (int) $value;
            }
        }
    }


    public static function fromRequest($request)
    {
        return new self($request);
    }


    public function toArray()
    {
        return [

            'fleet_no' => $this->fleet_no,
            'phone_number' => $this->phone_number,

            'manager_first_name' => $this->manager_first_name,
            'manager_last_name' => $this->manager_last_name,
            'manager_email' => $this->manager_email,

            'owner_first_name' => $this->owner_first_name,
            'owner_last_name' => $this->owner_last_name,

            'vehicle_make_model_id' => $this->vehicle_make_model_id,
            'vehicle_type_id' => $this->vehicle_type_id,
            'vehicle_type' => $this->vehicle_type,

            'truck_no' => $this->truck_no,

            'gps_unit_id' => $this->gps_unit_id,
            'gps_imei' => $this->gps_imei,
            'gps_device_type_id' => $this->gps_device_type_id,
            'gps_provider' => $this->gps_provider,
            'gps_status' => $this->gps_status,
            'last_sync_at' => $this->last_sync_at,

            'last_service_date' => $this->last_service_date,
            'next_service_due_date' => $this->next_service_due_date,

            'insurance_expiry_date' => $this->insurance_expiry_date,
            'vehicle_license_expiry_date' => $this->vehicle_license_expiry_date,

            'base_site' => $this->base_site,

            'status' => $this->status,
        ] + $this->wialonSettings;
    }

}
