<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use App\Relationships\FileRelationship;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IllegalHaulage extends Model
{
    use HasFactory, HasUuids, FileRelationship, HasPaymentStatus, HasIncidentAuditLog;

    protected $fillable = [
        'inspection_id',
        'driver_id',
        'vehicle_id',
        'waybill_id',
        'declared_quantity',
        'observed_quantity',
        'difference_quantity',
        'haulage_date',
        'haulage_location',
        'haulage_location_lat',
        'haulage_location_lng',
        'haulage_type',
        'cargo_description',
        'severity',
        'action_taken',
        'remarks',
        'status',
        'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment',
        'payment_status', 'created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'payment_status'  => 'boolean',
        'haulage_date' => 'date',
        'declared_quantity' => 'float',
        'observed_quantity' => 'float',
        'difference_quantity' => 'float',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverDetail::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function inspection()
    {
        return $this->belongsTo(GoodsInspection::class, 'inspection_id');
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            '1' => '<span class="badge bg-warning text-dark">Reviewed</span>',
            '2' => '<span class="badge bg-success">Resolved</span>',
            default => '<span class="badge bg-danger">Open</span>',
        };
    }

    public function getSeverityBadgeAttribute()
    {
        return match ($this->severity) {
            'critical' => '<span class="badge bg-danger">Critical</span>',
            'severe' => '<span class="badge bg-warning text-dark">Severe</span>',
            'moderate' => '<span class="badge bg-info text-dark">Moderate</span>',
            default => '<span class="badge bg-secondary">Minor</span>',
        };
    }

    public function getDocumentUrlAttribute(): ?string
    {
        $file = $this->relationLoaded('files')
            ? $this->files->firstWhere('type', 'document')
            : $this->files()->where('type', 'document')->latest()->first();

        if (!$file || empty($file->path)) {
            return null;
        }

        return $this->filePath($file->path);
    }

    public function getDocumentNameAttribute(): ?string
    {
        $file = $this->relationLoaded('files')
            ? $this->files->firstWhere('type', 'document')
            : $this->files()->where('type', 'document')->latest()->first();

        return $file?->name;
    }
}
