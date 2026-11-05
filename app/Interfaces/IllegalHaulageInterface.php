<?php

namespace App\Interfaces;

interface IllegalHaulageInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\IllegalHaulageDto $data);
    public function update($id, \App\Dto\IllegalHaulageDto $data);
}
