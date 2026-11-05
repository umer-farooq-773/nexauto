<?php

namespace App\Models;

use App\Models\Concerns\HasIncidentAuditLog;
use App\Relationships\FileRelationship;
use App\Models\Concerns\HasPaymentHistory;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IllegalDetention extends Model
{
    use HasFactory, HasUuids, FileRelationship, HasPaymentStatus, HasPaymentHistory, HasIncidentAuditLog;

    protected $fillable = [
        'waybill_id', 'vehicle_id', 'detention_date', 'detention_location',
        'detention_location_lat', 'detention_location_lng',
        'detained_by', 'reason', 'release_date', 'remarks', 'status',
        'client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment',
        'payment_status','cost','authority','created_by'
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'detention_date' => 'date',
        'release_date'   => 'date',
        'cost'           => 'decimal:2',
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
        return $this->status == '1'
            ? '<span class="badge bg-success">Released</span>'
            : '<span class="badge bg-danger">Detained</span>';
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
