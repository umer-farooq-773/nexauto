<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsInspection extends Model
{
    use HasFactory, HasUuids, HasIncidentAuditLog;

    protected $fillable = [
        'vehicle_id', 'waybill_id', 'fleet_no', 'vehicle_lorry_number', 'inspection_date', 'inspection_time',
        'inspection_type', 'inspection_location', 'inspection_location_lat', 'inspection_location_lng', 'inspector_name', 'route', 'driver_name',
        'driver_phone_id', 'goods_description', 'waybill_number', 'declared_quantity',
        'observed_quantity', 'cargo_secured', 'goods_match_waybill', 'unauthorised_items',
        'doors_tarpaulin_secure', 'visible_damage', 'route_diversion', 'illegal_haulage', 'illegal_shortage', 'breathaliser_test', 'driver_misconduct',
        'issues_found', 'goods_status', 'status',
        'remarks', 'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment', 'created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'inspection_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function illegalHaulage()
    {
        return $this->hasOne(IllegalHaulage::class, 'inspection_id');
    }

    public function illegalShortage()
    {
        return $this->hasOne(IllegalShortage::class, 'inspection_id');
    }

    public function routeDiversion()
    {
        return $this->hasOne(RouteDiversion::class, 'inspection_id');
    }

    public function breathaliserTest()
    {
        return $this->hasOne(BreathaliserTest::class, 'inspection_id');
    }

    public function driverMisconduct()
    {
        return $this->hasOne(DriverMisconduct::class, 'inspection_id');
    }

    public function findings()
    {
        return $this->hasMany(GoodsInspectionFinding::class, 'inspection_id');
    }

    public function cargos()
    {
        return $this->hasMany(GoodsInspectionCargo::class, 'inspection_id');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status == '1'
            ? '<span class="badge bg-success">Completed</span>'
            : '<span class="badge bg-warning">Pending</span>';
    }

    public function getGoodsBadgeAttribute()
    {
        return $this->goods_status == 'cleared'
            ? '<span class="badge bg-success">Cleared / Safe</span>'
            : '<span class="badge bg-danger">Issues Found</span>';
    }
}
