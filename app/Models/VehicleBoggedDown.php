<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use App\Models\Concerns\HasPaymentHistory;
use App\Relationships\FileRelationship;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBoggedDown extends Model
{
    use HasFactory, HasUuids, FileRelationship, HasPaymentStatus, HasPaymentHistory, HasIncidentAuditLog;

    protected $fillable = [
        'waybill_id',
        'vehicle_id',
        'bogged_date',
        'stuck_location',
        'stuck_location_lat',
        'stuck_location_lng',
        'ground_condition',
        'description',
        'recovered_date',
        'recovery_cost',
        'status',
        'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment',
        'payment_status','authority','created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'bogged_date'    => 'date',
        'recovered_date' => 'date',
    ];

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function waybills()
    {
        return $this->hasMany(Waybill::class, 'vehicle_id', 'vehicle_id');
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            '1' => '<span class="badge bg-success">Recovered</span>',
            '2' => '<span class="badge bg-warning text-dark">Recovery In Progress</span>',
            default => '<span class="badge bg-danger">Stuck / Pending</span>',
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
