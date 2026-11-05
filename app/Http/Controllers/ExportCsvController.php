<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCsvController extends Controller
{
    /**
     * Check if a column should be excluded from export/UI selection.
     */
    private function isExcludedColumn(string $column): bool
    {
        $col = strtolower(trim($column));

        $exactExclude = [
            'id',
            'password',
            'remember_token',
        ];

        if (in_array($col, $exactExclude, true)) {
            return true;
        }

        // Hide foreign key columns and IDs ending with '_id' (e.g. vehicle_id, driver_id, user_id, waybill_id)
        if (str_ends_with($col, '_id')) {
            return true;
        }

        // Hide audit/foreign key user references ending with '_by' (e.g. created_by, updated_by, client_approved_by)
        if (str_ends_with($col, '_by')) {
            return true;
        }

        return false;
    }

    /**
     * Fetch all raw column names directly from INFORMATION_SCHEMA.
     */
    private function getRawTableColumns(string $table): array
    {
        $dbName = DB::connection()->getDatabaseName();
        $results = DB::select(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION',
            [$dbName, $table]
        );

        return array_map(fn ($col) => $col->COLUMN_NAME, $results);
    }

    /**
     * Fetch sanitized column names for export selection, applying exclusion filters.
     */
    private function getTableColumns(string $table, array $exclude = []): array
    {
        $rawColumns = $this->getRawTableColumns($table);

        return array_values(
            array_filter(
                $rawColumns,
                function ($col) use ($exclude) {
                    if (in_array($col, $exclude, true)) {
                        return false;
                    }

                    return ! $this->isExcludedColumn($col);
                }
            )
        );
    }

    private function tableExists(string $table): bool
    {
        $dbName = DB::connection()->getDatabaseName();
        $result = DB::select(
            'SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$dbName, $table]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return in_array($column, $this->getRawTableColumns($table), true);
    }

    private function hasDriverRelatedColumn(string $table): bool
    {
        return $this->tableHasColumn($table, 'driver_id')
            || $this->tableHasColumn($table, 'waybill_id')
            || $this->tableHasColumn($table, 'vehicle_id')
            || $this->tableHasColumn($table, 'vehicle_maintenance_id')
            || $table === 'vehicles';
    }

    private function shouldAddDriverNameColumn(string $table): bool
    {
        if ($this->tableHasColumn($table, 'driver_name')) {
            return false;
        }

        return $this->hasDriverRelatedColumn($table);
    }

    private function hasVehicleRelatedColumn(string $table): bool
    {
        return $this->tableHasColumn($table, 'vehicle_id')
            || $this->tableHasColumn($table, 'waybill_id')
            || $this->tableHasColumn($table, 'vehicle_maintenance_id');
    }

    private function shouldAddFleetNoColumn(string $table): bool
    {
        if ($this->tableHasColumn($table, 'fleet_no')) {
            return false;
        }

        return $this->hasVehicleRelatedColumn($table);
    }

    private function shouldAddTruckNoColumn(string $table): bool
    {
        if ($this->tableHasColumn($table, 'truck_no')) {
            return false;
        }

        return $this->hasVehicleRelatedColumn($table);
    }

    private function shouldAddWaybillNumberColumn(string $table): bool
    {
        if ($this->tableHasColumn($table, 'waybill_number')) {
            return false;
        }

        return $this->tableHasColumn($table, 'waybill_id');
    }

    private function shouldAddMakeModelColumn(string $table): bool
    {
        if ($this->tableHasColumn($table, 'vehicle_make_model')) {
            return false;
        }

        return $this->tableHasColumn($table, 'vehicle_make_model_id');
    }

    private function shouldAddVehicleTypeColumn(string $table): bool
    {
        if ($this->tableHasColumn($table, 'vehicle_type')) {
            return false;
        }

        return $this->tableHasColumn($table, 'vehicle_type_id');
    }

    private function sanitizeTableName(string $table): string
    {
        return str_replace('`', '', $table);
    }

    private function getDriverNameColumnSql(string $table): string
    {
        $table = $this->sanitizeTableName($table);
        $pieces = [];

        if ($this->tableHasColumn($table, 'waybill_id')) {
            $pieces[] = "(
                SELECT CONCAT(d.first_name, ' ', d.last_name)
                FROM waybills w
                JOIN driver_details d ON w.driver_id = d.id
                WHERE w.id = `{$table}`.`waybill_id`
                LIMIT 1
            )";
        }

        if ($this->tableHasColumn($table, 'driver_id')) {
            $pieces[] = "(
                SELECT CONCAT(d.first_name, ' ', d.last_name)
                FROM driver_details d
                WHERE d.id = `{$table}`.`driver_id`
                LIMIT 1
            )";
        }

        if ($this->tableHasColumn($table, 'vehicle_id')) {
            $pieces[] = "(
                SELECT GROUP_CONCAT(CONCAT(d.first_name, ' ', d.last_name) SEPARATOR ', ')
                FROM driver_vehicle dv
                JOIN driver_details d ON dv.driver_id = d.id
                WHERE dv.vehicle_id = `{$table}`.`vehicle_id`
            )";
        }

        if ($this->tableHasColumn($table, 'vehicle_maintenance_id')) {
            $pieces[] = "(
                SELECT GROUP_CONCAT(CONCAT(d.first_name, ' ', d.last_name) SEPARATOR ', ')
                FROM vehicle_maintenances vm
                JOIN driver_vehicle dv ON dv.vehicle_id = vm.vehicle_id
                JOIN driver_details d ON dv.driver_id = d.id
                WHERE vm.id = `{$table}`.`vehicle_maintenance_id`
            )";
        }

        if ($table === 'vehicles') {
            $pieces[] = "(
                SELECT GROUP_CONCAT(CONCAT(d.first_name, ' ', d.last_name) SEPARATOR ', ')
                FROM driver_vehicle dv
                JOIN driver_details d ON dv.driver_id = d.id
                WHERE dv.vehicle_id = `vehicles`.`id`
            )";
        }

        if (empty($pieces)) {
            return "''";
        }

        return count($pieces) === 1 ? $pieces[0] : 'COALESCE('.implode(', ', $pieces).')';
    }

    private function getFleetNoColumnSql(string $table): string
    {
        $table = $this->sanitizeTableName($table);
        $pieces = [];

        if ($this->tableHasColumn($table, 'vehicle_id')) {
            $pieces[] = "(
                SELECT v.fleet_no
                FROM vehicles v
                WHERE v.id = `{$table}`.`vehicle_id`
                LIMIT 1
            )";
        }

        if ($this->tableHasColumn($table, 'waybill_id')) {
            $pieces[] = "(
                SELECT v.fleet_no
                FROM waybills w
                JOIN vehicles v ON w.vehicle_id = v.id
                WHERE w.id = `{$table}`.`waybill_id`
                LIMIT 1
            )";
        }

        if ($this->tableHasColumn($table, 'vehicle_maintenance_id')) {
            $pieces[] = "(
                SELECT v.fleet_no
                FROM vehicle_maintenances vm
                JOIN vehicles v ON vm.vehicle_id = v.id
                WHERE vm.id = `{$table}`.`vehicle_maintenance_id`
                LIMIT 1
            )";
        }

        if (empty($pieces)) {
            return "''";
        }

        return count($pieces) === 1 ? $pieces[0] : 'COALESCE('.implode(', ', $pieces).')';
    }

    private function getTruckNoColumnSql(string $table): string
    {
        $table = $this->sanitizeTableName($table);
        $pieces = [];

        if ($this->tableHasColumn($table, 'vehicle_id')) {
            $pieces[] = "(
                SELECT v.truck_no
                FROM vehicles v
                WHERE v.id = `{$table}`.`vehicle_id`
                LIMIT 1
            )";
        }

        if ($this->tableHasColumn($table, 'waybill_id')) {
            $pieces[] = "(
                SELECT v.truck_no
                FROM waybills w
                JOIN vehicles v ON w.vehicle_id = v.id
                WHERE w.id = `{$table}`.`waybill_id`
                LIMIT 1
            )";
        }

        if ($this->tableHasColumn($table, 'vehicle_maintenance_id')) {
            $pieces[] = "(
                SELECT v.truck_no
                FROM vehicle_maintenances vm
                JOIN vehicles v ON vm.vehicle_id = v.id
                WHERE vm.id = `{$table}`.`vehicle_maintenance_id`
                LIMIT 1
            )";
        }

        if (empty($pieces)) {
            return "''";
        }

        return count($pieces) === 1 ? $pieces[0] : 'COALESCE('.implode(', ', $pieces).')';
    }

    private function getWaybillNumberColumnSql(string $table): string
    {
        $table = $this->sanitizeTableName($table);

        return "(
            SELECT w.waybill_number
            FROM waybills w
            WHERE w.id = `{$table}`.`waybill_id`
            LIMIT 1
        )";
    }

    private function getMakeModelColumnSql(string $table): string
    {
        $table = $this->sanitizeTableName($table);

        return "(
            SELECT vmm.name
            FROM vehicle_make_models vmm
            WHERE vmm.id = `{$table}`.`vehicle_make_model_id`
            LIMIT 1
        )";
    }

    private function getVehicleTypeColumnSql(string $table): string
    {
        $table = $this->sanitizeTableName($table);

        return "(
            SELECT vt.name
            FROM vehicle_types vt
            WHERE vt.id = `{$table}`.`vehicle_type_id`
            LIMIT 1
        )";
    }

    private function getExportableColumns(string $table): array
    {
        $columns = $this->getTableColumns($table);

        if ($table === 'waybills') {
            $columns = array_values(array_filter(
                $columns,
                fn ($column) => ! $this->isHiddenWaybillColumn($column)
            ));
        }

        if ($this->shouldAddDriverNameColumn($table) && ! in_array('driver_name', $columns, true)) {
            $columns[] = 'driver_name';
        }

        if ($this->shouldAddFleetNoColumn($table) && ! in_array('fleet_no', $columns, true)) {
            $columns[] = 'fleet_no';
        }

        if ($this->shouldAddTruckNoColumn($table) && ! in_array('truck_no', $columns, true)) {
            $columns[] = 'truck_no';
        }

        if ($this->shouldAddWaybillNumberColumn($table) && ! in_array('waybill_number', $columns, true)) {
            $columns[] = 'waybill_number';
        }

        if ($this->shouldAddMakeModelColumn($table) && ! in_array('vehicle_make_model', $columns, true)) {
            $columns[] = 'vehicle_make_model';
        }

        if ($this->shouldAddVehicleTypeColumn($table) && ! in_array('vehicle_type', $columns, true)) {
            $columns[] = 'vehicle_type';
        }

        return $columns;
    }

    private function isHiddenWaybillColumn(string $column): bool
    {
        $hiddenColumns = [
            'checkpoints',
            'route_geometry',
            'origin_lat',
            'origin_lang',
            'origin_lng',
            'origin_latitude',
            'origin_longitude',
            'destination_lat',
            'destination_lang',
            'destination_lng',
            'destination_latitude',
            'destination_longitude',
            'distination_lat',
            'distination_lang',
            'distination_lng',
            'route_source',
            'actual_distance',
            'gps_enabled',
            'cargo_description',
            'quantity',
        ];

        return in_array(strtolower($column), $hiddenColumns, true);
    }

    public function getColumns(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'table' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $table = $request->input('table');

        if (! $this->tableExists($table)) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }

        try {
            $columns = $this->getExportableColumns($table);

            return response()->json([
                'success' => true,
                'columns' => $columns,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch columns: '.$e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request): StreamedResponse|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'table' => 'required|string|max:255',
                'columns' => 'required|array|min:1',
                'columns.*' => 'string|max:255',
                'search' => 'nullable|string|max:255',
                'from_date' => 'nullable|date|date_format:Y-m-d',
                'to_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:from_date',
                'sort_column' => 'nullable|string|max:255',
                'sort_direction' => 'nullable|in:asc,desc,ASC,DESC',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $table = $request->input('table');
            $columns = $request->input('columns');
            $search = trim((string) $request->input('search', ''));
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            $sortColumn = $request->input('sort_column');
            $sortDirection = strtolower((string) $request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

            if (! $this->tableExists($table)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found',
                ], 404);
            }

            if ($table === 'waybills') {
                $columns = array_values(array_filter(
                    $columns,
                    fn ($column) => ! $this->isHiddenWaybillColumn($column)
                ));
            }

            if (empty($columns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select at least one visible column to export.',
                ], 422);
            }

            $allColumns = $this->getExportableColumns($table);

            $invalidColumns = array_diff($columns, $allColumns);
            if (! empty($invalidColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid columns selected: '.implode(', ', $invalidColumns),
                ], 422);
            }

            $query = DB::table($table);

            // Date filtering
            $dateCol = null;
            if ($this->tableHasColumn($table, 'created_at')) {
                $dateCol = "$table.created_at";
            } elseif ($this->tableHasColumn($table, 'maintenance_date')) {
                $dateCol = "$table.maintenance_date";
            } elseif ($this->tableHasColumn($table, 'dispatch_date')) {
                $dateCol = "$table.dispatch_date";
            } elseif ($this->tableHasColumn($table, 'inspection_date')) {
                $dateCol = "$table.inspection_date";
            }

            if ($dateCol) {
                if ($fromDate && $toDate) {
                    $query->whereBetween($dateCol, [$fromDate.' 00:00:00', $toDate.' 23:59:59']);
                } elseif ($fromDate) {
                    $query->where($dateCol, '>=', $fromDate.' 00:00:00');
                } elseif ($toDate) {
                    $query->where($dateCol, '<=', $toDate.' 23:59:59');
                }
            }

            // Keyword Search filtering across direct table columns and relational columns
            if ($search !== '') {
                $rawCols = $this->getRawTableColumns($table);
                $searchableCols = array_values(array_filter($rawCols, function ($col) {
                    return ! in_array($col, ['password', 'remember_token', 'route_geometry', 'checkpoints'], true);
                }));

                $query->where(function ($q) use ($table, $search, $searchableCols) {
                    $hasFirst = false;

                    foreach ($searchableCols as $col) {
                        if (! $hasFirst) {
                            $q->where("$table.$col", 'LIKE', "%{$search}%");
                            $hasFirst = true;
                        } else {
                            $q->orWhere("$table.$col", 'LIKE', "%{$search}%");
                        }
                    }

                    if ($this->shouldAddDriverNameColumn($table)) {
                        $driverSql = $this->getDriverNameColumnSql($table);
                        if ($driverSql !== "''") {
                            $q->orWhereRaw("{$driverSql} LIKE ?", ["%{$search}%"]);
                        }
                    }

                    if ($this->shouldAddFleetNoColumn($table)) {
                        $fleetSql = $this->getFleetNoColumnSql($table);
                        if ($fleetSql !== "''") {
                            $q->orWhereRaw("{$fleetSql} LIKE ?", ["%{$search}%"]);
                        }
                    }

                    if ($this->shouldAddTruckNoColumn($table)) {
                        $truckSql = $this->getTruckNoColumnSql($table);
                        if ($truckSql !== "''") {
                            $q->orWhereRaw("{$truckSql} LIKE ?", ["%{$search}%"]);
                        }
                    }

                    if ($this->shouldAddWaybillNumberColumn($table)) {
                        $wbSql = $this->getWaybillNumberColumnSql($table);
                        if ($wbSql !== "''") {
                            $q->orWhereRaw("{$wbSql} LIKE ?", ["%{$search}%"]);
                        }
                    }

                    if ($this->shouldAddMakeModelColumn($table)) {
                        $mmSql = $this->getMakeModelColumnSql($table);
                        if ($mmSql !== "''") {
                            $q->orWhereRaw("{$mmSql} LIKE ?", ["%{$search}%"]);
                        }
                    }

                    if ($this->shouldAddVehicleTypeColumn($table)) {
                        $vtSql = $this->getVehicleTypeColumnSql($table);
                        if ($vtSql !== "''") {
                            $q->orWhereRaw("{$vtSql} LIKE ?", ["%{$search}%"]);
                        }
                    }
                });
            }

            // Additional standard filters passed from page query parameters
            $filterKeys = ['category', 'action', 'status', 'vehicle_id', 'driver_id'];
            foreach ($filterKeys as $fKey) {
                if ($request->filled($fKey) && $request->input($fKey) !== 'all' && $this->tableHasColumn($table, $fKey)) {
                    $query->where("$table.$fKey", $request->input($fKey));
                }
            }

            // Maintenance type filter for vehicle_maintenance_items / vehicle_maintenances
            if ($request->filled('maintenance_type_id') && $request->input('maintenance_type_id') !== 'all') {
                $mTypeId = $request->input('maintenance_type_id');
                if ($table === 'vehicle_maintenance_items') {
                    $query->whereExists(function ($sub) use ($mTypeId) {
                        $sub->select(DB::raw(1))
                            ->from('vehicle_maintenance_maintenance_type as vmmt')
                            ->whereColumn('vmmt.vehicle_maintenance_id', 'vehicle_maintenance_items.vehicle_maintenance_id')
                            ->where('vmmt.vehicle_maintenance_type_id', $mTypeId);
                    });
                } elseif ($table === 'vehicle_maintenances') {
                    $query->whereExists(function ($sub) use ($mTypeId) {
                        $sub->select(DB::raw(1))
                            ->from('vehicle_maintenance_maintenance_type as vmmt')
                            ->whereColumn('vmmt.vehicle_maintenance_id', 'vehicle_maintenances.id')
                            ->where('vmmt.vehicle_maintenance_type_id', $mTypeId);
                    });
                }
            }

            // Select expressions
            $selects = [];
            $driverNameRequested = in_array('driver_name', $columns, true);
            $fleetNoRequested = in_array('fleet_no', $columns, true);
            $truckNoRequested = in_array('truck_no', $columns, true);
            $waybillNumberRequested = in_array('waybill_number', $columns, true);
            $makeModelRequested = in_array('vehicle_make_model', $columns, true);
            $vehicleTypeRequested = in_array('vehicle_type', $columns, true);

            foreach ($columns as $col) {
                if ($col === 'driver_name' && $driverNameRequested && $this->shouldAddDriverNameColumn($table)) {
                    $selects[] = DB::raw($this->getDriverNameColumnSql($table).' as driver_name');
                } elseif ($col === 'fleet_no' && $fleetNoRequested && $this->shouldAddFleetNoColumn($table)) {
                    $selects[] = DB::raw($this->getFleetNoColumnSql($table).' as fleet_no');
                } elseif ($col === 'truck_no' && $truckNoRequested && $this->shouldAddTruckNoColumn($table)) {
                    $selects[] = DB::raw($this->getTruckNoColumnSql($table).' as truck_no');
                } elseif ($col === 'waybill_number' && $waybillNumberRequested && $this->shouldAddWaybillNumberColumn($table)) {
                    $selects[] = DB::raw($this->getWaybillNumberColumnSql($table).' as waybill_number');
                } elseif ($col === 'vehicle_make_model' && $makeModelRequested && $this->shouldAddMakeModelColumn($table)) {
                    $selects[] = DB::raw($this->getMakeModelColumnSql($table).' as vehicle_make_model');
                } elseif ($col === 'vehicle_type' && $vehicleTypeRequested && $this->shouldAddVehicleTypeColumn($table)) {
                    $selects[] = DB::raw($this->getVehicleTypeColumnSql($table).' as vehicle_type');
                } else {
                    $selects[] = "$table.$col";
                }
            }

            $query->select($selects);

            // Sorting
            if ($sortColumn && in_array($sortColumn, $allColumns, true)) {
                if ($sortColumn === 'driver_name' && $this->shouldAddDriverNameColumn($table)) {
                    $query->orderBy(DB::raw($this->getDriverNameColumnSql($table)), $sortDirection);
                } elseif ($sortColumn === 'fleet_no' && $this->shouldAddFleetNoColumn($table)) {
                    $query->orderBy(DB::raw($this->getFleetNoColumnSql($table)), $sortDirection);
                } elseif ($sortColumn === 'truck_no' && $this->shouldAddTruckNoColumn($table)) {
                    $query->orderBy(DB::raw($this->getTruckNoColumnSql($table)), $sortDirection);
                } elseif ($sortColumn === 'waybill_number' && $this->shouldAddWaybillNumberColumn($table)) {
                    $query->orderBy(DB::raw($this->getWaybillNumberColumnSql($table)), $sortDirection);
                } elseif ($this->tableHasColumn($table, $sortColumn)) {
                    $query->orderBy("$table.$sortColumn", $sortDirection);
                }
            } elseif ($this->tableHasColumn($table, 'created_at')) {
                $query->orderBy("$table.created_at", 'desc');
            } elseif ($this->tableHasColumn($table, 'id')) {
                $query->orderBy("$table.id", 'desc');
            }

            $data = $query->get();

            return $this->generateCsv($data, $columns, $table);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: '.$e->getMessage(),
            ], 500);
        }
    }

    private function generateCsv($data, array $columns, string $table): StreamedResponse
    {
        $filename = $table.'_'.date('Y_m_d').'.csv';

        return response()->streamDownload(function () use ($data, $columns) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            $headers = array_map(function ($column) {
                return ucwords(str_replace('_', ' ', $column));
            }, $columns);

            fputcsv($handle, $headers);

            foreach ($data as $row) {
                $rowData = [];
                foreach ($columns as $column) {
                    $value = $row->$column ?? '';

                    if (in_array($column, ['created_at', 'updated_at', 'deleted_at'], true) && $value) {
                        try {
                            $value = Carbon::parse($value)->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                        }
                    }

                    $rowData[] = $value;
                }
                fputcsv($handle, $rowData);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
