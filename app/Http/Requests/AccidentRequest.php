<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesIncidentDocument;
use Illuminate\Foundation\Http\FormRequest;

class AccidentRequest extends FormRequest
{
    use HandlesIncidentDocument;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function messages(): array
    {
        return [
            'accident_date.before_or_equal' => 'The accident date cannot be in the future. Please select today or a past date.',
        ];
    }

    public function rules(): array
    {
        return [
            'waybill_id'             => ['required', 'uuid', 'exists:waybills,id'],
            'vehicle_id'             => ['nullable', 'uuid', 'exists:vehicles,id'],
            'accident_date'          => ['required', 'date', 'before_or_equal:today'],
            'accident_location'      => ['required', 'string', 'max:255'],
            'accident_location_lat'  => ['nullable', 'numeric'],
            'accident_location_lng'  => ['nullable', 'numeric'],
            'accident_type'          => ['required', 'string', 'in:Loaded,Unloaded'],
            'accident_type_detail'   => ['nullable', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'authority'              => ['required', 'string'],
            'damage_cost'            => ['nullable', 'numeric', 'min:0'],
            'resolved_date'          => ['nullable', 'date', 'after_or_equal:accident_date'],
            'status'                 => ['required', 'in:0,1,2'],
            'driver_status'          => ['required', 'string', 'in:none,minor,moderate,severe,fatal'],
            'motor_mate_status'      => ['required', 'string', 'in:none,minor,moderate,severe,fatal'],
            'content'                => ['nullable', 'string', 'max:255'],
            'damage_extend'          => ['nullable', 'string', 'max:255'],
            'is_fatality'            => ['required', 'in:yes,no'],
            'fatality_type'          => ['nullable', 'string', 'in:internal_staff,third_party'],
            'accident_nature_detail' => ['required', 'string', 'max:255'],
            'is_damage_asset'        => ['required', 'in:yes,no'],
            'is_injury'              => ['required', 'in:yes,no'],
            'document'               => $this->incidentDocumentRules(),
        ];
    }
}
