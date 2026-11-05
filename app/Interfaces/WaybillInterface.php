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
}
