<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use App\Models\Concerns\HasPaymentHistory;
use App\Relationships\FileRelationship;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accident extends Model
{
    use HasFactory, HasUuids, FileRelationship, HasPaymentStatus, HasPaymentHistory, HasIncidentAuditLog;

    protected $fillable = [
        'waybill_id', 'vehicle_id', 'accident_date', 'accident_location',
        'accident_location_lat', 'accident_location_lng',
        'accident_type', 'accident_type_detail', 'description', 'damage_cost',
        'resolved_date', 'status', 'driver_status', 'motor_mate_status',
        'content', 'damage_extend', 'is_fatality', 'fatality_type',
        'accident_nature_detail', 'is_damage_asset', 'is_injury',
        'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment',
        'payment_status','authority','created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'accident_date'  => 'date',
        'resolved_date'  => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            '1' => '<span class="badge bg-success">Resolved</span>',
            '2' => '<span class="badge bg-warning text-dark">In Progress</span>',
            default => '<span class="badge bg-danger">Pending</span>',
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
