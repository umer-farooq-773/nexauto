<?php

namespace App\Dto;

use Illuminate\Support\Facades\Auth;

class VehicleBreakdownDto
{
    public readonly string $waybill_id;
    public readonly ?string $vehicle_id;
    public readonly string $breakdown_date;
    public readonly string $breakdown_location;
    public readonly ?string $breakdown_location_lat;
    public readonly ?string $breakdown_location_lng;
    public readonly ?string $breakdown_type;
    public readonly ?string $description;
    public readonly ?string $resolved_date;
    public readonly ?string $repair_cost;
    public readonly string $status;
    public readonly string $payment_status;
    public readonly string $authority;
    public readonly string $created_by;

    public function __construct($request)
    {
        $this->waybill_id             = $request['waybill_id'];
        $this->vehicle_id             = $request['vehicle_id'] ?? \App\Models\Waybill::find($this->waybill_id)?->vehicle_id;
        $this->breakdown_date         = $request['breakdown_date'];
        $this->breakdown_location     = $request['breakdown_location'];
        $this->breakdown_location_lat = $request['breakdown_location_lat'] ?? null;
        $this->breakdown_location_lng = $request['breakdown_location_lng'] ?? null;
        $this->breakdown_type         = $request['breakdown_type'] ?? null;
        $this->description        = $request['description'] ?? null;
        $this->resolved_date      = $request['resolved_date'] ?? null;
        $this->repair_cost        = $request['repair_cost'] ?? null;
        $this->status             = $request['status'];
        $this->payment_status     = $request['payment_status'] ?? '0';
        $this->authority          = $request['authority'] ?? '0';
        $this->created_by          = Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'waybill_id'             => $this->waybill_id,
            'vehicle_id'             => $this->vehicle_id,
            'breakdown_date'         => $this->breakdown_date,
            'breakdown_location'     => $this->breakdown_location,
            'breakdown_location_lat' => $this->breakdown_location_lat,
            'breakdown_location_lng' => $this->breakdown_location_lng,
            'breakdown_type'         => $this->breakdown_type,
            'description'        => $this->description,
            'resolved_date'      => $this->resolved_date,
            'repair_cost'        => $this->repair_cost,
            'status'             => $this->status,
            'payment_status'     => $this->payment_status,
            'authority'          => $this->authority,
            'created_by'          => $this->created_by,
        ];
    }
}
