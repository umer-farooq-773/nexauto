<?php

namespace App\Http\Controllers;

use App\Dto\BreathaliserTestDto;
use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Http\Requests\BreathaliserTestRequest;
use App\Interfaces\BreathaliserTestInterface;
use App\Interfaces\DriverDetailInterface;
use App\Interfaces\VehicleInterface;
use App\Models\PaymentHistory;
use App\Models\User;

class BreathaliserTestController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(BreathaliserTestInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.breathaliser-test', 'breathaliser_test');
    }

    public function index()
    {
        $data['all'] = $this->_repo->index();
        $all = $this->_repo->all();
        $data['total_count'] = count($all);
        $data['open_count'] = count($all->where('status', '0'));
        $data['review_count'] = count($all->where('status', '1'));
        $data['closed_count'] = count($all->where('status', '2'));
        $data['positive_count'] = count($all->where('test_result', 'positive'));
        $data['drivers'] = app()->make(DriverDetailInterface::class)->all();
        $data['vehicles'] = app()->make(VehicleInterface::class)->all();
        $data['waybills'] = \App\Models\Waybill::with(['vehicle', 'driver', 'status'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();
        $data['has_allocated'] = \App\Models\Waybill::whereHas('status', function ($q) {
            $q->where('value', 1);
        })->exists();

        return view($this->_directory . '.all', compact('data'));
    }

    public function show($id)
    {
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

        if (!$data) {
            abort(404);
        }

        return view($this->_directory . '.show', compact('data','payment_paid_by','payments','approved_by') + [
            'statusConfig' => [
                '0' => ['label' => 'Open',     'icon' => 'clock',        'class' => 'pending'],
                '1' => ['label' => 'Reviewed',  'icon' => 'search',       'class' => 'progress'],
                '2' => ['label' => 'Closed',    'icon' => 'check-double', 'class' => 'resolved'],
            ],
        ]);
    }

    public function edit($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) {
            abort(404);
        }

        $drivers = app()->make(DriverDetailInterface::class)->all();
        $vehicles = app()->make(VehicleInterface::class)->all();
        $waybills = \App\Models\Waybill::with(['vehicle', 'driver', 'status'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        return view($this->_directory . '.edit', compact('data', 'drivers', 'vehicles', 'waybills'));
    }

    public function store(BreathaliserTestRequest $request)
    {
        try {
            $record = $this->_repo->store(BreathaliserTestDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/breathaliser-test/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            
            // Pass test result data for email notification
            $additionalData = [
                'test_result' => $record->test_result,
            ];
            
            $this->notifyIncidentWaybill(
                $request->input('waybill_id'), 
                'breathaliser_test', 
                'Breathaliser Test', 
                $showUrl,
                $additionalData
            );

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'breathaliser_test', 'action' => 'store']);
        }
    }

    public function update(BreathaliserTestRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, BreathaliserTestDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/breathaliser-test/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            
            // Pass test result data for email notification
            $additionalData = [
                'test_result' => $record->test_result,
            ];
            
            $this->notifyIncidentWaybill(
                $request->input('waybill_id'), 
                'breathaliser_test', 
                'Breathaliser Test', 
                $showUrl,
                $additionalData
            );
            $this->fireIncidentStatusChangedEvent($request, $record, 'breathaliser_test', 'Breathaliser Test', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'breathaliser_test', 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Breathaliser Test', 'breathaliser_test', ['0', '1', '2'," "," "]);
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Breathaliser Test', 'breathaliser_test');
    }
}
