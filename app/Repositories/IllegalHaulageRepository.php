<?php

namespace App\Repositories;

use App\Dto\IllegalHaulageDto;
use App\Interfaces\IllegalHaulageInterface;
use App\Models\IllegalHaulage;

class IllegalHaulageRepository extends BaseRepository implements IllegalHaulageInterface
{
    public function __construct(IllegalHaulage $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['driver', 'vehicle', 'waybill'])->latest()->paginate(15);
    }

    public function store(IllegalHaulageDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, IllegalHaulageDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
