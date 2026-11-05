<?php

namespace App\Interfaces;

interface ImpoundmentInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\ImpoundmentDto $data);
    public function update($id, \App\Dto\ImpoundmentDto $data);
}
