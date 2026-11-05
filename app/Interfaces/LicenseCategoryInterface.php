<?php

namespace App\Interfaces;

interface LicenseCategoryInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\LicenseCategoryDto $data);
    public function update($id, \App\Dto\LicenseCategoryDto $data);
}
