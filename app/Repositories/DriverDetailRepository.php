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
            $vehicleId = $dataArray['vehicle_id'];
            $companyIdCard = $dataArray['company_id_card'];
            $licenseCard = $dataArray['license_card'];

            \Log::info('Driver store data', [
                'vehicle_id' => $vehicleId,
                'data_array' => $dataArray
            ]);

            unset($dataArray['image'], $dataArray['vehicle_id'], $dataArray['company_id_card'], $dataArray['license_card']);

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

            if ($vehicleId) {
                \Log::info('Attempting to sync vehicle in store', [
                    'driver_id' => $dataResult->id,
                    'vehicle_id' => $vehicleId
                ]);
                try {
                    $syncResult = $dataResult->vehicles()->sync([$vehicleId]);
                    \Log::info('Vehicle sync successful in store', [
                        'driver_id' => $dataResult->id,
                        'vehicle_id' => $vehicleId,
                        'sync_result' => $syncResult
                    ]);
                } catch (\Throwable $th) {
                    \Log::error('Driver vehicle sync error in store: ' . $th->getMessage(), [
                        'driver_id' => $dataResult->id,
                        'vehicle_id' => $vehicleId,
                        'exception' => $th,
                        'trace' => $th->getTraceAsString()
                    ]);
                    throw $th;
                }
            } else {
                \Log::warning('No vehicle ID provided in store', ['driver_id' => $dataResult->id]);
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
            $vehicleId = $dataArray['vehicle_id'];
            $image = $dataArray['image'];
            $companyIdCard = $dataArray['company_id_card'];
            $licenseCard = $dataArray['license_card'];
            unset($dataArray['vehicle_id'], $dataArray['image'], $dataArray['company_id_card'], $dataArray['license_card']);

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

            if ($vehicleId) {
                \Log::info('Attempting to sync vehicle', [
                    'driver_id' => $result->id,
                    'vehicle_id' => $vehicleId
                ]);
                try {
                    $syncResult = $result->vehicles()->sync([$vehicleId]);
                    \Log::info('Vehicle sync successful', [
                        'driver_id' => $result->id,
                        'vehicle_id' => $vehicleId,
                        'sync_result' => $syncResult
                    ]);
                } catch (\Throwable $th) {
                    \Log::error('Driver vehicle sync error on update: ' . $th->getMessage(), [
                        'driver_id' => $result->id,
                        'vehicle_id' => $vehicleId,
                        'exception' => $th,
                        'trace' => $th->getTraceAsString()
                    ]);
                    throw $th; // Now it will throw the error so we can see it
                }
            } else {
                \Log::info('No vehicle ID provided for driver', ['driver_id' => $result->id]);
            }

            return $result;
        } catch (\Throwable $th) {
            \Log::error('Driver update error: ' . $th->getMessage(), ['exception' => $th]);
            throw $th;
        }
    }
}
