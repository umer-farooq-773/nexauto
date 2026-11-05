<?php

namespace App\Repositories;

use App\Dto\DriverMisconductDto;
use App\Interfaces\DriverMisconductInterface;
use App\Models\DriverMisconduct;

class DriverMisconductRepository extends BaseRepository implements DriverMisconductInterface
{
    public function __construct(DriverMisconduct $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['driver', 'vehicle', 'waybill'])->latest()->paginate(15);
    }

    public function store(DriverMisconductDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, DriverMisconductDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
