<?php

namespace App\Dto;

class ImpoundmentDto
{
    public readonly string $waybill_id;
    public readonly ?string $vehicle_id;
    public readonly string $impound_date;
    public readonly string $impound_location;
    public readonly ?string $impound_location_lat;
    public readonly ?string $impound_location_lng;
    public readonly ?string $impound_authority;
    public readonly ?string $reason;
    public readonly ?string $release_date;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly ?string $cost;
    public readonly ?string $created_by;

    public function __construct($request)
    {
        $this->waybill_id            = $request['waybill_id'];
        $this->vehicle_id            = $request['vehicle_id'] ?? \App\Models\Waybill::find($this->waybill_id)?->vehicle_id;
        $this->impound_date          = $request['impound_date'];
        $this->impound_location      = $request['impound_location'];
        $this->impound_location_lat  = $request['impound_location_lat'] ?? null;
        $this->impound_location_lng  = $request['impound_location_lng'] ?? null;
        $this->impound_authority     = $request['impound_authority'] ?? null;
        $this->reason                = $request['reason'] ?? null;
        $this->release_date          = $request['release_date'] ?? null;
        $this->remarks               = $request['remarks'] ?? null;
        $this->status                = $request['status'];
        $this->cost                  = $request['cost'];
        $this->created_by            = $request['created_by'] ?? \Illuminate\Support\Facades\Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'waybill_id'            => $this->waybill_id,
            'vehicle_id'            => $this->vehicle_id,
            'impound_date'          => $this->impound_date,
            'impound_location'      => $this->impound_location,
            'impound_location_lat'  => $this->impound_location_lat,
            'impound_location_lng'  => $this->impound_location_lng,
            'impound_authority'     => $this->impound_authority,
            'reason'                => $this->reason,
            'release_date'          => $this->release_date,
            'remarks'               => $this->remarks,
            'status'                => $this->status,
            'cost'                  => $this->cost,
            'created_by'            => $this->created_by,
        ];
    }
}
