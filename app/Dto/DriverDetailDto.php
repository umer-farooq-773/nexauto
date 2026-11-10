<?php

namespace App\Dto;

class DriverDetailDto
{
    public readonly string $first_name;
    public readonly string $last_name;
    public readonly string $address;
    public readonly string $phone_number;
    public readonly ?string $date_of_birth;
    public readonly string $national_id_passport_number;
    public readonly ?string $date_employed;
    public readonly string $drivers_license_number;
    public readonly ?string $license_category_class;
    public readonly ?string $license_expiry_date;
    public readonly ?string $emergency_contact_name;
    public readonly ?string $emergency_contact_phone_number;
    public readonly ?string $status;
    public $file;
    public $company_id_card;
    public $license_card;
    public readonly ?string $employer_number;
    public readonly ?string $bvn_number;
    public readonly ?string $email;

    public function __construct($request)
    {
        $this->first_name                     = $request['first_name'];
        $this->last_name                      = $request['last_name'];
        $this->address                        = $request['address'];
        $this->phone_number                   = $request['phone_number'];
        $this->email                          = !empty($request['email']) ? strtolower(trim($request['email'])) : null;
        $this->date_of_birth                  = $request['date_of_birth'] ?? null;
        $this->national_id_passport_number    = $request['national_id_passport_number'];
        $this->date_employed                  = $request['date_employed'] ?? null;
        $this->drivers_license_number         = $request['drivers_license_number'];
        $this->license_category_class         = $request['license_category_class'] ?? null;
        $this->license_expiry_date            = $request['license_expiry_date'] ?? null;
        $this->emergency_contact_name         = $request['emergency_contact_name'] ?? null;
        $this->emergency_contact_phone_number = $request['emergency_contact_phone_number'] ?? null;
        $this->status                         = $request['status'];
        $this->file                           = $request['driver_profile'] ?? null;
        $this->company_id_card                = $request['company_id_card'] ?? null;
        $this->license_card                   = $request['license_card'] ?? null;
        $this->employer_number                = $request['employer_number'] ?? null;
        $this->bvn_number                     = $request['bvn_number'] ?? null;
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'first_name'                     => $this->first_name,
            'last_name'                      => $this->last_name,
            'address'                        => $this->address,
            'phone_number'                   => $this->phone_number,
            'date_of_birth'                  => $this->date_of_birth,
            'national_id_passport_number'    => $this->national_id_passport_number,
            'date_employed'                  => $this->date_employed,
            'drivers_license_number'         => $this->drivers_license_number,
            'license_category_class'         => $this->license_category_class,
            'license_expiry_date'            => $this->license_expiry_date,
            'emergency_contact_name'         => $this->emergency_contact_name,
            'emergency_contact_phone_number' => $this->emergency_contact_phone_number,
            'status'                         => $this->status,
            'image'                          => $this->file,
            'company_id_card'                => $this->company_id_card,
            'license_card'                   => $this->license_card,
            'bvn_number'                     => $this->bvn_number,
            'employer_number'                => $this->employer_number,
            'email'                          => $this->email,
        ];
    }
}
