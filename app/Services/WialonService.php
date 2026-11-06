<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WialonService
{
    protected const VALID_WIALON_VEHICLE_CLASSES = [
        // Cargo transport
        'pickup', 'van', 'truck', 'heavy_truck', 'dump_truck', 'logging_truck',
        'tractor_unit', 'trailers_semi', 'tanker_trailer', 'tank_trailer',
        'refrigerator_van', 'other_cargo',
        // Passenger transport
        'kick_scooter', 'bicycle', 'motorcycle', 'passenger_car', 'minibus',
        'bus', 'trolleybus', 'tram', 'other_passenger',
        // Agricultural machinery
        'tractor', 'combine', 'sprayer', 'baler', 'agricultural_spreader', 'mower',
        // Construction equipment
        'bulldozer', 'excavator', 'backhoe_loader', 'loader', 'skid_steer_loader',
        'forklift', 'hooklift', 'crane', 'manipulator', 'bucket_truck',
        'concrete_mixer', 'concrete_pump', 'piling_drilling_rig', 'crusher',
        'trencher', 'pipelayer', 'road_roller', 'road_grader', 'paver', 'scraper',
        // Special vehicles
        'ambulance', 'garbage_truck', 'fire_truck', 'street_cleaning_machine',
        'tow_truck', 'other_special', 'store_on_wheels',
        // Others
        'air_transport', 'off_road_vehicle', 'all_terrain_vehicle', 'snowmobile',
        'snowcat', 'rail_transport', 'water_transport', 'recreational_vehicle',
        'other_vehicle',
    ];

    protected string $host;
    protected string $token;
    protected ?int $creatorId;
    protected ?string $sessionId = null;
    protected bool $sessionRetried = false;
    protected ?int $cachedResourceId = null;


    public function __construct()
    {
        $this->host = rtrim(config('services.wialon.host'), '/');
        $this->token = config('services.wialon.token');
        $this->creatorId = config('services.wialon.creator_id') ? (int) config('services.wialon.creator_id') : null;
    }

    /**
     * Login to Wialon and get session ID
     * SECURITY: Does not log token or session ID
     */
    protected function login(): ?string
    {
        // Cache session ID for 10 minutes to avoid repeated logins
        $cacheKey = 'wialon_session_' . md5($this->token);

        $sessionId = Cache::get($cacheKey);

        if ($sessionId) {
            return $sessionId;
        }

        try {
            $response = Http::timeout(30)->get($this->host . '/wialon/ajax.html', [
                'svc' => 'token/login',
                'params' => json_encode([
                    'token' => $this->token
                ])
            ]);

            if (!$response->successful()) {
                Log::error('Wialon login HTTP failure', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('Wialon login failed', [
                    'error_code' => $data['error'],
                    'error_message' => $this->getWialonErrorMessage($data['error']),
                ]);
                return null;
            }

            if (isset($data['eid'])) {
                $sessionId = $data['eid'];
                Cache::put($cacheKey, $sessionId, 600); // 10 minutes

                Log::info('Wialon login successful', [
                    'user_id' => $data['user']['id'] ?? null,
                    'user_name' => $data['user']['nm'] ?? null,
                ]);

                return $sessionId;
            }

            Log::error('Wialon login failed: no session ID in response');
            return null;

        } catch (\Exception $e) {
            Log::error('Wialon login exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Make API request to Wialon with automatic session retry
     * SECURITY: Does not log session ID or token
     */
    protected function request(string $service, array $params = [], bool $isRetry = false)
    {
        if (!$this->sessionId) {
            $this->sessionId = $this->login();
        }

        if (!$this->sessionId) {
            throw new \RuntimeException('Cannot obtain Wialon session');
        }

        try {
            $requestData = [
                'svc' => $service,
                'params' => json_encode($params),
                'sid' => $this->sessionId
            ];

            Log::debug('Wialon API request', [
                'service' => $service,
                'params' => $params,
            ]);

            // Use longer timeout for report operations which can take time
            $timeout = str_starts_with($service, 'report/') ? 120 : 30;
            $response = Http::timeout($timeout)->get($this->host . '/wialon/ajax.html', $requestData);

            if (!$response->successful()) {
                Log::error('Wialon API HTTP failure', [
                    'service' => $service,
                    'status' => $response->status(),
                ]);
                throw new \RuntimeException("Wialon API HTTP error: {$response->status()}");
            }

            $data = $response->json();

            // Handle session expiry (error 1) with single retry
            if (isset($data['error']) && $data['error'] == 1 && !$isRetry) {
                Log::info('Wialon session expired, re-authenticating');

                Cache::forget('wialon_session_' . md5($this->token));
                $this->sessionId = null;
                $this->sessionId = $this->login();

                if ($this->sessionId) {
                    return $this->request($service, $params, true); // Retry once
                }

                throw new \RuntimeException('Wialon session expired and re-login failed');
            }

            // Handle other errors
            if (isset($data['error'])) {
                $errorCode = $data['error'];
                $errorMessage = $this->getWialonErrorMessage($errorCode);

                Log::error('Wialon API error', [
                    'service' => $service,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'params' => $params,
                ]);

                throw new \RuntimeException("Wialon API error {$errorCode}: {$errorMessage}");
            }

            Log::debug('Wialon API response success', [
                'service' => $service,
            ]);

            return $data;

        } catch (\RuntimeException $e) {
            // Re-throw runtime exceptions (from our error handling above)
            throw $e;
        } catch (\Exception $e) {
            Log::error('Wialon API exception', [
                'service' => $service,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException("Wialon API exception: {$e->getMessage()}", 0, $e);
        }
    }

    /** Read Wialon's trip detector settings for a unit. */
    public function getUnitTripDetector(int $unitId): array
    {
        return $this->request('unit/get_trip_detector', ['itemId' => $unitId]);
    }

    /** Update all trip detector fields; Wialon requires a complete detector payload. */
    public function updateUnitTripDetector(int $unitId, array $settings): array
    {
        $payload = [
            'itemId' => $unitId,
            'type' => (int) ($settings['wialon_movement_type'] ?? $settings['type'] ?? 1),
            'gpsCorrection' => (bool) ($settings['wialon_gps_correction'] ?? $settings['gpsCorrection'] ?? true),
            'minSat' => (int) ($settings['wialon_min_satellites'] ?? $settings['minSat'] ?? 2),
            'minMovingSpeed' => (int) ($settings['min_moving_speed_kmh'] ?? $settings['minMovingSpeed'] ?? 1),
            'minStayTime' => (int) ($settings['min_parking_seconds'] ?? $settings['minStayTime'] ?? 300),
            'maxMessagesDistance' => (int) ($settings['max_messages_distance_m'] ?? $settings['maxMessagesDistance'] ?? 10000),
            'minTripTime' => (int) ($settings['min_trip_seconds'] ?? $settings['minTripTime'] ?? 60),
            'minTripDistance' => (int) ($settings['min_trip_distance_m'] ?? $settings['minTripDistance'] ?? 100),
        ];

        try {
            return $this->request('unit/update_trip_detector', $payload);
        } catch (\RuntimeException $e) {
            throw $this->withSettingsRightMessage($e);
        }
    }

    /** Read all report settings, including speed limit, from Wialon. */
    public function getUnitReportSettings(int $unitId): array
    {
        return $this->request('unit/get_report_settings', ['itemId' => $unitId]);
    }

    /** Update only supplied report settings; omitted/null values are left unchanged. */
    public function updateUnitReportSettings(int $unitId, array $settings): array
    {
        $mapping = [
            'speed_limit_kmh' => 'speedLimit',
            'speeding_tolerance_kmh' => 'speedingTolerance',
            'speeding_min_duration_s' => 'speedingMinDuration',
            'speeding_mode' => 'speedingMode',
        ];
        $params = [];
        foreach ($mapping as $vehicleKey => $wialonKey) {
            $value = $settings[$vehicleKey] ?? $settings[$wialonKey] ?? null;
            if ($value !== null && $value !== '') {
                $params[$wialonKey] = (int) $value;
            }
        }

        if ($params === []) {
            return [];
        }

        try {
            return $this->request('unit/update_report_settings', [
                'itemId' => $unitId,
                'params' => $params,
            ]);
        } catch (\RuntimeException $e) {
            throw $this->withSettingsRightMessage($e);
        }
    }

    /** Pull Wialon settings into the vehicle's normalized column names. */
    public function pullUnitSettings(int $unitId): array
    {
        $trip = $this->getUnitTripDetector($unitId);
        $report = $this->getUnitReportSettings($unitId);

        return [
            'wialon_movement_type' => (int) ($trip['type'] ?? 1),
            'wialon_gps_correction' => (bool) ($trip['gpsCorrection'] ?? true),
            'wialon_min_satellites' => (int) ($trip['minSat'] ?? 2),
            'min_moving_speed_kmh' => (int) ($trip['minMovingSpeed'] ?? 1),
            'min_parking_seconds' => (int) ($trip['minStayTime'] ?? 300),
            'max_messages_distance_m' => (int) ($trip['maxMessagesDistance'] ?? 10000),
            'min_trip_seconds' => (int) ($trip['minTripTime'] ?? 60),
            'min_trip_distance_m' => (int) ($trip['minTripDistance'] ?? 100),
            'speed_limit_kmh' => isset($report['speedLimit']) ? (int) $report['speedLimit'] : null,
            'speeding_tolerance_kmh' => isset($report['speedingTolerance']) ? (int) $report['speedingTolerance'] : null,
            'speeding_min_duration_s' => isset($report['speedingMinDuration']) ? (int) $report['speedingMinDuration'] : null,
            'speeding_mode' => isset($report['speedingMode']) ? (int) $report['speedingMode'] : 0,
        ];
    }

    /** Push vehicle settings to Wialon and return the normalized settings sent. */
    public function pushUnitSettings(int $unitId, array $vehicleSettings): array
    {
        $this->updateUnitTripDetector($unitId, $vehicleSettings);
        $this->updateUnitReportSettings($unitId, $vehicleSettings);

        // Wialon returns an empty object on update. Read the values back so the
        // caller can detect rights, server-side validation, or persistence issues.
        $confirmed = $this->pullUnitSettings($unitId);
        foreach ($vehicleSettings as $field => $expected) {
            if (!array_key_exists($field, $confirmed) || $expected === null || $expected === '') {
                continue;
            }

            if ($field === 'wialon_gps_correction') {
                $matches = (bool) $expected === (bool) $confirmed[$field];
            } else {
                $matches = (int) $expected === (int) $confirmed[$field];
            }

            if (!$matches) {
                throw new \RuntimeException("Wialon did not confirm saving the {$field} setting.");
            }
        }

        return $confirmed;
    }

    private function withSettingsRightMessage(\RuntimeException $exception): \RuntimeException
    {
        if (str_contains(strtolower($exception->getMessage()), 'error 7')) {
            return new \RuntimeException(
                'Wialon API error 7: the API user needs the "edit report settings" right on the unit.',
                0,
                $exception,
            );
        }

        return $exception;
    }

    /**
     * Get human-readable error message for Wialon error codes
     */
    protected function getWialonErrorMessage(int $errorCode): string
    {
        $errorMessages = [
            1 => 'Invalid session',
            2 => 'Invalid service',
            3 => 'Invalid result',
            4 => 'Invalid input',
            5 => 'Error performing request',
            6 => 'Failed to fetch creator or create unit (Check if Wialon creatorId has Create units ACL rights, if hardware type ID is valid, or if account unit creation quota is reached)',
            7 => 'Access denied or billing transaction failed (Top account or insufficient permissions)',
            8 => 'Invalid user name or password',
            9 => 'Authorization server is unavailable',
            1001 => 'No messages for selected interval',
            1002 => 'Item with such unique property already exists',
            1003 => 'Only one request is allowed at the moment',
            1004 => 'Limit of messages has been exceeded',
            1005 => 'Execution time has exceeded the limit',
            1006 => 'Exceeding the limit of attempts to enter a name',
            1011 => 'Your IP has changed or session has expired',
        ];

        return $errorMessages[$errorCode] ?? "Unknown error (code: {$errorCode})";
    }

    /**
     * Get unit (vehicle) by ID
     */
    public function getUnit(int $unitId): ?array
    {
        $data = $this->request('core/search_item', [
            'id' => $unitId,
            'flags' => 0x00000001 | 0x00000100 | 0x00000400,
        ]);

        return $data['item'] ?? null;
    }

    /**
     * Check if a unit with the given IMEI already exists in Wialon
     *
     * @param string $imei The IMEI to search for
     * @return array|null Returns the existing unit data if found, null otherwise
     */
    public function findUnitByImei(string $imei): ?array
    {
        if (empty($imei)) {
            return null;
        }

        Log::info('Searching for existing Wialon unit by IMEI', [
            'imei' => $imei,
        ]);

        try {
            $data = $this->request('core/search_items', [
                'spec' => [
                    'itemsType' => 'avl_unit',
                    'propName' => 'sys_unique_id',
                    'propValueMask' => $imei,
                    'sortType' => 'sys_name',
                ],
                'force' => 1,
                'flags' => 0x00000001 | 0x00000100 | 0x00000400, // base + config + last message
                'from' => 0,
                'to' => 0,
            ]);

            $units = $data['items'] ?? [];

            // Filter to exact match (propValueMask can return partial matches)
            $matches = array_values(array_filter($units, function($unit) use ($imei) {
                return isset($unit['uid']) && (string)$unit['uid'] === (string)$imei;
            }));

            if (count($matches) > 0) {
                $existingUnit = $matches[0];

                Log::warning('Found existing Wialon unit with same IMEI', [
                    'imei' => $imei,
                    'unit_id' => $existingUnit['id'] ?? null,
                    'unit_name' => $existingUnit['nm'] ?? null,
                    'unit_uid' => $existingUnit['uid'] ?? null,
                ]);

                return $existingUnit;
            }

            Log::info('No existing Wialon unit found with IMEI', [
                'imei' => $imei,
            ]);

            return null;

        } catch (\Throwable $e) {
            Log::error('Error searching for Wialon unit by IMEI', [
                'imei' => $imei,
                'error' => $e->getMessage(),
            ]);

            // Return null instead of throwing - we'll proceed with creation
            // and let Wialon's own duplicate check handle it
            return null;
        }
    }

    /**
     * Check if a unit with the given name (fleet number) already exists in Wialon
     *
     * @param string $name The unit name (fleet number) to search for
     * @return array|null Returns the existing unit data if found, null otherwise
     */
    public function findUnitByName(string $name): ?array
    {
        if (empty($name)) {
            return null;
        }

        Log::info('Searching for existing Wialon unit by name', [
            'name' => $name,
        ]);

        try {
            $data = $this->request('core/search_items', [
                'spec' => [
                    'itemsType' => 'avl_unit',
                    'propName' => 'sys_name',
                    'propValueMask' => $name,
                    'sortType' => 'sys_name',
                ],
                'force' => 1,
                'flags' => 0x00000001, // base info only
                'from' => 0,
                'to' => 0,
            ]);

            $units = $data['items'] ?? [];

            // Filter to exact match (case-insensitive)
            $matches = array_values(array_filter($units, function($unit) use ($name) {
                $unitName = $unit['nm'] ?? '';
                return strcasecmp($unitName, $name) === 0;
            }));

            if (count($matches) > 0) {
                $existingUnit = $matches[0];

                Log::warning('Found existing Wialon unit with same name', [
                    'search_name' => $name,
                    'unit_id' => $existingUnit['id'] ?? null,
                    'unit_name' => $existingUnit['nm'] ?? null,
                ]);

                return $existingUnit;
            }

            Log::info('No existing Wialon unit found with name', [
                'name' => $name,
            ]);

            return null;

        } catch (\Throwable $e) {
            Log::error('Error searching for Wialon unit by name', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            // Return null instead of throwing
            return null;
        }
    }

    /**
     * Verify creator user from login response
     * The creator is the currently logged-in user from token/login
     */
    protected function verifyCreator(): int
    {
        if (!$this->creatorId) {
            throw new \InvalidArgumentException('WIALON_CREATOR_ID not configured');
        }

        Log::info('Verifying Wialon creator from login', [
            'configured_creator_id' => $this->creatorId,
        ]);

        Cache::forget('wialon_session_' . md5($this->token));
        $this->sessionId = null;

        try {
            $response = Http::timeout(30)->get($this->host . '/wialon/ajax.html', [
                'svc' => 'token/login',
                'params' => json_encode([
                    'token' => $this->token
                ])
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Wialon login failed during creator verification');
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new \RuntimeException('Wialon login error: ' . $this->getWialonErrorMessage($data['error']));
            }

            if (!isset($data['eid']) || !isset($data['user'])) {
                throw new \RuntimeException('Wialon login response missing user data');
            }

            // Cache the new session
            $this->sessionId = $data['eid'];
            Cache::put('wialon_session_' . md5($this->token), $this->sessionId, 600);

            $actualUserId = (int) $data['user']['id'];
            $actualUserName = $data['user']['nm'] ?? 'unknown';

            Log::info('Wialon creator verified from login', [
                'creator_id' => $actualUserId,
                'creator_name' => $actualUserName,
                'configured_creator_id' => $this->creatorId,
            ]);

            // Warning if mismatch but use actual user ID
            if ($actualUserId !== $this->creatorId) {
                Log::warning('Creator ID mismatch - using actual logged-in user', [
                    'configured' => $this->creatorId,
                    'actual' => $actualUserId,
                ]);
            }

            // Return the actual logged-in user ID
            return $actualUserId;

        } catch (\Exception $e) {
            Log::error('Creator verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to verify Wialon creator: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Verify hardware type exists in Wialon and return its integer system ID
     */
    public function verifyHardwareType(int|string $hwTypeIdOrName): int
    {
        Log::info('Verifying Wialon hardware type', [
            'hw_type' => $hwTypeIdOrName,
        ]);

        if (is_numeric($hwTypeIdOrName) && (int) $hwTypeIdOrName > 0) {
            $hwTypeId = (int) $hwTypeIdOrName;
            $data = $this->request('core/get_hw_types', [
                'filterType' => 'id',
                'filterValue' => [$hwTypeId],
                'includeType' => true,
            ]);

            if (is_array($data) && !empty($data)) {
                foreach ($data as $item) {
                    if (is_array($item) && isset($item['id']) && (int) $item['id'] === $hwTypeId) {
                        Log::info('Wialon hardware type verified by ID', [
                            'hw_type_id' => $hwTypeId,
                            'hw_type_name' => $item['name'] ?? 'unknown',
                            'tp' => $item['tp'] ?? null,
                        ]);
                        return $hwTypeId;
                    }
                }
            }
        }

        // If numeric ID lookup failed or a device name string was passed, search by name
        $searchName = (string) $hwTypeIdOrName;
        $dataByName = $this->request('core/get_hw_types', [
            'filterType' => 'name',
            'filterValue' => [$searchName],
            'includeType' => true,
        ]);

        if (is_array($dataByName) && !empty($dataByName)) {
            foreach ($dataByName as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $resolvedId = (int) $item['id'];
                    Log::info('Wialon hardware type verified by name', [
                        'search_name' => $searchName,
                        'resolved_hw_type_id' => $resolvedId,
                        'hw_type_name' => $item['name'] ?? $searchName,
                        'tp' => $item['tp'] ?? null,
                    ]);
                    return $resolvedId;
                }
            }
        }

        throw new \RuntimeException("Hardware type '{$hwTypeIdOrName}' not found in Wialon");
    }

    /**
     * Inspect accessible resources, ACL permissions, and validate context before unit creation.
     * Fails early with a clear, descriptive exception instead of returning generic Wialon error 6.
     *
     * @throws \RuntimeException
     * @return array Pre-validated diagnostic context
     */
    public function validateWialonUnitCreationContext(int $hwTypeId): array
    {
        Log::info('Validating Wialon unit creation context');

        // 1. Login to inspect token scope and user features
        $loginRes = Http::timeout(30)->get($this->host . '/wialon/ajax.html', [
            'svc' => 'token/login',
            'params' => json_encode([
                'token' => $this->token
            ])
        ]);

        if (!$loginRes->successful()) {
            throw new \RuntimeException('Wialon login HTTP failure during context validation');
        }

        $loginData = $loginRes->json();
        if (isset($loginData['error'])) {
            throw new \RuntimeException('Wialon login error during context validation: ' . $this->getWialonErrorMessage($loginData['error']));
        }

        if (!isset($loginData['eid']) || !isset($loginData['user'])) {
            throw new \RuntimeException('Wialon login response missing user data');
        }

        $user = $loginData['user'];
        $authUserId = (int) ($user['id'] ?? 0);
        $authUserName = $user['nm'] ?? 'unknown';
        $userBact = $user['bact'] ?? null;
        $userCrt = $user['crt'] ?? null;

        // Inspect token scope flags
        $tokenInfo = $loginData['token'] ?? [];
        $tokenFlags = $tokenInfo['fl'] ?? null;

        // Check if token has read-only restriction (fl = 256 / 0x100)
        if ($tokenFlags === 256) {
            Log::error('Wialon token validation failed: read-only token scope', [
                'user_id' => $authUserId,
                'user_name' => $authUserName,
                'token_flags' => $tokenFlags,
            ]);

            throw new \RuntimeException(
                "Authenticated Wialon user '{$authUserName}' (ID {$authUserId}) does not have Create Units permission for the selected resource. " .
                "The WIALON_TOKEN in .env has token scope fl=256 (Online Tracking Read-Only). " .
                "Please generate a Full Access API Token (access_type=0xffff) from Wialon and update WIALON_TOKEN in .env."
            );
        }

        // Verify Hardware Type
        $verifiedHwId = $this->verifyHardwareType($hwTypeId);

        // Fetch HW details for logging
        $hwTypesData = Http::timeout(30)->get($this->host . '/wialon/ajax.html', [
            'svc' => 'core/get_hw_types',
            'sid' => $loginData['eid'],
            'params' => json_encode([
                'filterType' => 'id',
                'filterValue' => [$verifiedHwId],
                'includeType' => true,
            ])
        ])->json();

        $hwName = 'unknown';
        if (is_array($hwTypesData) && !empty($hwTypesData)) {
            foreach ($hwTypesData as $hwItem) {
                if (isset($hwItem['id']) && (int)$hwItem['id'] === $verifiedHwId) {
                    $hwName = $hwItem['name'] ?? 'unknown';
                    break;
                }
            }
        }

        // Determine creator ID to use
        $targetCreatorId = $authUserId;

        // Sanitized diagnostic logging before core/create_unit
        Log::info('Wialon unit creation context pre-validation passed', [
            'authenticated_wialon_user_id' => $authUserId,
            'authenticated_user_name' => $authUserName,
            'selected_creator_id' => $targetCreatorId,
            'account_id' => $userBact,
            'parent_creator_id' => $userCrt,
            'acl_permissions' => $user['uacl'] ?? null,
            'hardware_type_id' => $verifiedHwId,
            'hardware_type_name' => $hwName,
            'token_scope_flags' => $tokenFlags ?? 'full_access',
        ]);

        return [
            'auth_user_id' => $authUserId,
            'auth_user_name' => $authUserName,
            'creator_id' => $targetCreatorId,
            'account_id' => $userBact,
            'hw_type_id' => $verifiedHwId,
            'hw_type_name' => $hwName,
            'sid' => $loginData['eid'],
        ];
    }


    /**
     * Get list of standard available Wialon Vehicle Types
     */
    public function getWialonVehicleTypes(): array
    {
        return [
            ['id' => 'truck', 'name' => 'Truck / Cargo'],
            ['id' => 'car', 'name' => 'Car / Passenger'],
            ['id' => 'bus', 'name' => 'Bus'],
            ['id' => 'minibus', 'name' => 'Minibus'],
            ['id' => 'van', 'name' => 'Van'],
            ['id' => 'pickup', 'name' => 'Pickup'],
            ['id' => 'motorcycle', 'name' => 'Motorcycle'],
            ['id' => 'tractor', 'name' => 'Tractor'],
            ['id' => 'special', 'name' => 'Special Machinery'],
            ['id' => 'trailer', 'name' => 'Trailer'],
            ['id' => 'boat', 'name' => 'Boat / Vessel'],
            ['id' => 'aircraft', 'name' => 'Aircraft'],
        ];
    }

    /**
     * Map our portal vehicle type slug to Wialon's profile field "vehicle_class" key.
     * Wialon UI "Unit type" reads vehicle_class (file/type_library keys), NOT vehicle_type.
     *
     * @see https://sdk.wialon.com/wiki/en/local/remoteapi2404/apiref/file/type_library
     */
    public function mapToWialonVehicleClass(?string $vehicleType): ?string
    {
        if ($vehicleType === null || trim($vehicleType) === '') {
            return null;
        }

        $key = strtolower(trim($vehicleType));

        $map = [
            'truck' => 'truck',
            'car' => 'passenger_car',
            'passenger_car' => 'passenger_car',
            'bus' => 'bus',
            'minibus' => 'minibus',
            'van' => 'van',
            'pickup' => 'pickup',
            'motorcycle' => 'motorcycle',
            'tractor' => 'tractor',
            'special' => 'other_special',
            'trailer' => 'trailers_semi',
            'boat' => 'water_transport',
            'aircraft' => 'air_transport',
            'heavy_truck' => 'heavy_truck',
            'dump_truck' => 'dump_truck',
        ];

        return $map[$key] ?? $key;
    }

    /**
     * Set a Wialon unit profile field (visible in the Wialon unit card).
     */
    protected function updateUnitProfileField(int $unitId, string $fieldName, string $value): void
    {
        $this->request('item/update_profile_field', [
            'itemId' => $unitId,
            'n' => $fieldName,
            'v' => $value,
        ]);
    }


    protected function syncUnitPhoneNumber(int $unitId, string $phoneNumber): void
    {
        // Send the raw phone number as-is. json_encode() + the HTTP client already
        // handle URL-encoding correctly — manually replacing '+' with '%2B' here
        // caused double-encoding (the '%' itself got re-encoded), so Wialon was
        // actually receiving the literal text "%2B..." instead of "+...".
        try {
            $this->request('unit/update_phone', [
                'itemId' => $unitId,
                'phoneNumber' => $phoneNumber,
            ]);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Wialon API error 1002')) {
                // Number already set to this value — not a real failure.
                Log::info('Wialon phone number already set to this value, skipping', [
                    'unit_id' => $unitId,
                    'phone_number' => $phoneNumber,
                ]);
            } else {
                // Any other error is real — retry once, then surface it.
                Log::warning('Wialon unit/update_phone failed, retrying once', [
                    'unit_id' => $unitId,
                    'phone_number' => $phoneNumber,
                    'error' => $e->getMessage(),
                ]);

                $this->request('unit/update_phone', [
                    'itemId' => $unitId,
                    'phoneNumber' => $phoneNumber,
                ]);
            }
        }

        // Backup: Wialon profile field
        try {
            if ($phoneNumber !== '') {
                $this->updateUnitProfileField($unitId, 'phone_number', $phoneNumber);
            }
        } catch (\Throwable $e) {
            Log::warning('Non-fatal: Wialon profile field phone_number update skipped', [
                'unit_id' => $unitId,
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync unit type to Wialon profile field vehicle_class (shown as "Unit type" in Wialon UI).
     *
     * Bug fixed: mapToWialonVehicleClass() previously fell back to returning
     * the raw, unmapped input string when it didn't recognize $vehicleType
     * (`$map[$key] ?? $key`). Wialon's "Unit type" dropdown only recognizes a
     * fixed list of keys (truck, passenger_car, bus, ...) from its own type
     * library — sending anything outside that list is silently accepted by
     * item/update_profile_field (no error) but shows as a BLANK dropdown in
     * the Wialon UI, exactly like your screenshot. That's the "sometimes unit
     * type doesn't store" bug: it only reproduces for whatever vehicle-type
     * values don't exactly match one of the known keys.
     */
    protected function syncUnitVehicleClass(int $unitId, ?string $vehicleType): void
    {
        if ($vehicleType === null || trim($vehicleType) === '') {
            return;
        }

        $vehicleClass = $this->mapToWialonVehicleClass($vehicleType);

        if ($vehicleClass === null || !in_array($vehicleClass, self::VALID_WIALON_VEHICLE_CLASSES, true)) {
            // Don't send a key Wialon won't recognize — it would silently show
            // as blank in the UI instead of failing loudly. Log it so it's visible.
            Log::warning('Wialon vehicle_class skipped: unrecognized vehicle type', [
                'unit_id' => $unitId,
                'vehicle_type_input' => $vehicleType,
                'attempted_vehicle_class' => $vehicleClass,
            ]);
            return;
        }

        try {
            $this->updateUnitProfileField($unitId, 'vehicle_class', $vehicleClass);
        } catch (\RuntimeException $e) {
            Log::warning('Wialon vehicle_class update failed, retrying once', [
                'unit_id' => $unitId,
                'vehicle_class' => $vehicleClass,
                'error' => $e->getMessage(),
            ]);

            $this->updateUnitProfileField($unitId, 'vehicle_class', $vehicleClass);
        }

        Log::info('Wialon unit vehicle_class profile field updated', [
            'unit_id' => $unitId,
            'vehicle_type_input' => $vehicleType,
            'vehicle_class' => $vehicleClass,
        ]);
    }

    public function provisionUnit(string $unitName, int $hwTypeId, string $imei, ?string $vehicleType = null, ?string $phoneNumber = null): array
    {
        if (config('services.wialon.test_mode')) {
            Log::warning('Wialon TEST MODE enabled - NOT creating real unit', [
                'unit_name' => $unitName,
                'hw_type_id' => $hwTypeId,
                'imei' => $imei,
                'vehicle_type' => $vehicleType,
                'phone_number' => $phoneNumber,
            ]);

            throw new \RuntimeException(
                'Wialon TEST MODE is enabled. Set WIALON_TEST_MODE=false in .env for production use. ' .
                'Test mode prevents real unit creation and should NOT be used in production.'
            );
        }

        Log::info('Wialon unit provisioning started', [
            'unit_name' => $unitName,
            'hw_type_id' => $hwTypeId,
            'imei' => $imei,
            'vehicle_type' => $vehicleType,
            'phone_number' => $phoneNumber,
        ]);

        // VALIDATION: Check if unit with this IMEI already exists in Wialon
        $existingUnit = $this->findUnitByImei($imei);
        if ($existingUnit !== null) {
            $existingUnitId = $existingUnit['id'] ?? 'unknown';
            $existingUnitName = $existingUnit['nm'] ?? 'unknown';

            Log::error('Cannot create unit: IMEI already exists in Wialon', [
                'requested_imei' => $imei,
                'existing_unit_id' => $existingUnitId,
                'existing_unit_name' => $existingUnitName,
            ]);

            throw new \InvalidArgumentException(
                "GPS IMEI {$imei} already exists in Wialon tracking system " .
                "(Unit ID: {$existingUnitId}, Name: {$existingUnitName}). " .
                "Each vehicle must have a unique IMEI."
            );
        }

        // VALIDATION: Check if unit with this name (fleet number) already exists in Wialon
        $existingUnitByName = $this->findUnitByName($unitName);
        if ($existingUnitByName !== null) {
            $existingNameUnitId = $existingUnitByName['id'] ?? 'unknown';

            Log::error('Cannot create unit: Fleet Number already exists in Wialon', [
                'requested_name' => $unitName,
                'existing_unit_id' => $existingNameUnitId,
            ]);

            throw new \InvalidArgumentException(
                "Fleet Number {$unitName} already exists in Wialon tracking system " .
                "(Unit ID: {$existingNameUnitId}). " .
                "Each vehicle must have a unique fleet number."
            );
        }

        // STEP 1: Pre-validate creation context & ACL permissions
        $context = $this->validateWialonUnitCreationContext($hwTypeId);
        $verifiedCreatorId = $context['creator_id'];
        $verifiedHwTypeId = $context['hw_type_id'];

        // STEP 2: Create unit (without IMEI initially, will be set in next step)
        Log::info('Creating Wialon unit', [
            'creator_id' => $verifiedCreatorId,
            'unit_name' => $unitName,
            'hw_type_id' => $verifiedHwTypeId,
            'hw_type_name' => $context['hw_type_name'],
            'account_id' => $context['account_id'],
            'vehicle_type' => $vehicleType,
            'phone_number' => $phoneNumber,
        ]);

        $createParams = [
            'creatorId' => $verifiedCreatorId,
            'name' => $unitName,
            'hwTypeId' => $verifiedHwTypeId,
            'dataFlags' => 1,
        ];

        // NOTE: core/create_unit does NOT accept a phoneNumber parameter — it
        // only takes creatorId, name, hwTypeId, dataFlags (per Wialon's own
        // API reference). It was silently ignored before; phone is correctly
        // set afterward in STEP 5 via unit/update_phone, which is the real API for it.

        $createData = $this->request('core/create_unit', $createParams);

        // STEP 3: Extract real unit ID
        if (!isset($createData['item']) || !isset($createData['item']['id'])) {
            Log::error('Wialon unit creation failed: invalid response structure', [
                'response' => $createData,
            ]);
            throw new \RuntimeException('Wialon unit creation failed: no unit ID in response');
        }

        $unitId = (int) $createData['item']['id'];
        $unitName = $createData['item']['nm'] ?? $unitName;

        if ($unitId <= 0) {
            throw new \RuntimeException('Wialon returned invalid unit ID: ' . $unitId);
        }

        Log::info('Wialon unit created successfully', [
            'unit_id' => $unitId,
            'unit_name' => $unitName,
        ]);

        // STEP 4: Assign IMEI to unit
        Log::info('Assigning IMEI to Wialon unit', [
            'unit_id' => $unitId,
            'imei' => $imei,
        ]);

        try {
            $updateParams = [
                'itemId' => $unitId,
                'deviceTypeId' => $verifiedHwTypeId,
                'uniqueId' => $imei,
            ];

            $updateData = $this->request('unit/update_device_type', $updateParams);

            Log::info('IMEI assigned to Wialon unit successfully', [
                'unit_id' => $unitId,
                'imei' => $imei,
            ]);

        } catch (\RuntimeException $e) {
            // IMEI assignment failed - this could mean duplicate IMEI
            // We should delete the unit we just created and fail
            Log::error('Failed to assign IMEI to Wialon unit - attempting cleanup', [
                'unit_id' => $unitId,
                'imei' => $imei,
                'error' => $e->getMessage(),
            ]);

            // Try to clean up the unit we just created
            try {
                $this->deleteUnit($unitId);
                Log::info('Cleaned up Wialon unit after IMEI assignment failure', [
                    'unit_id' => $unitId,
                ]);
            } catch (\Throwable $cleanupError) {
                Log::error('Failed to clean up Wialon unit after IMEI assignment failure', [
                    'unit_id' => $unitId,
                    'cleanup_error' => $cleanupError->getMessage(),
                ]);
            }

            // Check if error is about duplicate IMEI - throw as validation error
            if (str_contains($e->getMessage(), 'error 1002') || str_contains($e->getMessage(), 'already exists')) {
                throw new \InvalidArgumentException(
                    "GPS IMEI {$imei} is already in use by another vehicle in Wialon tracking system. " .
                    "Each vehicle must have a unique IMEI."
                );
            }

            throw new \RuntimeException(
                "Failed to assign IMEI to GPS tracking unit: {$e->getMessage()}"
            );
        }

        // STEP 5: Phone number — unit/update_phone (same service used successfully on edit)
        if (!empty($phoneNumber)) {
            try {
                $this->syncUnitPhoneNumber($unitId, $phoneNumber);

                Log::info('Wialon unit phone number updated', [
                    'unit_id' => $unitId,
                    'phone_number' => $phoneNumber,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Non-fatal: Wialon unit phone number update skipped', [
                    'unit_id' => $unitId,
                    'phone_number' => $phoneNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // STEP 6: Unit type — Wialon UI reads profile field "vehicle_class", not custom fields
        if (!empty($vehicleType)) {
            try {
                $this->syncUnitVehicleClass($unitId, $vehicleType);
            } catch (\Throwable $e) {
                Log::warning('Non-fatal: Wialon unit vehicle_class update skipped', [
                    'unit_id' => $unitId,
                    'vehicle_type' => $vehicleType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'unit_id' => $unitId,
            'name' => $unitName,
            'imei' => $imei,
            'vehicle_type' => $vehicleType,
            'phone_number' => $phoneNumber,
        ];
    }

    /**
     * Update the device type and IMEI of an existing Wialon unit.
     */
    public function updateDeviceType(int $unitId, int $hwTypeId, string $imei): array
    {
        Log::info('Updating Wialon unit device type and IMEI', [
            'unit_id' => $unitId,
            'hw_type_id' => $hwTypeId,
            'imei' => $imei,
        ]);

        // Verify hardware type before updating
        $this->verifyHardwareType($hwTypeId);

        // VALIDATION: Check if another unit with this IMEI already exists in Wialon
        $existingUnit = $this->findUnitByImei($imei);
        if ($existingUnit !== null) {
            $existingUnitId = (int)($existingUnit['id'] ?? 0);

            // If the existing unit is the same as the one we're updating, it's OK
            if ($existingUnitId !== $unitId) {
                $existingUnitName = $existingUnit['nm'] ?? 'unknown';

                Log::error('Cannot update unit: IMEI already exists in another Wialon unit', [
                    'current_unit_id' => $unitId,
                    'requested_imei' => $imei,
                    'existing_unit_id' => $existingUnitId,
                    'existing_unit_name' => $existingUnitName,
                ]);

                throw new \InvalidArgumentException(
                    "Cannot update IMEI to {$imei}: This IMEI is already in use by another vehicle " .
                    "in Wialon (Unit ID: {$existingUnitId}, Name: {$existingUnitName}). " .
                    "Each vehicle must have a unique IMEI."
                );
            }
        }

        // Update device type and IMEI in one call
        $params = [
            'itemId' => $unitId,
            'deviceTypeId' => $hwTypeId,
            'uniqueId' => $imei,
        ];

        $data = $this->request('unit/update_device_type', $params);

        Log::info('Wialon unit device type and IMEI updated successfully', [
            'unit_id' => $unitId,
            'hw_type_id' => $hwTypeId,
            'imei' => $imei,
        ]);

        return [
            'success' => true,
            'unit_id' => $unitId,
            'hw_type_id' => $hwTypeId,
            'imei' => $imei,
        ];
    }

    /**
     * Update unit name (Fleet No) in Wialon unit.
     */
    public function updateUnitName(int $unitId, string $name): array
    {
        Log::info('Updating Wialon unit name', [
            'unit_id' => $unitId,
            'name' => $name,
        ]);

        try {
            $data = $this->request('item/update_name', [
                'itemId' => $unitId,
                'name' => $name,
            ]);

            Log::info('Wialon unit name updated successfully', [
                'unit_id' => $unitId,
                'name' => $name,
            ]);

            return [
                'success' => true,
                'unit_id' => $unitId,
                'name' => $name,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to update Wialon unit name', [
                'unit_id' => $unitId,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update unit type (vehicle_class profile field) on an existing Wialon unit.
     */
    public function updateVehicleType(int $unitId, ?string $vehicleType): array
    {
        if (empty($vehicleType)) {
            return ['success' => true, 'unit_id' => $unitId, 'vehicle_type' => null];
        }

        Log::info('Updating Wialon unit vehicle_class', [
            'unit_id' => $unitId,
            'vehicle_type' => $vehicleType,
            'vehicle_class' => $this->mapToWialonVehicleClass($vehicleType),
        ]);

        try {
            $this->syncUnitVehicleClass($unitId, $vehicleType);

            return [
                'success' => true,
                'unit_id' => $unitId,
                'vehicle_type' => $vehicleType,
                'vehicle_class' => $this->mapToWialonVehicleClass($vehicleType),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to update Wialon vehicle_class', [
                'unit_id' => $unitId,
                'vehicle_type' => $vehicleType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Alias for updateVehicleType for backward compatibility.
     */
    public function updateVehicleTypeCustomField(int $unitId, ?string $vehicleType): array
    {
        return $this->updateVehicleType($unitId, $vehicleType);
    }

    /**
     * Update phone number in an existing Wialon unit.
     */
    public function updateUnitPhoneNumber(int $unitId, ?string $phoneNumber): array
    {
        $phone = $phoneNumber ?? '';

        Log::info('Updating Wialon unit phone number', [
            'unit_id' => $unitId,
            'phone_number' => $phone,
        ]);

        try {
            $this->syncUnitPhoneNumber($unitId, $phone);

            Log::info('Wialon unit phone number updated successfully', [
                'unit_id' => $unitId,
                'phone_number' => $phone,
            ]);

            return [
                'success' => true,
                'unit_id' => $unitId,
                'phone_number' => $phone,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to update Wialon unit phone number', [
                'unit_id' => $unitId,
                'phone_number' => $phone,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a unit from Wialon.
     *
     * @param int $unitId Wialon unit ID to delete
     * @return array Result of deletion
     * @throws \RuntimeException if deletion fails
     */
    public function deleteUnit(int $unitId): array
    {
        Log::info('Deleting Wialon unit', [
            'unit_id' => $unitId,
        ]);

        try {
            // First, verify the unit exists
            $unitData = $this->request('core/search_item', [
                'id' => $unitId,
                'flags' => 0x00000001, // base info only
            ]);

            if (!isset($unitData['item'])) {
                Log::warning('Wialon unit not found for deletion', [
                    'unit_id' => $unitId,
                ]);

                // Unit doesn't exist, consider it already deleted
                return [
                    'success' => true,
                    'unit_id' => $unitId,
                    'message' => 'Unit not found in Wialon (may already be deleted)',
                ];
            }

            $unitName = $unitData['item']['nm'] ?? 'Unknown';

            // Delete the unit using item/delete_item
            $deleteResult = $this->request('item/delete_item', [
                'itemId' => $unitId,
            ]);

            Log::info('Wialon unit deleted successfully', [
                'unit_id' => $unitId,
                'unit_name' => $unitName,
            ]);

            return [
                'success' => true,
                'unit_id' => $unitId,
                'unit_name' => $unitName,
                'message' => 'Unit deleted from Wialon successfully',
            ];

        } catch (\Throwable $e) {
            Log::error('Failed to delete Wialon unit', [
                'unit_id' => $unitId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw new \RuntimeException(
                "Failed to delete Wialon unit {$unitId}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get latest position of a unit
     */
    public function getUnitPosition(int $unitId): ?array
    {
        $data = $this->request('core/search_item', [
            'id' => $unitId,
            'flags' => 0x00000001 | 0x00000400, // base + last message/location
        ]);

        if (!isset($data['item'])) {
            return null;
        }

        $unit = $data['item'];

        // Wialon stores position in 'pos' or 'lmsg' (last message)
        $pos = $unit['pos'] ?? $unit['lmsg']['pos'] ?? null;

        if (!$pos) {
            return null;
        }

        return [
            'latitude' => $pos['y'] ?? null,  // Wialon: y = latitude
            'longitude' => $pos['x'] ?? null, // Wialon: x = longitude
            'timestamp' => $pos['t'] ?? null,
            'speed' => $pos['s'] ?? null,
            'course' => $pos['c'] ?? null,
        ];
    }

    /**
     * Get all routes from Wialon account
     */
    public function getRoutes(): array
    {
        // Routes are Wialon avl_route items. Resource flags do not include route
        // data, so searching avl_resource cannot reliably return route/rides.
        $data = $this->request('core/search_items', [
            'spec' => [
                'itemsType' => 'avl_route',
                'propName' => 'sys_name',
                'propValueMask' => '*',
                'sortType' => 'sys_name'
            ],
            'force' => 1,
            // base + configuration + checkpoints + schedules + rides
            'flags' => 0x00000001 | 0x00000100 | 0x00000200 | 0x00000400 | 0x00000800,
            'from' => 0,
            'to' => 0
        ]);

        Log::info('Wialon routes API request', [
            'service' => 'core/search_items',
            'params' => [
                'itemsType' => 'avl_route',
                'flags' => 0x00000001 | 0x00000100 | 0x00000200 | 0x00000400 | 0x00000800,
            ],
        ]);
        Log::info('Wialon routes API response', [
            'success' => isset($data['items']),
            'item_count' => isset($data['items']) ? count($data['items']) : 0
        ]);

        if (empty($data['items'])) {
            Log::warning('No Wialon routes found or route access is unavailable');
            return [];
        }

        return array_values(array_map(function (array $route): array {
            // `id` is already present in avl_route search results. Keep the
            // legacy p key for installations that still return it.
            $route['id'] = $route['id'] ?? null;

            return $route;
        }, $data['items']));
    }

    /**
     * Load a single route with all fields required by the Waybill map.
     * A per-item lookup avoids relying on a session search result that may have
     * been loaded with a smaller flag set elsewhere in Wialon.
     */
    private function getRouteDetails(int $routeId): ?array
    {
        $flags = 0x00000001 | 0x00000100 | 0x00000200 | 0x00000400 | 0x00000800;
        $params = ['id' => $routeId, 'flags' => $flags];

        Log::info('Wialon route detail API request', [
            'service' => 'core/search_item',
            'params' => $params,
        ]);

        $data = $this->request('core/search_item', $params);
        $route = $data['item'] ?? null;

        Log::debug('Wialon route raw response (sanitized)', [
            'service' => 'core/search_item',
            'route_id' => $routeId,
            'response' => $this->sanitizeForLog($route),
        ]);

        return is_array($route) ? $route : null;
    }

    /**
     * Get route assigned to a specific unit via unit's custom fields or schedules
     * Alternative method when resource access is not available
     */
    public function getRouteForUnitAlternative(int $unitId): ?array
    {
        // Try to get unit with schedule data
        $unitData = $this->request('core/search_item', [
            'id' => $unitId,
            'flags' => 0x00000001 | 0x00000100 | 0x00000040 | 0x00040000 // base + lastMessage + position + custom fields
        ]);

        if (!isset($unitData['item'])) {
            Log::warning('Unit not found', ['unit_id' => $unitId]);
            return null;
        }

        $unit = $unitData['item'];

        Log::info('Unit data retrieved', [
            'name' => $unit['nm'] ?? 'Unknown',
            'id' => $unit['id'] ?? 'Unknown',
            'keys' => array_keys($unit)
        ]);

        // Check if unit has custom fields with route information
        if (isset($unit['flds']) && is_array($unit['flds'])) {
            foreach ($unit['flds'] as $field) {
                if (isset($field['n']) && stripos($field['n'], 'route') !== false) {
                    Log::info('Found route field in unit', [
                        'field_name' => $field['n'],
                        'field_value' => $field['v'] ?? 'N/A'
                    ]);
                }
            }
        }

        // For now, return a mock route based on checkpoint name
        // This is a fallback until resource permissions are granted
        return [
            'id' => 'manual_' . $unitId,
            'n' => 'Route for ' . ($unit['nm'] ?? 'Unit'),
            'p' => [] // No checkpoints available without resource access
        ];
    }
    public function getRouteForUnit(int $unitId): ?array
    {
        // Get all routes from all resources
        $routes = $this->getRoutes();

        if (empty($routes)) {
            Log::info('No Wialon routes found in any resource');
            return null;
        }

        Log::info('Found Wialon routes', ['count' => count($routes), 'unit_id' => $unitId]);

        // 1. First search for a route explicitly assigned to this unit
        foreach ($routes as $route) {
            $routeId = (int) ($route['id'] ?? 0);
            if ($routeId === 0) {
                continue;
            }

            $route = $this->getRouteDetails($routeId) ?? $route;
            $routeName = $route['nm'] ?? $route['n'] ?? 'Unnamed';
            $configurationUnits = array_map('strval', $route['rcfg']['units'] ?? $route['un'] ?? []);
            $schedules = is_array($route['rs'] ?? null) ? $route['rs'] : [];
            $rides = is_array($route['rr'] ?? null) ? $route['rr'] : [];
            $routePoints = is_array($route['rpts'] ?? null) ? $route['rpts'] : [];
            $matchingRptUnits = array_values(array_map(
                'intval',
                array_filter(
                    array_column($routePoints, 'u'),
                    fn ($pointUnitId) => (int) $pointUnitId === $unitId,
                ),
            ));
            $geographicCheckpointCount = count(array_filter($routePoints, fn (array $point) =>
                isset($point['x'], $point['y'])
                && (float) $point['x'] !== 0.0
                && (float) $point['y'] !== 0.0,
            ));

            $scheduleAssignments = array_map(fn (array $schedule) => [
                'id' => $schedule['id'] ?? null,
                'name' => $schedule['n'] ?? null,
                'units' => array_map('strval', $schedule['cfg']['units'] ?? []),
            ], $schedules);
            Log::debug('Wialon route assignment comparison', [
                'route_id' => $routeId,
                'route_name' => $routeName,
                'wialon_unit_id' => $unitId,
                'matching_rpt_units' => $matchingRptUnits,
                'route_assigned' => $matchingRptUnits !== [],
                'geographic_checkpoint_count' => $geographicCheckpointCount,
                'rcfg_units' => $configurationUnits,
                'schedules' => $scheduleAssignments,
                'rides' => array_map(fn (array $ride) => [
                    'id' => $ride['id'] ?? null,
                    'u' => $ride['u'] ?? null,
                    'cu' => $ride['cu'] ?? null,
                ], $rides),
            ]);

            if ($matchingRptUnits !== []) {
                Log::info('Wialon route assignment match', [
                    'route_id' => $routeId,
                    'route_name' => $routeName,
                    'wialon_unit_id' => $unitId,
                    'matching_rpt_units' => $matchingRptUnits,
                    'route_assigned' => true,
                    'geographic_checkpoint_count' => $geographicCheckpointCount,
                    'source' => 'rpts[].u',
                ]);

                return $route;
            }

            if (in_array((string) $unitId, $configurationUnits, true)) {
                return $route;
            }

            foreach ($rides as $ride) {
                if ((string) ($ride['u'] ?? '') === (string) $unitId
                    || in_array($unitId, array_map('intval', $ride['cu'] ?? []), true)) {
                    return $route;
                }
            }

            foreach ($scheduleAssignments as $schedule) {
                if (in_array((string) $unitId, $schedule['units'], true)) {
                    Log::info('Wialon route assignment match', [
                        'route_id' => $routeId,
                        'schedule_id' => $schedule['id'],
                        'assigned_unit_id' => $unitId,
                        'matched' => true,
                        'source' => 'rs.*.cfg.units',
                    ]);

                    return $route;
                }
            }
        }

        // 2. If no explicit unit assignment matches, fallback to the latest route available in Wialon!
        usort($routes, function ($a, $b) {
            return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
        });

        $latestRoute = $routes[0];
        $latestRouteId = (int) ($latestRoute['id'] ?? 0);
        if ($latestRouteId > 0) {
            $latestRoute = $this->getRouteDetails($latestRouteId) ?? $latestRoute;
        }

        Log::info('Using latest Wialon route as fallback', [
            'unit_id' => $unitId,
            'fallback_route_id' => $latestRouteId,
            'fallback_route_name' => $latestRoute['nm'] ?? $latestRoute['n'] ?? 'Unnamed',
        ]);

        return $latestRoute;
    }

    /**
     * Get detailed route information including checkpoints
     * @deprecated Use getRoutes() instead - routes are already loaded with full details
     */
    public function getRouteDetail(int $routeId): ?array
    {
        $routes = $this->getRoutes();

        foreach ($routes as $route) {
            if ($route['id'] == $routeId) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Parse route checkpoints and calculate distance & duration.
     *
     * IMPORTANT (origin/destination resolution):
     * Wialon's `rpts` array is NOT guaranteed to be sorted in travel order —
     * it is very commonly returned sorted by point creation ID instead. That
     * means checkpoints[0]/checkpoints[last] are NOT a reliable stand-in for
     * the actual origin/destination of the route (e.g. an intermediate stop
     * like "Mandi Moor" can end up first in the array instead of the real
     * origin "Islamabad").
     *
     * The route's own name/description (e.g. "Islamabad - Rawalpindi") is the
     * authoritative source for the origin/destination *labels*. The rpts
     * array is only used for (a) drawing the path/line, and (b) supplying
     * coordinates for the origin/destination markers (best-effort, using the
     * first/last geographic points as the closest approximation available).
     * Every checkpoint keeps its own real name (e.g. "Mandi Moor",
     * "Faizabad") — those are never overwritten by the origin/destination
     * labels.
     */
    public function parseRouteData(array $route): array
    {
        $checkpoints = [];
        $routeCoordinates = [];
        $totalDistance = 0.0;

        // Get checkpoints from route
        $points = $route['rpts'] ?? $route['p'] ?? [];

        Log::info('Parsing route checkpoints', [
            'route_name' => $route['nm'] ?? $route['n'] ?? 'Unnamed',
            'checkpoint_count' => count($points)
        ]);

        foreach ($points as $index => $point) {
            $lat = isset($point['y']) ? (float) $point['y'] : null;
            $lng = isset($point['x']) ? (float) $point['x'] : null;
            $name = trim((string) ($point['n'] ?? $point['nm'] ?? $point['name'] ?? ''));
            $radius = isset($point['r']) ? (float) $point['r'] : 100;

            if ($lat !== null && $lng !== null && $lat != 0.0 && $lng != 0.0) {
                if ($name === '') {
                    $name = "Checkpoint " . (count($checkpoints) + 1);
                }

                $checkpoints[] = [
                    'id' => $point['i'] ?? $point['id'] ?? $index,
                    'name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'sequence' => count($checkpoints) + 1,
                    'radius' => $radius,
                    // Set below, once we know which entries are the route's
                    // geographic endpoints. Always present so the frontend
                    // can rely on the keys existing.
                    'is_origin' => false,
                    'is_destination' => false,
                ];

                $routeCoordinates[] = [
                    'lat' => $lat,
                    'lng' => $lng
                ];
            }
        }

        // Calculate distance if we have coordinates
        if (count($routeCoordinates) > 1) {
            for ($i = 0; $i < count($routeCoordinates) - 1; $i++) {
                $totalDistance += $this->haversineDistance(
                    $routeCoordinates[$i]['lat'],
                    $routeCoordinates[$i]['lng'],
                    $routeCoordinates[$i + 1]['lat'],
                    $routeCoordinates[$i + 1]['lng']
                );
            }
        }

        if (isset($route['d']) && (float) $route['d'] > 0) {
            $distVal = (float) $route['d'];
            $totalDistance = $distVal > 1000 ? $distVal / 1000 : $distVal;
        }

        // --- Origin / destination labels: route name/description FIRST ---
        $routeName = trim((string) ($route['nm'] ?? $route['n'] ?? $route['name'] ?? ''));
        $description = trim((string) ($route['rcfg']['descr'] ?? $route['descr'] ?? ''));

        $origin = '';
        $destination = '';

        if ($routeName !== '') {
            $parts = preg_split('/\s*(?:-|->|–|to|—|_)\s*/i', $routeName);
            if (count($parts) >= 2) {
                $origin = trim($parts[0]);
                $destination = trim(end($parts));
            } else {
                $origin = $routeName;
            }
        }

        if (($origin === '' || $destination === '') && $description !== '') {
            $parts = preg_split('/\s*(?:-|->|–|to|—|_)\s*/i', $description);
            if (count($parts) >= 2) {
                if ($origin === '') {
                    $origin = trim($parts[0]);
                }
                if ($destination === '') {
                    $destination = trim(end($parts));
                }
            } elseif ($destination === '') {
                $destination = $description;
            }
        }

        // Only fall back to the checkpoint list itself when the route has no
        // usable name/description at all — this is now the LAST resort,
        // not the first choice.
        if ($origin === '' && !empty($checkpoints)) {
            $origin = $checkpoints[0]['name'];
        }
        if ($destination === '' && count($checkpoints) > 1) {
            $destination = $checkpoints[count($checkpoints) - 1]['name'];
        }

        if ($origin === '') {
            $origin = 'Origin Point';
        }
        if ($destination === '') {
            $destination = 'Destination Point';
        }

        // --- Origin / destination coordinates for the map ---
        // Best-effort: use the first/last geographic checkpoints as the
        // closest available approximation, and flag those checkpoint
        // entries so the frontend renders them as the green/red endpoint
        // markers instead of numbered waypoint pins.
        $originLat = null;
        $originLng = null;
        $destinationLat = null;
        $destinationLng = null;

        if (!empty($checkpoints)) {
            $checkpoints[0]['is_origin'] = true;
            $originLat = $checkpoints[0]['latitude'];
            $originLng = $checkpoints[0]['longitude'];

            $lastIndex = count($checkpoints) - 1;
            if ($lastIndex > 0) {
                $checkpoints[$lastIndex]['is_destination'] = true;
                $destinationLat = $checkpoints[$lastIndex]['latitude'];
                $destinationLng = $checkpoints[$lastIndex]['longitude'];
            }
        }

        $distanceKm = round($totalDistance, 2);
        $durationText = '';
        if ($distanceKm > 0) {
            $avgSpeed = 60.0;
            $hoursTotal = $distanceKm / $avgSpeed;
            $hours = (int) floor($hoursTotal);
            $minutes = (int) round(($hoursTotal - $hours) * 60);

            if ($hours > 0) {
                $durationText .= $hours . ' Hour' . ($hours > 1 ? 's' : '');
            }
            if ($minutes > 0) {
                if ($hours > 0) {
                    $durationText .= ' ';
                }
                $durationText .= $minutes . ' Min' . ($minutes > 1 ? 's' : '');
            }
        } else {
            $durationText = '1 Hour';
        }

        return [
            'id' => $route['id'] ?? null,
            'name' => $routeName !== '' ? $routeName : ($origin . ' - ' . $destination),
            'origin' => $origin,
            'destination' => $destination,
            'origin_lat' => $originLat,
            'origin_lng' => $originLng,
            'destination_lat' => $destinationLat,
            'destination_lng' => $destinationLng,
            'distance_km' => $distanceKm > 0 ? $distanceKm : 10.0,
            'estimated_duration' => $durationText,
            'checkpoints' => $checkpoints,
            'route_coordinates' => $routeCoordinates
        ];
    }

    /**
     * Get complete route data for a unit (vehicle)
     */
    public function getVehicleRouteData(string|int $storedUnitIdentifier, ?string $storedImei = null, array $vehicleContext = []): array
    {
        $result = [
            'route' => null,
            'checkpoints' => [],
            'route_coordinates' => [],
            'unit_position' => null,
        ];

        $unit = $this->resolveUnit($storedUnitIdentifier, $storedImei);
        if ($unit === null) {
            Log::warning('Wialon unit could not be resolved for waybill route', $vehicleContext + [
                'database_gps_unit_id' => (string) $storedUnitIdentifier,
                'database_gps_imei' => $storedImei,
            ]);

            return $result;
        }

        $unitId = (int) $unit['id'];
        $result['unit'] = [
            'id' => $unitId,
            'uid' => $unit['uid'] ?? null,
            'name' => $unit['nm'] ?? null,
        ];
        Log::info('Wialon waybill unit resolved', $vehicleContext + [
            'database_gps_unit_id' => (string) $storedUnitIdentifier,
            'database_gps_imei' => $storedImei,
            'wialon_unit_id' => $unitId,
            'wialon_unit_uid' => $unit['uid'] ?? null,
            'wialon_unit_name' => $unit['nm'] ?? null,
        ]);

        // Get position only after resolving the internal Wialon item ID.
        $position = $this->getUnitPosition($unitId);
        $result['unit_position'] = $position;

        // Get assigned route
        $route = $this->getRouteForUnit($unitId);

        if (!$route) {
            return $result;
        }

        // Parse route data
        $parsed = $this->parseRouteData($route);

        $result['route'] = [
            'id' => $parsed['id'],
            'name' => $parsed['name'],
            'origin' => $parsed['origin'],
            'destination' => $parsed['destination'],
            'origin_lat' => $parsed['origin_lat'],
            'origin_lng' => $parsed['origin_lng'],
            'destination_lat' => $parsed['destination_lat'],
            'destination_lng' => $parsed['destination_lng'],
            'distance_km' => $parsed['distance_km'],
            'estimated_duration' => $parsed['estimated_duration'],
        ];

        $result['checkpoints'] = $parsed['checkpoints'];
        $result['route_coordinates'] = $parsed['route_coordinates'];
        $result['route_assigned'] = true;
        $result['has_sufficient_geographic_checkpoints'] = count($parsed['checkpoints']) >= 2;

        return $result;
    }

    /** Resolve either a stored Wialon item ID or a stored UID/IMEI to a unit. */
    private function resolveUnit(string|int $storedUnitIdentifier, ?string $storedImei = null): ?array
    {
        $identifier = trim((string) $storedUnitIdentifier);
        $imei = trim((string) $storedImei);

        if ($identifier !== '' && ctype_digit($identifier)) {
            $unit = $this->getUnit((int) $identifier);
            if ($unit !== null) {
                return $unit;
            }
        }

        // Backward compatibility: older rows put the 15-digit UID in
        // gps_unit_id. Prefer gps_imei when present, otherwise use that value.
        $uid = $imei !== '' ? $imei : $identifier;
        if ($uid === '') {
            return null;
        }

        $data = $this->request('core/search_items', [
            'spec' => [
                'itemsType' => 'avl_unit',
                'propName' => 'sys_unique_id',
                'propValueMask' => $uid,
                'sortType' => 'sys_name',
            ],
            'force' => 1,
            'flags' => 0x00000001 | 0x00000100 | 0x00000400,
            'from' => 0,
            'to' => 0,
        ]);

        $units = $data['items'] ?? [];
        $matches = array_values(array_filter($units, fn (array $unit) => (string) ($unit['uid'] ?? '') === $uid));

        if (count($matches) !== 1) {
            Log::warning('Wialon UID lookup did not return exactly one unit', [
                'wialon_unit_uid' => $uid,
                'match_count' => count($matches),
            ]);

            return null;
        }

        return $matches[0];
    }

    /** Remove credentials if Wialon returns them in custom/advanced properties. */
    private function sanitizeForLog(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $sanitized = [];
        foreach ($value as $key => $item) {
            $keyName = strtolower((string) $key);
            if (in_array($keyName, ['token', 'sid', 'eid', 'password', 'pwd', 'psw', 'secret', 'authorization'], true)) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            $sanitized[$key] = $this->sanitizeForLog($item);
        }

        return $sanitized;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Synchronize a saved Waybill route to Wialon as a real avl_route object.
     *
     * Uses the correct Wialon API: resource/update_route
     * (NOT item/update_custom_field — that only tags the unit with text metadata
     *  and never creates a visible route in the Wialon portal.)
     *
     * Route points sequence: Origin -> Checkpoint 1 -> ... -> Destination.
     * Returns the Wialon route ID on success so the caller can persist it.
     */
    public function syncWaybillRoute(int|string $unitId, array $waybillData): ?array
    {
        $unitIdInt = (int) $unitId;

        // ----------------------------------------------------------------
        // Build the ordered rpts (route points) array
        // ----------------------------------------------------------------
        $points = [];

        // 1. Origin
        if (!empty($waybillData['origin'])) {
            $points[] = [
                'n' => (string) $waybillData['origin'],
                'y' => (float) ($waybillData['origin_lat'] ?? 0),  // Wialon y = latitude
                'x' => (float) ($waybillData['origin_lng'] ?? 0),  // Wialon x = longitude
                'r' => 100,
            ];
        }

        // 2. Intermediate checkpoints
        $checkpoints = $waybillData['checkpoints'] ?? [];
        if (is_string($checkpoints)) {
            $checkpoints = json_decode($checkpoints, true) ?? [];
        }

        if (is_array($checkpoints)) {
            foreach ($checkpoints as $cp) {
                $cpName = $cp['name'] ?? ($cp['formatted'] ?? 'Checkpoint');
                $cpLat  = (float) ($cp['lat'] ?? $cp['latitude']  ?? 0);
                $cpLng  = (float) ($cp['lng'] ?? $cp['longitude'] ?? 0);

                // Skip checkpoints without coordinates
                if ($cpLat == 0.0 && $cpLng == 0.0) {
                    continue;
                }

                $points[] = [
                    'n' => (string) $cpName,
                    'y' => $cpLat,
                    'x' => $cpLng,
                    'r' => 100,
                ];
            }
        }

        // 3. Destination
        if (!empty($waybillData['destination'])) {
            $points[] = [
                'n' => (string) $waybillData['destination'],
                'y' => (float) ($waybillData['destination_lat'] ?? 0),
                'x' => (float) ($waybillData['destination_lng'] ?? 0),
                'r' => 100,
            ];
        }

        $routeName = 'WB-' . ($waybillData['waybill_number'] ?? 'UNKNOWN')
            . ': ' . ($waybillData['origin'] ?? 'Origin')
            . ' → ' . ($waybillData['destination'] ?? 'Destination');

        Log::info('Wialon route sync initiated (resource/update_route)', [
            'wialon_unit_id' => $unitIdInt,
            'waybill_number' => $waybillData['waybill_number'] ?? null,
            'route_name'     => $routeName,
            'points_count'   => count($points),
            'points'         => array_map(
                fn ($p) => ['name' => $p['n'], 'lat' => $p['y'], 'lng' => $p['x']],
                $points
            ),
        ]);

        if (count($points) < 2) {
            Log::warning('Wialon route sync skipped: need at least origin + destination (2 points).', [
                'points_found' => count($points),
            ]);
            return null;
        }

        try {
            // STEP 1 — Resolve the Wialon Resource that will own the route
            $resourceId = $this->getWialonResourceId();

            // STEP 2 — Determine create vs update
            $existingRouteId = isset($waybillData['wialon_route_id'])
                && is_numeric($waybillData['wialon_route_id'])
                && (int) $waybillData['wialon_route_id'] > 0
                    ? (int) $waybillData['wialon_route_id']
                    : null;

            // STEP 3 — Create / update the real Wialon Route
            $wialonRouteId = $this->createOrUpdateWialonRoute(
                $resourceId,
                $points,
                $routeName,
                $existingRouteId,
                $unitIdInt
            );

            Log::info('Wialon route sync completed — real route created in Wialon portal', [
                'wialon_unit_id'  => $unitIdInt,
                'wialon_route_id' => $wialonRouteId,
                'resource_id'     => $resourceId,
                'route_name'      => $routeName,
                'points_count'    => count($points),
            ]);

            return [
                'success'         => true,
                'wialon_unit_id'  => $unitIdInt,
                'wialon_route_id' => $wialonRouteId,
                'resource_id'     => $resourceId,
                'route_name'      => $routeName,
                'points_count'    => count($points),
            ];

        } catch (\Throwable $e) {
            Log::error('Wialon route sync failed', [
                'wialon_unit_id' => $unitIdInt,
                'waybill_number' => $waybillData['waybill_number'] ?? null,
                'error'          => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Find the first accessible Wialon Resource (avl_resource) the token user
     * can manage routes in.
     *
     * The result is cached in the application cache for 10 minutes and in
     * memory for the current request to avoid repeated Wialon round-trips.
     *
     * @throws \RuntimeException when no accessible resource is found.
     */
    public function getWialonResourceId(): int
    {
        // In-memory cache (same request / object lifetime)
        if (isset($this->cachedResourceId)) {
            return $this->cachedResourceId;
        }

        $cacheKey = 'wialon_resource_id_' . md5($this->token);
        $cached   = Cache::get($cacheKey);

        if ($cached !== null) {
            $this->cachedResourceId = (int) $cached;
            return $this->cachedResourceId;
        }

        Log::info('Searching for accessible Wialon resource (avl_resource)');

        $data = $this->request('core/search_items', [
            'spec' => [
                'itemsType'     => 'avl_resource',
                'propName'      => 'sys_name',
                'propValueMask' => '*',
                'sortType'      => 'sys_name',
            ],
            'force' => 1,
            'flags' => 0x00000001, // base info only — we only need the ID and name
            'from'  => 0,
            'to'    => 0,
        ]);

        $items = $data['items'] ?? [];

        Log::info('Wialon avl_resource search result', [
            'resource_count' => count($items),
            'resources'      => array_map(
                fn ($r) => ['id' => $r['id'] ?? null, 'name' => $r['nm'] ?? null],
                $items
            ),
        ]);

        if (empty($items)) {
            throw new \RuntimeException(
                'No accessible Wialon resource (avl_resource) found. ' .
                'The token user must have at least one avl_resource with "Manage routes" rights. ' .
                'Please create a Resource in Wialon (e.g. "Routes Resource") and grant the API user ' .
                'access rights via Wialon → Users → <user> → Access Rights.'
            );
        }

        // Use the first resource — most single-account setups have exactly one.
        $resourceId = (int) ($items[0]['id'] ?? 0);

        if ($resourceId <= 0) {
            throw new \RuntimeException(
                'Wialon returned an avl_resource but with an invalid ID (0 or negative). ' .
                'Response: ' . json_encode($items[0])
            );
        }

        Log::info('Wialon resource selected for route creation', [
            'resource_id'   => $resourceId,
            'resource_name' => $items[0]['nm'] ?? 'unknown',
        ]);

        Cache::put($cacheKey, $resourceId, 600);
        $this->cachedResourceId = $resourceId;

        return $resourceId;
    }

    /**
     * Create or update a real Wialon Route (avl_route) inside the given resource.
     *
     * This is the correct Wialon API for route management. It creates a visible
     * route in the Wialon portal under the resource's Routes tab.
     *
     * @param int      $resourceId      Wialon avl_resource item ID (kept for logging/context —
     *                                  routes are actually owned by creatorId, not the resource)
     * @param array    $points          Route points: [['n'=>name, 'x'=>lng, 'y'=>lat, 'r'=>radius], ...]
     * @param string   $routeName       Display name for the route in Wialon
     * @param int|null $existingRouteId Pass a previous wialon_route_id to update; null to create new
     * @param int|null $unitId          Wialon unit (vehicle) ID to attach to this route, if any
     *
     * @return int  The Wialon route ID (persist in waybill.wialon_route_id)
     * @throws \RuntimeException on API failure or missing route ID in response
     *
     * NOTE: There is no single Wialon service that creates a route with checkpoints
     * in one call. "resource/update_route" (used previously) does not exist in the
     * Wialon API and always fails with error 2 ("Invalid service"). The correct flow
     * is three separate calls:
     *   1. core/create_route          — creates the empty route, returns its ID
     *   2. route/update_checkpoints   — pushes the ordered stop points onto it
     *   3. route/update_config        — attaches vehicle(s)/units to the route
     */
    public function createOrUpdateWialonRoute(
        int    $resourceId,
        array  $points,
        string $routeName,
        ?int   $existingRouteId = null,
        ?int   $unitId = null
    ): int {
        $isUpdate = ($existingRouteId !== null && $existingRouteId > 0);
        $routeId  = $isUpdate ? $existingRouteId : 0;

        // Build checkPoints in the format route/update_checkpoints actually expects.
        // 'f' => 1 means "point from the map" (a plain lat/lng stop, not a geofence or unit).
        $checkPoints = [];
        foreach ($points as $seq => $point) {
            $checkPoints[] = [
                'f' => 1,
                'n' => (string) ($point['n'] ?? 'Point ' . ($seq + 1)),
                'y' => (float)  ($point['y'] ?? 0),    // latitude
                'x' => (float)  ($point['x'] ?? 0),    // longitude
                'r' => (int)    ($point['r'] ?? 100),  // radius in metres
            ];
        }

        // STEP 1 — create the route shell if this is a new route.
        // core/create_route only creates; there is no separate "update name" call
        // needed for an existing route, so this step is skipped on update.
        if (!$isUpdate) {
            Log::info('Calling Wialon core/create_route', [
                'creator_id' => $this->creatorId,
                'route_name' => $routeName,
            ]);

            $createResult = $this->request('core/create_route', [
                'creatorId' => $this->creatorId,
                'name'      => $routeName,
                'dataFlags' => 1,
            ]);

            $routeId = (int) ($createResult['item']['id'] ?? 0);

            if ($routeId <= 0) {
                Log::error('Wialon core/create_route returned invalid route ID', [
                    'response' => $createResult,
                ]);
                throw new \RuntimeException(
                    "Wialon core/create_route call succeeded but returned no valid route ID. " .
                    "Ensure creatorId ({$this->creatorId}) has rights to create routes."
                );
            }

            Log::info('Wialon route shell created', [
                'wialon_route_id' => $routeId,
                'route_name'      => $routeName,
            ]);
        }

        // STEP 2 — push checkpoints onto the route (works the same for create/update)
        Log::info('Calling Wialon route/update_checkpoints', [
            'route_id'          => $routeId,
            'checkpoints_count' => count($checkPoints),
        ]);

        $this->request('route/update_checkpoints', [
            'itemId'      => $routeId,
            'checkPoints' => $checkPoints,
        ]);

        // STEP 3 — attach the vehicle/unit to the route, if one was given
        if (!empty($unitId)) {
            Log::info('Calling Wialon route/update_config', [
                'route_id' => $routeId,
                'unit_id'  => $unitId,
            ]);

            $this->request('route/update_config', [
                'itemId' => $routeId,
                'config' => [
                    'color' => 16711680, // ARGB red, default — cosmetic only
                    'descr' => $routeName,
                    'units' => [$unitId],
                ],
            ]);
        }

        Log::info('Wialon route created/updated in Wialon portal', [
            'resource_id'     => $resourceId,
            'wialon_route_id' => $routeId,
            'call_mode'       => $isUpdate ? 'update' : 'create',
            'route_name'      => $routeName,
            'unit_id'         => $unitId,
        ]);

        return $routeId;
    }

    /**
     * Delete a Wialon Route (avl_route).
     * Safe to call even if the route no longer exists in Wialon — errors are
     * logged as warnings rather than thrown.
     *
     * Routes are deleted like any other Wialon item, via item/delete_item —
     * "resource/update_route" with callMode "delete" (used previously) does not
     * exist and would fail the same way route creation did.
     *
     * @param int $resourceId  Wialon avl_resource item ID (kept for logging only)
     * @param int $routeId     Wialon route ID to delete
     */
    public function deleteWialonRoute(int $resourceId, int $routeId): void
    {
        if ($routeId <= 0) {
            Log::warning('deleteWialonRoute skipped: invalid route ID provided', [
                'resource_id' => $resourceId,
                'route_id'    => $routeId,
            ]);
            return;
        }

        Log::info('Deleting Wialon route', [
            'resource_id' => $resourceId,
            'route_id'    => $routeId,
        ]);

        try {
            $this->request('item/delete_item', [
                'itemId' => $routeId,
            ]);

            Log::info('Wialon route deleted successfully', [
                'resource_id' => $resourceId,
                'route_id'    => $routeId,
            ]);
        } catch (\Throwable $e) {
            // Log but don't rethrow — the route may already be gone in Wialon
            Log::warning('Wialon route deletion failed (may already be deleted)', [
                'resource_id' => $resourceId,
                'route_id'    => $routeId,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
