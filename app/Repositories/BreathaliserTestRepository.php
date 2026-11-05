<?php

namespace App\Repositories;

use App\Dto\BreathaliserTestDto;
use App\Interfaces\BreathaliserTestInterface;
use App\Models\BreathaliserTest;

class BreathaliserTestRepository extends BaseRepository implements BreathaliserTestInterface
{
    public function __construct(BreathaliserTest $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['driver', 'vehicle', 'waybill'])->latest()->paginate(15);
    }

    public function store(BreathaliserTestDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, BreathaliserTestDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
