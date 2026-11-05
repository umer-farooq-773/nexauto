<?php

namespace App\Dto;

class BreathaliserTestDto
{
    public readonly string $driver_id;
    public readonly ?string $vehicle_id;
    public readonly string $waybill_id;
    public readonly string $test_date;
    public readonly string $test_location;
    public readonly ?string $test_location_lat;
    public readonly ?string $test_location_lng;
    public readonly ?string $device_number;
    public readonly ?string $bac_level;
    public readonly string $test_result;
    public readonly ?string $action_taken;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly ?string $created_by;

    public function __construct($request)
    {
        $waybill = \App\Models\Waybill::find($request['waybill_id']);

        $this->driver_id        = $request['driver_id'] ?? $waybill?->driver_id;
        $this->vehicle_id       = $request['vehicle_id'] ?? $waybill?->vehicle_id;
        $this->waybill_id       = $request['waybill_id'];
        $this->test_date        = $request['test_date'];
        $this->test_location    = $request['test_location'];
        $this->test_location_lat = $request['test_location_lat'] ?? null;
        $this->test_location_lng = $request['test_location_lng'] ?? null;
        $this->device_number    = $request['device_number'] ?? null;
        $this->bac_level = $request['bac_level'] ?? null;
        $this->test_result = $request['test_result'];
        $this->action_taken = $request['action_taken'] ?? null;
        $this->remarks = $request['remarks'] ?? null;
        $this->status = $request['status'];
        $this->created_by = $request['created_by'] ?? \Illuminate\Support\Facades\Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'driver_id' => $this->driver_id,
            'vehicle_id' => $this->vehicle_id,
            'waybill_id' => $this->waybill_id,
            'test_date' => $this->test_date,
            'test_location' => $this->test_location,
            'test_location_lat' => $this->test_location_lat,
            'test_location_lng' => $this->test_location_lng,
            'device_number' => $this->device_number,
            'bac_level' => $this->bac_level,
            'test_result' => $this->test_result,
            'action_taken' => $this->action_taken,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_by' => $this->created_by,
        ];
    }
}
