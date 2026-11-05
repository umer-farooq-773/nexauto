<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteTrip extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'trip_name',
        'origin',
        'destination',
        'distance_km',
        'estimated_duration',
        'description',
        'status',
    ];

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge bg-success">' . __('Active') . '</span>';
        }
        return '<span class="badge bg-danger">' . __('Inactive') . '</span>';
    }
}
