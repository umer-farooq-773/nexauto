<?php

namespace App\Http\Controllers;

use App\Dto\IllegalHaulageDto;
use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Http\Requests\IllegalHaulageRequest;
use App\Interfaces\DriverDetailInterface;
use App\Interfaces\IllegalHaulageInterface;
use App\Interfaces\VehicleInterface;
use App\Models\User;

class IllegalHaulageController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(IllegalHaulageInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.illegal-haulage', 'illegal_haulage');
    }

    public function index()
    {
        $data['all'] = $this->_repo->index();
        $all = $this->_repo->all();
        $data['total_count'] = count($all);
        $data['open_count'] = count($all->where('status', '0'));
        $data['review_count'] = count($all->where('status', '1'));
        $data['resolved_count'] = count($all->where('status', '2'));
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
        // dd(request()->route()->getName());
        $data = $this->_repo->show($id);
        $approved_by = null;
        if ($data->client_approved_by) {
            $approved_by = User::where('id', $data->client_approved_by)->select('first_name', 'last_name')->first();
        }
        $approved_by_name = null;

        if ($data->client_approved_by) {
            $user = User::where('id', $data->client_approved_by)
                ->select('first_name', 'last_name')
                ->first();

            $approved_by_name = $user ? $user->first_name . ' ' . $user->last_name : null;
        }
        if (!$data) {
            abort(404);
        }

        return view($this->_directory . '.show', compact('data','approved_by','approved_by_name') + [
            'statusConfig' => [
                '0' => ['label' => 'Open',         'icon' => 'clock',        'class' => 'pending'],
                '1' => ['label' => 'Under Review',  'icon' => 'search',       'class' => 'progress'],
                '2' => ['label' => 'Resolved',      'icon' => 'check-double', 'class' => 'resolved'],
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

    public function store(IllegalHaulageRequest $request)
    {
        try {
            $record = $this->_repo->store(IllegalHaulageDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/illegal-haulage/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'illegal_haulage', 'Illegal Haulage', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'illegal_haulage', 'action' => 'store']);
        }
    }

    public function update(IllegalHaulageRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, IllegalHaulageDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/illegal-haulage/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'illegal_haulage', 'Illegal Haulage', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'illegal_haulage', 'Illegal Haulage', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'illegal_haulage', 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Illegal Haulage', 'illegal_haulage', ['0', '1', '2'],'', '2');
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Illegal Haulage', 'illegal_haulage');
    }
}
