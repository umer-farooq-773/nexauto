<?php

namespace App\Models;

use App\Helper\FileUpload;
use App\Relationships\FileRelationship;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaybillFile extends Model
{
    use HasFactory, HasUuids, FileUpload, FileRelationship;

    protected $fillable = [
        'waybill_id',
        'name',
        'path',
        'type',
        'status',
    ];

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function getUrlAttribute()
    {
        return $this->filePath($this->path);
    }
}
