<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DriverReportExcelService
{
    /**
     * key => [ label, group ]
     * group is used only to organise the sidebar checkboxes (Incident vs Behavior vs Inspection)
     */
    public const TYPES = [
        'accident'           => ['label' => 'Accident',            'group' => 'incident'],
        'impoundment'        => ['label' => 'Impoundment',         'group' => 'incident'],
        'vehicle_breakdown'  => ['label' => 'Vehicle Breakdown',   'group' => 'incident'],
        'illegal_detention'  => ['label' => 'Illegal Detention',   'group' => 'incident'],
        'bogged_down'        => ['label' => 'Bogged Down',         'group' => 'incident'],
        'route_diversion'    => ['label' => 'Route Diversion',     'group' => 'behavior'],
        'illegal_haulage'    => ['label' => 'Illegal Haulage',     'group' => 'behavior'],
        'illegal_shortage'   => ['label' => 'Illegal Shortage',    'group' => 'behavior'],
        'breathaliser_test'  => ['label' => 'Breathaliser Test',   'group' => 'behavior'],
        'driver_misconduct'  => ['label' => 'Driver Misconduct',   'group' => 'behavior'],
        'goods_inspection'   => ['label' => 'Goods Inspection',    'group' => 'inspection'],
    ];

    /**
     * Fetch the unified, filtered, sorted collection of records.
     *
     * @param array{
     *   driver_id?: string|null,
     *   types?: string[]|null,
     *   date_from?: string|null,
     *   date_to?: string|null,
     *   sort?: string, // incident_date|driver_name|incident_type
     *   direction?: string // asc|desc
     * } $filters
     */
    public function getRecords(array $filters): Collection
    {
        $types     = $filters['types'] ?? array_keys(self::TYPES);
        $driverId  = array_filter((array) ($filters['driver_id'] ?? []));
        $from      = $filters['date_from'] ?? null;
        $to        = $filters['date_to'] ?? null;
        $sort      = $filters['sort'] ?? 'incident_date';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $builders = [];

        if (in_array('accident', $types, true)) {
            $builders[] = $this->accidents($driverId, $from, $to);
        }
        if (in_array('impoundment', $types, true)) {
            $builders[] = $this->impoundments($driverId, $from, $to);
        }
        if (in_array('vehicle_breakdown', $types, true)) {
            $builders[] = $this->vehicleBreakdowns($driverId, $from, $to);
        }
        if (in_array('illegal_detention', $types, true)) {
            $builders[] = $this->illegalDetentions($driverId, $from, $to);
        }
        if (in_array('bogged_down', $types, true)) {
            $builders[] = $this->boggedDowns($driverId, $from, $to);
        }
        if (in_array('illegal_haulage', $types, true)) {
            $builders[] = $this->illegalHaulages($driverId, $from, $to);
        }
        if (in_array('illegal_shortage', $types, true)) {
            $builders[] = $this->illegalShortages($driverId, $from, $to);
        }
        if (in_array('breathaliser_test', $types, true)) {
            $builders[] = $this->breathaliserTests($driverId, $from, $to);
        }
        if (in_array('driver_misconduct', $types, true)) {
            $builders[] = $this->driverMisconducts($driverId, $from, $to);
        }
        if (in_array('route_diversion', $types, true)) {
            $builders[] = $this->routeDiversions($driverId, $from, $to);
        }
        if (in_array('goods_inspection', $types, true)) {
            $builders[] = $this->goodsInspections($driverId, $from, $to);
        }

        if (empty($builders)) {
            return collect();
        }

        $first = array_shift($builders);
        foreach ($builders as $b) {
            $first->unionAll($b);
        }

        $allowedSort = ['incident_date', 'driver_name', 'incident_type', 'fleet_no'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'incident_date';
        }

        // Sort must be applied on the outer union query.
        $wrapped = DB::query()->fromSub($first, 'u')->orderBy($sort, $direction);

        return collect($wrapped->get());
    }

    /**
     * Counts per incident_type, for chart rendering (Chart.js on the page + Excel summary sheet).
     */
    public function getTypeCounts(array $filters): Collection
    {
        return $this->getRecords($filters)
            ->groupBy('incident_type')
            ->map->count()
            ->sortDesc();
    }

    protected function baseFilter($q, string $dateCol, $driverIds, ?string $from, ?string $to, string $driverCol = 'd.id')
    {
        $driverIds = array_filter((array) $driverIds);
        if (!empty($driverIds)) {
            $q->whereIn($driverCol, $driverIds);
        }
        if ($from) {
            $q->whereDate($dateCol, '>=', $from);
        }
        if ($to) {
            $q->whereDate($dateCol, '<=', $to);
        }

        return $q;
    }

    protected function driverNameExpr(string $alias = 'd'): string
    {
        return "NULLIF(TRIM(CONCAT(COALESCE({$alias}.first_name,''),' ',COALESCE({$alias}.last_name,''))), '')";
    }

    // ---- Group A: vehicle-only tables, driver resolved via waybill ----

    protected function accidents($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('accidents as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Accident' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.accident_date as incident_date',
                't.accident_location as location',
                't.authority as reference',
                't.damage_cost as cost',
                't.status',
            ]);

        return $this->baseFilter($q, 't.accident_date', $driverId, $from, $to);
    }

    protected function impoundments($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('impoundments as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Impoundment' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.impound_date as incident_date',
                't.impound_location as location',
                't.impound_authority as reference',
                't.cost',
                't.status',
            ]);

        return $this->baseFilter($q, 't.impound_date', $driverId, $from, $to);
    }

    protected function vehicleBreakdowns($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('vehicle_breakdowns as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Vehicle Breakdown' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.breakdown_date as incident_date',
                't.breakdown_location as location',
                't.authority as reference',
                't.repair_cost as cost',
                't.status',
            ]);

        return $this->baseFilter($q, 't.breakdown_date', $driverId, $from, $to);
    }

    protected function illegalDetentions($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('illegal_detentions as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Illegal Detention' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.detention_date as incident_date',
                't.detention_location as location',
                't.detained_by as reference',
                't.cost',
                't.status',
            ]);

        return $this->baseFilter($q, 't.detention_date', $driverId, $from, $to);
    }

    protected function boggedDowns($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('vehicle_bogged_downs as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Bogged Down' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.bogged_date as incident_date',
                't.stuck_location as location',
                't.authority as reference',
                't.recovery_cost as cost',
                't.status',
            ]);

        return $this->baseFilter($q, 't.bogged_date', $driverId, $from, $to);
    }

    // ---- Group B: direct driver_id tables ----

    protected function illegalHaulages($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('illegal_haulages as t')
            ->leftJoin('driver_details as d', 'd.id', '=', 't.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Illegal Haulage' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.haulage_date as incident_date',
                't.haulage_location as location',
                't.haulage_type as reference',
                DB::raw('NULL as cost'),
                't.status',
            ]);

        return $this->baseFilter($q, 't.haulage_date', $driverId, $from, $to, 't.driver_id');
    }

    protected function illegalShortages($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('illegal_shortages as t')
            ->leftJoin('driver_details as d', 'd.id', '=', 't.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Illegal Shortage' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.shortage_date as incident_date',
                't.shortage_location as location',
                't.shortage_type as reference',
                DB::raw('NULL as cost'),
                't.status',
            ]);

        return $this->baseFilter($q, 't.shortage_date', $driverId, $from, $to, 't.driver_id');
    }

    protected function breathaliserTests($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('breathaliser_tests as t')
            ->leftJoin('driver_details as d', 'd.id', '=', 't.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Breathaliser Test' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.test_date as incident_date',
                't.test_location as location',
                't.test_result as reference',
                DB::raw('NULL as cost'),
                't.status',
            ]);

        return $this->baseFilter($q, 't.test_date', $driverId, $from, $to, 't.driver_id');
    }

    protected function driverMisconducts($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('driver_misconducts as t')
            ->leftJoin('driver_details as d', 'd.id', '=', 't.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Driver Misconduct' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw($this->driverNameExpr() . ' as driver_name'),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.misconduct_date as incident_date',
                't.misconduct_location as location',
                't.misconduct_type as reference',
                DB::raw('NULL as cost'),
                't.status',
            ]);

        return $this->baseFilter($q, 't.misconduct_date', $driverId, $from, $to, 't.driver_id');
    }

    // ---- Group C: goods_inspections (waybill-linked) ----

    protected function routeDiversions($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('goods_inspections as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->where('t.route_diversion', 'Yes')
            ->select([
                DB::raw("'Route Diversion' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw("COALESCE(" . $this->driverNameExpr() . ", t.driver_name) as driver_name"),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.inspection_date as incident_date',
                't.inspection_location as location',
                't.route as reference',
                DB::raw('NULL as cost'),
                DB::raw('t.status'),
            ]);

        return $this->baseFilter($q, 't.inspection_date', $driverId, $from, $to);
    }

    protected function goodsInspections($driverId, ?string $from, ?string $to)
    {
        $q = DB::table('goods_inspections as t')
            ->leftJoin('waybills as w', 'w.id', '=', 't.waybill_id')
            ->leftJoin('driver_details as d', 'd.id', '=', 'w.driver_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->select([
                DB::raw("'Goods Inspection' as incident_type"),
                't.id',
                'd.id as driver_id',
                DB::raw("COALESCE(" . $this->driverNameExpr() . ", t.driver_name) as driver_name"),
                't.vehicle_id',
                'v.fleet_no',
                'v.truck_no',
                't.inspection_date as incident_date',
                't.inspection_location as location',
                't.goods_status as reference',
                DB::raw('NULL as cost'),
                DB::raw('t.status'),
            ]);

        return $this->baseFilter($q, 't.inspection_date', $driverId, $from, $to);
    }

    /**
     * Convenience for populating the driver filter <select>.
     */
    public function getDriversForFilter(): Collection
    {
        return DB::table('driver_details as d')
            ->select('id', DB::raw($this->driverNameExpr() . ' as full_name'))
            ->orderBy('d.first_name')
            ->get();
    }
}
