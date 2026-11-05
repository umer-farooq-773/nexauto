<?php

namespace App\Dto;

use Illuminate\Support\Facades\Auth;

class VehicleBoggedDownDto
{
    public readonly string $waybill_id;
    public readonly ?string $vehicle_id;
    public readonly string $bogged_date;
    public readonly string $stuck_location;
    public readonly ?string $stuck_location_lat;
    public readonly ?string $stuck_location_lng;
    public readonly ?string $ground_condition;
    public readonly ?string $description;
    public readonly ?string $recovered_date;
    public readonly ?string $recovery_cost;
    public readonly string $status;
    public readonly string $authority;
    public readonly string $created_by;

    public function __construct($request)
    {
        $this->waybill_id         = $request['waybill_id'];
        $this->vehicle_id         = $request['vehicle_id'] ?? \App\Models\Waybill::find($this->waybill_id)?->vehicle_id;
        $this->bogged_date        = $request['bogged_date'];
        $this->stuck_location     = $request['stuck_location'];
        $this->stuck_location_lat = $request['stuck_location_lat'] ?? null;
        $this->stuck_location_lng = $request['stuck_location_lng'] ?? null;
        $this->ground_condition   = $request['ground_condition'] ?? null;
        $this->description        = $request['description'] ?? null;
        $this->recovered_date     = $request['recovered_date'] ?? null;
        $this->recovery_cost      = $request['recovery_cost'] ?? null;
        $this->status             = $request['status'];
        $this->authority           = $request['authority'];
        $this->created_by          = Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'waybill_id'         => $this->waybill_id,
            'vehicle_id'         => $this->vehicle_id,
            'bogged_date'        => $this->bogged_date,
            'stuck_location'     => $this->stuck_location,
            'stuck_location_lat' => $this->stuck_location_lat,
            'stuck_location_lng' => $this->stuck_location_lng,
            'ground_condition'   => $this->ground_condition,
            'description'        => $this->description,
            'recovered_date'     => $this->recovered_date,
            'recovery_cost'      => $this->recovery_cost,
            'status'             => $this->status,
            'authority'          => $this->authority,
            'created_by'         => $this->created_by,
        ];
    }
}
