<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\WialonReportTemplate;
use App\Models\WialonUnitGroup;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WialonReportService extends WialonService
{
    protected const DATAFLAG_BASE            = 0x00000001; // 1
    protected const DATAFLAG_REPORTS         = 0x00002000; // 8192 — report templates
    protected const DATAFLAG_BASE_AND_REPORTS = self::DATAFLAG_BASE | self::DATAFLAG_REPORTS; // 8193

    /** Export format bitmask values, per Wialon report/export_result docs. */
    public const EXPORT_FORMAT_HTML = 1;
    public const EXPORT_FORMAT_PDF  = 2;
    public const EXPORT_FORMAT_XLS  = 4;
    public const EXPORT_FORMAT_XLSX = 8;
    public const EXPORT_FORMAT_XML  = 16;
    public const EXPORT_FORMAT_CSV  = 32;

    /**
     * Single main synchronization method coordinating Templates, Units (Vehicles), and Unit Groups.
     * Full reconciliation with Create + Update + Delete support.
     * Protected against API failures (fetches all data first before touching DB).
     *
     * @return array
     */
    public function syncWialon(): array
    {
        Log::info('Starting full Wialon synchronization (templates, units, groups)...');

        // 1. FAILED SYNC PROTECTION:
        // Fetch all authoritative datasets from Wialon API first.
        // If any call fails with auth/network error, an exception is thrown BEFORE any DB operation.
        $rawTemplates = $this->fetchAuthoritativeTemplates();
        $rawUnits     = $this->fetchAuthoritativeUnits();
        $rawGroups    = $this->fetchAuthoritativeUnitGroups();

        // 2. DATABASE TRANSACTION:
        // Perform complete reconciliation atomically. Roll back on any failure.
        return DB::transaction(function () use ($rawTemplates, $rawUnits, $rawGroups) {
            $templateStats = $this->reconcileTemplates($rawTemplates);
            $unitStats     = $this->reconcileUnits($rawUnits);
            $groupStats    = $this->reconcileUnitGroups($rawGroups);

            // Fetch fresh reconciled datasets to hydrate frontend immediately
            $templates = WialonReportTemplate::active()->orderBy('name')->get();
            $vehicles  = Vehicle::whereNotNull('gps_unit_id')
                ->where('gps_unit_id', '!=', '')
                ->where('status', '!=', '0')
                ->orderBy('fleet_no')
                ->get(['id', 'fleet_no', 'truck_no', 'gps_unit_id']);
            $groups    = WialonUnitGroup::active()->orderBy('name')->get();

            $templatesPayload = $templates->map(fn (WialonReportTemplate $t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'resourceId'   => (int) $t->wialon_resource_id,
                'templateId'   => (int) $t->wialon_template_id,
                'templateType' => $t->type,
                'objectType'   => $t->normalized_type,
            ])->values()->all();

            $vehiclesPayload = $vehicles->map(fn (Vehicle $v) => [
                'id'           => (int) $v->gps_unit_id,
                'fleet_no'     => $v->fleet_no,
                'truck_no'     => $v->truck_no,
                'display_name' => $v->fleet_no . ($v->truck_no ? " ({$v->truck_no})" : ''),
            ])->values()->all();

            $groupsPayload = $groups->map(fn (WialonUnitGroup $g) => [
                'id'           => (int) $g->wialon_group_id,
                'name'         => $g->name,
                'units_count'  => (int) $g->units_count,
                'display_name' => $g->name . " ({$g->units_count} units)",
            ])->values()->all();

            Log::info('Wialon synchronization completed successfully', [
                'templates' => $templateStats,
                'vehicles'  => $unitStats,
                'groups'    => $groupStats,
            ]);

            return [
                'success'   => true,
                'message'   => 'Wialon synchronized successfully.',
                'templates' => $templateStats,
                'vehicles'  => $unitStats,
                'groups'    => $groupStats,
                'data'      => [
                    'templates' => $templatesPayload,
                    'vehicles'  => $vehiclesPayload,
                    'groups'    => $groupsPayload,
                ],
            ];
        });
    }

    /**
     * Fetch authoritative report templates across all accessible Wialon resources.
     *
     * @return array<int, array{resource_id: int, template_id: int, name: string, type: ?string}>
     */
    public function fetchAuthoritativeTemplates(): array
    {
        $resourceIds = $this->getAllWialonResourceIds();

        if (empty($resourceIds)) {
            Log::info('No Wialon resources found or accessible');
            return [];
        }

        $allTemplates = [];

        foreach ($resourceIds as $resId) {
            $data = $this->request('core/search_item', [
                'id'    => $resId,
                'flags' => self::DATAFLAG_BASE_AND_REPORTS,
            ]);

            $item = $data['item'] ?? null;
            if (!$item) {
                continue;
            }

            // Wialon returns templates under 'rep' as { "<templateId>": { "n": "Name", "ct": "avl_unit", ... } }
            $rawTemplates = $item['rep'] ?? [];

            foreach ($rawTemplates as $templateId => $template) {
                $allTemplates[] = [
                    'resource_id' => (int) $resId,
                    'template_id' => (int) $templateId,
                    'name'        => trim((string) ($template['n'] ?? ('Template ' . $templateId))),
                    'type'        => isset($template['ct']) ? trim((string) $template['ct']) : null,
                ];
            }
        }

        return $allTemplates;
    }

    /**
     * Fetch authoritative units (vehicles) from Wialon API.
     *
     * @return array<int, array{id: int, name: string, imei: ?string, hw_type_id: ?int}>
     */
    public function fetchAuthoritativeUnits(): array
    {
        $data = $this->request('core/search_items', [
            'spec' => [
                'itemsType'     => 'avl_unit',
                'propName'      => 'sys_name',
                'propValueMask' => '*',
                'sortType'      => 'sys_name',
            ],
            'force' => 1,
            'flags' => 0x00000001 | 0x00000100, // base properties (id, nm, hw) + config (uid/imei)
            'from'  => 0,
            'to'    => 0,
        ]);

        $items = $data['items'] ?? [];
        $units = [];

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            $name = trim((string) ($item['nm'] ?? ''));
            if ($id > 0 && $name !== '') {
                $units[] = [
                    'id'         => $id,
                    'name'       => $name,
                    'imei'       => isset($item['uid']) && trim((string) $item['uid']) !== '' ? trim((string) $item['uid']) : null,
                    'hw_type_id' => isset($item['hw']) && is_numeric($item['hw']) ? (int) $item['hw'] : null,
                ];
            }
        }

        return $units;
    }

    /**
     * Fetch authoritative unit groups from Wialon API.
     *
     * @return array<int, array{id: int, name: string, units_count: int}>
     */
    public function fetchAuthoritativeUnitGroups(): array
    {
        $data = $this->request('core/search_items', [
            'spec' => [
                'itemsType'     => 'avl_unit_group',
                'propName'      => 'sys_name',
                'propValueMask' => '*',
                'sortType'      => 'sys_name',
            ],
            'force' => 1,
            'flags' => 0x00000001 | 0x00000100, // base properties + units array (u)
            'from'  => 0,
            'to'    => 0,
        ]);

        $items = $data['items'] ?? [];
        $groups = [];

        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            $name = trim((string) ($item['nm'] ?? ''));
            if ($id > 0 && $name !== '') {
                $unitsList = $item['u'] ?? [];
                $groups[] = [
                    'id'          => $id,
                    'name'        => $name,
                    'units_count' => is_array($unitsList) ? count($unitsList) : 0,
                ];
            }
        }

        return $groups;
    }

    /**
     * Reconcile report templates table: CREATE + UPDATE + DELETE.
     * Stable identity: (wialon_resource_id, wialon_template_id).
     *
     * @param array<int, array{resource_id: int, template_id: int, name: string, type: ?string}> $authoritativeTemplates
     * @return array{created: int, updated: int, deleted: int}
     */
    public function reconcileTemplates(array $authoritativeTemplates): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deleted' => 0];
        $seenKeys = [];
        $now = now();

        foreach ($authoritativeTemplates as $t) {
            $key = $t['resource_id'] . ':' . $t['template_id'];
            $seenKeys[$key] = true;

            $existing = WialonReportTemplate::where('wialon_resource_id', $t['resource_id'])
                ->where('wialon_template_id', $t['template_id'])
                ->first();

            if ($existing) {
                $existing->forceFill([
                    'name'      => $t['name'],
                    'type'      => $t['type'],
                    'synced_at' => $now,
                    'status'    => '1',
                ])->save();
                $stats['updated']++;
            } else {
                WialonReportTemplate::create([
                    'wialon_resource_id' => $t['resource_id'],
                    'wialon_template_id' => $t['template_id'],
                    'name'               => $t['name'],
                    'type'               => $t['type'],
                    'synced_at'          => $now,
                    'status'             => '1',
                ]);
                $stats['created']++;
            }
        }

        // DELETE / DEACTIVATE templates that no longer exist in Wialon
        $localTemplates = WialonReportTemplate::all();
        foreach ($localTemplates as $local) {
            $key = $local->wialon_resource_id . ':' . $local->wialon_template_id;
            if (!isset($seenKeys[$key])) {
                $local->delete();
                $stats['deleted']++;
            }
        }

        return $stats;
    }

    /**
     * Reconcile vehicles with Wialon units: CREATE + UPDATE + DELETE/DEACTIVATE.
     * Preserves local application-created vehicle records that are not Wialon-managed.
     *
     * @param array<int, array{id: int, name: string, imei: ?string, hw_type_id?: ?int}> $authoritativeUnits
     * @return array{created: int, updated: int, deleted: int}
     */
     public function reconcileUnits(array $authoritativeUnits): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deleted' => 0];
        $authoritativeUnitIds = [];
        $now = now();

        $unitsById   = [];
        $unitsByName = [];
        $unitsByImei = [];

        foreach ($authoritativeUnits as $u) {
            $authoritativeUnitIds[$u['id']] = true;
            $unitsById[$u['id']] = $u;
            $unitsByName[mb_strtoupper(trim($u['name']))][] = $u;
            if ($u['imei']) {
                $unitsByImei[mb_strtoupper(trim($u['imei']))][] = $u;
            }
        }

        $matchedUnitIds = [];

        // 1. Check existing vehicles with gps_unit_id
        $vehiclesWithGps = Vehicle::whereNotNull('gps_unit_id')->where('gps_unit_id', '!=', '')->get();
        foreach ($vehiclesWithGps as $vehicle) {
            $unitId = (int) $vehicle->gps_unit_id;

            if (isset($unitsById[$unitId])) {
                $unit = $unitsById[$unitId];
                $matchedUnitIds[$unitId] = $vehicle->id;

                $updateData = [
                    'gps_hw_type_id'  => $unit['hw_type_id'] ?? $vehicle->gps_hw_type_id,
                    'gps_imei'        => $unit['imei'] ?? $vehicle->gps_imei,
                    'gps_provider'    => 'wialon',
                    'gps_sync_status' => 'synced',
                    'last_sync_at'    => $now,
                ];

                if ($vehicle->gps_provider === 'wialon' || empty($vehicle->fleet_no)) {
                    $updateData['fleet_no'] = $unit['name'];
                }

                $vehicle->forceFill($updateData)->save();

                $stats['updated']++;
            } else {
                // Vehicle was linked to a Wialon unit that is now deleted in Wialon
                if ($vehicle->gps_provider === 'wialon') {
                    // Only unlink Wialon GPS properties to protect any local vehicle data
                    $vehicle->forceFill([
                        'gps_unit_id'     => null,
                        'gps_sync_status' => 'pending',
                        'last_sync_at'    => $now,
                    ])->save();
                    $stats['deleted']++;
                }
            }
        }

        $defaultMakeModelId = \App\Models\VehicleMakeModel::value('id');
        $defaultVehicleTypeId = \App\Models\VehicleType::value('id');

        // 2. For unmatched Wialon units, attempt to match unlinked local vehicles by name or IMEI
        foreach ($authoritativeUnits as $unit) {
            if (isset($matchedUnitIds[$unit['id']])) {
                continue;
            }

            $normalName = mb_strtoupper(trim($unit['name']));
            $normalImei = $unit['imei'] ? mb_strtoupper(trim($unit['imei'])) : null;

            $localVehicle = Vehicle::where(function ($q) use ($normalName, $normalImei) {
                $q->whereRaw('UPPER(TRIM(fleet_no)) = ?', [$normalName])
                  ->orWhereRaw('UPPER(TRIM(truck_no)) = ?', [$normalName]);
                if ($normalImei) {
                    $q->orWhereRaw('UPPER(TRIM(gps_imei)) = ?', [$normalImei]);
                }
            })->where(function ($q) {
                $q->whereNull('gps_unit_id')->orWhere('gps_unit_id', '');
            })->first();

            if ($localVehicle) {
                $localVehicle->forceFill([
                    'gps_unit_id'     => $unit['id'],
                    'gps_hw_type_id'  => $unit['hw_type_id'] ?? $localVehicle->gps_hw_type_id,
                    'gps_imei'        => $unit['imei'] ?? $localVehicle->gps_imei,
                    'gps_provider'    => 'wialon',
                    'gps_sync_status' => 'synced',
                    'status'          => '1',
                    'last_sync_at'    => $now,
                ])->save();

                $matchedUnitIds[$unit['id']] = $localVehicle->id;
                $stats['updated']++;
            } else {
                // 3. Create a new vehicle record for this Wialon unit
                Vehicle::create([
                    'fleet_no'                    => $unit['name'],
                    'truck_no'                    => $unit['name'],
                    'manager_first_name'          => 'Wialon',
                    'manager_last_name'           => 'Unit',
                    'manager_email'               => 'wialon@unit.local',
                    'owner_first_name'            => 'Wialon',
                    'owner_last_name'             => 'Owner',
                    'vehicle_make_model_id'       => $defaultMakeModelId,
                    'vehicle_type_id'             => $defaultVehicleTypeId,
                    'base_site'                   => 'Main Depot',
                    'last_service_date'           => $now,
                    'next_service_due_date'       => $now->copy()->addMonths(6),
                    'insurance_expiry_date'       => $now->copy()->addYear(),
                    'vehicle_license_expiry_date' => $now->copy()->addYear(),
                    'gps_unit_id'                 => $unit['id'],
                    'gps_hw_type_id'              => $unit['hw_type_id'] ?? null,
                    'gps_imei'                    => $unit['imei'],
                    'gps_provider'                => 'wialon',
                    'gps_status'                  => true,
                    'gps_sync_status'             => 'synced',
                    'status'                      => '1',
                    'last_sync_at'                => $now,
                ]);

                $matchedUnitIds[$unit['id']] = true;
                $stats['created']++;
            }
        }

        return $stats;
    }

    /**
     * Reconcile unit groups table: CREATE + UPDATE + DELETE.
     * Stable identity: wialon_group_id.
     *
     * @param array<int, array{id: int, name: string, units_count: int}> $authoritativeGroups
     * @return array{created: int, updated: int, deleted: int}
     */
    public function reconcileUnitGroups(array $authoritativeGroups): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'deleted' => 0];
        $seenGroupIds = [];
        $now = now();

        foreach ($authoritativeGroups as $g) {
            $seenGroupIds[$g['id']] = true;

            $existing = WialonUnitGroup::where('wialon_group_id', $g['id'])->first();

            if ($existing) {
                $existing->forceFill([
                    'name'        => $g['name'],
                    'units_count' => $g['units_count'],
                    'synced_at'   => $now,
                    'status'      => '1',
                ])->save();
                $stats['updated']++;
            } else {
                WialonUnitGroup::create([
                    'wialon_group_id' => $g['id'],
                    'name'            => $g['name'],
                    'units_count'     => $g['units_count'],
                    'synced_at'       => $now,
                    'status'          => '1',
                ]);
                $stats['created']++;
            }
        }

        // DELETE groups that no longer exist in Wialon
        $localGroups = WialonUnitGroup::all();
        foreach ($localGroups as $local) {
            if (!isset($seenGroupIds[$local->wialon_group_id])) {
                $local->delete();
                $stats['deleted']++;
            }
        }

        return $stats;
    }

    /**
     * Helper to sync report templates individually.
     *
     * @return \Illuminate\Support\Collection<WialonReportTemplate>
     */
    public function syncReportTemplates(?int $resourceId = null): \Illuminate\Support\Collection
    {
        $rawTemplates = $this->fetchAuthoritativeTemplates();
        if ($resourceId !== null) {
            $rawTemplates = array_values(array_filter($rawTemplates, fn ($t) => $t['resource_id'] === $resourceId));
        }

        DB::transaction(fn () => $this->reconcileTemplates($rawTemplates));

        return WialonReportTemplate::active()->orderBy('name')->get();
    }

    /**
     * Helper to sync units (vehicles) individually.
     *
     * @return \Illuminate\Support\Collection<Vehicle>
     */
    public function syncUnits(): \Illuminate\Support\Collection
    {
        $rawUnits = $this->fetchAuthoritativeUnits();
        DB::transaction(fn () => $this->reconcileUnits($rawUnits));

        return Vehicle::whereNotNull('gps_unit_id')
            ->where('gps_unit_id', '!=', '')
            ->where('status', '!=', '0')
            ->orderBy('fleet_no')
            ->get();
    }

    /**
     * Helper to sync unit groups individually.
     *
     * @return \Illuminate\Support\Collection<WialonUnitGroup>
     */
    public function syncUnitGroups(): \Illuminate\Support\Collection
    {
        $rawGroups = $this->fetchAuthoritativeUnitGroups();
        DB::transaction(fn () => $this->reconcileUnitGroups($rawGroups));

        return WialonUnitGroup::active()->orderBy('name')->get();
    }

    /**
     * Get all accessible Wialon Resource IDs.
     *
     * @return array<int>
     */
    public function getAllWialonResourceIds(): array
    {
        try {
            $data = $this->request('core/search_items', [
                'spec' => [
                    'itemsType'     => 'avl_resource',
                    'propName'      => 'sys_name',
                    'propValueMask' => '*',
                    'sortType'      => 'sys_name',
                ],
                'force' => 1,
                'flags' => 0x00000001, // base info only
                'from'  => 0,
                'to'    => 0,
            ]);

            $items = $data['items'] ?? [];
            $ids = [];
            foreach ($items as $item) {
                if (isset($item['id'])) {
                    $ids[] = (int) $item['id'];
                }
            }
            return $ids;
        } catch (\Throwable $e) {
            Log::error('Failed to fetch Wialon resource IDs', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fetch all active units (vehicles) from Wialon live.
     *
     * @return array<int, array{gps_unit_id: int, fleet_no: string, truck_no: string}>
     */
    public function getWialonUnits(): array
    {
        try {
            $authoritative = $this->fetchAuthoritativeUnits();
            return array_map(fn ($u) => [
                'gps_unit_id' => $u['id'],
                'fleet_no'    => $u['name'],
                'truck_no'    => 'Wialon Unit',
            ], $authoritative);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch Wialon units live', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fetch all active unit groups from Wialon live or local DB.
     *
     * @return array<int, array{id: int, name: string, units_count: int}>
     */
    public function getWialonUnitGroups(): array
    {
        try {
            return $this->fetchAuthoritativeUnitGroups();
        } catch (\Throwable $e) {
            Log::error('Failed to fetch Wialon unit groups live, using local DB', [
                'error' => $e->getMessage(),
            ]);

            return WialonUnitGroup::active()->orderBy('name')->get()->map(fn ($g) => [
                'id'          => (int) $g->wialon_group_id,
                'name'        => $g->name,
                'units_count' => (int) $g->units_count,
            ])->all();
        }
    }

    /**
     * Execute Wialon report for a specific object (Unit or Unit Group).
     *
     * @param int $resourceId
     * @param int $templateId
     * @param int $objectId Unit ID or Unit Group ID
     * @param int $fromTimestamp Unix timestamp
     * @param int $toTimestamp Unix timestamp
     * @return array
     */
    public function executeReport(
        int $resourceId,
        int $templateId,
        int $objectId,
        int $fromTimestamp,
        int $toTimestamp
    ): array {
        $this->cleanupReport();

        Log::info('Wialon report/exec_report', [
            'resource_id' => $resourceId,
            'template_id' => $templateId,
            'object_id'   => $objectId,
            'from'        => $fromTimestamp,
            'to'          => $toTimestamp,
        ]);

        try {
            $result = $this->request('report/exec_report', [
                'reportResourceId'  => $resourceId,
                'reportTemplateId'  => $templateId,
                'reportObjectId'    => $objectId,
                'reportObjectSecId' => 0,
                'interval'          => [
                    'from'  => $fromTimestamp,
                    'to'    => $toTimestamp,
                    'flags' => 0,
                ],
            ]);

            if (empty($result)) {
                throw new \RuntimeException('Wialon returned empty response for report execution');
            }

            return $result;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Wialon connection timeout during report execution', [
                'message'     => $e->getMessage(),
                'resource_id' => $resourceId,
            ]);
            throw new \RuntimeException('Connection to Wialon server timed out. Please try a shorter time range.');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Wialon HTTP request failed during report execution', [
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
            ]);
            throw new \RuntimeException('Failed to communicate with Wialon server. Please try again later.');
        } catch (\Throwable $e) {
            Log::error('Wialon report execution error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * List the tables present in the currently loaded report result.
     */
    public function getReportTables(): array
    {
        $data = $this->request('report/get_report_tables', []);

        return $data ?? [];
    }

    /**
     * Get result rows from a report table.
     */
    public function getReportRows(int $tableIndex, int $indexFrom, int $indexTo, int $level = 0): array
    {
        try {
            $data = $this->request('report/get_result_rows', [
                'tableIndex' => $tableIndex,
                'indexFrom'  => $indexFrom,
                'indexTo'    => $indexTo,
                'level'      => $level,
            ]);

            return $data ?? [];
        } catch (\Throwable $e) {
            Log::error('Failed to fetch report rows', [
                'tableIndex' => $tableIndex,
                'error'      => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fetch chart series data for the currently loaded report result.
     */
    public function getReportChart(int $tableIndex, int $from, int $to, int $level = 0): array
    {
        $data = $this->request('report/get_result_chart', [
            'tableIndex' => $tableIndex,
            'from'       => $from,
            'to'         => $to,
            'level'      => $level,
        ]);

        return $data ?? [];
    }

    /**
     * Fetch map-related points/routes for the currently loaded report result.
     */
    public function getReportMap(int $tableIndex, int $indexFrom, int $indexTo, int $level = 0): array
    {
        $data = $this->request('report/get_result_map', [
            'tableIndex' => $tableIndex,
            'indexFrom'  => $indexFrom,
            'indexTo'    => $indexTo,
            'level'      => $level,
        ]);

        return $data ?? [];
    }

    /**
     * Export the currently loaded report result to a file and return raw bytes.
     *
     * @param int    $format One of the EXPORT_FORMAT_* constants
     * @param string $outputFileName
     * @return array{contents: string, contentType: string}
     */
    public function exportReport(int $format, string $outputFileName = 'Report'): array
    {
        if (!$this->sessionId) {
            $this->sessionId = $this->login();
        }

        $response = Http::timeout(60)->get($this->host . '/wialon/ajax.html', [
            'svc'    => 'report/export_result',
            'params' => json_encode([
                'format'         => $format,
                'outputFileName' => $outputFileName,
                'headings'       => '1',
                'compress'       => 0,
            ]),
            'sid'    => $this->sessionId,
        ]);

        if (!$response->successful()) {
            Log::error('Wialon report/export_result HTTP failure', [
                'status' => $response->status(),
            ]);
            throw new \RuntimeException("Wialon export failed with HTTP {$response->status()}");
        }

        return [
            'contents'    => $response->body(),
            'contentType' => $response->header('Content-Type') ?? 'application/octet-stream',
        ];
    }

    /**
     * Load raw messages (positions) for the unit in the given interval to map the route.
     */
    public function getUnitRouteCoordinates(int $unitId, int $fromTimestamp, int $toTimestamp): array
    {
        try {
            $data = $this->request('messages/load_interval', [
                'itemId'    => $unitId,
                'timeFrom'  => $fromTimestamp,
                'timeTo'    => $toTimestamp,
                'flags'     => 0x00000001, // position information
                'flagsMask' => 0x00000001,
                'loadCount' => 2000,
            ]);

            $messages = $data['messages'] ?? [];
            $points = [];

            foreach ($messages as $msg) {
                if (isset($msg['pos']) && isset($msg['pos']['x']) && isset($msg['pos']['y'])) {
                    $points[] = [
                        'lng' => (double) $msg['pos']['x'],
                        'lat' => (double) $msg['pos']['y'],
                        't'   => isset($msg['t']) ? date('Y-m-d H:i:s', $msg['t']) : null,
                    ];
                }
            }

            return $points;
        } catch (\Throwable $e) {
            Log::warning('Failed to load unit route coordinates from Wialon', [
                'unit_id' => $unitId,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Clear the report result from the current session.
     */
    public function cleanupReport(): void
    {
        try {
            $this->request('report/cleanup_result', []);
        } catch (\Throwable $e) {
            Log::debug('Wialon report/cleanup_result no-op or failed harmlessly', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
