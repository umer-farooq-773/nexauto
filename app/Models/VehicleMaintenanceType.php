<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VehicleMaintenanceType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function vehicleMaintenances(): BelongsToMany
    {
        return $this->belongsToMany(VehicleMaintenance::class, 'vehicle_maintenance_maintenance_type', 'vehicle_maintenance_type_id', 'vehicle_maintenance_id');
    }
}
