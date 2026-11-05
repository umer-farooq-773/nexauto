<?php

namespace App\Models;

use App\Models\Concerns\HasPaymentHistory;
use App\Models\Concerns\HasPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    use HasFactory, HasUuids, HasPaymentStatus, HasPaymentHistory;

    protected $fillable = [
        'reference_no',
        'vehicle_id',
        'maintenance_date',
        'next_maintenance_date',
        'expected_completion_date',
        'odometer',
        'cost',
        'service_provider',
        'priority',
        'description',
        'status',
        'client_approved',
        'client_approved_at',
        'client_approved_by',
        'client_approved_comment',
        'payment_status',
    ];

    protected $casts = [
        'client_approved' => 'boolean',
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'expected_completion_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_no)) {
                $count = static::count() + 1;
                $model->reference_no = 'MT-' . str_pad($count, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceItem::class, 'vehicle_maintenance_id')->orderBy('created_at', 'asc');
    }

    public function installedComponents(): HasMany
    {
        return $this->hasMany(VehicleComponent::class, 'vehicle_maintenance_id');
    }

    public function maintenanceFiles()
    {
        return $this->hasMany(File::class, 'file_id', 'id')
                    ->where('file_type', self::class)
                    ->where('type', 'maintenance_file');
    }

    public function getStatusBadgeAttribute()
    {
        return match ((string) $this->status) {
            '1' => '<span class="badge bg-success">Completed</span>',
            '2' => '<span class="badge bg-warning text-dark">In Progress</span>',
            default => '<span class="badge bg-danger">Pending</span>',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ((string) $this->status) {
            '1' => 'Completed',
            '2' => 'In Progress',
            default => 'Pending',
        };
    }

    public function getFormattedReferenceNoAttribute()
    {
        return $this->reference_no ?: ('#MT-' . substr($this->id, 0, 8));
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'file', 'file_type', 'file_id');
    }

    public function maintenanceTypes(): BelongsToMany
    {
        return $this->belongsToMany(VehicleMaintenanceType::class, 'vehicle_maintenance_maintenance_type', 'vehicle_maintenance_id', 'vehicle_maintenance_type_id');
    }
}
