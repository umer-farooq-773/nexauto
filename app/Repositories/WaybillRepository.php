<?php

namespace App\Repositories;

use App\Interfaces\WaybillInterface;
use App\Dto\WaybillDto;
use App\Models\Waybill;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaybillRepository extends BaseRepository implements WaybillInterface
{
    public function __construct(Waybill $model)
    {
        $this->setModel($model);
    }

    public function show($id)
    {
        return $this->_model->with(['cargos', 'vehicle', 'driver', 'driverPlans.driver', 'status', 'route_trip', 'files'])->find($id);
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
                'driverPlans.driver',
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
            'driverPlans.driver:id,first_name,last_name',
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
        return $this->_model->with(['cargos', 'vehicle', 'driver', 'driverPlans.driver', 'status', 'route_trip', 'files'])
            ->whereHas('status', function ($query) {
                $query->where('value', 0);
            })
            ->latest()
            ->paginate(15);
    }

    public function store(WaybillDto $data)
    {
        return DB::transaction(function () use ($data) {
            $dataArray = $data->toArray();
            $this->lockVehicleForWaybill(
                (string) $dataArray['vehicle_id'],
                null,
                $this->isActiveVehicleWaybillStatus($dataArray['status_id'] ?? null),
            );
            $cargos = $dataArray['cargos'] ?? null;
            $drivers = $dataArray['drivers'];
            unset($dataArray['cargos'], $dataArray['drivers']);
            $dataArray['driver_id'] = (string) $drivers[0]['driver_id'];
            $dataArray['created_by'] = Auth::id();

            $waybill = $this->add($this->_model, $dataArray);
            $this->createDriverPlans($waybill, $drivers);
            $this->syncCargos($waybill, $cargos, $dataArray);

            return $waybill->fresh(['cargos', 'driverPlans.driver']);
        });
    }

    public function update($id, WaybillDto $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $dataArray = $data->toArray();
            $this->lockVehicleForWaybill(
                (string) $dataArray['vehicle_id'],
                (string) $id,
                $this->isActiveVehicleWaybillStatus($dataArray['status_id'] ?? null),
            );

            $result = $this->_model->newQuery()->with('driverPlans')->lockForUpdate()->find($id);
            if (!$result) {
                return false;
            }

            $cargos = $dataArray['cargos'] ?? null;
            $drivers = $dataArray['drivers'];
            unset($dataArray['cargos'], $dataArray['drivers']);
            $dataArray['driver_id'] = (string) $drivers[0]['driver_id'];

            $this->syncDriverPlans($result, $drivers);
            $result->update($dataArray);
            $this->syncCargos($result, $cargos, $dataArray);

            $statusValue = \App\Models\Status::query()->whereKey($dataArray['status_id'] ?? null)->value('value');
            if (in_array((int) $statusValue, [3, 4, 6], true)) {
                $result->driverPlans()->whereIn('status', ['active', 'in_progress'])->update(['status' => 'completed']);
            }

            return $result->fresh(['cargos', 'driverPlans.driver']);
        });
    }

    public function lockVehicleForWaybill(string $vehicleId, ?string $ignoreWaybillId = null, bool $requireAvailability = true): void
    {
        if (!$requireAvailability || empty($vehicleId)) {
            return;
        }

        $vehicle = Vehicle::query()->lockForUpdate()->find($vehicleId);
        if (!$vehicle) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'The selected vehicle is no longer available.',
            ]);
        }

        $activeWaybill = Waybill::query()
            ->where('vehicle_id', $vehicleId)
            ->whereHas('status', fn ($query) => $query->whereIn('value', Waybill::ACTIVE_STATUS_VALUES))
            ->when($ignoreWaybillId, fn ($query) => $query->whereKeyNot($ignoreWaybillId))
            ->lockForUpdate()
            ->first(['id', 'waybill_number']);

        if ($activeWaybill) {
            throw ValidationException::withMessages([
                'vehicle_id' => sprintf(
                    'This vehicle is already assigned to active waybill %s. It becomes available when that waybill is delivered or completed.',
                    $activeWaybill->waybill_number,
                ),
            ]);
        }
    }

    private function isActiveVehicleWaybillStatus(?string $statusId): bool
    {
        if ($statusId === null) {
            return false;
        }

        $statusValue = \App\Models\Status::query()->whereKey($statusId)->value('value');

        return $statusValue !== null
            && in_array((int) $statusValue, Waybill::ACTIVE_STATUS_VALUES, true);
    }

    public function driversBusy(array $driverIds, ?string $ignoreWaybillId = null): array
    {
        $driverIds = array_values(array_unique(array_filter($driverIds)));
        if ($driverIds === []) {
            return [];
        }

        $waybills = Waybill::query()
            ->with(['status:id,value,badge_label', 'driver:id,first_name,last_name', 'driverPlans.driver:id,first_name,last_name'])
            ->whereHas('status', fn ($query) => $query->whereIn('value', Waybill::ACTIVE_STATUS_VALUES))
            ->when($ignoreWaybillId, fn ($query) => $query->whereKeyNot($ignoreWaybillId))
            ->get();

        $matches = [];
        foreach ($waybills as $waybill) {
            $plans = $waybill->driverPlans;
            $assignedDrivers = $plans->isNotEmpty()
                ? $plans->map(fn ($plan) => $plan->driver)->filter()
                : collect([$waybill->driver])->filter();

            foreach ($assignedDrivers as $driver) {
                if (in_array((string) $driver->id, $driverIds, true)) {
                    $value = (int) $waybill->status->value;
                    $matches[] = [
                        'driver_id' => (string) $driver->id,
                        'driver_name' => $driver->full_name,
                        'waybill_id' => (string) $waybill->id,
                        'waybill_number' => (string) $waybill->waybill_number,
                        'status_value' => $value,
                        'status_label' => $waybill->status->badge_label ?: match ($value) {
                            0 => 'Awaiting', 1 => 'Allocated', 2 => 'In transit', 5 => 'Return transit', default => 'Active',
                        },
                    ];
                }
            }
        }

        return $matches;
    }

    private function extractPlannedSeconds(array $driver): ?int
    {
        if (isset($driver['duration_seconds']) && $driver['duration_seconds'] !== '' && $driver['duration_seconds'] !== null) {
            return (int) $driver['duration_seconds'];
        }

        if (isset($driver['duration_minutes']) && $driver['duration_minutes'] !== '' && $driver['duration_minutes'] !== null) {
            return (int) $driver['duration_minutes'] * 60;
        }

        return null;
    }

    private function createDriverPlans(Waybill $waybill, array $drivers): void
    {
        foreach (array_values($drivers) as $index => $driver) {
            $waybill->driverPlans()->create([
                'driver_id' => (string) $driver['driver_id'],
                'sequence' => $index + 1,
                'planned_seconds' => $this->extractPlannedSeconds($driver),
                'status' => 'pending',
            ]);
        }
    }

    private function syncDriverPlans(Waybill $waybill, array $drivers): void
    {
        $drivers = array_values($drivers);
        $existing = $waybill->driverPlans()->orderBy('sequence')->get();

        // Only active (in-progress) plans cannot have their assigned driver swapped mid-trip
        foreach ($existing->where('status', 'active') as $plan) {
            $submitted = $drivers[$plan->sequence - 1] ?? null;

            if ($submitted === null || (string) $submitted['driver_id'] !== (string) $plan->driver_id) {
                throw ValidationException::withMessages([
                    'drivers' => "Driver plan row {$plan->sequence} is active and cannot be changed or removed.",
                ]);
            }
        }

        $submittedIds = array_map(fn ($driver) => (string) $driver['driver_id'], $drivers);
        $pending = $existing->where('status', 'pending');
        foreach ($pending as $plan) {
            if (!in_array((string) $plan->driver_id, $submittedIds, true)) {
                $plan->delete();
            }
        }

        $remainingPending = $waybill->driverPlans()->where('status', 'pending')->get();
        foreach ($remainingPending->values() as $index => $plan) {
            $plan->sequence = 50000 + $index;
            $plan->save();
        }

        foreach ($drivers as $index => $driver) {
            $sequence = $index + 1;
            $seconds = $this->extractPlannedSeconds($driver);
            $plan = $waybill->driverPlans()->where('driver_id', (string) $driver['driver_id'])->first();

            if ($plan) {
                $plan->sequence = $sequence;
                if ($seconds !== null || $plan->status === 'pending') {
                    $plan->planned_seconds = $seconds;
                }
                $plan->save();
                continue;
            }

            // If not found by driver_id, check if there is an existing plan at this sequence to update
            $planAtSeq = $existing->firstWhere('sequence', $sequence);
            if ($planAtSeq && $planAtSeq->status !== 'active') {
                $planAtSeq->driver_id = (string) $driver['driver_id'];
                $planAtSeq->planned_seconds = $seconds;
                $planAtSeq->save();
                continue;
            }

            $waybill->driverPlans()->create([
                'driver_id' => (string) $driver['driver_id'],
                'sequence' => $sequence,
                'planned_seconds' => $seconds,
                'status' => 'pending',
            ]);
        }
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
