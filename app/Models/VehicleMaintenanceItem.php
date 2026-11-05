<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleMaintenanceItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vehicle_maintenance_id',
        'category',
        'action',
        'location',
        'axle',
        'position',
        'item_name',
        'brand',
        'model',
        'size',
        'old_serial_no',
        'new_serial_no',
        'tread_depth_mm',
        'reason',
        'quantity',
        'unit_cost',
        'cost',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'tread_depth_mm' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'cost' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenance::class, 'vehicle_maintenance_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path) {
            return Storage::disk('public')->url($this->photo_path);
        }
        return null;
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match (strtolower($this->category ?? '')) {
            'tyre', 'tire' => '<span class="badge bg-primary text-white"><i class="ri-disc-line me-1"></i> Tyre</span>',
            'battery' => '<span class="badge bg-warning text-dark"><i class="ri-battery-2-charge-line me-1"></i> Battery</span>',
            'telematics', 'gps' => '<span class="badge bg-info text-white"><i class="ri-radar-line me-1"></i> Telematics</span>',
            'brake' => '<span class="badge bg-danger text-white"><i class="ri-stop-circle-line me-1"></i> Brake</span>',
            'engine' => '<span class="badge bg-secondary text-white"><i class="ri-settings-4-line me-1"></i> Engine</span>',
            default => '<span class="badge bg-dark text-white"><i class="ri-tools-line me-1"></i> ' . ucfirst($this->category ?? 'General') . '</span>',
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match (strtolower($this->action ?? '')) {
            'replace', 'replaced' => '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ri-loop-right-line me-1"></i> Replaced</span>',
            'install', 'installed' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="ri-add-circle-line me-1"></i> Installed</span>',
            'repair', 'repaired' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="ri-hammer-line me-1"></i> Repaired</span>',
            'rotate', 'rotated' => '<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="ri-refresh-line me-1"></i> Rotated</span>',
            'inspect', 'inspected' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="ri-eye-line me-1"></i> Inspected</span>',
            'calibrate', 'calibrated' => '<span class="badge bg-purple-subtle text-dark border border-purple-subtle"><i class="ri-sound-module-line me-1"></i> Calibrated</span>',
            default => '<span class="badge bg-light text-dark border">' . ucfirst($this->action ?? 'Service') . '</span>',
        };
    }

    public function getItemDescriptionAttribute(): string
    {
        $parts = [];
        if ($this->brand) $parts[] = $this->brand;
        if ($this->size) $parts[] = $this->size;
        if ($this->model) $parts[] = $this->model;
        if ($this->item_name && empty($parts)) $parts[] = $this->item_name;
        
        return !empty($parts) ? implode(' ', $parts) : ($this->item_name ?? ucfirst($this->category));
    }
}
