<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class RouteDiversionRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'diversion_date.before_or_equal' => 'The diversion date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'waybill_id'             => ['required', 'uuid', 'exists:waybills,id'],
            'vehicle_id'             => ['nullable', 'uuid', 'exists:vehicles,id'],
            'diversion_date'         => ['required', 'date', 'before_or_equal:today'],
            'diversion_location'     => ['required', 'string', 'max:255'],
            'diversion_location_lat' => ['nullable', 'numeric'],
            'diversion_location_lng' => ['nullable', 'numeric'],
            'original_route'         => ['required', 'string', 'max:255'],
            'diverted_route'         => ['required', 'string', 'max:255'],
            'diversion_reason'       => ['required', 'string', 'max:1000'],
            'expected_rejoin_date'   => ['nullable', 'date', 'after_or_equal:diversion_date'],
            'actual_rejoin_date'     => ['nullable', 'date', 'after_or_equal:diversion_date'],
            'remarks'                => ['nullable', 'string'],
            'document'               => $this->incidentDocumentRules(),
            'status'                 => ['required', 'in:0,1,2'],
        ];
    }
}
