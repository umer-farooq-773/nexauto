<?php

namespace App\Models;

use App\Relationships\FileRelationship;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverMate extends Model
{
    use HasFactory, HasUuids, FileRelationship;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'phone_number',
        'date_of_birth',
        'national_id_passport_number',
        'date_employed',
        'emergency_contact_name',
        'emergency_contact_phone_number',
        'status',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge bg-success">' . __('Active') . '</span>';
        }
        return '<span class="badge bg-danger">' . __('Inactive') . '</span>';
    }

    public function image()
    {
        return $this->fileUrl('image');
    }

    public function companyIdCardImage()
    {
        return $this->fileUrl('company_id_card');
    }

    public function drivers()
    {
        return $this->belongsToMany(DriverDetail::class, 'driver_mate_pivot', 'driver_mate_id', 'driver_id')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }
}
