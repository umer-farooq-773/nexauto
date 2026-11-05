<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class BreathaliserTestRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'test_date.before_or_equal' => 'The test date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'driver_id'         => ['required', 'uuid', 'exists:driver_details,id'],
            'vehicle_id'        => ['nullable', 'uuid', 'exists:vehicles,id'],
            'waybill_id'        => ['required', 'uuid', 'exists:waybills,id'],
            'test_date'         => ['required', 'date', 'before_or_equal:today'],
            'test_location'     => ['required', 'string', 'max:255'],
            'test_location_lat' => ['nullable', 'numeric'],
            'test_location_lng' => ['nullable', 'numeric'],
            'device_number'     => ['nullable', 'string', 'max:255'],
            'bac_level'         => ['nullable', 'numeric', 'min:0'],
            'test_result'       => ['required', 'in:negative,borderline,positive'],
            'action_taken'      => ['nullable', 'string', 'max:1000'],
            'remarks'           => ['nullable', 'string'],
            'document'          => $this->incidentDocumentRules(),
            'status'            => ['required', 'in:0,1,2'],
        ];
    }
}
