<?php

namespace App\Http\Controllers;

use App\Dto\IllegalShortageDto;
use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Http\Requests\IllegalShortageRequest;
use App\Interfaces\DriverDetailInterface;
use App\Interfaces\IllegalShortageInterface;
use App\Interfaces\VehicleInterface;
use App\Models\User;
use App\Models\Waybill;
use Illuminate\Http\Request;

class IllegalShortageController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(IllegalShortageInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.illegal-shortage', 'illegal_shortage');
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
        $data['waybills'] = Waybill::with(['vehicle', 'driver', 'status'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();
        $data['has_allocated'] = Waybill::whereHas('status', function ($q) {
            $q->where('value', 1);
        })->exists();

        return view($this->_directory . '.all', compact('data'));
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) {
            abort(404);
        }

        $approved_by = null;
        $approved_by_name = null;

        if ($data->client_approved_by) {
            $approved_by = User::where('id', $data->client_approved_by)
                ->select('first_name', 'last_name')
                ->first();

            $approved_by_name = $approved_by ? $approved_by->first_name . ' ' . $approved_by->last_name : null;
        }

        return view($this->_directory . '.show', compact('data', 'approved_by', 'approved_by_name') + [
            'statusConfig' => [
                '0' => ['label' => 'Open',         'icon' => 'clock',        'class' => 'pending'],
                '1' => ['label' => 'Under Review', 'icon' => 'search',       'class' => 'progress'],
                '2' => ['label' => 'Resolved',     'icon' => 'check-double', 'class' => 'resolved'],
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
        $waybills = Waybill::with(['vehicle', 'driver', 'status'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        return view($this->_directory . '.edit', compact('data', 'drivers', 'vehicles', 'waybills'));
    }

    public function store(IllegalShortageRequest $request)
    {
        try {
            $record = $this->_repo->store(IllegalShortageDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/illegal-shortage/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'illegal_shortage', 'Illegal Shortage', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'illegal_shortage', 'action' => 'store']);
        }
    }

    public function update(IllegalShortageRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, IllegalShortageDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/illegal-shortage/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'illegal_shortage', 'Illegal Shortage', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'illegal_shortage', 'Illegal Shortage', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'illegal_shortage', 'action' => 'update', 'record_id' => $id]);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Illegal Shortage', 'illegal_shortage', ['0', '1', '2'], '', '2');
    }

    public function clientApprove(Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Illegal Shortage', 'illegal_shortage');
    }
}
