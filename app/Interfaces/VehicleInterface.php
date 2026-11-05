<?php

namespace App\Interfaces;

interface VehicleInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\VehicleDto $data);
    public function update($id, \App\Dto\VehicleDto $data);
}
