<?php

namespace App\Repositories;

use App\Interfaces\ImpoundmentInterface;
use App\Dto\ImpoundmentDto;
use App\Models\Impoundment;

class ImpoundmentRepository extends BaseRepository implements ImpoundmentInterface
{
    public function __construct(Impoundment $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with('vehicle')->latest()->paginate(15);
    }

    public function store(ImpoundmentDto $data)
    {
        return $this->add($this->_model, $data->toArray());
    }

    public function update($id, ImpoundmentDto $data)
    {
        $result = $this->checkRecord($id);
        return $result->update($data->toArray());
    }
}
