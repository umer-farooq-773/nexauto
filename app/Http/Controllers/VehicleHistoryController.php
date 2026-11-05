<?php

namespace App\Http\Controllers;

use App\Exports\VehicleHistoryExport;
use App\Exports\VehicleHistoryTripsSheet;
use App\Models\Vehicle;
use App\Services\VehicleHistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class VehicleHistoryController extends Controller
{
    public function __construct(
        protected VehicleHistoryService $vehicleHistoryService
    ) {}

    /**
     * Display the Wialon-style Vehicle Track / Track Player interface.
     */
    public function index(Request $request)
    {
        // 1. Fetch all live active units directly from Wialon, merged with DB vehicle metadata
        $formattedVehicles = collect($this->vehicleHistoryService->getWialonLiveUnits());

        // 2. Preselected vehicle if provided in query string
        $preselectedVehicleId = $request->query('vehicle_id');
        $preselectedUnitId = $request->query('unit_id');

        if (!$preselectedVehicleId && $preselectedUnitId) {
            $matched = $formattedVehicles->first(fn($v) => (string)$v['unit_id'] === (string)$preselectedUnitId);
            if ($matched) {
                $preselectedVehicleId = (string) $matched['id'];
            }
        }

        // Don't auto-select first vehicle - let dropdown start empty
        // if (!$preselectedVehicleId && $formattedVehicles->isNotEmpty()) {
        //     $preselectedVehicleId = (string) $formattedVehicles->first()['id'];
        // }

        // 3. Default time range: Today 00:00:00 to now
        $defaultFrom = Carbon::now()->startOfDay()->format('Y-m-d H:i');
        $defaultTo   = Carbon::now()->format('Y-m-d H:i');

        return view('auth.pages.vehicle_history.index', [
            'vehicles'             => $formattedVehicles,
            'preselectedVehicleId' => $preselectedVehicleId,
            'defaultFrom'          => $defaultFrom,
            'defaultTo'            => $defaultTo,
            'wialonHost'           => config('services.wialon.host'),
            'embedded'             => $request->boolean('embedded'),
        ]);
    }

    /**
     * AJAX endpoint to fetch historical GPS track coordinates, telemetry, trips, and sensors.
     */
    public function getTrackData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'vehicle_id'       => ['required_without:unit_id', 'nullable', 'string'],
                'unit_id'          => ['required_without:vehicle_id', 'nullable', 'integer'],
                'from'             => ['required', 'string'],
                'to'               => ['required', 'string'],
                'trip_detector'    => ['nullable', 'boolean'],
                'show_annotations' => ['nullable', 'boolean'],
            ]);

            $vehicleIdentifier = $validated['vehicle_id'] ?? $validated['unit_id'];

            // Parse datetime strings
            try {
                $fromCarbon = Carbon::parse($validated['from']);
                $toCarbon   = Carbon::parse($validated['to']);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format provided. Please use standard date/time format (YYYY-MM-DD HH:MM).',
                ], 422);
            }

            if ($toCarbon->lt($fromCarbon)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The "To" date/time must be after the "From" date/time.',
                ], 422);
            }

            // Check if from date is too far in future
            if ($fromCarbon->isFuture() && $fromCarbon->diffInHours(now()) > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date/time cannot be in the future.',
                ], 422);
            }

            // Enforce maximum interval constraint (up to 90 days to prevent server memory saturation)
            $diffInDays = $fromCarbon->diffInDays($toCarbon);
            if ($diffInDays > 90) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date range exceeds the maximum limit of 90 days. Please select a shorter interval.',
                ], 422);
            }

            $fromTimestamp = $fromCarbon->timestamp;
            $toTimestamp   = $toCarbon->timestamp;

            $options = [
                'trip_detector'    => $request->boolean('trip_detector', true),
                'show_annotations' => $request->boolean('show_annotations', true),
            ];

            $trackData = $this->vehicleHistoryService->getVehicleTrackData(
                $vehicleIdentifier,
                $fromTimestamp,
                $toTimestamp,
                $options
            );

            return response()->json($trackData);

        } catch (\InvalidArgumentException $e) {
            Log::warning('Track data validation warning', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Track data generation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = config('app.debug')
                ? $e->getMessage()
                : 'Unable to retrieve track data from Wialon. Please verify your connection or try again later.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }
    }

    /**
     * Export vehicle historical track data into PDF, Excel (.xlsx), or CSV report.
     */
    public function exportReport(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            $validated = $request->validate([
                'vehicle_id'       => ['required_without:unit_id', 'nullable', 'string'],
                'unit_id'          => ['required_without:vehicle_id', 'nullable', 'integer'],
                'from'             => ['required', 'string'],
                'to'               => ['required', 'string'],
                'trip_detector'    => ['nullable', 'boolean'],
                'format'           => ['required', 'in:pdf,xlsx,csv'],
            ]);

            $vehicleIdentifier = $validated['vehicle_id'] ?? $validated['unit_id'];

            try {
                $fromCarbon = Carbon::parse($validated['from']);
                $toCarbon   = Carbon::parse($validated['to']);
            } catch (\Exception $e) {
                return back()->with('error', 'Invalid date format provided. Please use standard date/time format.');
            }

            if ($toCarbon->lt($fromCarbon)) {
                return back()->with('error', 'The "To" date/time must be after the "From" date/time.');
            }

            $fromTimestamp = $fromCarbon->timestamp;
            $toTimestamp   = $toCarbon->timestamp;

            $options = [
                'trip_detector'    => $request->boolean('trip_detector', true),
                'show_annotations' => true,
            ];

            $trackData = $this->vehicleHistoryService->getVehicleTrackData(
                $vehicleIdentifier,
                $fromTimestamp,
                $toTimestamp,
                $options
            );

            $format = strtolower($validated['format']);
            $fleetNo = str($trackData['vehicle']['fleet_no'] ?? 'Unit')->slug();
            $dateSlug = $fromCarbon->format('Ymd_Hi') . '_to_' . $toCarbon->format('Ymd_Hi');
            $baseFileName = "vehicle_history_{$fleetNo}_{$dateSlug}";

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('auth.pages.vehicle_history.pdf.report', [
                    'trackData' => $trackData,
                ])->setPaper('a4', 'portrait');

                return $pdf->download("{$baseFileName}.pdf");
            }

            if ($format === 'xlsx') {
                return Excel::download(
                    new VehicleHistoryExport($trackData),
                    "{$baseFileName}.xlsx",
                    \Maatwebsite\Excel\Excel::XLSX
                );
            }

            if ($format === 'csv') {
                return Excel::download(
                    new VehicleHistoryTripsSheet($trackData),
                    "{$baseFileName}_trips.csv",
                    \Maatwebsite\Excel\Excel::CSV
                );
            }

            return back()->with('error', 'Unsupported export format.');

        } catch (\Throwable $e) {
            Log::error('Vehicle history export exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }
}
