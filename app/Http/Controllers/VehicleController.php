<?php

namespace App\Http\Controllers;

use App\Dto\VehicleDto;
use App\Interfaces\VehicleInterface;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(VehicleInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.vehicle', 'vehicle');
    }

    public function index()
    {
        $data['all'] = $this->_repo->index(['vehicle_make_model']);

        $totalVehicles = $this->_repo->all();
        $data['all_vehicle_count'] = count($totalVehicles);
        $data['active_vehicle_count'] = count($totalVehicles->where('status', '1'));
        $data['active_driver_count'] = count(app()->make('App\Interfaces\DriverDetailInterface')->all()->where('status', '1'));
        $data['in_maintenance_count'] = count($totalVehicles->where('status', '2'));

        return view($this->_directory . '.all', compact('data'));
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);
        $data->load(['drivers', 'vehicle_make_model', 'vehicle_type', 'gps_device_type', 'creator']);
        return view($this->_directory . '.show', compact('data'));
    }

    public function vehicle_home()
    {
        $data['driver_detail_count'] = count(app()->make('App\Interfaces\DriverDetailInterface')->all());
        $data['driver_mate_count'] = count(app()->make('App\Interfaces\DriverMateInterface')->all());
        $data['route_trip_count'] = count(app()->make('App\Interfaces\RouteTripInterface')->all());

        $totalVehicles = $this->_repo->all();
        $data['all_vehicle_count']    = count($totalVehicles);
        $data['active_vehicle_count']  = count($totalVehicles->where('status', '1'));
        $data['in_maintenance_count']  = count($totalVehicles->where('status', '2'));
        $data['unserviceable_count']   = count($totalVehicles->where('status', '0'));

        $allWaybills = app()->make('App\Interfaces\WaybillInterface')->all();
        $data['total_waybill_count']    = count($allWaybills);
        $data['awaiting_waybill_count'] = $allWaybills->filter(fn($w) => $w->status?->value === 0)->count();
        $data['allocated_waybill_count'] = $allWaybills->filter(fn($w) => $w->status?->value === 1)->count();

        $data['maintenance_count'] = count(app()->make('App\Interfaces\VehicleMaintenanceInterface')->all());

        // Operation Performance chart — last 12 months (same data as main dashboard)
        $chartData = $this->getAssetsAndMaintenanceData();

        return view($this->_directory . '.home', compact('data', 'chartData'));
    }

    /**
     * Build chart data for the specified period (weekly, monthly, yearly).
     */
    private function getAssetsAndMaintenanceData(string $period = 'weekly'): array
    {
        $labels = [];
        $assets = [];
        $maintenance = [];

        switch ($period) {
            case 'weekly':
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $labels[] = $day->format('D, d M');
                    $assets[] = \App\Models\Vehicle::whereDate('created_at', $day->toDateString())->count();
                    $maintenance[] = \App\Models\VehicleMaintenance::whereDate('created_at', $day->toDateString())->count();
                }
                break;

            case 'monthly':
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $labels[] = $month->format('M Y');
                    $assets[] = \App\Models\Vehicle::whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)->count();
                    $maintenance[] = \App\Models\VehicleMaintenance::whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)->count();
                }
                break;

            case 'yearly':
                for ($i = 4; $i >= 0; $i--) {
                    $year = now()->subYears($i)->year;
                    $labels[] = (string) $year;
                    $assets[] = \App\Models\Vehicle::whereYear('created_at', $year)->count();
                    $maintenance[] = \App\Models\VehicleMaintenance::whereYear('created_at', $year)->count();
                }
                break;
        }

        return ['labels' => $labels, 'assets' => $assets, 'maintenance' => $maintenance];
    }

    /**
     * AJAX – returns Operation Performance chart data for the chosen period.
     */
    public function chartFilterData(Request $request): \Illuminate\Http\JsonResponse
    {
        $period = $request->input('period', 'weekly');
        $chartData = $this->getAssetsAndMaintenanceData($period);

        return response()->json($chartData);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);
        $data->load('drivers');
        return view($this->_directory . '.edit', compact('data'));
    }

    public function store(VehicleRequest $request)
    {
        try {
            $this->_repo->store(VehicleDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.list')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            \Log::error('Vehicle Store Error: ' . $th->getMessage(), ['exception' => $th]);
            $errorMessage = config('app.debug') ? $th->getMessage() : __('language.something_went_wrong');
            return redirect()->route($this->_route . '.list')->with('error', $errorMessage);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request Validation $validation
     * @return \Illuminate\Http\Response
     */
    public function update(VehicleRequest $request, $id)
    {
        try {
            $this->_repo->update($id, VehicleDto::fromRequest($request->validated()));
            return redirect()->route($this->_route . '.list')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            \Log::error('Vehicle Update Error: ' . $th->getMessage(), ['exception' => $th]);
            if ($th instanceof NotFoundHttpException) {
                $message = $th->getMessage();
                return redirect()->route($this->_route . '.list')->with('error', $message);
            } else {
                $errorMessage = config('app.debug') ? $th->getMessage() : __('language.something_went_wrong');
                return redirect()->route($this->_route . '.list')->with('error', $errorMessage);
            }
        }
    }

    /**
     * Remove the specified vehicle from storage and Wialon.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->_repo->delete(null, $id);
            return redirect()->route($this->_route . '.list')->with('success', __('language.deleted_successfully'));
        } catch (\Throwable $th) {
            \Log::error('Vehicle Delete Error: ' . $th->getMessage(), ['exception' => $th]);
            $errorMessage = config('app.debug') ? $th->getMessage() : __('language.something_went_wrong');
            return redirect()->route($this->_route . '.list')->with('error', $errorMessage);
        }
    }

    /**
     * Display vehicle tracking map (can show specific vehicle or all vehicles if no active waybill)
     *
     * @param  Vehicle  $vehicle
     * @return \Illuminate\View\View
     */
    public function vehicleTracking(Vehicle $vehicle)
    {
        $vehicle->load('drivers');
        
        // Check if vehicle has GPS unit
        $matchedUnitId = $vehicle->gps_unit_id
            ? (string) $vehicle->gps_unit_id
            : null;

        // Try to find an active waybill for this vehicle (in_transit status = 2)
        $activeWaybill = \App\Models\Waybill::where('vehicle_id', $vehicle->id)
            ->whereHas('status', function($q) {
                $q->where('value', 2); // in_transit
            })
            ->with(['driver', 'status'])
            ->first();

        return view('auth.pages.vehicle.partials._map_tracking', [
            'wialonToken'   => config('services.wialon.token'),
            'wialonHost'    => config('services.wialon.host'),
            'appEnv'        => config('app.env'),
            'vehicle'       => $vehicle,
            'fleetNo'       => $vehicle->fleet_no,
            'matchedUnitId' => $matchedUnitId,
            'waybill'       => $activeWaybill,
            'embedded'      => request()->boolean('embedded'),
        ]);
    }
}
