<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class VehicleBoggedDownRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'bogged_date.before_or_equal' => 'The bogged date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'waybill_id'         => ['required', 'uuid', 'exists:waybills,id'],
            'vehicle_id'         => ['nullable', 'uuid', 'exists:vehicles,id'],
            'bogged_date'        => ['required', 'date', 'before_or_equal:today'],
            'stuck_location'     => ['required', 'string', 'max:255'],
            'stuck_location_lat' => ['nullable', 'numeric'],
            'stuck_location_lng' => ['nullable', 'numeric'],
            'ground_condition'   => ['nullable', 'string', 'max:255'],
            'authority'          => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'recovered_date'     => ['nullable', 'date', 'after_or_equal:bogged_date'],
            'recovery_cost'      => ['nullable', 'numeric', 'min:0'],
            'document'           => $this->incidentDocumentRules(),
            'status'             => ['required', 'in:0,1,2'],
        ];
    }
}
