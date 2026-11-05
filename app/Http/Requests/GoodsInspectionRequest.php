<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class GoodsInspectionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('fleet_no') && $this->filled('vehicle_id')) {
            $this->merge([
                'fleet_no' => Vehicle::whereKey($this->input('vehicle_id'))->value('fleet_no'),
            ]);
        }
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'inspection_id'           => ['nullable', 'uuid', 'exists:goods_inspections,id'],
            'incident_type'           => ['nullable', 'string', 'max:50'],
            'vehicle_id'              => ['nullable', 'uuid', 'exists:vehicles,id'],
            'waybill_id'              => ['nullable', 'uuid', 'exists:waybills,id'],
            'fleet_no'                => ['required', 'string', 'max:50'],
            'vehicle_lorry_number'    => ['nullable', 'string', 'max:100'],
            'inspection_date'         => ['required', 'date'],
            'inspection_time'         => ['nullable', 'string', 'max:50'],
            'inspection_type'         => ['required', 'string'],
            'inspection_location'     => ['nullable', 'string', 'max:255'],
            'inspection_location_lat' => ['nullable', 'numeric'],
            'inspection_location_lng' => ['nullable', 'numeric'],
            'inspector_name'          => ['required', 'string', 'max:255'],
            'route'                   => ['nullable', 'string', 'max:255'],
            'driver_name'             => ['nullable', 'string', 'max:255'],
            'driver_phone_id'         => ['nullable', 'string', 'max:100'],
            'goods_description'       => ['nullable', 'string'],
            'waybill_number'          => ['nullable', 'string', 'max:100'],
            'declared_quantity'       => ['nullable', 'string', 'max:50'],
            'observed_quantity'       => ['nullable', 'string', 'max:50'],
            'cargo_secured'           => ['nullable', 'string', 'max:20'],
            'goods_match_waybill'     => ['nullable', 'string', 'max:20'],
            'unauthorised_items'      => ['nullable', 'string', 'max:20'],
            'doors_tarpaulin_secure'  => ['nullable', 'string', 'max:20'],
            'visible_damage'          => ['nullable', 'string', 'max:20'],
            'route_diversion'         => ['nullable', 'string', 'max:50'],
            'illegal_haulage'         => ['nullable', 'string', 'max:50'],
            'illegal_shortage'        => ['nullable', 'string', 'max:50'],
            'breathaliser_test'       => ['nullable', 'string', 'max:50'],
            'driver_misconduct'       => ['nullable', 'string', 'max:50'],
            'issues_found'            => ['nullable', 'string'],
            'goods_status'            => ['required', 'string'],
            'status'                  => ['nullable', 'in:0,1'],
            'remarks'                 => ['nullable', 'string'],
            'cargos'                  => ['nullable', 'array'],
            'cargos.*.waybill_cargo_id' => ['nullable'],
            'cargos.*.cargo_description' => ['nullable', 'string'],
            'cargos.*.declared_quantity' => ['nullable', 'numeric'],
            'cargos.*.observed_quantity' => ['nullable', 'numeric'],
            'cargos.*.difference_quantity' => ['nullable', 'numeric'],
            'cargos.*.quantity_unit'  => ['nullable', 'string'],
            'cargos.*.cargo_weight'   => ['nullable', 'numeric'],
            'cargos.*.cargo_weight_kg' => ['nullable', 'numeric'],
            'cargos.*.weight_unit'    => ['nullable', 'string'],
            'cargos.*.volume_unit'    => ['nullable', 'string'],
            'cargos.*.goods_match_waybill' => ['nullable', 'string'],
            'cargos.*.illegal_shortage' => ['nullable', 'string'],
            'cargos.*.illegal_haulage' => ['nullable', 'string'],
            'cargos.*.remarks'        => ['nullable', 'string'],
        ];
    }
}
