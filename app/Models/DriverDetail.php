<?php

namespace App\Models;

use App\Relationships\FileRelationship;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    use HasFactory, HasUuids, FileRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'address',
        'phone_number',
        'date_of_birth',
        'national_id_passport_number',
        'date_employed',
        'drivers_license_number',
        'license_category_class',
        'license_expiry_date',
        'emergency_contact_name',
        'emergency_contact_phone_number',
        'status',
        'bvn_number',
        'employer_number',
        'email',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function breathaliserTests()
    {
        return $this->hasMany(BreathaliserTest::class, 'driver_id');
    }

    public function driverMisconducts()
    {
        return $this->hasMany(DriverMisconduct::class, 'driver_id');
    }

    public function illegalHaulages()
    {
        return $this->hasMany(IllegalHaulage::class, 'driver_id');
    }

    public function illegalShortages()
    {
        return $this->hasMany(IllegalShortage::class, 'driver_id');
    }

    public function waybills()
    {
        return $this->hasMany(Waybill::class, 'driver_id');
    }

    public function getStatusBadgeAttribute()
    {
        if ((int) $this->status === 1) {
            return '<span class="badge bg-success">' . __('Active') . '</span>';
        }
        else {
            return '<span class="badge bg-danger">' . __('Inactive') . '</span>';
        }
    }

    public function image()
    {
        return $this->fileUrl('image');
    }

    public function companyIdCardImage()
    {
        return $this->fileUrl('company_id_card');
    }

    public function licenseCardImage()
    {
        return $this->fileUrl('license_card');
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'driver_vehicle', 'driver_id', 'vehicle_id');
    }

    public function mates()
    {
        return $this->belongsToMany(DriverMate::class, 'driver_mate_pivot', 'driver_id', 'driver_mate_id')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function eldLogs()
    {
        return $this->hasMany(DriverEldLog::class, 'driver_id')->latest();
    }

    public function driverPlans()
    {
        return $this->hasMany(WaybillDriverPlan::class, 'driver_id');
    }
}
