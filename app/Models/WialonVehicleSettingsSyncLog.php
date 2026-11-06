<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WialonVehicleSettingsSyncLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'direction',
        'status',
        'settings',
        'error',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
