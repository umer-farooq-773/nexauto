<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'trip_name'          => ['required', 'string', 'max:255'],
            'origin'             => ['required', 'string', 'max:255'],
            'destination'        => ['required', 'string', 'max:255'],
            'distance_km'        => ['nullable', 'numeric', 'min:0'],
            'estimated_duration' => ['nullable', 'string', 'max:100'],
            'description'        => ['nullable', 'string'],
            'status'             => ['required', 'boolean'],
        ];
    }
}
