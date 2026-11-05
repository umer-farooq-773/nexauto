<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'fleet_no' => ['required','string','max:50'],
            'phone_number' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^\+\d{10,15}$/', // Format: +[country code][number], 10-15 digits total
            ],

            'manager_first_name' => ['required','string','max:100'],
            'manager_last_name' => ['required','string','max:100'],
            'manager_email' => ['required','email','max:150'],

            'owner_first_name' => ['required','string','max:100'],
            'owner_last_name' => ['required','string','max:100'],

            'make_model' => ['required','exists:vehicle_make_models,id'],
            'vehicle_type' => ['required','string','max:100'],

            'truck_no' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vehicles', 'truck_no')->ignore($this->route('vehicle'))
            ],

            // gps_unit_id is intentionally NOT accepted here — Wialon
            // generates it, the backend stores it. Any value submitted by
            // the client for this field is ignored (see VehicleDto/
            // VehicleRepository, which strip it before persisting).
            'gps_provider' => ['nullable','string','max:30','in:wialon,tracksolid,other'],
            'gps_status' => ['nullable','boolean'],

            'gps_imei' => [
                Rule::requiredIf(fn () => $this->input('gps_provider') === 'wialon'),
                'nullable',
                'string',
                'regex:/^\d{15}$/', // Must be exactly 15 digits
                Rule::unique('vehicles', 'gps_imei')->ignore($this->route('vehicle')),
            ],

            // The dropdown value from gps_device_types — required only
            // when gps_provider = wialon. Adjust the field name here to
            // match whatever the actual add-vehicle form input is named.
            'gps_device_type_id' => [
                Rule::requiredIf(fn () => $this->input('gps_provider') === 'wialon'),
                'nullable',
                'uuid',
                Rule::exists('gps_device_types', 'id')->where('status', 1),
            ],

            'last_service_date' => ['required','date'],
            'next_service_due_date' => ['required','date','after:last_service_date'],

            'insurance_expiry_date' => ['required','date'],
            'vehicle_license_expiry_date' => ['required','date'],

            'base_site' => ['required','string','max:150'],

            'status' => ['required','in:0,1,2'],

            'drivers' => ['nullable','array'],
            'drivers.*' => ['uuid', 'exists:driver_details,id']
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must be in international format: +[country code][number] (e.g., +923499088073). Must be 10-15 digits total.',
            'gps_imei.regex' => 'GPS IMEI must be exactly 15 digits with no spaces or special characters.',
            'gps_imei.required_if' => 'GPS IMEI is required when GPS provider is Wialon.',
            'gps_imei.unique' => 'This IMEI is already assigned to another vehicle.',
            'gps_device_type_id.required_if' => 'GPS Device Type is required when GPS provider is Wialon.',
            'truck_no.unique' => 'This vehicle registration number is already in use.',
        ];
    }
}
