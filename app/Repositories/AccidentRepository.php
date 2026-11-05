<?php

namespace App\Repositories;

use App\Interfaces\AccidentInterface;
use App\Dto\AccidentDto;
use App\Models\Accident;

class AccidentRepository extends BaseRepository implements AccidentInterface
{
    public function __construct(Accident $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with('vehicle')->latest()->paginate(15);
    }

    public function store(AccidentDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, AccidentDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
