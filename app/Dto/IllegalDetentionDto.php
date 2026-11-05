<?php

namespace App\Dto;

use Illuminate\Support\Facades\Auth;

class IllegalDetentionDto
{
    public readonly string $waybill_id;
    public readonly ?string $vehicle_id;
    public readonly string $detention_date;
    public readonly string $detention_location;
    public readonly ?string $detention_location_lat;
    public readonly ?string $detention_location_lng;
    public readonly ?string $detained_by;
    public readonly ?string $cost;
    public readonly ?string $reason;
    public readonly ?string $release_date;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly string $authority;
    public readonly string $created_by;

    public function __construct($request)
    {
        $this->waybill_id              = $request['waybill_id'];
        $this->vehicle_id              = $request['vehicle_id'] ?? \App\Models\Waybill::find($this->waybill_id)?->vehicle_id;
        $this->detention_date          = $request['detention_date'];
        $this->detention_location      = $request['detention_location'];
        $this->detention_location_lat  = $request['detention_location_lat'] ?? null;
        $this->detention_location_lng  = $request['detention_location_lng'] ?? null;
        $this->detained_by             = $request['detained_by'] ?? null;
        $this->reason              = $request['reason'] ?? null;
        $this->release_date        = $request['release_date'] ?? null;
        $this->remarks             = $request['remarks'] ?? null;
        $this->status              = $request['status'];
        $this->cost                = $request['cost'];
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
            'waybill_id'             => $this->waybill_id,
            'vehicle_id'             => $this->vehicle_id,
            'detention_date'         => $this->detention_date,
            'detention_location'     => $this->detention_location,
            'detention_location_lat' => $this->detention_location_lat,
            'detention_location_lng' => $this->detention_location_lng,
            'detained_by'            => $this->detained_by,
            'reason'             => $this->reason,
            'release_date'       => $this->release_date,
            'remarks'            => $this->remarks,
            'status'             => $this->status,
            'cost'               => $this->cost,
            'authority'          => $this->authority,
            'created_by'         => $this->created_by,
        ];
    }
}
