<?php

namespace App\Interfaces;

interface VehicleMaintenanceInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\VehicleMaintenanceDto $data);
    public function update($id, \App\Dto\VehicleMaintenanceDto $data);
}
