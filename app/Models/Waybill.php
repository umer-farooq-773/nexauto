<?php

namespace App\Models;

use App\Relationships\FileRelationship;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waybill extends Model
{
    use HasFactory, HasUuids;
    use FileRelationship;

    public const ACTIVE_STATUS_VALUES = [0, 1, 2, 5];

    protected $fillable = [
        'waybill_number',
        'vehicle_id',
        'driver_id',
        'status_id',
        'route_trip_id',
        'origin',
        'origin_lat',
        'origin_lng',
        'destination',
        'destination_lat',
        'destination_lng',
        'checkpoints',
        'route_geometry',
        'wialon_route_id',
        'route_source',
        'distance',
        'estimated_duration',
        'dispatch_date',
        'delivery_date',
        'cargo_description',
        'quantity',
        'quantity_unit',
        'cargo_weight_kg',
        'weight_unit',
        'volume_unit',
        'estimated_dispatch_time',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'delivery_date' => 'date',
        'checkpoints' => 'array',
        'route_geometry' => 'array',
        'origin_lat' => 'float',
        'origin_lng' => 'float',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'quantity' => 'float',
        'cargo_weight_kg' => 'float',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(DriverDetail::class, 'driver_id');
    }

    public function driverPlans()
    {
        return $this->hasMany(WaybillDriverPlan::class, 'waybill_id')->orderBy('sequence');
    }

    public function eldLogs()
    {
        return $this->hasMany(DriverEldLog::class, 'waybill_id');
    }

    public function drivers()
    {
        return $this->hasManyThrough(
            DriverDetail::class,
            WaybillDriverPlan::class,
            'waybill_id',
            'id',
            'id',
            'driver_id',
        )->orderBy('waybill_driver_plans.sequence');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function route_trip()
    {
        return $this->belongsTo(RouteTrip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute()
    {
        return $this->status?->badge_label ?? 'Unknown';
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->status) {
            return '<span class="badge bg-secondary">Unknown</span>';
        }

        $badgeClass = $this->status->badge_class ?: 'bg-secondary';
        $label = e($this->status->badge_label ?: $this->status->value ?: 'Unknown');

        return '<span class="badge ' . e($badgeClass) . '">' . $label . '</span>';
    }

    public function getPodUrlAttribute(): ?string
    {
        $file = $this->relationLoaded('files')
            ? ($this->files->firstWhere('type', 'pod') ?? $this->files->firstWhere('type', 'delivery_document'))
            : ($this->files()->whereIn('type', ['pod', 'delivery_document'])->latest()->first());

        if (!$file || empty($file->path)) {
            return null;
        }

        return $this->filePath($file->path);
    }

    public function getPodNameAttribute(): ?string
    {
        $file = $this->relationLoaded('files')
            ? ($this->files->firstWhere('type', 'pod') ?? $this->files->firstWhere('type', 'delivery_document'))
            : ($this->files()->whereIn('type', ['pod', 'delivery_document'])->latest()->first());

        return $file?->name;
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

    public function getDocumentPathAttribute(): ?string
    {
        $file = $this->relationLoaded('files')
            ? $this->files->firstWhere('type', 'document')
            : $this->files()->where('type', 'document')->latest()->first();

        return $file?->path;
    }
    public function impoundment()
    {
        return $this->hasOne(Impoundment::class);
    }

    public function accident()
    {
        return $this->hasOne(Accident::class);
    }

    public function vehicle_breakdown()
    {
        return $this->hasOne(VehicleBreakdown::class)->where('incident_type', 'breakdown')->latestOfMany();
    }

    public function vehicle_bogged_down()
    {
        return $this->hasOne(VehicleBoggedDown::class)->latestOfMany();
    }

    public function illegal_detention()
    {
        return $this->hasOne(IllegalDetention::class);
    }

    public function illegal_haulage()
    {
        return $this->hasOne(IllegalHaulage::class);
    }

    public function illegal_shortage()
    {
        return $this->hasOne(IllegalShortage::class);
    }

    public function breathaliser_test()
    {
        return $this->hasOne(BreathaliserTest::class);
    }

    public function route_diversion()
    {
        return $this->hasOne(RouteDiversion::class);
    }

    public function driver_misconduct()
    {
        return $this->hasOne(DriverMisconduct::class);
    }

    public function goods_inspection()
    {
        return $this->hasOne(GoodsInspection::class);
    }

    // Has Many Relation for Showing Incident Detail here

    public function accidents()
    {
        return $this->hasMany(Accident::class);
    }

    public function vehicle_breakdowns()
    {
        return $this->hasMany(VehicleBreakdown::class);
    }

    public function vehicle_bogged_downs()
    {
        return $this->hasMany(VehicleBoggedDown::class);
    }

    public function impoundments()
    {
        return $this->hasMany(Impoundment::class);
    }

    public function route_diversions()
    {
        return $this->hasMany(RouteDiversion::class);
    }

    public function illegal_detentions()
    {
        return $this->hasMany(IllegalDetention::class);
    }

    public function illegal_haulages()
    {
        return $this->hasMany(IllegalHaulage::class);
    }

    public function illegal_shortages()
    {
        return $this->hasMany(IllegalShortage::class);
    }

    public function breathaliser_tests()
    {
        return $this->hasMany(BreathaliserTest::class);
    }

    public function driver_misconducts()
    {
        return $this->hasMany(DriverMisconduct::class);
    }

    public function goods_inspections()
    {
        return $this->hasMany(GoodsInspection::class);
    }

    public function getIncidentStatusBadgeAttribute()
    {
        // Get relationship data - use loaded relations if available, otherwise return empty
        $impoundment = $this->relationLoaded('impoundment') ? $this->impoundment : null;
        $accident = $this->relationLoaded('accident') ? $this->accident : null;
        $vehicleBreakdown = $this->relationLoaded('vehicle_breakdown') ? $this->vehicle_breakdown : null;
        $vehicleBoggedDown = $this->relationLoaded('vehicle_bogged_down') ? $this->vehicle_bogged_down : null;
        $illegalDetention = $this->relationLoaded('illegal_detention') ? $this->illegal_detention : null;
        $illegalHaulage = $this->relationLoaded('illegal_haulage') ? $this->illegal_haulage : null;
        $illegalShortage = $this->relationLoaded('illegal_shortage') ? $this->illegal_shortage : null;
        $breathaliserTest = $this->relationLoaded('breathaliser_test') ? $this->breathaliser_test : null;
        $routeDiversion = $this->relationLoaded('route_diversion') ? $this->route_diversion : null;
        $driverMisconduct = $this->relationLoaded('driver_misconduct') ? $this->driver_misconduct : null;

        $incidents = collect([
            $this->buildIncidentBadgeData('impoundment', $impoundment, $impoundment ? route('impoundment.show', $impoundment->id) : null, fn ($record) => [
                'label' => $record->status == '1' ? 'Impoundment Released' : 'Impounded',
                'class' => $record->status == '1' ? 'bg-success' : 'bg-danger text-white',
            ]),
            $this->buildIncidentBadgeData('accident', $accident, $accident ? route('accident.show', $accident->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Accident Resolved',
                    '2' => 'Accident In Progress',
                    default => 'Accident Pending',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-success',
                    '2' => 'bg-warning text-dark',
                    default => 'bg-danger',
                },
            ]),
            $this->buildIncidentBadgeData('vehicle_breakdown', $vehicleBreakdown, $vehicleBreakdown ? route('vehicle_breakdown.show', $vehicleBreakdown->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Breakdown Resolved',
                    '2' => 'Breakdown In Progress',
                    default => 'Breakdown Pending',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-success',
                    '2' => 'bg-warning text-dark',
                    default => 'bg-danger',
                },
            ]),
            $this->buildIncidentBadgeData('vehicle_bogged_down', $vehicleBoggedDown, $vehicleBoggedDown ? route('vehicle_bogged_down.show', $vehicleBoggedDown->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Bogged Down Recovered',
                    '2' => 'Bogged Down In Progress',
                    default => 'Bogged Down Pending',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-success',
                    '2' => 'bg-warning text-dark',
                    default => 'bg-danger',
                },
            ]),
            $this->buildIncidentBadgeData('illegal_detention', $illegalDetention, $illegalDetention ? route('illegal_detention.show', $illegalDetention->id) : null, fn ($record) => [
                'label' => $record->status == '1' ? 'Illegal Detention Released' : 'Detained',
                'class' => $record->status == '1' ? 'bg-success' : 'bg-danger text-white',
            ]),
            $this->buildIncidentBadgeData('illegal_haulage', $illegalHaulage, $illegalHaulage ? route('illegal_haulage.show', $illegalHaulage->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Illegal Haulage Review',
                    '2' => 'Illegal Haulage Resolved',
                    default => 'Illegal Haulage Open',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-warning text-dark',
                    '2' => 'bg-success',
                    default => 'bg-danger',
                },
            ]),
            $this->buildIncidentBadgeData('illegal_shortage', $illegalShortage, $illegalShortage ? route('illegal_shortage.show', $illegalShortage->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Illegal Shortage Review',
                    '2' => 'Illegal Shortage Resolved',
                    default => 'Illegal Shortage Open',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-warning text-dark',
                    '2' => 'bg-success',
                    default => 'bg-danger',
                },
            ]),
            $this->buildIncidentBadgeData('breathaliser_test', $breathaliserTest, $breathaliserTest ? route('breathaliser_test.show', $breathaliserTest->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Breathaliser Review',
                    '2' => 'Breathaliser Closed',
                    default => 'Breathaliser Open',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-warning text-dark',
                    '2' => 'bg-success',
                    default => 'bg-danger',
                },
            ]),
            $this->buildIncidentBadgeData('route_diversion', $routeDiversion, $routeDiversion ? route('route_diversion.show', $routeDiversion->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Route Diversion Completed',
                    '2' => 'Route Diversion Cancelled',
                    default => 'Route Diversion Active',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-success',
                    '2' => 'bg-secondary',
                    default => 'bg-warning text-dark',
                },
            ]),
            $this->buildIncidentBadgeData('driver_misconduct', $driverMisconduct, $driverMisconduct ? route('driver_misconduct.show', $driverMisconduct->id) : null, fn ($record) => [
                'label' => match ($record->status) {
                    '1' => 'Driver Misconduct Review',
                    '2' => 'Driver Misconduct Resolved',
                    default => 'Driver Misconduct Open',
                },
                'class' => match ($record->status) {
                    '1' => 'bg-warning text-dark',
                    '2' => 'bg-success',
                    default => 'bg-danger',
                },
            ]),
        ])->filter();

        if ($incidents->isEmpty()) {
            return '<span class="badge bg-light text-muted border">None</span>';
        }

        return $incidents
            ->sortByDesc(fn ($incident) => $incident['record']->updated_at ?? $incident['record']->created_at)
            ->map(function ($incident) {
                return '<a href="' . $incident['route'] . '" class="badge ' . $incident['class'] . ' text-decoration-none border-0 me-1 mb-1">' . $incident['label'] . '</a>';
            })
            ->implode(' ');
    }

    protected function buildIncidentBadgeData(string $type, $record, ?string $route, callable $resolver): ?array
    {
        if (!$record) {
            return null;
        }

        $badge = $resolver($record);

        return [
            'type' => $type,
            'record' => $record,
            'route' => $route,
            'label' => $badge['label'],
            'class' => $badge['class'],
        ];
    }

    /**
     * Returns the total number of loaded incident relations that have a record.
     * Uses the same singular relations as getIncidentStatusBadgeAttribute so no
     * extra queries are fired — the relations are already eager-loaded by the repo.
     */
    public function getIncidentCountAttribute(): int
    {
        $relations = [
            'impoundment',
            'accident',
            'vehicle_breakdown',
            'vehicle_bogged_down',
            'illegal_detention',
            'illegal_haulage',
            'breathaliser_test',
            'route_diversion',
            'driver_misconduct',
        ];

        return collect($relations)
            ->filter(fn ($rel) => $this->relationLoaded($rel) && $this->{$rel} !== null)
            ->count();
    }

    public function waybillFiles()
    {
        return $this->hasMany(WaybillFile::class);
    }

    public function cargos()
    {
        return $this->hasMany(WaybillCargo::class, 'waybill_id');
    }
}
