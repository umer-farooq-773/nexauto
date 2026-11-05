<?php

namespace App\Interfaces;

interface VehicleTypeInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\VehicleTypeDto $data);
    public function update($id, \App\Dto\VehicleTypeDto $data);
}
