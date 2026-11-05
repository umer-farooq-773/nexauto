<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vehicle_id'               => ['required', 'uuid', 'exists:vehicles,id'],
            'maintenance_type_ids'     => ['required', 'array', 'min:1'],
            'maintenance_type_ids.*'   => ['required', 'uuid', 'exists:vehicle_maintenance_types,id'],
            'maintenance_date'         => ['required', 'date'],
            'next_maintenance_date'    => ['nullable', 'date', 'after:maintenance_date'],
            'expected_completion_date' => ['nullable', 'date'],
            'odometer'                 => ['nullable', 'string', 'max:50'],
            'cost'                     => ['nullable', 'numeric', 'min:0'],
            'service_provider'         => ['nullable', 'string', 'max:255'],
            'priority'                 => ['nullable', 'string', 'max:50'],
            'description'              => ['nullable', 'string'],
            'status'                   => ['required', 'in:0,1,2'],
            'maintenance_files'        => ['nullable', 'array'],
            'maintenance_files.*'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'items'                    => ['nullable', 'array'],
            'items.*.id'               => ['nullable', 'string'],
            'items.*.category'         => ['nullable', 'string', 'in:tyre,battery,telematics,brake,engine,general,other'],
            'items.*.action'           => ['nullable', 'string'],
            'items.*.location'         => ['nullable', 'string', 'max:255'],
            'items.*.axle'             => ['nullable', 'string', 'max:255'],
            'items.*.position'         => ['nullable', 'string', 'max:255'],
            'items.*.item_name'        => ['nullable', 'string', 'max:255'],
            'items.*.brand'            => ['nullable', 'string', 'max:255'],
            'items.*.model'            => ['nullable', 'string', 'max:255'],
            'items.*.size'             => ['nullable', 'string', 'max:255'],
            'items.*.old_serial_no'    => ['nullable', 'string', 'max:255'],
            'items.*.new_serial_no'    => ['nullable', 'string', 'max:255'],
            'items.*.tread_depth_mm'   => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'items.*.reason'           => ['nullable', 'string', 'max:255'],
            'items.*.quantity'         => ['nullable', 'integer', 'min:1'],
            'items.*.unit_cost'        => ['nullable', 'numeric', 'min:0'],
            'items.*.cost'             => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'            => ['nullable', 'string'],
            'item_photos'              => ['nullable', 'array'],
            'item_photos.*'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:10240'],
            'deleted_item_ids'         => ['nullable', 'array'],
            'deleted_item_ids.*'       => ['nullable', 'string'],
        ];
    }
}
