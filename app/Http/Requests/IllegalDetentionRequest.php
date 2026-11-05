<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class IllegalDetentionRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'detention_date.before_or_equal' => 'The detention date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'waybill_id'             => ['required', 'uuid', 'exists:waybills,id'],
            'vehicle_id'             => ['nullable', 'uuid', 'exists:vehicles,id'],
            'detention_date'         => ['required', 'date', 'before_or_equal:today'],
            'detention_location'     => ['required', 'string', 'max:255'],
            'detention_location_lat' => ['nullable', 'numeric'],
            'detention_location_lng' => ['nullable', 'numeric'],
            'detained_by'            => ['nullable', 'string', 'max:255'],
            'reason'                 => ['nullable', 'string', 'max:255'],
            'release_date'           => ['nullable', 'date', 'after_or_equal:detention_date'],
            'remarks'                => ['nullable', 'string'],
            'document'               => $this->incidentDocumentRules(),
            'status'                 => ['required', 'in:0,1'],
            'cost'                   => ['required', 'numeric', 'min:0'],
            'authority'              => ['required', 'string', 'max:255'],
        ];
    }
}
