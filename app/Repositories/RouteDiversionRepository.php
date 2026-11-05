<?php

namespace App\Repositories;

use App\Dto\RouteDiversionDto;
use App\Interfaces\RouteDiversionInterface;
use App\Models\RouteDiversion;

class RouteDiversionRepository extends BaseRepository implements RouteDiversionInterface
{
    public function __construct(RouteDiversion $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['vehicle', 'waybill'])->latest()->paginate(15);
    }

    public function store(RouteDiversionDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, RouteDiversionDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
