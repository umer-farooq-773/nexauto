<?php

namespace App\Repositories;

use App\Dto\GoodsInspectionDto;
use App\Exceptions\InspectionIncidentsRequiredException;
use App\Interfaces\GoodsInspectionInterface;
use App\Models\GoodsInspection;
use App\Services\InspectionIncidentRequirementService;
use Illuminate\Support\Facades\DB;

class GoodsInspectionRepository extends BaseRepository implements GoodsInspectionInterface
{
    public function __construct(
        GoodsInspection $model,
        protected InspectionIncidentRequirementService $incidentRequirements
    ) {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['vehicle', 'cargos', 'waybill'])->latest()->paginate(15);
    }

    public function show($id)
    {
        return $this->_model->with(['vehicle', 'cargos.waybillCargo', 'waybill.cargos', 'findings'])->find($id);
    }

    public function store(GoodsInspectionDto $data)
    {
        return DB::transaction(function () use ($data) {
            $payload = $this->preparePayload($data->toArray());
            $cargos = $payload['cargos'] ?? null;
            unset($payload['cargos']);

            if (!empty($data->inspection_id)) {
                $inspection = $this->_model->find($data->inspection_id);
                if ($inspection) {
                    $inspection->update($payload);
                    $this->syncInspectionCargos($inspection, $cargos);
                    $this->ensureRequiredIncidentsCreated($inspection, $payload['status'] ?? '0');

                    return $inspection->fresh(['cargos']);
                }
            }

            $inspection = $this->add($this->_model, $payload);
            $this->syncInspectionCargos($inspection, $cargos);
            $this->ensureRequiredIncidentsCreated($inspection, $payload['status'] ?? '0');

            return $inspection->fresh(['cargos']);
        });
    }

    public function update($id, GoodsInspectionDto $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $result = $this->checkRecord($id);
            $payload = $this->preparePayload($data->toArray());
            $cargos = $payload['cargos'] ?? null;
            unset($payload['cargos']);

            $result->update($payload);
            $this->syncInspectionCargos($result, $cargos);
            $this->ensureRequiredIncidentsCreated($result, $payload['status'] ?? '0');

            return $result->fresh(['cargos']);
        });
    }

    /**
     * Persist inspection findings as a draft (never completed) so incidents can be attached.
     */
    public function saveDraft(array $payload, ?string $id = null): GoodsInspection
    {
        return DB::transaction(function () use ($payload, $id) {
            $payload = $this->preparePayload($payload);
            $payload['status'] = '0';
            $cargos = $payload['cargos'] ?? null;
            unset($payload['cargos']);

            if ($id) {
                $inspection = $this->checkRecord($id);
                if (!$inspection) {
                    abort(404);
                }
                $inspection->update($payload);
                $this->syncInspectionCargos($inspection, $cargos);

                return $inspection->fresh(['cargos']);
            }

            $inspection = $this->add($this->_model, $payload);
            $this->syncInspectionCargos($inspection, $cargos);

            return $inspection->fresh(['cargos']);
        });
    }

    protected function syncInspectionCargos(GoodsInspection $inspection, ?array $cargos): void
    {
        if ($cargos !== null) {
            $inspection->cargos()->delete();

            foreach ($cargos as $item) {
                if (empty($item['cargo_description']) && (!isset($item['declared_quantity']) || $item['declared_quantity'] === '') && (!isset($item['observed_quantity']) || $item['observed_quantity'] === '')) {
                    continue;
                }
                $declared = isset($item['declared_quantity']) && $item['declared_quantity'] !== '' ? (float) $item['declared_quantity'] : null;
                $observed = isset($item['observed_quantity']) && $item['observed_quantity'] !== '' ? (float) $item['observed_quantity'] : null;
                $diff = ($declared !== null && $observed !== null) ? abs($declared - $observed) : (isset($item['difference_quantity']) ? (float)$item['difference_quantity'] : null);

                $inspection->cargos()->create([
                    'waybill_cargo_id'    => !empty($item['waybill_cargo_id']) ? $item['waybill_cargo_id'] : null,
                    'cargo_description'   => $item['cargo_description'] ?? null,
                    'declared_quantity'   => $declared,
                    'observed_quantity'   => $observed,
                    'difference_quantity' => $diff,
                    'quantity_unit'       => $item['quantity_unit'] ?? null,
                    'cargo_weight_kg'     => isset($item['cargo_weight_kg']) && $item['cargo_weight_kg'] !== '' ? (float) $item['cargo_weight_kg'] : null,
                    'weight_unit'         => $item['weight_unit'] ?? null,
                    'volume_unit'         => $item['volume_unit'] ?? null,
                    'goods_match_waybill' => $item['goods_match_waybill'] ?? 'Yes',
                    'illegal_shortage'    => $item['illegal_shortage'] ?? 'No',
                    'illegal_haulage'     => $item['illegal_haulage'] ?? 'No',
                ]);
            }
        }
    }

    protected function preparePayload(array $payload): array
    {
        return $this->incidentRequirements->applyWaybillDeclaredQuantity($payload);
    }

    protected function ensureRequiredIncidentsCreated(GoodsInspection $inspection, mixed $status): void
    {
        if ((string) $status !== '1') {
            return;
        }

        $evaluation = $this->incidentRequirements->evaluate($inspection);
        foreach ($evaluation['missing'] as $missingKey) {
            $this->incidentRequirements->createIncident($inspection, $missingKey);
        }
    }
}

