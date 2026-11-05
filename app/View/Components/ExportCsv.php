<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class ExportCsv extends Component
{
    public $table;
    public $columns;
    public $exportRoute;
    public $columnsRoute;

    public function __construct($table, $exportRoute = null, $columnsRoute = null)
    {
        $this->table = $table;
        $this->exportRoute = $exportRoute ?? route('export.csv.export');
        $this->columnsRoute = $columnsRoute ?? route('export.csv.columns');
        $this->columns = $this->fetchColumns($table);
    }

    public static function isExcludedColumn(string $column): bool
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

    private function fetchColumns(string $table): array
    {
        try {
            // Use raw INFORMATION_SCHEMA query — works reliably on all environments
            $dbName = DB::connection()->getDatabaseName();
            $results = DB::select(
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$dbName, $table]
            );

            if (empty($results)) {
                $rawCols = [];
            } else {
                $rawCols = array_map(fn($col) => $col->COLUMN_NAME, $results);
            }

            // Check relations before filtering out ID columns
            $hasDriverRelated = $this->hasDriverRelatedColumn($rawCols, $table);
            $hasVehicleRelated = $this->hasVehicleRelatedColumn($rawCols, $table);
            $hasWaybillRelated = in_array('waybill_id', $rawCols, true);
            $hasMakeModelRelated = in_array('vehicle_make_model_id', $rawCols, true);
            $hasVehicleTypeRelated = in_array('vehicle_type_id', $rawCols, true);

            // Filter out internal and ID columns
            $cols = array_values(
                array_filter(
                    $rawCols,
                    fn($col) => !self::isExcludedColumn($col)
                )
            );

            if ($hasDriverRelated && !in_array('driver_name', $cols, true)) {
                $cols[] = 'driver_name';
            }

            if ($hasVehicleRelated) {
                if (!in_array('fleet_no', $cols, true)) {
                    $cols[] = 'fleet_no';
                }
                if (!in_array('truck_no', $cols, true)) {
                    $cols[] = 'truck_no';
                }
            }

            if ($hasWaybillRelated && !in_array('waybill_number', $cols, true)) {
                $cols[] = 'waybill_number';
            }

            if ($hasMakeModelRelated && !in_array('vehicle_make_model', $cols, true)) {
                $cols[] = 'vehicle_make_model';
            }

            if ($hasVehicleTypeRelated && !in_array('vehicle_type', $cols, true)) {
                $cols[] = 'vehicle_type';
            }

            return $cols;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function hasDriverRelatedColumn(array $columns, string $table = ''): bool
    {
        return in_array('driver_id', $columns, true)
            || in_array('waybill_id', $columns, true)
            || in_array('vehicle_id', $columns, true)
            || in_array('vehicle_maintenance_id', $columns, true)
            || $table === 'vehicles';
    }

    private function hasVehicleRelatedColumn(array $columns, string $table = ''): bool
    {
        return in_array('vehicle_id', $columns, true)
            || in_array('waybill_id', $columns, true)
            || in_array('vehicle_maintenance_id', $columns, true);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.export-csv');
    }
}
