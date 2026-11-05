<?php

namespace App\Repositories;

use App\Interfaces\VehicleTypeInterface;
use App\Dto\VehicleTypeDto;
use App\Models\VehicleType;

class VehicleTypeRepository extends BaseRepository implements VehicleTypeInterface
{
    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(VehicleType $model)
    {
        $this->setModel($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VehicleTypeDto $data)
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
    public function update($id, VehicleTypeDto $data)
    {
        $result = $this->checkRecord($id);

        $dataArray = $data->toArray();
        return $result->update($dataArray);
    }
}
