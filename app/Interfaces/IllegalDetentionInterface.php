<?php

namespace App\Interfaces;

interface IllegalDetentionInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\IllegalDetentionDto $data);
    public function update($id, \App\Dto\IllegalDetentionDto $data);
}
