<?php

namespace App\Interfaces;

interface RouteDiversionInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\RouteDiversionDto $data);
    public function update($id, \App\Dto\RouteDiversionDto $data);
}
