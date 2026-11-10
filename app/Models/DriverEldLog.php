<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverEldLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'driver_eld_logs';

    protected $fillable = [
        'driver_id',
        'waybill_id',
        'vehicle_id',
        'event_type',
        'status',
        'latitude',
        'longitude',
        'location_name',
        'speed_kmh',
        'elapsed_seconds',
        'remaining_seconds',
        'planned_seconds',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed_kmh' => 'float',
        'elapsed_seconds' => 'integer',
        'remaining_seconds' => 'integer',
        'planned_seconds' => 'integer',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverDetail::class, 'driver_id');
    }

    public function waybill()
    {
        return $this->belongsTo(Waybill::class, 'waybill_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function getEventBadgeClassAttribute(): string
    {
        return match ($this->event_type) {
            'start_duty', 'resume_duty' => 'bg-success',
            'pause_duty', 'rest'        => 'bg-warning text-dark',
            'end_duty'                  => 'bg-secondary',
            'movement_detected'         => 'bg-info text-dark',
            'overtime_violation'        => 'bg-danger',
            'checkpoint_passed'         => 'bg-primary',
            default                     => 'bg-dark',
        };
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event_type) {
            'start_duty'         => 'Duty Started',
            'resume_duty'        => 'Duty Resumed',
            'pause_duty'         => 'Duty Paused / Rest',
            'rest'               => 'Rest Break',
            'end_duty'           => 'Duty Completed',
            'movement_detected'  => 'Vehicle Movement Alert',
            'overtime_violation' => 'Overtime Violation',
            'checkpoint_passed'  => 'Checkpoint Reached',
            'manual_remark'      => 'Driver Remark',
            default              => ucwords(str_replace('_', ' ', $this->event_type)),
        };
    }

    public static function formatDuration(?int $seconds): string
    {
        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return '0 Mins';
        }
        if ($seconds >= 3600) {
            $hours = floor($seconds / 3600);
            $mins = floor(($seconds % 3600) / 60);
            return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours} Hours";
        }
        $mins = max(1, round($seconds / 60));
        return "{$mins} Mins";
    }

    public static function formatDigitalTime(?int $seconds): string
    {
        $seconds = max(0, (int) $seconds);
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    public function getFormattedElapsedAttribute(): string
    {
        return self::formatDigitalTime($this->elapsed_seconds);
    }

    public function getFormattedPlannedAttribute(): string
    {
        return self::formatDuration($this->planned_seconds);
    }
}

