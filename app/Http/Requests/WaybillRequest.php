<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Interfaces\WaybillInterface;
use App\Models\DriverDetail;

class WaybillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if (!$this->exists('drivers') && $this->filled('driver_id')) {
            $this->merge(['drivers' => [[
                'driver_id' => $this->input('driver_id'),
                'duration_minutes' => null,
            ]]]);
        }

        $driverRows = $this->input('drivers');
        if (is_array($driverRows)) {
            foreach ($driverRows as $i => $row) {
                if (isset($row['duration_seconds']) && $row['duration_seconds'] !== '' && (!isset($row['duration_minutes']) || $row['duration_minutes'] === '')) {
                    $driverRows[$i]['duration_minutes'] = max(1, (int) ceil((int) $row['duration_seconds'] / 60));
                } elseif (isset($row['duration_minutes']) && $row['duration_minutes'] !== '' && (!isset($row['duration_seconds']) || $row['duration_seconds'] === '')) {
                    $driverRows[$i]['duration_seconds'] = (int) $row['duration_minutes'] * 60;
                }
            }
            if (isset($driverRows[0]['driver_id'])) {
                $this->merge(['driver_id' => $driverRows[0]['driver_id']]);
            }
            $this->merge(['drivers' => $driverRows]);
        }

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
        $drivers = $this->input('drivers', []);
        $driverCount = is_array($drivers) ? count($drivers) : 0;
        $rules = [
            'waybill_number'          => ['required', 'string', 'max:100', Rule::unique('waybills', 'waybill_number')->ignore($this->route('waybill'))],
            'vehicle_id'              => ['required', 'uuid', 'exists:vehicles,id'],
            'driver_id'               => ['required', 'uuid', 'exists:driver_details,id'],
            'drivers'                 => ['required', 'array', 'min:1'],
            'drivers.*.driver_id'     => ['required', 'uuid', 'distinct:strict', 'exists:driver_details,id'],
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

        foreach (is_array($drivers) ? array_keys($drivers) : [] as $index) {
            $durationRequired = $driverCount > 1 && $index < $driverCount - 1;
            $rules["drivers.{$index}.duration_minutes"] = $durationRequired
                ? ['required', 'integer', 'min:1']
                : ['nullable', 'integer', 'min:1'];
            $rules["drivers.{$index}.duration_seconds"] = ['nullable', 'integer', 'min:1'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $drivers = $this->input('drivers', []);
            if (!is_array($drivers) || $drivers === []) {
                return;
            }

            $driverIds = array_values(array_filter(array_map(
                fn ($row) => is_array($row) ? ($row['driver_id'] ?? null) : null,
                $drivers,
            )));
            if (count($driverIds) !== count(array_unique($driverIds))
                || DriverDetail::whereIn('id', $driverIds)->count() !== count($driverIds)) {
                return;
            }

            $conflicts = app(WaybillInterface::class)->driversBusy(
                $driverIds,
                $this->route('waybill') ? (string) $this->route('waybill') : null,
            );

            $blocking = array_values(array_filter($conflicts, fn (array $row) => in_array((int) $row['status_value'], [1, 2, 5], true)));
            if ($blocking !== []) {
                $messages = array_map(fn (array $row) => sprintf('%s is already assigned to waybill %s (%s).', $row['driver_name'], $row['waybill_number'], $row['status_label']), $blocking);
                $validator->errors()->add('drivers', implode(' ', array_unique($messages)));
            }

            $awaiting = array_values(array_filter($conflicts, fn (array $row) => (int) $row['status_value'] === 0));
            $warnings = array_map(fn (array $row) => sprintf('%s is also listed on awaiting waybill %s.', $row['driver_name'], $row['waybill_number']), $awaiting);
            $this->attributes->set('waybill_driver_warnings', array_values(array_unique($warnings)));

            // Driver allocations are independent of the Wialon trip-detection threshold.
            // Validate only against the waybill's estimated route duration, when provided.
            $estimatedDurationStr = $this->input('estimated_duration');
            $maxSeconds = $this->parseDurationToSeconds($estimatedDurationStr);

            $totalPlannedSeconds = 0;
            $hasMultipleDrivers = count($drivers) > 1;

            foreach ($drivers as $idx => $driverRow) {
                $durSec = null;
                if (isset($driverRow['duration_seconds']) && $driverRow['duration_seconds'] !== '' && $driverRow['duration_seconds'] !== null) {
                    $durSec = (int) $driverRow['duration_seconds'];
                } elseif (isset($driverRow['duration_minutes']) && $driverRow['duration_minutes'] !== '' && $driverRow['duration_minutes'] !== null) {
                    $durSec = (int) $driverRow['duration_minutes'] * 60;
                }

                if ($durSec === null) {
                    continue;
                }

                $totalPlannedSeconds += $durSec;

                // Validate maximum trip duration if route duration is defined
                if ($maxSeconds !== null && $maxSeconds > 0 && $durSec > $maxSeconds) {
                    $durHuman = \App\Models\DriverEldLog::formatDuration($durSec);
                    $maxHuman = \App\Models\DriverEldLog::formatDuration($maxSeconds);
                    $msg = "Driver " . ($idx + 1) . " planned time ({$durSec}s / {$durHuman}) cannot exceed the vehicle's trip driving time of {$maxSeconds}s ({$maxHuman}).";
                    $validator->errors()->add("drivers.{$idx}.duration_seconds", $msg);
                    $validator->errors()->add("drivers.{$idx}.duration_minutes", $msg);
                }
            }

            if ($maxSeconds !== null && $maxSeconds > 0 && $hasMultipleDrivers && $totalPlannedSeconds > $maxSeconds) {
                $totalHuman = \App\Models\DriverEldLog::formatDuration($totalPlannedSeconds);
                $maxHuman = \App\Models\DriverEldLog::formatDuration($maxSeconds);
                $validator->errors()->add(
                    'drivers',
                    "Total planned driving time ({$totalPlannedSeconds}s / {$totalHuman}) exceeds the vehicle's trip driving time of {$maxSeconds}s ({$maxHuman})."
                );
            }
        });
    }

    public function parseDurationToSeconds(?string $duration): ?int
    {
        if (empty($duration)) {
            return null;
        }

        if (is_numeric($duration)) {
            $num = (int) $duration;
            return $num > 1000 ? $num : $num * 60;
        }

        $str = strtolower(trim($duration));
        $totalSeconds = 0;

        if (preg_match('/(\d+)\s*(?:hours|hour|hrs|hr|h)/i', $str, $matches)) {
            $totalSeconds += (int) $matches[1] * 3600;
        }

        if (preg_match('/(\d+)\s*(?:minutes|minute|mins|min|m)/i', $str, $matches)) {
            $totalSeconds += (int) $matches[1] * 60;
        }

        if (preg_match('/(\d+)\s*(?:seconds|second|secs|sec|s)/i', $str, $matches)) {
            $totalSeconds += (int) $matches[1];
        }

        if ($totalSeconds > 0) {
            return $totalSeconds;
        }

        if (preg_match('/^(\d+):(\d+)(?::(\d+))?/', $str, $matches)) {
            $h = (int) $matches[1];
            $m = (int) $matches[2];
            $s = isset($matches[3]) ? (int) $matches[3] : 0;
            return ($h * 3600) + ($m * 60) + $s;
        }

        return null;
    }

    protected function parseDurationToMinutes(?string $duration): ?int
    {
        if (empty($duration)) {
            return null;
        }

        if (is_numeric($duration)) {
            $num = (int) $duration;
            return $num > 500 ? (int) round($num / 60) : $num;
        }

        $str = strtolower(trim($duration));
        $totalMinutes = 0;

        if (preg_match('/(\d+)\s*(?:hours|hour|hrs|hr|h)/i', $str, $matches)) {
            $totalMinutes += (int) $matches[1] * 60;
        }

        if (preg_match('/(\d+)\s*(?:minutes|minute|mins|min|m)/i', $str, $matches)) {
            $totalMinutes += (int) $matches[1];
        }

        if ($totalMinutes > 0) {
            return $totalMinutes;
        }

        if (preg_match('/^(\d+):(\d+)/', $str, $matches)) {
            return ((int) $matches[1] * 60) + (int) $matches[2];
        }

        return null;
    }
}

