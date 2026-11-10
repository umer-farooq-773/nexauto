<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $driverId = $this->route('driver');

        return [
            'first_name'                    => ['required', 'string', 'max:255'],
            'last_name'                     => ['required', 'string', 'max:255'],
            'address'                       => ['required', 'string', 'max:500'],
            'phone_number'                  => ['required', 'string', 'max:20'],
            'date_of_birth'                 => ['nullable', 'date'],
            'national_id_passport_number'   => [
                'required', 'string', 'max:100',
                Rule::unique('driver_details', 'national_id_passport_number')->ignore($driverId),
            ],
            'date_employed'                 => ['nullable', 'date'],
            'drivers_license_number'        => [
                'required', 'string', 'max:100',
                Rule::unique('driver_details', 'drivers_license_number')->ignore($driverId),
            ],
            'license_category_class'        => ['nullable', 'string', 'max:50'],
            'license_expiry_date'           => ['nullable', 'date'],
            'emergency_contact_name'        => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone_number'=> ['nullable', 'string', 'max:20'],
            'status'                        => ['required'],
            'employer_number'               => ['required'],
            'bvn_number'                    => ['required'],
            'driver_profile'                => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'company_id_card'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'license_card'                  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
