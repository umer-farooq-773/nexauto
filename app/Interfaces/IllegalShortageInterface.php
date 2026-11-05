<?php

namespace App\Interfaces;

use App\Dto\IllegalShortageDto;

interface IllegalShortageInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(IllegalShortageDto $data);
    public function update($id, IllegalShortageDto $data);
}
