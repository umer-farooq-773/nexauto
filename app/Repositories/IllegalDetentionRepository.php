<?php

namespace App\Repositories;

use App\Interfaces\IllegalDetentionInterface;
use App\Dto\IllegalDetentionDto;
use App\Models\IllegalDetention;

class IllegalDetentionRepository extends BaseRepository implements IllegalDetentionInterface
{
    public function __construct(IllegalDetention $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with('vehicle')->latest()->paginate(15);
    }

    public function store(IllegalDetentionDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, IllegalDetentionDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
