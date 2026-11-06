<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WaybillDriverPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'waybill_id',
        'driver_id',
        'sequence',
        'planned_seconds',
        'status',
        'actual_driven_seconds',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'planned_seconds' => 'integer',
        'actual_driven_seconds' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function driver()
    {
        return $this->belongsTo(DriverDetail::class);
    }
}
