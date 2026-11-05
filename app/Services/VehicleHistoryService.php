<?php

namespace App\Services;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VehicleHistoryService extends WialonService
{
    /**
     * Color palette for different trips when "By trips" color mode is used.
     */
    protected const TRIP_COLORS = [
        '#2563eb', // Blue
        '#16a34a', // Green
        '#d97706', // Amber
        '#9333ea', // Purple
        '#dc2626', // Red
        '#0891b2', // Cyan
        '#ea580c', // Orange
        '#4f46e5', // Indigo
        '#059669', // Emerald
        '#db2777', // Pink
    ];

    /**
     * Fetch all active units directly from Wialon live, merged with DB vehicles when available.
     *
     * @return array
     */
    public function getWialonLiveUnits(): array
    {
        try {
            // Flags: 0x01 (base: id, nm, uid) | 0x100 (config) | 0x400 (last pos: pos, lmsg)
            $flags = 0x00000001 | 0x00000100 | 0x00000400;

            $data = $this->request('core/search_items', [
                'spec' => [
                    'itemsType'     => 'avl_unit',
                    'propName'      => 'sys_name',
                    'propValueMask' => '*',
                    'sortType'      => 'sys_name',
                ],
                'force' => 1,
                'flags' => $flags,
                'from'  => 0,
                'to'    => 0,
            ]);

            $items = $data['items'] ?? [];
            $dbVehicles = Vehicle::with(['drivers', 'vehicle_make_model'])->get();

            $units = [];
            foreach ($items as $item) {
                $unitId = (int) ($item['id'] ?? 0);
                if ($unitId <= 0) continue;

                $unitName = $item['nm'] ?? ('Unit ' . $unitId);
                $imei = (string) ($item['uid'] ?? '');

                // Match with local DB vehicle if exists
                $dbMatch = $dbVehicles->first(function ($v) use ($unitId, $imei, $unitName) {
                    return ((string) $v->gps_unit_id === (string) $unitId)
                        || (!empty($imei) && (string) $v->gps_imei === $imei)
                        || (strcasecmp((string) $v->fleet_no, $unitName) === 0);
                });

                $driverName = '—';
                if ($dbMatch && $dbMatch->drivers && $dbMatch->drivers->isNotEmpty()) {
                    $driver = $dbMatch->drivers->first();
                    $driverName = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
                }

                $lastGpsTime = null;
                $lastGpsTimestamp = null;
                $lastLat = null;
                $lastLng = null;
                $lastSpeed = 0;

                if (isset($item['pos']) && is_array($item['pos'])) {
                    $lastGpsTimestamp = $item['pos']['t'] ?? null;
                    $lastLat = $item['pos']['y'] ?? null;
                    $lastLng = $item['pos']['x'] ?? null;
                    $lastSpeed = (int) round((float) ($item['pos']['s'] ?? 0));
                    if ($lastGpsTimestamp) {
                        $lastGpsTime = date('d M Y, H:i:s', $lastGpsTimestamp);
                    }
                } elseif (isset($item['lmsg']) && isset($item['lmsg']['t'])) {
                    $lastGpsTimestamp = $item['lmsg']['t'];
                    $lastGpsTime = date('d M Y, H:i:s', $lastGpsTimestamp);
                }

                $units[] = [
                    'id'               => $dbMatch ? (string) $dbMatch->id : (string) $unitId,
                    'unit_id'          => $unitId,
                    'fleet_no'         => $dbMatch?->fleet_no ?? $unitName,
                    'truck_no'         => $dbMatch?->truck_no ?? 'Wialon Unit',
                    'driver_name'      => $driverName,
                    'make_model'       => $dbMatch?->vehicle_make_model ? $dbMatch->vehicle_make_model->name : 'Wialon GPS Tracker',
                    'type'             => $dbMatch?->vehicle_type ?? 'Truck',
                    'imei'             => $imei,
                    'last_gps_time'    => $lastGpsTime,
                    'last_gps_ts'      => $lastGpsTimestamp,
                    'last_lat'         => $lastLat,
                    'last_lng'         => $lastLng,
                    'last_speed'       => $lastSpeed,
                    'is_in_db'         => $dbMatch !== null,
                ];
            }

            // Sort: units with recent GPS points first, then alphabetically by fleet_no
            usort($units, function ($a, $b) {
                $tsA = $a['last_gps_ts'] ?? 0;
                $tsB = $b['last_gps_ts'] ?? 0;
                if ($tsA !== $tsB) {
                    return $tsB <=> $tsA;
                }
                return strcasecmp($a['fleet_no'], $b['fleet_no']);
            });

            return $units;
        } catch (\Throwable $e) {
            Log::error('Failed to fetch live Wialon units', [
                'error' => $e->getMessage(),
            ]);

            // Fallback to database vehicles with GPS unit IDs
            return Vehicle::with(['drivers', 'vehicle_make_model'])
                ->whereNotNull('gps_unit_id')
                ->where('gps_unit_id', '!=', '')
                ->get()
                ->map(function ($veh) {
                    $driverName = '—';
                    if ($veh->drivers && $veh->drivers->isNotEmpty()) {
                        $d = $veh->drivers->first();
                        $driverName = trim(($d->first_name ?? '') . ' ' . ($d->last_name ?? ''));
                    }
                    return [
                        'id'            => (string) $veh->id,
                        'unit_id'       => (int) $veh->gps_unit_id,
                        'fleet_no'      => $veh->fleet_no ?? 'Asset',
                        'truck_no'      => $veh->truck_no ?? 'N/A',
                        'driver_name'   => $driverName,
                        'make_model'    => $veh->vehicle_make_model ? $veh->vehicle_make_model->name : 'Fleet Truck',
                        'type'          => $veh->vehicle_type ?? 'Truck',
                        'imei'          => $veh->gps_imei ?? '',
                        'last_gps_time' => null,
                        'last_gps_ts'   => null,
                        'is_in_db'      => true,
                    ];
                })->all();
        }
    }

    /**
     * Fetch complete historical track data for a vehicle within the given time range.
     *
     * @param string|int $vehicleIdentifier UUID of vehicle in database or Wialon unit ID
     * @param int $fromTimestamp Unix timestamp
     * @param int $toTimestamp Unix timestamp
     * @param array $options ['trip_detector' => bool, 'show_annotations' => bool]
     * @return array
     */
    public function getVehicleTrackData(string|int $vehicleIdentifier, int $fromTimestamp, int $toTimestamp, array $options = []): array
    {
        // 1. Resolve vehicle and Wialon unit ID
        $vehicle = null;
        $unitId = null;

        if (is_numeric($vehicleIdentifier) && (int) $vehicleIdentifier > 0) {
            $unitId = (int) $vehicleIdentifier;
            $vehicle = Vehicle::where('gps_unit_id', $unitId)->with(['drivers', 'vehicle_make_model'])->first();
        } else {
            $vehicle = Vehicle::with(['drivers', 'vehicle_make_model'])->find($vehicleIdentifier);
            if ($vehicle && $vehicle->gps_unit_id) {
                $unitId = (int) $vehicle->gps_unit_id;
            } elseif (is_numeric($vehicleIdentifier)) {
                $unitId = (int) $vehicleIdentifier;
            }
        }

        // If unitId is still not resolved, try resolving by IMEI or integer ID
        if (!$unitId && is_string($vehicleIdentifier) && ctype_digit($vehicleIdentifier)) {
            $unitId = (int) $vehicleIdentifier;
        }

        if (!$unitId) {
            throw new \InvalidArgumentException('Vehicle does not have a valid GPS Unit ID assigned.');
        }

        Log::info('Loading historical vehicle track data', [
            'vehicle_id'   => $vehicle?->id,
            'fleet_no'     => $vehicle?->fleet_no,
            'unit_id'      => $unitId,
            'from'         => date('Y-m-d H:i:s', $fromTimestamp),
            'to'           => date('Y-m-d H:i:s', $toTimestamp),
        ]);

        // 2. Fetch unit configuration & sensors from Wialon
        $unitInfo = $this->fetchUnitInfoAndSensors($unitId);
        $sensorsConfig = $unitInfo['sensors'] ?? [];

        // 3. Fetch raw messages from Wialon
        $rawMessages = $this->fetchUnitMessages($unitId, $fromTimestamp, $toTimestamp);

        if (empty($rawMessages)) {
            return [
                'success'       => true,
                'vehicle'       => $this->formatVehicleData($vehicle, $unitInfo),
                'unit_id'       => $unitId,
                'period'        => [
                    'from'               => $fromTimestamp,
                    'to'                 => $toTimestamp,
                    'from_formatted'     => date('d M Y, H:i', $fromTimestamp),
                    'to_formatted'       => date('d M Y, H:i', $toTimestamp),
                    'duration_seconds'   => max(0, $toTimestamp - $fromTimestamp),
                    'duration_formatted' => $this->formatDuration(max(0, $toTimestamp - $fromTimestamp)),
                ],
                'summary'       => [
                    'total_mileage_km'   => 0,
                    'total_duration_sec' => 0,
                    'moving_duration_sec'=> 0,
                    'stop_duration_sec'  => 0,
                    'max_speed'          => 0,
                    'avg_speed'          => 0,
                    'points_count'       => 0,
                    'trips_count'        => 0,
                    'stops_count'        => 0,
                ],
                'trips'         => [],
                'stops'         => [],
                'points'        => [],
                'annotations'   => [],
                'sensors_meta'  => $sensorsConfig,
                'message'       => 'No GPS messages found for the selected vehicle in this time range.',
            ];
        }

        // 4. Fetch trips from Wialon API if available, or compute locally
        $useTripDetector = $options['trip_detector'] ?? true;
        $wialonTrips = $useTripDetector ? $this->fetchWialonTrips($unitId, $fromTimestamp, $toTimestamp) : [];

        // 5. Process raw messages into structured playback points
        $processedPoints = $this->processTrackPoints($rawMessages, $sensorsConfig);
        unset($rawMessages);

        // 6. Detect or map trips & stops
        $tripsAndStops = $this->detectTripsAndStops($processedPoints, $wialonTrips, $useTripDetector);
        $trips = $tripsAndStops['trips'];
        $stops = $tripsAndStops['stops'];

        // Assign trip index and trip colors to points
        $this->assignTripsToPoints($processedPoints, $trips);

        // 7. Calculate overall mileage and metrics
        $metrics = $this->calculateTrackMetrics($processedPoints, $trips, $stops);

        // 8. Build map annotations (Trip start, Trip end, Stops, Speed peaks)
        $annotations = ($options['show_annotations'] ?? true)
            ? $this->buildAnnotations($trips, $stops, $processedPoints, $metrics['max_speed_point'] ?? null)
            : [];

        return [
            'success'      => true,
            'vehicle'      => $this->formatVehicleData($vehicle, $unitInfo),
            'unit_id'      => $unitId,
            'period'       => [
                'from'               => $fromTimestamp,
                'to'                 => $toTimestamp,
                'from_formatted'     => date('d M Y, H:i', $fromTimestamp),
                'to_formatted'       => date('d M Y, H:i', $toTimestamp),
                'duration_seconds'   => max(0, $toTimestamp - $fromTimestamp),
                'duration_formatted' => $this->formatDuration(max(0, $toTimestamp - $fromTimestamp)),
            ],
            'summary'      => [
                'total_mileage_km'    => $metrics['total_mileage_km'],
                'total_duration_sec'  => $metrics['total_duration_sec'],
                'total_duration_fmt'  => $this->formatDuration($metrics['total_duration_sec']),
                'moving_duration_sec' => $metrics['moving_duration_sec'],
                'moving_duration_fmt' => $this->formatDuration($metrics['moving_duration_sec']),
                'stop_duration_sec'   => $metrics['stop_duration_sec'],
                'stop_duration_fmt'   => $this->formatDuration($metrics['stop_duration_sec']),
                'max_speed'           => $metrics['max_speed'],
                'avg_speed'           => $metrics['avg_speed'],
                'points_count'        => count($processedPoints),
                'trips_count'         => count($trips),
                'stops_count'         => count($stops),
            ],
            'trips'        => $trips,
            'stops'        => $stops,
            'points'       => $processedPoints,
            'annotations'  => $annotations,
            'sensors_meta' => $sensorsConfig,
        ];
    }

    /**
     * Fetch unit details and sensor configuration from Wialon.
     */
    protected function fetchUnitInfoAndSensors(int $unitId): array
    {
        try {
            // Flags: 0x01 (base) | 0x100 (config) | 0x400 (last pos) | 0x1000 (sensors: 4096)
            $flags = 0x00000001 | 0x00000100 | 0x00000400 | 0x00001000;
            $data = $this->request('core/search_item', [
                'id'    => $unitId,
                'flags' => $flags,
            ]);

            $item = $data['item'] ?? [];
            $rawSensors = $item['sens'] ?? [];
            $sensors = [];

            foreach ($rawSensors as $sId => $sData) {
                $sensors[] = [
                    'id'          => $sData['id'] ?? (int) $sId,
                    'name'        => $sData['n'] ?? 'Sensor ' . $sId,
                    'type'        => $sData['t'] ?? 'custom',
                    'description' => $sData['d'] ?? '',
                    'units'       => $sData['m'] ?? '',
                    'param'       => $sData['p'] ?? '',
                    'tbl'         => $sData['tbl'] ?? [],
                ];
            }

            return [
                'id'       => $item['id'] ?? $unitId,
                'name'     => $item['nm'] ?? 'Vehicle Unit',
                'uid'      => $item['uid'] ?? '',
                'sensors'  => $sensors,
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch Wialon unit sensors', [
                'unit_id' => $unitId,
                'error'   => $e->getMessage(),
            ]);

            return [
                'id'      => $unitId,
                'name'    => 'Vehicle Unit',
                'uid'     => '',
                'sensors' => [],
            ];
        }
    }

    /**
     * Fetch raw historical messages from Wialon for the interval.
     */
    protected function fetchUnitMessages(int $unitId, int $fromTimestamp, int $toTimestamp): array
    {
        try {
            // flags: 0 (regular data messages), flagsMask: 0
            $data = $this->request('messages/load_interval', [
                'itemId'    => $unitId,
                'timeFrom'  => $fromTimestamp,
                'timeTo'    => $toTimestamp,
                'flags'     => 0,
                'flagsMask' => 0,
                'loadCount' => 10000,
            ]);

            $messages = $data['messages'] ?? [];
            $totalCount = (int) ($data['count'] ?? count($messages));

            // If more messages exist than the first loadCount, retrieve remaining
            if ($totalCount > count($messages) && count($messages) > 0) {
                try {
                    $moreData = $this->request('messages/get_messages', [
                        'indexFrom' => count($messages),
                        'indexTo'   => min($totalCount - 1, 15000), // safety ceiling
                    ]);
                    if (!empty($moreData['messages'])) {
                        $messages = array_merge($messages, $moreData['messages']);
                    }
                } catch (\Throwable $e) {
                    Log::debug('Could not fetch extra messages batch', ['error' => $e->getMessage()]);
                }
            }

            return $messages;
        } catch (\Throwable $e) {
            Log::error('Failed to load Wialon historical messages', [
                'unit_id' => $unitId,
                'from'    => $fromTimestamp,
                'to'      => $toTimestamp,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Fetch trips detected by Wialon unit detector if available.
     */
    protected function fetchWialonTrips(int $unitId, int $fromTimestamp, int $toTimestamp): array
    {
        try {
            $data = $this->request('unit/get_trips', [
                'itemId'   => $unitId,
                'timeFrom' => $fromTimestamp,
                'timeTo'   => $toTimestamp,
            ]);

            return is_array($data) ? $data : ($data['trips'] ?? []);
        } catch (\Throwable $e) {
            Log::debug('Wialon unit/get_trips not available or failed', [
                'unit_id' => $unitId,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Process raw Wialon GPS messages into optimized playback coordinate points.
     */
    protected function processTrackPoints(array $rawMessages, array $sensorsConfig): array
    {
        $points = [];
        $loggedParams = false; // Only log once for debugging

        foreach ($rawMessages as $msg) {
            if (!isset($msg['pos']) || !isset($msg['pos']['x']) || !isset($msg['pos']['y'])) {
                continue;
            }

            $lng = (float) $msg['pos']['x'];
            $lat = (float) $msg['pos']['y'];

            // Skip invalid 0,0 coordinates
            if ($lat == 0.0 && $lng == 0.0) {
                continue;
            }

            $timestamp = (int) ($msg['t'] ?? 0);
            $speed = isset($msg['pos']['s']) ? (int) round((float) $msg['pos']['s']) : 0;
            $course = isset($msg['pos']['c']) ? (int) round((float) $msg['pos']['c']) : 0;
            $altitude = isset($msg['pos']['z']) ? (int) round((float) $msg['pos']['z']) : 0;
            $sats = isset($msg['pos']['sc']) ? (int) $msg['pos']['sc'] : (int) ($msg['p']['sats'] ?? ($msg['p']['sat'] ?? 0));
            $params = $msg['p'] ?? [];

            // Debug logging: log first message params to understand structure
            if (!$loggedParams && !empty($params)) {
                Log::info('Wialon message parameters sample', [
                    'params_keys' => array_keys($params),
                    'params_sample' => array_slice($params, 0, 10, true),
                    'sensors_configured' => count($sensorsConfig),
                    'sensor_names' => array_column($sensorsConfig, 'name'),
                ]);
                $loggedParams = true;
            }

            // Evaluate sensor values
            $evaluatedSensors = $this->evaluateSensors($params, $sensorsConfig, $speed);

            $points[] = [
                't'         => $timestamp,
                'time'      => date('H:i:s', $timestamp),
                'datetime'  => date('Y-m-d H:i:s', $timestamp),
                'date_fmt'  => date('d M Y, H:i:s', $timestamp),
                'lat'       => $lat,
                'lng'       => $lng,
                'speed'     => $speed,
                'course'    => $course,
                'alt'       => $altitude,
                'sats'      => $sats,
                'params'    => $params,
                'sensors'   => $evaluatedSensors,
                'status'    => $speed > 0 ? 'moving' : 'stopped',
                'trip_idx'  => 0,
            ];
        }

        // Sort points strictly by timestamp ascending
        usort($points, fn($a, $b) => $a['t'] <=> $b['t']);

        return $points;
    }

    /**
     * Evaluate configured sensors and standard telemetry for a message parameter dictionary.
     */
    protected function evaluateSensors(array $params, array $sensorsConfig, int $speed): array
    {
        $evaluated = [];

        // First, evaluate all Wialon-configured sensors
        $sensorsByType = [];
        foreach ($sensorsConfig as $sensor) {
            $sName = $sensor['name'] ?? null;
            $sParam = $sensor['param'] ?? null;
            $sType = strtolower($sensor['type'] ?? 'custom');
            $sUnits = $sensor['units'] ?? '';

            if (!$sName || empty($sParam)) {
                continue;
            }

            // Store sensor by type for later use
            $sensorsByType[$sType] = $sensor;

            if (isset($params[$sParam])) {
                $rawVal = $params[$sParam];
                
                // Handle table-based sensors (e.g., digital inputs with state mapping)
                if (!empty($sensor['tbl']) && is_array($sensor['tbl'])) {
                    $table = $sensor['tbl'];
                    $valStr = $this->evaluateTableSensor($rawVal, $table);
                } else {
                    $valStr = is_numeric($rawVal) ? (string) round((float) $rawVal, 2) : (string) $rawVal;
                    if (!empty($sUnits)) {
                        $valStr .= ' ' . $sUnits;
                    }
                }
                
                $evaluated[$sName] = $valStr;
            }
        }

        // 1. Ignition resolution
        $ignition = null;
        if (!isset($evaluated['Ignition'])) {
            if (isset($params['ign'])) {
                $ignition = ((int) $params['ign']) > 0;
            } elseif (isset($params['io_1'])) {
                $ignition = ((int) $params['io_1']) > 0;
            } elseif (isset($params['in1'])) {
                $ignition = ((int) $params['in1']) > 0;
            } elseif (isset($params['acc'])) {
                $ignition = ((int) $params['acc']) > 0;
            } elseif ($speed > 2) {
                $ignition = true; // infer from movement
            }
            $evaluated['Ignition'] = $ignition !== null ? ($ignition ? 'On' : 'Off') : '--';
        }

        // 2. Fuel Level resolution (only if not already from Wialon sensor)
        if (!isset($evaluated['Fuel Level'])) {
            $fuel = null;
            if (isset($params['fuel_level'])) {
                $fuel = (float) $params['fuel_level'];
            } elseif (isset($params['can_fuel'])) {
                $fuel = (float) $params['can_fuel'];
            } elseif (isset($params['fls'])) {
                $fuel = (float) $params['fls'];
            } elseif (isset($params['tank_level'])) {
                $fuel = (float) $params['tank_level'];
            } elseif (isset($params['fuel'])) {
                $fuel = (float) $params['fuel'];
            }
            $evaluated['Fuel Level'] = $fuel !== null ? round($fuel, 1) . ' L' : '--';
        }

        // 3. Engine Hours resolution
        if (!isset($evaluated['Engine Hours'])) {
            $eh = null;
            if (isset($params['engine_hours'])) {
                $eh = (float) $params['engine_hours'];
            } elseif (isset($params['eh'])) {
                $eh = (float) $params['eh'];
            } elseif (isset($params['hours'])) {
                $eh = (float) $params['hours'];
            } elseif (isset($params['engine_time'])) {
                $eh = (float) $params['engine_time'];
            }
            $evaluated['Engine Hours'] = $eh !== null ? round($eh, 1) . ' h' : '--';
        }

        // 4. Fuel Rate resolution
        if (!isset($evaluated['Fuel Rate'])) {
            $fuelRate = null;
            if (isset($params['fuel_rate'])) {
                $fuelRate = (float) $params['fuel_rate'];
            } elseif (isset($params['consumption'])) {
                $fuelRate = (float) $params['consumption'];
            } elseif (isset($params['fuel_consumption'])) {
                $fuelRate = (float) $params['fuel_consumption'];
            }
            $evaluated['Fuel Rate'] = $fuelRate !== null ? round($fuelRate, 1) . ' L/h' : '--';
        }

        // 5. Voltage resolution
        if (!isset($evaluated['Voltage'])) {
            $voltage = null;
            if (isset($params['pwr_ext'])) {
                $voltage = (float) $params['pwr_ext'];
            } elseif (isset($params['voltage'])) {
                $voltage = (float) $params['voltage'];
            } elseif (isset($params['battery'])) {
                $voltage = (float) $params['battery'];
            } elseif (isset($params['pwr_int'])) {
                $voltage = (float) $params['pwr_int'];
            }
            $evaluated['Voltage'] = $voltage !== null ? round($voltage, 1) . ' V' : '--';
        }

        // 6. Temperature resolution
        if (!isset($evaluated['Temperature'])) {
            $temp = null;
            if (isset($params['temp'])) {
                $temp = (float) $params['temp'];
            } elseif (isset($params['temp1'])) {
                $temp = (float) $params['temp1'];
            } elseif (isset($params['t1'])) {
                $temp = (float) $params['t1'];
            } elseif (isset($params['temperature'])) {
                $temp = (float) $params['temperature'];
            }
            $evaluated['Temperature'] = $temp !== null ? round($temp, 1) . ' °C' : '--';
        }

        // 7. Mileage/Odometer resolution (often useful)
        if (!isset($evaluated['Mileage'])) {
            $mileage = null;
            if (isset($params['mileage'])) {
                $mileage = (float) $params['mileage'];
            } elseif (isset($params['odometer'])) {
                $mileage = (float) $params['odometer'];
            } elseif (isset($params['can_mileage'])) {
                $mileage = (float) $params['can_mileage'];
            }
            if ($mileage !== null) {
                $evaluated['Mileage'] = round($mileage / 1000, 0) . ' km';
            }
        }

        // 8. Voltage sensor (some trackers have separate voltage sensor)
        if (!isset($evaluated['Voltage sensor'])) {
            $voltageSensor = null;
            if (isset($params['adc1'])) {
                $voltageSensor = (float) $params['adc1'];
            } elseif (isset($params['ain1'])) {
                $voltageSensor = (float) $params['ain1'];
            }
            if ($voltageSensor !== null) {
                $evaluated['Voltage sensor'] = round($voltageSensor, 2) . ' V';
            }
        }

        return $evaluated;
    }

    /**
     * Evaluate a sensor value using a Wialon calibration table.
     */
    protected function evaluateTableSensor($rawValue, array $table): string
    {
        if (empty($table)) {
            return is_numeric($rawValue) ? (string) round((float) $rawValue, 2) : (string) $rawValue;
        }

        // Wialon tables are arrays of [x, a, b] where x is input, result = a*x + b
        // Or they can be simple lookup tables
        $numVal = is_numeric($rawValue) ? (float) $rawValue : 0;
        
        // Sort table by x value
        usort($table, fn($a, $b) => ($a['x'] ?? 0) <=> ($b['x'] ?? 0));
        
        // Find the appropriate range
        foreach ($table as $i => $row) {
            $x = $row['x'] ?? 0;
            if ($numVal <= $x) {
                if ($i === 0) {
                    // Before first point, use first calibration
                    $a = $row['a'] ?? 1;
                    $b = $row['b'] ?? 0;
                    return (string) round($a * $numVal + $b, 2);
                }
                
                // Interpolate between previous and current point
                $prevRow = $table[$i - 1];
                $x1 = $prevRow['x'] ?? 0;
                $y1 = ($prevRow['a'] ?? 1) * $x1 + ($prevRow['b'] ?? 0);
                $y2 = ($row['a'] ?? 1) * $x + ($row['b'] ?? 0);
                
                if ($x !== $x1) {
                    $result = $y1 + ($y2 - $y1) * ($numVal - $x1) / ($x - $x1);
                    return (string) round($result, 2);
                }
                
                return (string) round($y2, 2);
            }
        }
        
        // After last point, use last calibration
        $lastRow = end($table);
        $a = $lastRow['a'] ?? 1;
        $b = $lastRow['b'] ?? 0;
        return (string) round($a * $numVal + $b, 2);
    }

    /**
     * Detect trips and stops from processed points or match with Wialon trip results.
     */
    protected function detectTripsAndStops(array $points, array $wialonTrips, bool $useTripDetector): array
    {
        $trips = [];
        $stops = [];

        if (empty($points)) {
            return ['trips' => [], 'stops' => []];
        }

        // If trip detector is disabled, treat entire loaded interval as single continuous track
        if (!$useTripDetector) {
            $firstPt = $points[0];
            $lastPt = end($points);
            $dist = $this->calculatePointsDistance($points);
            $duration = max(1, $lastPt['t'] - $firstPt['t']);
            $maxSpd = max(array_column($points, 'speed') ?: [0]);
            $speeds = array_filter(array_column($points, 'speed') ?: [], fn($s) => $s > 0);
            $avgSpd = count($speeds) > 0 ? round(array_sum($speeds) / count($speeds), 1) : 0;

            $trips[] = [
                'id'               => 1,
                'index'            => 0,
                'name'             => 'Continuous Track',
                'start_time'       => $firstPt['t'],
                'end_time'         => $lastPt['t'],
                'start_formatted'  => date('d M Y, H:i', $firstPt['t']),
                'end_formatted'    => date('d M Y, H:i', $lastPt['t']),
                    'start_time_fmt'   => date('d M Y, H:i', $firstPt['t']),
                'end_time_fmt'     => date('d M Y, H:i', $lastPt['t']),
                'start_point'      => [$firstPt['lng'], $firstPt['lat']],
                'end_point'        => [$lastPt['lng'], $lastPt['lat']],
                'distance_km'      => round($dist, 2),
                'duration_sec'     => $duration,
                'duration_fmt'     => $this->formatDuration($duration),
                'max_speed'        => $maxSpd,
                'avg_speed'        => $avgSpd,
                'points_count'     => count($points),
                'start_idx'        => 0,
                'end_idx'          => count($points) - 1,
                'color'            => self::TRIP_COLORS[0],
            ];

            return ['trips' => $trips, 'stops' => $stops];
        }

        // Local robust trip detector algorithm:
        // A trip is a continuous movement interval (speed > 2 km/h or consecutive movement).
        // A stop/parking is detected when vehicle speed == 0 for >= 3 minutes (180 seconds).
        $currentTripPoints = [];
        $currentTripStartIdx = 0;
        $stopStartPt = null;
        $stopPoints = [];

        $stopThresholdSec = 180; // 3 minutes stationary qualifies as a Stop
        $tripIndex = 0;

        foreach ($points as $idx => $pt) {
            $isMoving = $pt['speed'] > 2;

            if ($isMoving) {
                // If we were previously stopped for longer than threshold, record stop
                if ($stopStartPt !== null) {
                    $stopDuration = $pt['t'] - $stopStartPt['t'];
                    if ($stopDuration >= $stopThresholdSec) {
                        $stops[] = [
                            'id'           => count($stops) + 1,
                            'start_time'   => $stopStartPt['t'],
                            'end_time'     => $pt['t'],
                            'start_fmt'    => date('d M Y, H:i', $stopStartPt['t']),
                            'end_fmt'      => date('d M Y, H:i', $pt['t']),
                            'duration_sec' => $stopDuration,
                            'duration_fmt' => $this->formatDuration($stopDuration),
                            'lat'          => $stopStartPt['lat'],
                            'lng'          => $stopStartPt['lng'],
                            'location'     => sprintf('%.5f, %.5f', $stopStartPt['lat'], $stopStartPt['lng']),
                        ];

                        // Close previous trip if it had significant points
                        if (count($currentTripPoints) >= 2) {
                            $tripDist = $this->calculatePointsDistance($currentTripPoints);
                            if ($tripDist >= 0.05) { // at least 50 meters
                                $first = $currentTripPoints[0];
                                $last = end($currentTripPoints);
                                $tDuration = max(1, $last['t'] - $first['t']);
                                $tSpeeds = array_filter(array_column($currentTripPoints, 'speed') ?: [], fn($s) => $s > 0);
                                $tAvg = count($tSpeeds) > 0 ? round(array_sum($tSpeeds) / count($tSpeeds), 1) : 0;
                                $tMax = max(array_column($currentTripPoints, 'speed') ?: [0]);

                                $color = self::TRIP_COLORS[$tripIndex % count(self::TRIP_COLORS)];

                                $trips[] = [
                                    'id'              => $tripIndex + 1,
                                    'index'           => $tripIndex,
                                    'name'            => 'Trip ' . ($tripIndex + 1),
                                    'start_time'      => $first['t'],
                                    'end_time'        => $last['t'],
                                    'start_formatted' => date('d M Y, H:i', $first['t']),
                                    'end_formatted'   => date('d M Y, H:i', $last['t']),
                                    'start_time_fmt'  => date('d M Y, H:i', $first['t']),
                                    'end_time_fmt'    => date('d M Y, H:i', $last['t']),
                                    'start_point'     => [$first['lng'], $first['lat']],
                                    'end_point'       => [$last['lng'], $last['lat']],
                                    'distance_km'     => round($tripDist, 2),
                                    'duration_sec'    => $tDuration,
                                    'duration_fmt'    => $this->formatDuration($tDuration),
                                    'max_speed'       => $tMax,
                                    'avg_speed'       => $tAvg,
                                    'points_count'    => count($currentTripPoints),
                                    'start_idx'       => $currentTripStartIdx,
                                    'end_idx'         => $idx - count($stopPoints),
                                    'color'           => $color,
                                ];
                                $tripIndex++;
                            }
                        }

                        $currentTripPoints = [];
                        $currentTripStartIdx = $idx;
                    }

                    $stopStartPt = null;
                    $stopPoints = [];
                }

                $currentTripPoints[] = $pt;
            } else {
                // Vehicle is stationary
                if ($stopStartPt === null) {
                    $stopStartPt = $pt;
                }
                $stopPoints[] = $pt;
                $currentTripPoints[] = $pt;
            }
        }

        // Close final trip
        if (count($currentTripPoints) >= 2) {
            $tripDist = $this->calculatePointsDistance($currentTripPoints);
            $first = $currentTripPoints[0];
            $last = end($currentTripPoints);
            $tDuration = max(1, $last['t'] - $first['t']);
            $tSpeeds = array_filter(array_column($currentTripPoints, 'speed') ?: [], fn($s) => $s > 0);
            $tAvg = count($tSpeeds) > 0 ? round(array_sum($tSpeeds) / count($tSpeeds), 1) : 0;
            $tMax = max(array_column($currentTripPoints, 'speed') ?: [0]);

            $color = self::TRIP_COLORS[$tripIndex % count(self::TRIP_COLORS)];

            $trips[] = [
                'id'              => $tripIndex + 1,
                'index'           => $tripIndex,
                'name'            => 'Trip ' . ($tripIndex + 1),
                'start_time'      => $first['t'],
                'end_time'        => $last['t'],
                'start_formatted' => date('d M Y, H:i', $first['t']),
                'end_formatted'   => date('d M Y, H:i', $last['t']),
                'start_time_fmt'  => date('d M Y, H:i', $first['t']),
                'end_time_fmt'    => date('d M Y, H:i', $last['t']),
                'start_point'     => [$first['lng'], $first['lat']],
                'end_point'       => [$last['lng'], $last['lat']],
                'distance_km'     => round($tripDist, 2),
                'duration_sec'    => $tDuration,
                'duration_fmt'    => $this->formatDuration($tDuration),
                'max_speed'       => $tMax,
                'avg_speed'       => $tAvg,
                'points_count'    => count($currentTripPoints),
                'start_idx'       => $currentTripStartIdx,
                'end_idx'         => count($points) - 1,
                'color'           => $color,
            ];
        }

        // If no trip was segmented (e.g. all points formed 1 continuous movement), create single trip
        if (empty($trips) && count($points) >= 2) {
            $firstPt = $points[0];
            $lastPt = end($points);
            $dist = $this->calculatePointsDistance($points);
            $duration = max(1, $lastPt['t'] - $firstPt['t']);
            $maxSpd = max(array_column($points, 'speed') ?: [0]);
            $speeds = array_filter(array_column($points, 'speed') ?: [], fn($s) => $s > 0);
            $avgSpd = count($speeds) > 0 ? round(array_sum($speeds) / count($speeds), 1) : 0;

            $trips[] = [
                'id'              => 1,
                'index'           => 0,
                'name'            => 'Trip 1',
                'start_time'      => $firstPt['t'],
                'end_time'        => $lastPt['t'],
                'start_formatted' => date('d M Y, H:i', $firstPt['t']),
                'end_formatted'   => date('d M Y, H:i', $lastPt['t']),
                'start_time_fmt'  => date('d M Y, H:i', $firstPt['t']),
                'end_time_fmt'    => date('d M Y, H:i', $lastPt['t']),
                'start_point'     => [$firstPt['lng'], $firstPt['lat']],
                'end_point'       => [$lastPt['lng'], $lastPt['lat']],
                'distance_km'     => round($dist, 2),
                'duration_sec'    => $duration,
                'duration_fmt'    => $this->formatDuration($duration),
                'max_speed'       => $maxSpd,
                'avg_speed'       => $avgSpd,
                'points_count'    => count($points),
                'start_idx'       => 0,
                'end_idx'         => count($points) - 1,
                'color'           => self::TRIP_COLORS[0],
            ];
        }

        return ['trips' => $trips, 'stops' => $stops];
    }

    /**
     * Map each GPS point to its enclosing trip index and color.
     */
    protected function assignTripsToPoints(array &$points, array $trips): void
    {
        foreach ($trips as $trip) {
            $sTime = $trip['start_time'];
            $eTime = $trip['end_time'];
            $idx = $trip['index'];
            $color = $trip['color'];

            for ($i = 0; $i < count($points); $i++) {
                if ($points[$i]['t'] >= $sTime && $points[$i]['t'] <= $eTime) {
                    $points[$i]['trip_idx'] = $idx;
                    $points[$i]['trip_color'] = $color;
                }
            }
        }
    }

    /**
     * Calculate track summary metrics.
     */
    protected function calculateTrackMetrics(array $points, array $trips, array $stops): array
    {
        if (empty($points)) {
            return [
                'total_mileage_km'    => 0,
                'total_duration_sec'  => 0,
                'moving_duration_sec' => 0,
                'stop_duration_sec'   => 0,
                'max_speed'           => 0,
                'avg_speed'           => 0,
                'max_speed_point'     => null,
            ];
        }

        $totalMileage = $this->calculatePointsDistance($points);
        $first = $points[0];
        $last = end($points);
        $totalDuration = max(0, $last['t'] - $first['t']);

        $stopDuration = 0;
        foreach ($stops as $stop) {
            $stopDuration += $stop['duration_sec'] ?? 0;
        }

        $movingDuration = max(0, $totalDuration - $stopDuration);

        $maxSpeed = 0;
        $maxSpeedPoint = null;
        $movingSpeeds = [];

        foreach ($points as $pt) {
            if ($pt['speed'] > $maxSpeed) {
                $maxSpeed = $pt['speed'];
                $maxSpeedPoint = $pt;
            }
            if ($pt['speed'] > 0) {
                $movingSpeeds[] = $pt['speed'];
            }
        }

        $avgSpeed = count($movingSpeeds) > 0 ? round(array_sum($movingSpeeds) / count($movingSpeeds), 1) : 0;

        return [
            'total_mileage_km'    => round($totalMileage, 2),
            'total_duration_sec'  => $totalDuration,
            'moving_duration_sec' => $movingDuration,
            'stop_duration_sec'   => $stopDuration,
            'max_speed'           => $maxSpeed,
            'avg_speed'           => $avgSpeed,
            'max_speed_point'     => $maxSpeedPoint,
        ];
    }

    /**
     * Build annotations pins for the map (Trip Starts, Trip Ends, Stops, Max Speed).
     */
    protected function buildAnnotations(array $trips, array $stops, array $points, ?array $maxSpeedPoint): array
    {
        $annotations = [];

        // 1. Overall Track Start
        if (!empty($points)) {
            $first = $points[0];
            $annotations[] = [
                'id'          => 'start',
                'type'        => 'track_start',
                'title'       => 'Track Start',
                'description' => 'Route started at ' . $first['date_fmt'],
                'lat'         => $first['lat'],
                'lng'         => $first['lng'],
                'time'        => $first['t'],
                'time_fmt'    => $first['time'],
                'icon'        => 'ri-flag-fill',
                'color'       => '#10b981',
            ];
        }

        // 2. Trips start / end annotations
        foreach ($trips as $trip) {
            $annotations[] = [
                'id'          => 'trip_start_' . $trip['id'],
                'type'        => 'trip_start',
                'title'       => $trip['name'] . ' Start',
                'description' => 'Departed: ' . $trip['start_time_fmt'] . ' | Distance: ' . $trip['distance_km'] . ' km',
                'lat'         => $trip['start_point'][1],
                'lng'         => $trip['start_point'][0],
                'time'        => $trip['start_time'],
                'time_fmt'    => $trip['start_time_fmt'],
                'icon'        => 'ri-play-circle-fill',
                'color'       => $trip['color'] ?? '#2563eb',
            ];

            $annotations[] = [
                'id'          => 'trip_end_' . $trip['id'],
                'type'        => 'trip_end',
                'title'       => $trip['name'] . ' End',
                'description' => 'Arrived: ' . $trip['end_time_fmt'] . ' | Duration: ' . $trip['duration_fmt'],
                'lat'         => $trip['end_point'][1],
                'lng'         => $trip['end_point'][0],
                'time'        => $trip['end_time'],
                'time_fmt'    => $trip['end_time_fmt'],
                'icon'        => 'ri-checkbox-circle-fill',
                'color'       => $trip['color'] ?? '#2563eb',
            ];
        }

        // 3. Stops & Parkings
        foreach ($stops as $stop) {
            $annotations[] = [
                'id'          => 'stop_' . $stop['id'],
                'type'        => 'stop',
                'title'       => 'Vehicle Stop (' . $stop['duration_fmt'] . ')',
                'description' => 'Stopped: ' . $stop['start_fmt'] . ' - ' . $stop['end_fmt'],
                'lat'         => $stop['lat'],
                'lng'         => $stop['lng'],
                'time'        => $stop['start_time'],
                'time_fmt'    => $stop['start_fmt'],
                'icon'        => 'ri-parking-box-fill',
                'color'       => '#f59e0b',
            ];
        }

        // 4. Max Speed event
        if ($maxSpeedPoint && $maxSpeedPoint['speed'] > 20) {
            $annotations[] = [
                'id'          => 'max_speed',
                'type'        => 'speed_event',
                'title'       => 'Max Speed: ' . $maxSpeedPoint['speed'] . ' km/h',
                'description' => 'Recorded at ' . $maxSpeedPoint['time'],
                'lat'         => $maxSpeedPoint['lat'],
                'lng'         => $maxSpeedPoint['lng'],
                'time'        => $maxSpeedPoint['t'],
                'time_fmt'    => $maxSpeedPoint['time'],
                'icon'        => 'ri-speed-up-fill',
                'color'       => '#ef4444',
            ];
        }

        // 5. Track End
        if (!empty($points) && count($points) > 1) {
            $last = end($points);
            $annotations[] = [
                'id'          => 'end',
                'type'        => 'track_end',
                'title'       => 'Track End',
                'description' => 'Finished at ' . $last['date_fmt'],
                'lat'         => $last['lat'],
                'lng'         => $last['lng'],
                'time'        => $last['t'],
                'time_fmt'    => $last['time'],
                'icon'        => 'ri-stop-circle-fill',
                'color'       => '#ef4444',
            ];
        }

        return $annotations;
    }

    /**
     * Calculate cumulative distance between consecutive coordinates using Haversine formula.
     */
    protected function calculatePointsDistance(array $points): float
    {
        $totalKm = 0.0;
        $count = count($points);

        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i + 1];

            $totalKm += $this->haversineDistance($p1['lat'], $p1['lng'], $p2['lat'], $p2['lng']);
        }

        return $totalKm;
    }

    /**
     * Format duration seconds to human-readable string (e.g., '2h 15m 30s').
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0s';
        }

        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        $parts = [];
        if ($h > 0) $parts[] = "{$h}h";
        if ($m > 0 || $h > 0) $parts[] = "{$m}m";
        if ($s > 0 || empty($parts)) $parts[] = "{$s}s";

        return implode(' ', $parts);
    }

    /**
     * Format vehicle information for client-side display.
     */
    protected function formatVehicleData(?Vehicle $vehicle, array $unitInfo): array
    {
        $driverName = '—';
        if ($vehicle && $vehicle->drivers && $vehicle->drivers->isNotEmpty()) {
            $d = $vehicle->drivers->first();
            $driverName = trim(($d->first_name ?? '') . ' ' . ($d->last_name ?? ''));
        }

        $makeModel = $vehicle?->vehicle_make_model ? $vehicle->vehicle_make_model->name : 'Fleet Vehicle';

        return [
            'id'          => (string) ($vehicle?->id ?? $unitInfo['id']),
            'fleet_no'    => $vehicle?->fleet_no ?? $unitInfo['name'] ?? 'Asset',
            'truck_no'    => $vehicle?->truck_no ?? 'N/A',
            'unit_id'     => $unitInfo['id'],
            'unit_name'   => $unitInfo['name'],
            'driver_name' => $driverName,
            'make_model'  => $makeModel,
            'type'        => $vehicle?->vehicle_type ?? 'Truck',
            'status'      => $vehicle?->vehicle_status ?? 'Active',
            'imei'        => $vehicle?->gps_imei ?? $unitInfo['uid'] ?? '',
        ];
    }
}
