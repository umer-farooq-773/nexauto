<?php

namespace App\Repositories;

use App\Interfaces\VehicleMaintenanceInterface;
use App\Dto\VehicleMaintenanceDto;
use App\Models\VehicleMaintenance;
use Illuminate\Support\Facades\Log;

class VehicleMaintenanceRepository extends BaseRepository implements VehicleMaintenanceInterface
{
    private $_maintenanceFilePath = 'maintenance/';
    public function __construct(VehicleMaintenance $model)
    {
        $this->setModel($model);
    }

    public function index($relation = null)
    {
        return $this->_model->with(['vehicle.drivers', 'maintenanceTypes'])->latest()->paginate(15);
    }

    public function store(VehicleMaintenanceDto $data)
    {
        $dataArray = $data->toArray();
        $files     = $data->maintenance_files;
        $typeIds   = $data->maintenance_type_ids;

        $record = $this->add($this->_model, $dataArray);

        // Sync maintenance types
        if (!empty($typeIds)) {
            $record->maintenanceTypes()->sync($typeIds);
        }

        if (!empty($files)) {
            try {
                $uploaded = $this->uploadMultipleFiles($files, $this->_maintenanceFilePath);
                foreach ($uploaded as $file) {
                    $record->maintenanceFiles()->create([
                        'name'      => $file['file_name'],
                        'path'      => $file['file_path'],
                        'file_type' => get_class($record),   // App\Models\VehicleMaintenance
                        'file_id'   => $record->id,
                        'type'      => 'maintenance_file',
                        'status'    => '1',
                    ]);
                }
            } catch (\Throwable $th) {
                Log::error('Maintenance file upload error: ' . $th->getMessage(), [
                    'maintenance_id' => $record->id
                ]);
            }
        }

        return $record;
    }

    public function update($id, VehicleMaintenanceDto $data)
    {
        $result    = $this->checkRecord($id);
        $dataArray = $data->toArray();
        $files     = $data->maintenance_files;
        $typeIds   = $data->maintenance_type_ids;

        $result->update($dataArray);

        // Sync maintenance types
        if (!empty($typeIds)) {
            $result->maintenanceTypes()->sync($typeIds);
        } else {
            $result->maintenanceTypes()->detach();
        }

        if (!empty($files)) {
            try {
                $uploaded = $this->uploadMultipleFiles($files, $this->_maintenanceFilePath);
                foreach ($uploaded as $file) {
                    $result->maintenanceFiles()->create([
                        'name'      => $file['file_name'],
                        'path'      => $file['file_path'],
                        'file_type' => get_class($result),   // App\Models\VehicleMaintenance
                        'file_id'   => $result->id,
                        'type'      => 'maintenance_file',
                        'status'    => '1',
                    ]);
                }
            } catch (\Throwable $th) {
                \Log::error('Maintenance file upload error on update: ' . $th->getMessage(), [
                    'maintenance_id' => $result->id
                ]);
            }
        }

        return $result;
    }
}
