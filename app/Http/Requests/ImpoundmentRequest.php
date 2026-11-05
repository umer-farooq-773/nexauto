<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class ImpoundmentRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'impound_date.before_or_equal' => 'The impound date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'waybill_id'           => ['required', 'uuid', 'exists:waybills,id'],
            'vehicle_id'           => ['nullable', 'uuid', 'exists:vehicles,id'],
            'impound_date'         => ['required', 'date', 'before_or_equal:today'],
            'impound_location'     => ['required', 'string', 'max:255'],
            'impound_location_lat' => ['nullable', 'numeric'],
            'impound_location_lng' => ['nullable', 'numeric'],
            'impound_authority'    => ['nullable', 'string', 'max:255'],
            'reason'               => ['nullable', 'string', 'max:255'],
            'release_date'         => ['nullable', 'date', 'after_or_equal:impound_date'],
            'remarks'              => ['nullable', 'string'],
            'document'             => $this->incidentDocumentRules(),
            'status'               => ['required', 'in:0,1'],
            'cost'                 => ['required', 'numeric', 'min:0'],
        ];
    }
}
