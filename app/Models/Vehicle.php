<?php

namespace App\Models;

use App\Relationships\FileRelationship;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Vehicle extends Model
{
    use HasFactory, HasUuids, FileRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fleet_no',
        'phone_number',
        'manager_first_name',
        'manager_last_name',
        'manager_email',
        'owner_first_name',
        'owner_last_name',
        'vehicle_make_model_id',
        'vehicle_type_id',
        'vehicle_type',
        'truck_no',
        'gps_unit_id',
        'gps_imei',
        'gps_provider',
        'gps_status',
        'gps_hw_type_id',
        'gps_sync_status',
        'gps_sync_error',
        'last_sync_at',
        'wialon_movement_type',
        'wialon_gps_correction',
        'wialon_min_satellites',
        'min_moving_speed_kmh',
        'min_parking_seconds',
        'max_messages_distance_m',
        'min_trip_seconds',
        'min_trip_distance_m',
        'speed_limit_kmh',
        'speeding_tolerance_kmh',
        'speeding_min_duration_s',
        'speeding_mode',
        'wialon_settings_synced_at',
        'last_service_date',
        'next_service_due_date',
        'insurance_expiry_date',
        'vehicle_license_expiry_date',
        'base_site',
        'status',
        'authority',
        'created_by'
    ];

    protected $casts = [
        'last_service_date' => 'datetime',
        'next_service_due_date' => 'datetime',
        'insurance_expiry_date' => 'datetime',
        'vehicle_license_expiry_date' => 'datetime',
        'last_sync_at' => 'datetime',
        'gps_status' => 'boolean',
        'wialon_movement_type' => 'integer',
        'wialon_gps_correction' => 'boolean',
        'wialon_min_satellites' => 'integer',
        'min_moving_speed_kmh' => 'integer',
        'min_parking_seconds' => 'integer',
        'max_messages_distance_m' => 'integer',
        'min_trip_seconds' => 'integer',
        'min_trip_distance_m' => 'integer',
        'speed_limit_kmh' => 'integer',
        'speeding_tolerance_kmh' => 'integer',
        'speeding_min_duration_s' => 'integer',
        'speeding_mode' => 'integer',
        'wialon_settings_synced_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d M Y');
    }

    public function getVehicleOwnerNameAttribute()
    {
        return trim($this->owner_first_name . ' ' . $this->owner_last_name);
    }

    public function getVehicleManagerNameAttribute()
    {
        return trim($this->manager_first_name . ' ' . $this->manager_last_name);
    }

    public function image()
    {
        return $this->fileUrl('image');
    }

    public function drivers()
    {
        return $this->belongsToMany(
            DriverDetail::class,
            'driver_vehicle',
            'vehicle_id',
            'driver_id'
        );
    }

    public function vehicle_make_model()
    {
        return $this->belongsTo(VehicleMakeModel::class);
    }

    public function vehicle_type()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function gps_device_type()
    {
        return $this->belongsTo(GpsDeviceType::class, 'gps_hw_type_id', 'wialon_hw_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getVehicleStatusAttribute()
    {
        $status = "";

        if($this->status == 0){
            $status = 'Unserviceable';
        }
        elseif($this->status == 1){
            $status = 'Active';
        }
        elseif($this->status == 2){
            $status = 'Serviceable';
        }

        return $status;
    }

    public function components()
    {
        return $this->hasMany(VehicleComponent::class, 'vehicle_id')->orderBy('category')->orderBy('location');
    }

    public function activeComponents()
    {
        return $this->hasMany(VehicleComponent::class, 'vehicle_id')->where('status', 'active')->orderBy('category')->orderBy('location');
    }

    public function componentHistory()
    {
        return $this->hasMany(VehicleComponent::class, 'vehicle_id')->where('status', '!=', 'active')->orderBy('removed_date', 'desc');
    }

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class, 'vehicle_id')->orderBy('maintenance_date', 'desc');
    }

    public function wialonSettingSyncLogs()
    {
        return $this->hasMany(WialonVehicleSettingsSyncLog::class, 'vehicle_id')->latest();
    }
}
