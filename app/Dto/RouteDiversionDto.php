<?php

namespace App\Dto;

class RouteDiversionDto
{
    public readonly string $waybill_id;
    public readonly ?string $vehicle_id;
    public readonly string $diversion_date;
    public readonly string $diversion_location;
    public readonly ?string $diversion_location_lat;
    public readonly ?string $diversion_location_lng;
    public readonly string $original_route;
    public readonly string $diverted_route;
    public readonly string $diversion_reason;
    public readonly ?string $expected_rejoin_date;
    public readonly ?string $actual_rejoin_date;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly ?string $created_by;

    public function __construct($request)
    {
        $waybill = \App\Models\Waybill::find($request['waybill_id']);

        $this->waybill_id                = $request['waybill_id'];
        $this->vehicle_id                = $request['vehicle_id'] ?? $waybill?->vehicle_id;
        $this->diversion_date            = $request['diversion_date'];
        $this->diversion_location        = $request['diversion_location'];
        $this->diversion_location_lat    = $request['diversion_location_lat'] ?? null;
        $this->diversion_location_lng    = $request['diversion_location_lng'] ?? null;
        $this->original_route            = $request['original_route'] ?? trim(($waybill?->origin ?? '') . ' -> ' . ($waybill?->destination ?? ''));
        $this->diverted_route        = $request['diverted_route'];
        $this->diversion_reason      = $request['diversion_reason'];
        $this->expected_rejoin_date  = $request['expected_rejoin_date'] ?? null;
        $this->actual_rejoin_date    = $request['actual_rejoin_date'] ?? null;
        $this->remarks               = $request['remarks'] ?? null;
        $this->status                = $request['status'];
        $this->created_by            = $request['created_by'] ?? \Illuminate\Support\Facades\Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'waybill_id' => $this->waybill_id,
            'vehicle_id' => $this->vehicle_id,
            'diversion_date' => $this->diversion_date,
            'diversion_location' => $this->diversion_location,
            'diversion_location_lat' => $this->diversion_location_lat,
            'diversion_location_lng' => $this->diversion_location_lng,
            'original_route' => $this->original_route,
            'diverted_route' => $this->diverted_route,
            'diversion_reason' => $this->diversion_reason,
            'expected_rejoin_date' => $this->expected_rejoin_date,
            'actual_rejoin_date' => $this->actual_rejoin_date,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_by' => $this->created_by,
        ];
    }
}
