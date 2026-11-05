<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Dto\AccidentDto;
use App\Interfaces\AccidentInterface;
use App\Interfaces\VehicleInterface;
use App\Http\Requests\AccidentRequest;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AccidentController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(AccidentInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.accident', 'accident');
    }

    public function index()
    {
        $data['all']              = $this->_repo->index();
        $all                      = $this->_repo->all();
        $data['total_count']      = count($all);
        $data['resolved_count']   = count($all->where('status', '1'));
        $data['pending_count']    = count($all->where('status', '0'));
        $data['inprogress_count'] = count($all->where('status', '2'));
        $data['vehicles']         = app()->make(VehicleInterface::class)->all();
        $data['waybills']         = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        $data['has_allocated']    = \App\Models\Waybill::whereHas('status', function($q) {
            $q->where('value', 1); // Allocated
        })->exists();

        return view($this->_directory . '.all', compact('data'));
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);

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

        return view($this->_directory . '.show', compact('data','approved_by','payment_paid_by','payments'));
    }

    public function edit($id)
    {
        $data     = $this->_repo->show($id);
        if (!$data) abort(404);
        $vehicles = app()->make(VehicleInterface::class)->all();
        $waybills = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();
        return view($this->_directory . '.edit', compact('data', 'vehicles', 'waybills'));
    }

    public function store(AccidentRequest $request)
    {
        try {
            $record = $this->_repo->store(AccidentDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/accident/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'accident', 'Accident', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'accident', 'action' => 'store']);
        }
    }

    public function update(AccidentRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, AccidentDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/accident/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'accident', 'Accident', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'accident', 'Accident', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'accident', 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Accident', 'accident', ['0', '1'], 'resolved_date');
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Accident', 'accident');
    }

    public function paymentProcess(\Illuminate\Http\Request $request, $id)
    {
        Log::info('Payment Process Request', [
        'id' => $id,
        'request' => $request->all()
        ]);
        return $this->handlePaymentProcess($request, $id, 'Accident', ['allowed_statuses' => ['0', '1', '2'],'date_column' => 'resolved_date','status_that_sets_date' => '1',]);
    }
}
