<?php

namespace App\Repositories;

use App\Interfaces\WaybillFileInterface;
use App\Models\WaybillFile;

class WaybillFileRepository extends BaseRepository implements WaybillFileInterface
{
    public function __construct(WaybillFile $model)
    {
        $this->setModel($model);
    }

    public function findByWaybill($waybillId)
    {
        return $this->_model->where('waybill_id', $waybillId)->latest()->paginate(20);
    }

    public function store(array $data)
    {
        return $this->add($this->_model, $data);
    }

    public function show($id)
    {
        return $this->get_by_id($this->_model, $id);
    }

    public function update($id, array $data)
    {
        $file = $this->checkRecord($id);
        return $file->update($data);
    }

    public function removeFile($id)
    {
        $file = $this->checkRecord($id);
        if ($file->path) {
            $file->deleteFile($file->path);
        }
        return $file->delete();
    }
}
