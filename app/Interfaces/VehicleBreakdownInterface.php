<?php

namespace App\Interfaces;

interface VehicleBreakdownInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\VehicleBreakdownDto $data);
    public function update($id, \App\Dto\VehicleBreakdownDto $data);
}
