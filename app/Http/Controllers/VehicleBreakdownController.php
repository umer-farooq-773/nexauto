<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Dto\VehicleBreakdownDto;
use App\Interfaces\VehicleBreakdownInterface;
use App\Interfaces\VehicleInterface;
use App\Http\Requests\VehicleBreakdownRequest;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class VehicleBreakdownController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(VehicleBreakdownInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.vehicle-breakdown', 'vehicle_breakdown');
    }

    private function getModuleContext(): array
    {
        return [
            'route_prefix' => 'vehicle_breakdown',
            'module_title' => 'Vehicle Breakdown',
            'record_title' => 'Breakdown Record',
            'date_label' => 'Breakdown Date',
            'location_label' => 'Breakdown Location',
            'type_label' => 'Breakdown Type',
            'type_placeholder' => 'e.g. Engine Failure, Flat Tyre',
            'cost_label' => 'Repair Cost',
            'resolved_date_label' => 'Resolved Date',
            'description_label' => 'Description',
            'description_placeholder' => 'Breakdown details...',
        ];
    }

    public function index()
    {
        $module = $this->getModuleContext();
        $data['all']             = $this->_repo->index();
        $all                     = $this->_repo->all();
        $data['total_count']     = count($all);
        $data['resolved_count']  = count($all->where('status', '1'));
        $data['pending_count']   = count($all->where('status', '0'));
        $data['inprogress_count'] = count($all->where('status', '2'));
        $data['vehicles']        = app()->make(VehicleInterface::class)->all();
        $data['waybills']        = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        $data['has_allocated']   = \App\Models\Waybill::whereHas('status', function($q) {
            $q->where('value', 1); // Allocated
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

        $payment_paid_by = null;
        if ($data->created_by) {
            $payment_paid_by = User::where('id', $data->created_by)->select('first_name', 'last_name')->first();
        }

        $payments = PaymentHistory::where('payable_type', get_class($data))->where('payable_id', $data->id)->orderBy('created_at', 'desc')->get();

        if (!$data) abort(404);
        return view($this->_directory . '.show', compact('data', 'module', 'approved_by', 'payment_paid_by', 'payments'));
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

    public function store(VehicleBreakdownRequest $request)
    {
        $module = $this->getModuleContext();
        try {
            $record = $this->_repo->store(VehicleBreakdownDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/vehicle-breakdown/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'vehicle_breakdown', 'Vehicle Breakdown', $showUrl);

            return redirect()->route($module['route_prefix'] . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $module['route_prefix'] . '.index', ['module' => $module['route_prefix'], 'action' => 'store']);
        }
    }

    public function update(VehicleBreakdownRequest $request, $id)
    {
        $module = $this->getModuleContext();
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, VehicleBreakdownDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/vehicle-breakdown/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'vehicle_breakdown', 'Vehicle Breakdown', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'vehicle_breakdown', 'Vehicle Breakdown', $showUrl);

            return redirect()->route($module['route_prefix'] . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $module['route_prefix'] . '.index', ['module' => $module['route_prefix'], 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Vehicle Breakdown', 'vehicle_breakdown');
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        try {
            $record = $this->_repo->show($id);
            if (!$record) abort(404);

            // Verify that both client approval and payment approval are done
            if (!$record->client_approved) {
                return redirect()->back()->with('error', __('Client approval is required before changing status.'));
            }

            if ((string) ($record->payment_status ?? '') !== '2') {
                return redirect()->back()->with('error', __('Payment must be processed before changing status.'));
            }

            return $this->handleStatusUpdate($request, $id, 'Vehicle Breakdown', 'vehicle_breakdown',['0', '1','2'], 'resolved_date', '1' );
        } catch (\Throwable $th) {
            Log::error('Status Update Error: ' . $th->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', __('An error occurred while updating status.'));
        }
    }

    public function paymentProcess(\Illuminate\Http\Request $request, $id)
    {
        Log::info('Payment Process Request', [
        'id' => $id,
        'request' => $request->all()
        ]);
        return $this->handlePaymentProcess($request, $id, 'Vehicle Breakdown', ['allowed_statuses'      => ['0', '1', '2'],'date_column' => 'resolved_date','status_that_sets_date' => '1',]);
    }
}
