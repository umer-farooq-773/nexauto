<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use App\Relationships\FileRelationship;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteDiversion extends Model
{
    use HasFactory, HasUuids, FileRelationship, HasPaymentStatus, HasIncidentAuditLog;

    protected $fillable = [
        'inspection_id',
        'waybill_id', 'vehicle_id', 'diversion_date', 'diversion_location',
        'diversion_location_lat', 'diversion_location_lng',
        'original_route', 'diverted_route', 'diversion_reason',
        'expected_rejoin_date', 'actual_rejoin_date', 'remarks', 'status',
        'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment',
        'payment_status', 'created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'payment_status'  => 'boolean',
        'diversion_date'       => 'date',
        'expected_rejoin_date' => 'date',
        'actual_rejoin_date'   => 'date',
    ];

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function driver(){
        return $this->belongsTo(DriverDetail::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inspection()
    {
        return $this->belongsTo(GoodsInspection::class, 'inspection_id');
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            '1' => '<span class="badge bg-success">Completed</span>',
            '2' => '<span class="badge bg-secondary">Cancelled</span>',
            default => '<span class="badge bg-warning text-dark">Active Diversion</span>',
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
