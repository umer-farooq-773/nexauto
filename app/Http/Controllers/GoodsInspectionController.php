<?php

namespace App\Http\Controllers;

use App\Dto\GoodsInspectionDto;
use App\Exceptions\InspectionIncidentsRequiredException;
use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Http\Requests\GoodsInspectionRequest;
use App\Interfaces\GoodsInspectionInterface;
use App\Interfaces\VehicleInterface;
use App\Models\GoodsInspectionFinding;
use App\Services\InspectionIncidentRequirementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GoodsInspectionController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(
        GoodsInspectionInterface $repo,
        protected InspectionIncidentRequirementService $incidentRequirements
    ) {
        $this->setRepo($repo, 'auth.pages.goods-inspection', 'goods_inspection');
    }

    public function index()
    {
        $data['all']             = $this->_repo->index();
        $all                     = $this->_repo->all();
        $data['total_count']     = count($all);
        $data['completed_count'] = count($all->where('status', '1'));
        $data['pending_count']   = count($all->where('status', '0'));
        $data['cleared_count']   = count($all->where('goods_status', 'cleared'));
        $data['issues_count']    = count($all->where('goods_status', 'issues'));
        $data['vehicles']        = app()->make(VehicleInterface::class)->all();
        $data['waybills']        = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip', 'driver', 'cargos'])
                                        ->whereHas('status', fn($q) => $q->where('value', 2))->get();

        return view($this->_directory . '.all', compact('data'));
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);
        $incidentEvaluation = $this->incidentRequirements->evaluate($data);
        return view($this->_directory . '.show', compact('data', 'incidentEvaluation'));
    }

    public function edit($id)
    {
        $data     = $this->_repo->show($id);
        if (!$data) abort(404);
        $vehicles = app()->make(VehicleInterface::class)->all();
        $waybills = \App\Models\Waybill::with(['vehicle', 'status', 'route_trip', 'driver', 'cargos'])
                        ->where(function($q) use ($data) {
                            $q->whereHas('status', fn($sq) => $sq->where('value', 2))
                              ->orWhere('id', $data->waybill_id);
                        })->get();
        $incidentEvaluation = $this->incidentRequirements->evaluate($data);
        return view($this->_directory . '.edit', compact('data', 'vehicles', 'waybills', 'incidentEvaluation'));
    }


    public function store(GoodsInspectionRequest $request)
    {
        try {
            $created = $this->_repo->store(GoodsInspectionDto::fromRequest($request->validated()));
            if ($created && (string) ($created->status ?? '') === '1') {
                app(\App\Services\IncidentWaybillNotificationService::class)->notifyGoodsInspectionCompleted($created);
            }
            return $this->inspectionSavedResponse($request, __('language.successfully_created'));
        } catch (InspectionIncidentsRequiredException $e) {
            return $this->incidentsRequiredResponse($request, $e);
        } catch (\Throwable $th) {
            Log::error('Goods inspection creation failed', [
                'user_id' => auth()->id(),
                'exception' => $th,
            ]);
            return $this->inspectionErrorResponse($request, __('language.something_went_wrong'));
        }
    }

    public function update(GoodsInspectionRequest $request, $id)
    {
        try {
            $this->_repo->update($id, GoodsInspectionDto::fromRequest($request->validated()));
            $updated = $this->_repo->show($id);
            if ($updated && (string) ($updated->status ?? '') === '1') {
                app(\App\Services\IncidentWaybillNotificationService::class)->notifyGoodsInspectionCompleted($updated);
            }
            return $this->inspectionSavedResponse($request, __('language.updated_successfully'));
        } catch (InspectionIncidentsRequiredException $e) {
            return $this->incidentsRequiredResponse($request, $e);
        } catch (\Throwable $th) {
            if ($th instanceof NotFoundHttpException) {
                return $this->inspectionErrorResponse($request, $th->getMessage());
            }
            return $this->inspectionErrorResponse($request, __('language.something_went_wrong'));
        }
    }

    public function evaluateIncidents(GoodsInspectionRequest $request)
    {
        $payload = $request->validated();
        $inspection = !empty($payload['inspection_id'] ?? null)
            ? ($this->_repo->show($payload['inspection_id']) ?: new \App\Models\GoodsInspection())
            : new \App\Models\GoodsInspection();

        $inspection->fill($this->incidentRequirements->applyWaybillDeclaredQuantity($payload));

        return response()->json($this->incidentRequirements->evaluate($inspection, $payload));
    }

    public function createLinkedIncident(GoodsInspectionRequest $request)
    {
        $validated = $request->validated();
        $type = $request->validate([
            'incident_type' => ['required', 'string'],
        ])['incident_type'];

        try {
            $inspection = $this->_repo->saveDraft($validated, $validated['inspection_id'] ?? null);
            $result = $this->incidentRequirements->createIncident($inspection, $type);

            if ($result['created'] && $result['notify_type'] && $inspection->waybill_id) {
                $this->touchIncidentWaybill($inspection->waybill_id);
                $this->notifyIncidentWaybill(
                    $inspection->waybill_id,
                    $result['notify_type'],
                    $result['label'],
                    $result['show_url']
                );
            }

            return response()->json([
                'message' => $result['created']
                    ? $result['label'] . ' incident created.'
                    : $result['label'] . ' incident already exists.',
                'created' => $result['created'],
                'incident_id' => $result['record']->id,
                'inspection_id' => $inspection->id,
                'edit_url' => $result['edit_url'],
                'evaluation' => $this->incidentRequirements->evaluate($inspection->fresh()),
            ]);
        } catch (InspectionIncidentsRequiredException $e) {
            return $this->incidentsRequiredResponse($request, $e);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $th) {
            Log::error('Inspection incident creation failed', [
                'user_id' => auth()->id(),
                'type' => $type,
                'exception' => $th,
            ]);

            return response()->json([
                'message' => $th->getMessage() ?: __('language.something_went_wrong'),
            ], 422);
        }
    }

    public function editFinding(GoodsInspectionFinding $finding)
    {
        $finding->load('inspection');

        return view($this->_directory . '.finding-edit', compact('finding'));
    }

    public function updateFinding(Request $request, GoodsInspectionFinding $finding)
    {
        $data = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $finding->update($data);

        return redirect()
            ->route($this->_route . '.edit', $finding->inspection_id)
            ->with('success', __('language.updated_successfully'));
    }

    public function clientApprove(Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Goods Inspection', 'goods_inspection');
    }

    public function destroy($id)
    {
        try {
            $this->_repo->destroy($id);
            return redirect()->route($this->_route . '.index')->with('success', __('language.deleted_successfully'));
        } catch (\Throwable $th) {
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    protected function inspectionSavedResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route($this->_route . '.index')->with('success', $message);
    }

    protected function inspectionErrorResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->route($this->_route . '.index')->with('error', $message);
    }

    protected function incidentsRequiredResponse(Request $request, InspectionIncidentsRequiredException $e)
    {
        $payload = [
            'message' => $e->getMessage(),
            'missing_incidents' => $e->evaluation['missing'],
            'evaluation' => $e->evaluation,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, 422);
        }

        throw ValidationException::withMessages([
            'incidents' => $e->getMessage(),
        ]);
    }
}
