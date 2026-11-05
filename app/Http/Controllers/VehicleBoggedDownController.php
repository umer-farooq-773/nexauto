<?php

namespace App\Http\Controllers;

use App\Dto\VehicleBoggedDownDto;
use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Http\Requests\VehicleBoggedDownRequest;
use App\Interfaces\VehicleBoggedDownInterface;
use App\Interfaces\VehicleInterface;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class VehicleBoggedDownController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(VehicleBoggedDownInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.vehicle-bogged-down', 'vehicle_bogged_down');
    }

    private function getModuleContext(): array
    {
        return [
            'route_prefix' => 'vehicle_bogged_down',
            'module_title' => 'Vehicle Bogged Down',
            'record_title' => 'Bogged Down Record',
        ];
    }

    public function index()
    {
        $module = $this->getModuleContext();
        $data['all']              = $this->_repo->index();
        $all                      = $this->_repo->all();
        $data['total_count']      = count($all);
        $data['resolved_count']   = count($all->where('status', '1'));
        $data['pending_count']    = count($all->where('status', '0'));
        $data['inprogress_count'] = count($all->where('status', '2'));
        $data['vehicles']         = app()->make(VehicleInterface::class)->all();
        $data['waybills']         = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        $data['has_allocated']    = \App\Models\Waybill::whereHas('status', function ($q) {
            $q->where('value', 1);
        })->exists();

        return view($this->_directory . '.all', compact('data', 'module'));
    }

    public function show($id)
    {
        $module = $this->getModuleContext();
        $data = $this->_repo->show($id);

        $approved_by = null;
        if ($data->client_approved_by) {
            $approved_by = User::where('id', $data->client_approved_by)->select('first_name', 'last_name')->first();
        }
        // return $approved_by;
        $payment_paid_by = null;
        if ($data->created_by) {
            $payment_paid_by = User::where('id', $data->created_by)->select('first_name', 'last_name')->first();
        }

        $payments = PaymentHistory::where('payable_type', get_class($data))->where('payable_id', $data->id)->orderBy('created_at', 'desc')->get();

        if (!$data) abort(404);

        return view($this->_directory . '.show', compact('data', 'module','approved_by','approved_by','payment_paid_by','payments'));
    }

    public function edit($id)
    {
        $module = $this->getModuleContext();
        $data     = $this->_repo->show($id);
        if (!$data) abort(404);
        $vehicles = app()->make(VehicleInterface::class)->all();
        $waybills = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        return view($this->_directory . '.edit', compact('data', 'vehicles', 'waybills', 'module'));
    }

    public function store(VehicleBoggedDownRequest $request)
    {
        try {
            $record = $this->_repo->store(VehicleBoggedDownDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/vehicle-bogged-down/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'vehicle_bogged_down', 'Vehicle Bogged Down', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'vehicle_bogged_down', 'action' => 'store']);
        }
    }

    public function update(VehicleBoggedDownRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, VehicleBoggedDownDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/vehicle-bogged-down/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'vehicle_bogged_down', 'Vehicle Bogged Down', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'vehicle_bogged_down', 'Vehicle Bogged Down', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'vehicle_bogged_down', 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Vehicle Bogged Down', 'vehicle_bogged_down', ['0','1'], 'recovered_date', '1');
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Vehicle Bogged Down', 'vehicle_bogged_down');
    }

    public function paymentProcess(\Illuminate\Http\Request $request, $id)
    {
        Log::info('Payment Process Request', [
        'id' => $id,
        'request' => $request->all()
        ]);
        return $this->handlePaymentProcess($request, $id, 'Vehicle Bogged Down', ['allowed_statuses'    => ['0', '1', '2'],'date_column' => 'recovered_date','status_that_sets_date' => '1',]);
    }
}
