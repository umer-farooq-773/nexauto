<?php

namespace App\Repositories;

use App\Interfaces\DriverMateInterface;
use App\Dto\DriverMateDto;
use App\Models\DriverMate;

class DriverMateRepository extends BaseRepository implements DriverMateInterface
{
    protected $_imgPath = 'driver-mates/';

    public function __construct(DriverMate $model)
    {
        $this->setModel($model);
    }

    public function show($id)
    {
        return $this->_model->with('drivers')->findOrFail($id);
    }

    public function store(DriverMateDto $data)
    {
        $dataArray = $data->toArray();

        $image = $dataArray['image'] ?? null;
        $companyIdCard = $dataArray['company_id_card'] ?? null;
        $driverId = $dataArray['driver_id'];

        unset($dataArray['image']);
        unset($dataArray['company_id_card']);
        unset($dataArray['driver_id']);

        $dataResult = $this->add($this->_model, $dataArray);

        $dataResult->drivers()->sync([
            $driverId => ['assigned_at' => now()],
        ]);

        if ($image != null) {
            $imageUploaded = $this->uploadFile($image, $this->_imgPath);
            $imageUploaded['type'] = 'image';
            $dataResult->files()->create($imageUploaded);
        }

        if ($companyIdCard != null) {
            $companyUploaded = $this->uploadFile($companyIdCard, $this->_imgPath);
            $companyUploaded['type'] = 'company_id_card';
            $dataResult->files()->create($companyUploaded);
        }

        return true;
    }

    public function update($id, DriverMateDto $data)
    {
        $result = $this->checkRecord($id);
        $dataArray = $data->toArray();

        $image = $dataArray['image'] ?? null;
        $companyIdCard = $dataArray['company_id_card'] ?? null;
        $driverId = $dataArray['driver_id'];

        unset($dataArray['image'], $dataArray['company_id_card'], $dataArray['driver_id']);

        $result->update($dataArray);

        $result->drivers()->sync([
            $driverId => ['assigned_at' => now()],
        ]);

        if ($image != null) {
            try {
                $imageUploaded = $this->uploadFile($image, $this->_imgPath);
                if ($imageUploaded && isset($imageUploaded['path'])) {
                    $imageUploaded['type'] = 'image';
                    $result->files()->updateOrCreate(['type' => 'image'], $imageUploaded);
                }
            } catch (\Throwable $th) {
                \Log::error('DriverMate image upload error on update: ' . $th->getMessage(), ['driver_mate_id' => $result->id]);
            }
        }

        if ($companyIdCard != null) {
            try {
                $companyUploaded = $this->uploadFile($companyIdCard, $this->_imgPath);
                if ($companyUploaded && isset($companyUploaded['path'])) {
                    $companyUploaded['type'] = 'company_id_card';
                    $result->files()->updateOrCreate(['type' => 'company_id_card'], $companyUploaded);
                }
            } catch (\Throwable $th) {
                \Log::error('DriverMate company_id_card upload error on update: ' . $th->getMessage(), ['driver_mate_id' => $result->id]);
            }
        }

        return $result;
    }
}
