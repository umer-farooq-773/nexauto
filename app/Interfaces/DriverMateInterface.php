<?php

namespace App\Interfaces;

interface DriverMateInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\DriverMateDto $data);
    public function update($id, \App\Dto\DriverMateDto $data);
}
