<?php

namespace App\Interfaces;

interface VehicleBoggedDownInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\VehicleBoggedDownDto $data);
    public function update($id, \App\Dto\VehicleBoggedDownDto $data);
}
