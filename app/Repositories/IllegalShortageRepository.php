<?php

namespace App\Repositories;

use App\Dto\IllegalShortageDto;
use App\Interfaces\IllegalShortageInterface;
use App\Models\IllegalShortage;

class IllegalShortageRepository extends BaseRepository implements IllegalShortageInterface
{
    public function __construct(IllegalShortage $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['driver', 'vehicle', 'waybill'])->latest()->paginate(15);
    }

    public function store(IllegalShortageDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, IllegalShortageDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
