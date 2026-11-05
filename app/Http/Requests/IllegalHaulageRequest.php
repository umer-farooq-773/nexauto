<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class IllegalHaulageRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'haulage_date.before_or_equal' => 'The haulage date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'driver_id'            => ['required', 'uuid', 'exists:driver_details,id'],
            'vehicle_id'           => ['nullable', 'uuid', 'exists:vehicles,id'],
            'waybill_id'           => ['required', 'uuid', 'exists:waybills,id'],
            'observed_quantity'    => ['required', 'numeric', 'min:0'],
            'haulage_date'         => ['required', 'date', 'before_or_equal:today'],
            'haulage_location'     => ['required', 'string', 'max:255'],
            'haulage_location_lat' => ['nullable', 'numeric'],
            'haulage_location_lng' => ['nullable', 'numeric'],
            'haulage_type'         => ['required', 'string', 'max:255'],
            'cargo_description'    => ['nullable', 'string', 'max:1000'],
            'severity'             => ['required', 'in:minor,moderate,severe,critical'],
            'action_taken'         => ['nullable', 'string', 'max:1000'],
            'remarks'              => ['nullable', 'string'],
            'document'             => $this->incidentDocumentRules(),
            'status'               => ['required', 'in:0,1,2'],
        ];
    }
}
