<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleComponent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vehicle_id',
        'category',
        'component_name',
        'location',
        'serial_no',
        'brand',
        'model',
        'size',
        'installed_date',
        'installed_odometer',
        'status',
        'removed_date',
        'removed_reason',
        'vehicle_maintenance_id',
        'vehicle_maintenance_item_id',
    ];

    protected $casts = [
        'installed_date' => 'date',
        'removed_date' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenance::class, 'vehicle_maintenance_id');
    }

    public function maintenanceItem(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenanceItem::class, 'vehicle_maintenance_item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRemoved($query)
    {
        return $query->where('status', '!=', 'active');
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match (strtolower($this->category ?? '')) {
            'tyre', 'tire' => '<span class="badge bg-primary text-white"><i class="ri-disc-line me-1"></i> Tyre</span>',
            'battery' => '<span class="badge bg-warning text-dark"><i class="ri-battery-2-charge-line me-1"></i> Battery</span>',
            'telematics', 'gps' => '<span class="badge bg-info text-white"><i class="ri-radar-line me-1"></i> Telematics</span>',
            default => '<span class="badge bg-secondary text-white"><i class="ri-tools-line me-1"></i> ' . ucfirst($this->category ?? 'Component') . '</span>',
        };
    }
}
