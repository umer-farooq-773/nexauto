<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Dto\ImpoundmentDto;
use App\Interfaces\ImpoundmentInterface;
use App\Interfaces\VehicleInterface;
use App\Http\Requests\ImpoundmentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Constants\Constants;
use App\Models\Impoundment;
use App\Models\PaymentHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImpoundmentController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(ImpoundmentInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.incident.impoundment', 'impoundment');
    }

    public function index()
    {
        $data['all']             = $this->_repo->index()->load('vehicle.drivers');
        $all                     = $this->_repo->all();
        $data['total_count']     = count($all);
        $data['released_count']  = count($all->where('status', '1'));
        $data['impounded_count'] = count($all->where('status', '0'));
        $data['vehicles']        = app()->make(VehicleInterface::class)->all();

        $data['waybills']        = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip'])
            ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        $data['has_allocated']   = \App\Models\Waybill::whereHas('status', function($q) {
            $q->where('value', 1); // Allocated
        })->exists();

        return view($this->_directory . '.all', compact('data'));
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);
        
        // Only load the related waybill if it exists, not all vehicle waybills
        // This is much more efficient than loading all waybills for the vehicle
        $waybills = $data->waybill_id 
            ? collect([$data->waybill()->with(['driver:id,first_name,last_name', 'route_trip:id,trip_name'])->first()]) 
            : collect();
            
        $approved_by = null;
        if ($data->client_approved_by) {
            $approved_by = User::where('id', $data->client_approved_by)->select('first_name', 'last_name')->first();
        }
        $payment_paid_by = null;
        if ($data->created_by) {
            $payment_paid_by = User::where('id', $data->created_by)->select('first_name', 'last_name')->first();
        }

        $payments = PaymentHistory::where('payable_type', get_class($data))->where('payable_id', $data->id)->orderBy('created_at', 'desc')->get();

        return view($this->_directory . '.show', compact('data', 'waybills', 'approved_by','payments','payment_paid_by'));
    }


    public function changeStatus(Request $request, $id)
    {
        try {
            // Find the record
            $impoundment = Impoundment::findOrFail($id);

            $impoundment->status = $request->status;
            $impoundment->release_date = Carbon::now()->format('D M Y');
            $impoundment->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $impoundment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
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

    public function store(ImpoundmentRequest $request)
    {
        try {
            $record = $this->_repo->store(ImpoundmentDto::fromRequest($request->validated()));
            $this->syncIncidentDocument($request, $record, 'incidents/impoundment/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'impoundment', 'Impoundment', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'impoundment', 'action' => 'store']);
        }
    }

    public function update(ImpoundmentRequest $request, $id)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $this->_repo->update($id, ImpoundmentDto::fromRequest($request->validated()));
            $record = $this->_repo->show($id);
            $this->syncIncidentDocument($request, $record, 'incidents/impoundment/');
            $this->touchIncidentWaybill($request->input('waybill_id'));
            $showUrl = route($this->_route . '.show', $record);
            $this->notifyIncidentWaybill($request->input('waybill_id'), 'impoundment', 'Impoundment', $showUrl);
            $this->fireIncidentStatusChangedEvent($request, $record, 'impoundment', 'Impoundment', $showUrl);

            return redirect()->route($this->_route . '.index')->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse($th, $this->_route . '.index', ['module' => 'impoundment', 'action' => 'update', 'record_id' => $id]);
        }
    }

    /**
     * Status-only update — used by the show-page modal and the edit-page status field.
     * Only Admin, SuperAdmin, and Champs Management can call this, and only after
     * the Client Director has approved the record.
     */
    // public function statusUpdate(\Illuminate\Http\Request $request, $id)
    // {
    //     try {
    //         $existing = $this->_repo->show($id);
    //         if (!$existing) abort(404);

    //         // Must have client approval first
    //         if (!($existing->client_approved ?? false)) {
    //             return redirect()->back()
    //                 ->with('error', 'Client Director approval is required before changing the status of this incident.');
    //         }

    //         // Only Admin, SuperAdmin, Champs Management may change status
    //         $user = auth()->user();
    //         if (!$user->hasRole([Constants::ADMIN, Constants::SUPERADMIN, Constants::CHAMPS_MANAGEMENT])) {
    //             return redirect()->back()
    //                 ->with('error', 'You do not have permission to change the status of this incident.');
    //         }

    //         $request->validate([
    //             'status'  => ['required', 'in:0,1'],
    //             'remarks' => ['nullable', 'string'],
    //         ]);

    //         $updateData = ['status' => $request->input('status')];
    //         if ($request->filled('remarks')) {
    //             $updateData['remarks'] = $request->input('remarks');
    //         }

    //         // Store old status for event
    //         $oldStatus = (string) $existing->status;
    //         $existing->update($updateData);
    //         $record = $this->_repo->show($id);

    //         $showUrl = route($this->_route . '.show', $record);

    //         // Fire status changed event if status actually changed
    //         if ($oldStatus !== (string) $record->status) {
    //             $request->merge(['_incident_old_status' => $oldStatus]);
    //             $this->fireIncidentStatusChangedEvent($request, $record, 'impoundment', 'Impoundment', $showUrl);
    //         }

    //         return redirect()->route($this->_route . '.show', $id)
    //             ->with('success', __('language.updated_successfully'));

    //     } catch (\Throwable $th) {
    //         return $this->incidentFailureResponse($th, $this->_route . '.show', ['module' => 'impoundment', 'action' => 'statusUpdate', 'record_id' => $id]);
    //     }
    // }

    /**
     * Client Director approves the record.
     */
    public function clientApprove(\Illuminate\Http\Request $request, $impoundmentId)
    {
        return $this->handleClientApprove($request, $impoundmentId, 'Impoundment', 'impoundment');
    }

    public function paymentProcess(\Illuminate\Http\Request $request, $id)
    {
        Log::info('Impoundment Request', [
        'id' => $id,
        'request' => $request->all()
        ]);
        return $this->handlePaymentProcess($request, $id, 'Impoundment',['allowed_statuses'      => ['0', '1'],'date_column' => 'release_date','status_that_sets_date' => '1',]);
    }

    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Impoundment', 'impoundment');
    }
}
