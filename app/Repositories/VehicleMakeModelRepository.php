<?php

namespace App\Repositories;

use App\Interfaces\VehicleMakeModelInterface;
use App\Dto\VehicleMakeModelDto;
use App\Models\VehicleMakeModel;

class VehicleMakeModelRepository extends BaseRepository implements VehicleMakeModelInterface
{
    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(VehicleMakeModel $model)
    {
        $this->setModel($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VehicleMakeModelDto $data)
    {
        $dataArray = $data->toArray();
        $image = $dataArray['image'];

        unset($dataArray['image']);

        $dataResult = $this->add($this->_model, $dataArray);

        if ($image != null) {
            $imageUploaded = $this->uploadFile($image, $this->_imgPath);
            $dataResult->file()->create($imageUploaded);
        }

        return true;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, VehicleMakeModelDto $data)
    {
        $result = $this->checkRecord($id);

        $dataArray = $data->toArray();
        return $result->update($dataArray);
    }
}
