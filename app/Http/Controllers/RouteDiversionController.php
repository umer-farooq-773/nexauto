<?php

namespace App\Http\Controllers;

use App\Dto\RouteDiversionDto;
use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Http\Requests\RouteDiversionRequest;
use App\Interfaces\RouteDiversionInterface;
use App\Interfaces\VehicleInterface;
use App\Models\User;

class RouteDiversionController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(RouteDiversionInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.route-diversion', 'route_diversion');
    }

    public function index()
    {
        $data['all'] = $this->_repo->index();
        $all = $this->_repo->all();
        $data['total_count'] = count($all);
        $data['active_count'] = count($all->where('status', '0'));
        $data['completed_count'] = count($all->where('status', '1'));
        $data['cancelled_count'] = count($all->where('status', '2'));
        $data['vehicles'] = app()->make(VehicleInterface::class)->all();
        $data['waybills'] = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
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
        if (!$data) {
            abort(404);
        }

        return view($this->_directory . '.show', compact('data','approved_by','payment_paid_by') + [
            'statusConfig' => [
                '0' => ['label' => 'Active',    'icon' => 'clock',        'class' => 'pending'],
                '1' => ['label' => 'Completed', 'icon' => 'check-double', 'class' => 'resolved'],
                '2' => ['label' => 'Cancelled', 'icon' => 'ban',          'class' => 'progress'],
            ],
        ]);
    }

    public function edit($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) {
            abort(404);
        }

        $vehicles = app()->make(VehicleInterface::class)->all();
        $waybills = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        return view($this->_directory . '.edit', compact('data', 'vehicles', 'waybills'));
    }

    public function store(RouteDiversionRequest $request)
    {
        try {
            $record = $this->_repo->store(RouteDiversionDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/route-diversion/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'route_diversion', 'Route Diversion', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'route_diversion', 'action' => 'store']);
        }
    }

    public function update(RouteDiversionRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, RouteDiversionDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/route-diversion/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'route_diversion', 'Route Diversion', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'route_diversion', 'Route Diversion', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'route_diversion', 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Route Diversion', 'route_diversion', ['0', '1', '2'], " ", " ");
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Route Diversion', 'route_diversion');
    }
}
