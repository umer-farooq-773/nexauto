<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsInspectionFinding extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'inspection_id',
        'finding_type',
        'remarks',
        'status',
        'created_by',
    ];

    public function inspection()
    {
        return $this->belongsTo(GoodsInspection::class, 'inspection_id');
    }

    public function getLabelAttribute(): string
    {
        return \App\Services\InspectionIncidentRequirementService::TYPE_META[$this->finding_type]['label']
            ?? $this->finding_type;
    }
}
