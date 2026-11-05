<?php

namespace App\Interfaces;

interface AccidentInterface
{
    public function index();
    public function all($relation = null);
    public function show($id);
    public function destroy($id);
    public function store(\App\Dto\AccidentDto $data);
    public function update($id, \App\Dto\AccidentDto $data);
}
