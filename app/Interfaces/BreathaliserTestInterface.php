<?php

namespace App\Interfaces;

interface BreathaliserTestInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\BreathaliserTestDto $data);
    public function update($id, \App\Dto\BreathaliserTestDto $data);
}
