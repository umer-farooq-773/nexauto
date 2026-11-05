<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class VehicleBreakdownRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'breakdown_date.before_or_equal' => 'The breakdown date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'waybill_id'             => ['required', 'uuid', 'exists:waybills,id'],
            'vehicle_id'             => ['nullable', 'uuid', 'exists:vehicles,id'],
            'breakdown_date'         => ['required', 'date', 'before_or_equal:today'],
            'breakdown_location'     => ['required', 'string', 'max:255'],
            'breakdown_location_lat' => ['nullable', 'numeric'],
            'breakdown_location_lng' => ['nullable', 'numeric'],
            'breakdown_type'         => ['nullable', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'authority'              => ['nullable', 'string'],
            'resolved_date'          => ['nullable', 'date', 'after_or_equal:breakdown_date'],
            'repair_cost'            => ['nullable', 'numeric', 'min:0'],
            'document'               => $this->incidentDocumentRules(),
            'status'                 => ['required', 'in:0,1,2'],
        ];
    }
}
