<?php

namespace App\Interfaces;

use App\Models\Waybill;

interface WaybillInterface
{
    public function index();
    public function awaiting();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\WaybillDto $data);
    public function update($id, \App\Dto\WaybillDto $data);
    public function showWithIncidents(string $id);
    /** @return array<int, array{driver_id:string,driver_name:string,waybill_id:string,waybill_number:string,status_value:int,status_label:string}> */
    public function driversBusy(array $driverIds, ?string $ignoreWaybillId = null): array;
}
