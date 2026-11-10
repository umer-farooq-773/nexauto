<?php

namespace App\Repositories;

use App\Interfaces\DriverDetailInterface;
use App\Dto\DriverDetailDto;
use App\Models\DriverDetail;

class DriverDetailRepository extends BaseRepository implements DriverDetailInterface
{
    protected $_imgPath = 'drivers/';

    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(DriverDetail $model)
    {
        $this->setModel($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DriverDetailDto $data)
    {
        try {
            $dataArray = $data->toArray();
            $image = $dataArray['image'];
            $companyIdCard = $dataArray['company_id_card'];
            $licenseCard = $dataArray['license_card'];

            unset($dataArray['image'], $dataArray['company_id_card'], $dataArray['license_card']);

            $dataResult = $this->add($this->_model, $dataArray);

            if (!$dataResult) {
                throw new \Exception('Failed to create driver record');
            }

            if ($image != null) {
                try {
                    $imageUploaded = $this->uploadFile($image, $this->_imgPath);
                    if ($imageUploaded && isset($imageUploaded['path'])) {
                        $imageUploaded['type'] = 'image';
                        $dataResult->files()->create($imageUploaded);
                    }
                } catch (\Throwable $th) {
                    \Log::error('Driver image upload error: ' . $th->getMessage(), ['driver_id' => $dataResult->id]);
                }
            }

            foreach (['company_id_card' => $companyIdCard, 'license_card' => $licenseCard] as $type => $file) {
                if ($file != null) {
                    try {
                        $uploaded = $this->uploadFile($file, $this->_imgPath);
                        if ($uploaded && isset($uploaded['path'])) {
                            $uploaded['type'] = $type;
                            $dataResult->files()->create($uploaded);
                        }
                    } catch (\Throwable $th) {
                        \Log::error("Driver {$type} upload error: " . $th->getMessage(), ['driver_id' => $dataResult->id]);
                    }
                }
            }

            return true;
        } catch (\Throwable $th) {
            \Log::error('Driver store error: ' . $th->getMessage(), ['exception' => $th]);
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, DriverDetailDto $data)
    {
        try {
            $result = $this->checkRecord($id);

            if (!$result) {
                throw new \Exception('Driver not found');
            }

            $dataArray = $data->toArray();
            $image = $dataArray['image'];
            $companyIdCard = $dataArray['company_id_card'];
            $licenseCard = $dataArray['license_card'];
            unset($dataArray['image'], $dataArray['company_id_card'], $dataArray['license_card']);

            $result->update($dataArray);

            if ($image != null) {
                try {
                    $imageUploaded = $this->uploadFile($image, $this->_imgPath);
                    if ($imageUploaded && isset($imageUploaded['path'])) {
                        $imageUploaded['type'] = 'image';
                        $result->files()->updateOrCreate(['type' => 'image'], $imageUploaded);
                    }
                } catch (\Throwable $th) {
                    \Log::error('Driver image upload error on update: ' . $th->getMessage(), ['driver_id' => $result->id]);
                }
            }

            foreach (['company_id_card' => $companyIdCard, 'license_card' => $licenseCard] as $type => $file) {
                if ($file != null) {
                    try {
                        $uploaded = $this->uploadFile($file, $this->_imgPath);
                        if ($uploaded && isset($uploaded['path'])) {
                            $uploaded['type'] = $type;
                            $result->files()->updateOrCreate(['type' => $type], $uploaded);
                        }
                    } catch (\Throwable $th) {
                        \Log::error("Driver {$type} upload error on update: " . $th->getMessage(), ['driver_id' => $result->id]);
                    }
                }
            }

            return $result;
        } catch (\Throwable $th) {
            \Log::error('Driver update error: ' . $th->getMessage(), ['exception' => $th]);
            throw $th;
        }
    }
}
