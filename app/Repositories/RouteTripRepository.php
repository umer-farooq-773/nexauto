<?php

namespace App\Repositories;

use App\Interfaces\RouteTripInterface;
use App\Dto\RouteTripDto;
use App\Models\RouteTrip;

class RouteTripRepository extends BaseRepository implements RouteTripInterface
{
    public function __construct(RouteTrip $model)
    {
        $this->setModel($model);
    }

    public function store(RouteTripDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, RouteTripDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
