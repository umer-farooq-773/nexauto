<?php

namespace App\Interfaces;

interface GoodsInspectionInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\GoodsInspectionDto $data);
    public function update($id, \App\Dto\GoodsInspectionDto $data);
    public function saveDraft(array $payload, ?string $id = null): \App\Models\GoodsInspection;
}
