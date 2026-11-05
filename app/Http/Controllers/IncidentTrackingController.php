<?php

namespace App\Http\Controllers;

use App\Services\IncidentTrackingService;
use App\Services\WialonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentTrackingController extends Controller
{
    public function __construct(
        protected IncidentTrackingService $incidentTrackingService,
        protected WialonService $wialonService
    ) {}

    /**
     * Display the full-screen interactive live tracking map for all incidents and fleet vehicles.
     */
    public function index(Request $request)
    {
        $filters = [
            'vehicle_id'     => $request->query('vehicle_id'),
            'waybill_number' => $request->query('waybill_number'),
            'fleet_no'       => $request->query('fleet_no'),
            'location'       => $request->query('location'),
        ];

        $incidents     = $this->incidentTrackingService->getAllMapIncidents($filters);
        $fleetVehicles = $this->incidentTrackingService->getFleetVehiclesForMap($incidents);
        $filterOptions = $this->incidentTrackingService->getFilterOptions($incidents, $fleetVehicles);

        // Calculate KPI summary metrics
        $totalIncidents = count($incidents);
        $incidentCount  = count(array_filter($incidents, fn ($i) => $i['category'] === 'incident'));
        $behaviorCount  = count(array_filter($incidents, fn ($i) => $i['category'] === 'driver_behavior'));
        $totalVehicles  = count($fleetVehicles);

        return view('auth.pages.incident.track', [
            'incidents'      => $incidents,
            'fleetVehicles'  => $fleetVehicles,
            'filterOptions'  => $filterOptions,
            'totalIncidents' => $totalIncidents,
            'incidentCount'  => $incidentCount,
            'behaviorCount'  => $behaviorCount,
            'totalVehicles'  => $totalVehicles,
            'wialonToken'    => config('services.wialon.token'),
            'wialonHost'     => config('services.wialon.host'),
            'geoapifyApiKey' => config('services.geoapify.api_key'),
            'appEnv'         => config('app.env'),
            'embedded'       => $request->boolean('embedded'),
        ]);
    }

    /**
     * AJAX endpoint to fetch updated live incident and vehicle data.
     */
    public function getData(Request $request): JsonResponse
    {
        $filters = [
            'vehicle_id'     => $request->query('vehicle_id'),
            'waybill_number' => $request->query('waybill_number'),
            'fleet_no'       => $request->query('fleet_no'),
            'location'       => $request->query('location'),
        ];

        $incidents     = $this->incidentTrackingService->getAllMapIncidents($filters);
        $fleetVehicles = $this->incidentTrackingService->getFleetVehiclesForMap($incidents);
        $filterOptions = $this->incidentTrackingService->getFilterOptions($incidents, $fleetVehicles);

        return response()->json([
            'success'       => true,
            'incidents'     => $incidents,
            'fleetVehicles' => $fleetVehicles,
            'filterOptions' => $filterOptions,
            'totalIncidents'=> count($incidents),
            'totalVehicles' => count($fleetVehicles),
        ]);
    }
}
