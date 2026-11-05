<?php

namespace App\Interfaces;

interface RouteTripInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\RouteTripDto $data);
    public function update($id, \App\Dto\RouteTripDto $data);
}
