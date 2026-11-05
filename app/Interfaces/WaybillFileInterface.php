<?php

namespace App\Interfaces;

interface WaybillFileInterface
{
    public function findByWaybill($waybillId);
    public function store(array $data);
    public function show($id);
    public function update($id, array $data);
    public function removeFile($id);
}
