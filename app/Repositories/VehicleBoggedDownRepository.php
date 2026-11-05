<?php

namespace App\Repositories;

use App\Dto\VehicleBoggedDownDto;
use App\Interfaces\VehicleBoggedDownInterface;
use App\Models\VehicleBoggedDown;

class VehicleBoggedDownRepository extends BaseRepository implements VehicleBoggedDownInterface
{
    public function __construct(VehicleBoggedDown $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with('vehicle')->latest()->paginate(15);
    }

    public function store(VehicleBoggedDownDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, VehicleBoggedDownDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
