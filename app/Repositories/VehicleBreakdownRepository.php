<?php

namespace App\Repositories;

use App\Interfaces\VehicleBreakdownInterface;
use App\Dto\VehicleBreakdownDto;
use App\Models\VehicleBreakdown;

class VehicleBreakdownRepository extends BaseRepository implements VehicleBreakdownInterface
{
    public function __construct(VehicleBreakdown $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->query()->with('vehicle')->latest()->paginate(15);
    }

    public function all($relation = null)
    {
        return $this->query()->get();
    }

    public function show($id)
    {
        return $this->query()->find($id);
    }

    public function destroy($id)
    {
        $result = $this->show($id);
        return $result?->delete();
    }

    public function store(VehicleBreakdownDto $data)
    {
        return $this->add($this->_model, array_merge($data->toArray(), [
            'incident_type' => 'breakdown',
        ]));
    }

    public function update($id, VehicleBreakdownDto $data)
    {
        $result = $this->show($id);
        return $result->update($data->toArray());
    }

    private function query()
    {
        return $this->_model->newQuery()->where('incident_type', 'breakdown');
    }
}
