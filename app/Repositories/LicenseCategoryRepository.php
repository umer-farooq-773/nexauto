<?php

namespace App\Repositories;

use App\Interfaces\LicenseCategoryInterface;
use App\Dto\LicenseCategoryDto;
use App\Models\LicenseCategory;

class LicenseCategoryRepository extends BaseRepository implements LicenseCategoryInterface
{
    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(LicenseCategory $model)
    {
        $this->setModel($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LicenseCategoryDto $data)
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
    public function update($id, LicenseCategoryDto $data)
    {
        $result = $this->checkRecord($id);

        $dataArray = $data->toArray();
        return $result->update($dataArray);
    }
}
