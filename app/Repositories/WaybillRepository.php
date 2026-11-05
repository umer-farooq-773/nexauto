<?php

namespace App\Repositories;

use App\Interfaces\WaybillInterface;
use App\Dto\WaybillDto;
use App\Models\Waybill;
use Illuminate\Support\Facades\Auth;

class WaybillRepository extends BaseRepository implements WaybillInterface
{
    public function __construct(Waybill $model)
    {
        $this->setModel($model);
    }

    public function show($id)
    {
        return $this->_model->with(['cargos', 'vehicle', 'driver', 'status', 'route_trip', 'files'])->find($id);
    }

    public function showWithIncidents(string $id)
    {
        return $this->get_record_by_id_with_pagination(
            $this->_model,
            $id,
            [
                'cargos',
                'vehicle',
                'driver',
                'status',
                'route_trip',
                'files',
                'creator',
                'impoundment',
                'accident',
                'vehicle_breakdown',
                'vehicle_bogged_down',
                'illegal_detention',
                'illegal_haulage',
                'illegal_shortage',
                'breathaliser_test',
                'route_diversion',
                'driver_misconduct',
                'accidents',
                'vehicle_breakdowns',
                'vehicle_bogged_downs',
                'impoundments',
                'route_diversions',
                'illegal_detentions',
                'illegal_haulages',
                'illegal_shortages',
                'breathaliser_tests',
                'driver_misconducts',
                'goods_inspections',
            ]
        );
    }

    public function index($relation = null)
    {
        // Load incident relations so incident_status_badge and incident_count
        // accessors on the Waybill model can resolve correctly on the list page.
        return $this->get_all($this->_model, [
            'cargos',
            'vehicle:id,fleet_no,truck_no,gps_unit_id',
            'driver:id,first_name,last_name',
            'status:id,value,badge_label,badge_class',
            'route_trip:id,trip_name,origin,destination',
            'impoundment',
            'accident',
            'vehicle_breakdown',
            'vehicle_bogged_down',
            'illegal_detention',
            'illegal_haulage',
            'illegal_shortage',
            'breathaliser_test',
            'route_diversion',
            'driver_misconduct',
        ]);
    }

    public function awaiting()
    {
        return $this->_model->with(['cargos', 'vehicle', 'driver', 'status', 'route_trip', 'files'])
            ->whereHas('status', function ($query) {
                $query->where('value', 0);
            })
            ->latest()
            ->paginate(15);
    }

    public function store(WaybillDto $data)
    {
        $dataArray = $data->toArray();
        $cargos = $dataArray['cargos'] ?? null;
        unset($dataArray['cargos']);

        // Set created_by to authenticated user ID
        $dataArray['created_by'] = Auth::id();

        $waybill = $this->add($this->_model, $dataArray);
        $this->syncCargos($waybill, $cargos, $dataArray);

        return $waybill->fresh(['cargos']);
    }

    public function update($id, WaybillDto $data)
    {
        $result = $this->checkRecord($id);
        if (!$result) {
            return false;
        }

        $dataArray = $data->toArray();
        $cargos = $dataArray['cargos'] ?? null;
        unset($dataArray['cargos']);

        $result->update($dataArray);
        $this->syncCargos($result, $cargos, $dataArray);

        return $result->fresh(['cargos']);
    }

    protected function syncCargos(Waybill $waybill, ?array $cargos, array $dataArray): void
    {
        if ($cargos !== null) {
            $waybill->cargos()->delete();

            $validCargos = array_filter($cargos, function ($item) {
                return !empty($item['cargo_description']) ||
                    (isset($item['quantity']) && $item['quantity'] !== '') ||
                    (isset($item['cargo_weight_kg']) && $item['cargo_weight_kg'] !== '');
            });

            if (!empty($validCargos)) {
                foreach ($validCargos as $cargoData) {
                    $waybill->cargos()->create([
                        'cargo_description' => $cargoData['cargo_description'] ?? null,
                        'quantity'          => isset($cargoData['quantity']) && $cargoData['quantity'] !== '' ? (float) $cargoData['quantity'] : null,
                        'quantity_unit'     => $cargoData['quantity_unit'] ?? null,
                        'cargo_weight_kg'   => isset($cargoData['cargo_weight_kg']) && $cargoData['cargo_weight_kg'] !== '' ? (float) $cargoData['cargo_weight_kg'] : null,
                        'weight_unit'       => $cargoData['weight_unit'] ?? null,
                        'volume_unit'       => $cargoData['volume_unit'] ?? null,
                    ]);
                }
            } elseif (!empty($dataArray['cargo_description']) || isset($dataArray['quantity'])) {
                // Fallback to single cargo row from root fields if no addons given
                $waybill->cargos()->create([
                    'cargo_description' => $dataArray['cargo_description'] ?? null,
                    'quantity'          => isset($dataArray['quantity']) && $dataArray['quantity'] !== '' ? (float) $dataArray['quantity'] : null,
                    'quantity_unit'     => $dataArray['quantity_unit'] ?? null,
                    'cargo_weight_kg'   => isset($dataArray['cargo_weight_kg']) && $dataArray['cargo_weight_kg'] !== '' ? (float) $dataArray['cargo_weight_kg'] : null,
                    'weight_unit'       => $dataArray['weight_unit'] ?? null,
                    'volume_unit'       => $dataArray['volume_unit'] ?? null,
                ]);
            }
        }
    }
}

