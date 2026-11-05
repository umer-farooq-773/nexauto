<?php

namespace App\Interfaces;

interface VehicleMakeModelInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\VehicleMakeModelDto $data);
    public function update($id, \App\Dto\VehicleMakeModelDto $data);
}
