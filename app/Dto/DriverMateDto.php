<?php

namespace App\Dto;

class DriverMateDto
{
    public readonly string $first_name;
    public readonly string $last_name;
    public readonly string $address;
    public readonly string $phone_number;
    public readonly ?string $date_of_birth;
    public readonly string $national_id_passport_number;
    public readonly ?string $date_employed;
    public readonly ?string $emergency_contact_name;
    public readonly ?string $emergency_contact_phone_number;
    public readonly bool $status;
    public readonly string $driver_id;
    /** @var \Illuminate\Http\UploadedFile|null */
    public $file;

    /** @var \Illuminate\Http\UploadedFile|null */
    public $companyIdCard;

    public function __construct($request)
    {
        $this->first_name                    = $request['first_name'];
        $this->last_name                     = $request['last_name'];
        $this->address                       = $request['address'];
        $this->phone_number                  = $request['phone_number'];
        $this->date_of_birth                 = $request['date_of_birth'] ?? null;
        $this->national_id_passport_number   = $request['national_id_passport_number'];
        $this->date_employed                 = $request['date_employed'] ?? null;
        $this->emergency_contact_name        = $request['emergency_contact_name'] ?? null;
        $this->emergency_contact_phone_number = $request['emergency_contact_phone_number'] ?? null;
        $this->status                        = $request['status'];
        $this->driver_id                     = $request['driver_id'];
        $this->file = $request['mate_profile'] ?? null;
        $this->companyIdCard = $request['company_id_card'] ?? null;
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'first_name'                      => $this->first_name,
            'last_name'                       => $this->last_name,
            'address'                         => $this->address,
            'phone_number'                    => $this->phone_number,
            'date_of_birth'                   => $this->date_of_birth,
            'national_id_passport_number'     => $this->national_id_passport_number,
            'date_employed'                   => $this->date_employed,
            'emergency_contact_name'          => $this->emergency_contact_name,
            'emergency_contact_phone_number'  => $this->emergency_contact_phone_number,
            'status'                          => $this->status,
            'driver_id'                       => $this->driver_id,
            'image'                           => $this->file,
            'company_id_card'                => $this->companyIdCard,
        ];
    }
}
