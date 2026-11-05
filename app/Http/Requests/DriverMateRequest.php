<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverMateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $mateId = $this->route('driver_mate');

        return [
            'first_name'                     => ['required', 'string', 'max:255'],
            'last_name'                      => ['required', 'string', 'max:255'],
            'address'                        => ['required', 'string', 'max:500'],
            'phone_number'                   => ['required', 'string', 'max:20'],
            'driver_id'                      => ['required', 'uuid', 'exists:driver_details,id'],
            'date_of_birth'                  => ['nullable', 'date'],
            'national_id_passport_number'    => [
                'required', 'string', 'max:100',
                Rule::unique('driver_mates', 'national_id_passport_number')->ignore($mateId),
            ],
            'date_employed'                  => ['nullable', 'date'],
            'emergency_contact_name'         => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone_number' => ['nullable', 'string', 'max:20'],
            'status'                         => ['required', 'boolean'],
            'mate_profile'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
            'company_id_card'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
        ];
    }
}
