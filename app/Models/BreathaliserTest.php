<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use App\Relationships\FileRelationship;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreathaliserTest extends Model
{
    use HasFactory, HasUuids, FileRelationship, HasPaymentStatus, HasIncidentAuditLog;

    protected $fillable = [
        'inspection_id',
        'driver_id', 'vehicle_id', 'waybill_id',
        'test_date', 'test_location',
        'test_location_lat', 'test_location_lng',
        'device_number',
        'bac_level', 'test_result', 'action_taken', 'remarks', 'status',
        'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment',
        'payment_status', 'created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'payment_status'  => 'boolean',
        'test_date' => 'date',
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
            '2' => '<span class="badge bg-success">Closed</span>',
            default => '<span class="badge bg-danger">Open</span>',
        };
    }

    public function getResultBadgeAttribute()
    {
        return match ($this->test_result) {
            'positive' => '<span class="badge bg-danger">Positive</span>',
            'borderline' => '<span class="badge bg-warning text-dark">Borderline</span>',
            default => '<span class="badge bg-success">Negative</span>',
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
