<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaybillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $normalizeUnit = function ($unit) {
            if (!$unit) return $unit;
            $u = strtoupper(trim($unit));
            return match ($u) {
                'BAGS', 'BAG' => 'BAG',
                'CARTONS', 'CARTON', 'CTN' => 'CTN',
                'BOXES', 'BOX' => 'BOX',
                'PIECES', 'PIECE', 'PCS' => 'PCS',
                'UNITS', 'UNIT' => 'UNIT',
                'PALLETS', 'PALLET', 'PLT' => 'PLT',
                'PACKAGES', 'PACKAGE', 'PKGS', 'PKG' => 'PKG',
                'CRATES', 'CRATE', 'CRT' => 'CRT',
                'DRUMS', 'DRUM', 'DRM' => 'DRM',
                'BARRELS', 'BARREL', 'BRL' => 'BRL',
                'BUNDLES', 'BUNDLE', 'BDL' => 'BDL',
                'BALES', 'BALE', 'BAL' => 'BAL',
                'CONTAINERS', 'CONTAINER', 'CNTR' => 'CNTR',
                'CYLINDERS', 'CYLINDER', 'CYL' => 'CYL',
                'M3', 'CUBIC METER', 'CBM' => 'CBM',
                'LITERS', 'LITRES', 'LITER', 'LITRE', 'L' => 'L',
                'GALLONS', 'GALLON', 'GAL' => 'GAL',
                'TONNES', 'TONS', 'TON', 'MT' => 'MT',
                'KG', 'KILOGRAM', 'KILOGRAMS' => 'KG',
                'G', 'GRAM', 'GRAMS' => 'G',
                'LB', 'LBS', 'POUND', 'POUNDS' => 'LB',
                default => $u,
            };
        };

        $cargos = $this->input('cargos');
        if (is_array($cargos) && !empty($cargos)) {
            $normalizedCargos = [];
            foreach ($cargos as $c) {
                if (isset($c['quantity_unit'])) {
                    $c['quantity_unit'] = $normalizeUnit($c['quantity_unit']);
                }
                if (isset($c['weight_unit'])) {
                    $c['weight_unit'] = $normalizeUnit($c['weight_unit']);
                }
                if (isset($c['volume_unit'])) {
                    $c['volume_unit'] = $normalizeUnit($c['volume_unit']);
                }
                $normalizedCargos[] = $c;
            }
            $this->merge(['cargos' => $normalizedCargos]);
            $first = reset($normalizedCargos);
            if (!$this->filled('quantity') && isset($first['quantity']) && $first['quantity'] !== '') {
                $this->merge(['quantity' => $first['quantity']]);
            }
            if (!$this->filled('cargo_description') && !empty($first['cargo_description'])) {
                $this->merge(['cargo_description' => $first['cargo_description']]);
            }
            if (!$this->filled('quantity_unit') && !empty($first['quantity_unit'])) {
                $this->merge(['quantity_unit' => $first['quantity_unit']]);
            }
            $weight = $first['cargo_weight'] ?? $first['cargo_weight_kg'] ?? null;
            if (!$this->filled('cargo_weight_kg') && $weight !== null && $weight !== '') {
                $this->merge(['cargo_weight_kg' => $weight]);
            }
            if (!$this->filled('weight_unit') && !empty($first['weight_unit'])) {
                $this->merge(['weight_unit' => $first['weight_unit']]);
            }
            if (!$this->filled('volume_unit') && !empty($first['volume_unit'])) {
                $this->merge(['volume_unit' => $first['volume_unit']]);
            }
        }

        if ($this->filled('quantity_unit')) {
            $this->merge(['quantity_unit' => $normalizeUnit($this->input('quantity_unit'))]);
        }
        if ($this->filled('weight_unit')) {
            $this->merge(['weight_unit' => $normalizeUnit($this->input('weight_unit'))]);
        }
        if ($this->filled('volume_unit')) {
            $this->merge(['volume_unit' => $normalizeUnit($this->input('volume_unit'))]);
        }
    }

    public function rules(): array
    {
        return [
            'waybill_number'          => ['required', 'string', 'max:100', Rule::unique('waybills', 'waybill_number')->ignore($this->route('waybill'))],
            'vehicle_id'              => ['required', 'uuid', 'exists:vehicles,id'],
            'driver_id'               => ['required', 'uuid', 'exists:driver_details,id'],
            'status_id'               => ['required', 'uuid', 'exists:statuses,id'],
            'route_trip_id'           => ['nullable', 'uuid', 'exists:route_trips,id'],
            'origin'                  => ['nullable', 'string', 'max:255'],
            'origin_lat'              => ['nullable', 'numeric'],
            'origin_lng'              => ['nullable', 'numeric'],
            'destination'             => ['nullable', 'string', 'max:255'],
            'destination_lat'         => ['nullable', 'numeric'],
            'destination_lng'         => ['nullable', 'numeric'],
            'checkpoints'             => ['nullable'],
            'route_geometry'          => ['nullable'],
            'wialon_route_id'         => ['nullable', 'string', 'max:255'],
            'route_source'            => ['nullable', 'string', 'max:50'],
            'distance'                => ['nullable', 'numeric', 'min:0'],
            'estimated_duration'      => ['nullable', 'string', 'max:100'],
            'dispatch_date'           => ['required', 'date'],
            'estimated_dispatch_time' => ['nullable'],
            'delivery_date'          => ['nullable', 'date', 'after_or_equal:dispatch_date'],
            'cargo_description'       => ['nullable', 'string', 'max:255'],
            'quantity'                => ['nullable', 'numeric', 'min:0'],
            'quantity_unit'           => ['nullable', 'string', Rule::in([
                'PCS', 'UNIT', 'ITEM', 'PKG', 'CTN', 'BOX', 'CS', 'CRT', 'BAG', 'SACK',
                'BDL', 'BAL', 'PLT', 'SKD', 'DRM', 'BRL', 'ROL', 'COIL', 'REL', 'CNTR', 'CYL',
            ])],
            'cargo_weight_kg'         => ['nullable', 'numeric', 'min:0'],
            'weight_unit'             => ['nullable', 'string', Rule::in(['KG', 'MT', 'G', 'LB', 'QTL'])],
            'volume_unit'             => ['nullable', 'string', Rule::in(['CBM', 'CFT', 'L', 'GAL'])],
            'notes'                   => ['nullable', 'string'],
            'document'                => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'cargos'                  => ['nullable', 'array'],
            'cargos.*.cargo_description' => ['nullable', 'string', 'max:255'],
            'cargos.*.quantity'       => ['nullable', 'numeric', 'min:0'],
            'cargos.*.quantity_unit'  => ['nullable', 'string'],
            'cargos.*.cargo_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'cargos.*.weight_unit'    => ['nullable', 'string'],
            'cargos.*.volume_unit'    => ['nullable', 'string'],
        ];
    }
}

