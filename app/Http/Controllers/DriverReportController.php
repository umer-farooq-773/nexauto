<?php

namespace App\Http\Controllers;

use App\Exports\DriverIncidentReportExport;
use App\Services\DriverReportExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DriverReportController extends Controller
{
    public function __construct(protected DriverReportExcelService $service)
    {
    }

    public function index()
    {
        $drivers = $this->service->getDriversForFilter();
        $types   = DriverReportExcelService::TYPES;

        return view('driver_report.index', compact('drivers', 'types'));
    }

    /**
     * AJAX endpoint — returns filtered records + chart data as JSON.
     * Called on every filter change for the "real time" sidebar behaviour.
     */
    public function data(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $records = $this->service->getRecords($filters);
        $counts  = $this->service->getTypeCounts($filters);

        return response()->json([
            'total'   => $records->count(),
            'records' => $records->map(fn ($r) => [
                'incident_type' => $r->incident_type,
                'driver_name'   => $r->driver_name ?: 'Unassigned',
                'fleet_no'      => $r->fleet_no,
                'truck_no'      => $r->truck_no,
                'incident_date' => $r->incident_date,
                'location'      => $r->location,
                'reference'     => $r->reference,
                'cost'          => $r->cost,
                'status'        => $r->status,
            ]),
            'chart' => [
                'labels' => $counts->keys(),
                'values' => $counts->values(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $fileName = 'driver-incident-report-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new DriverIncidentReportExport($this->service, $filters),
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX,
            ['includeCharts' => true]
        );
    }

    protected function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'driver_id'   => ['nullable', 'array'],
            'driver_id.*' => ['string'],
            'types'     => ['nullable', 'array'],
            'types.*'   => ['string', 'in:' . implode(',', array_keys(DriverReportExcelService::TYPES))],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'sort'      => ['nullable', 'string'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        return $validated;
    }
}
