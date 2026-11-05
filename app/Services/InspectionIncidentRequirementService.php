<?php

namespace App\Services;

use App\Models\BreathaliserTest;
use App\Models\DriverMisconduct;
use App\Models\GoodsInspection;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\RouteDiversion;
use App\Models\Vehicle;
use App\Models\Waybill;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InspectionIncidentRequirementService
{
    public const ILLEGAL_HAULAGE = 'illegal_haulage';
    public const ILLEGAL_SHORTAGE = 'illegal_shortage';
    public const ROUTE_DIVERSION = 'route_diversion';
    public const BREATHALISER = 'breathaliser_test';
    public const DRIVER_MISCONDUCT = 'driver_misconduct';

    /**
     * Dedicated incident modules in the system.
     */
    public const TYPE_META = [
        self::ILLEGAL_HAULAGE => [
            'label' => 'Illegal Haulage',
            'model' => IllegalHaulage::class,
            'edit_route' => 'illegal_haulage.edit',
            'show_route' => 'illegal_haulage.show',
            'notify_type' => 'illegal_haulage',
            'icon' => 'ri-truck-line',
            'badge_color' => 'danger',
        ],
        self::ILLEGAL_SHORTAGE => [
            'label' => 'Illegal Shortage',
            'model' => IllegalShortage::class,
            'edit_route' => 'illegal_shortage.edit',
            'show_route' => 'illegal_shortage.show',
            'notify_type' => 'illegal_shortage',
            'icon' => 'ri-scales-line',
            'badge_color' => 'warning',
        ],
        self::ROUTE_DIVERSION => [
            'label' => 'Route Diversion',
            'model' => RouteDiversion::class,
            'edit_route' => 'route_diversion.edit',
            'show_route' => 'route_diversion.show',
            'notify_type' => 'route_diversion',
            'icon' => 'ri-route-line',
            'badge_color' => 'info',
        ],
        self::BREATHALISER => [
            'label' => 'Breathaliser Test',
            'model' => BreathaliserTest::class,
            'edit_route' => 'breathaliser_test.edit',
            'show_route' => 'breathaliser_test.show',
            'notify_type' => 'breathaliser_test',
            'icon' => 'ri-test-tube-line',
            'badge_color' => 'danger',
        ],
        self::DRIVER_MISCONDUCT => [
            'label' => 'Driver Misconduct',
            'model' => DriverMisconduct::class,
            'edit_route' => 'driver_misconduct.edit',
            'show_route' => 'driver_misconduct.show',
            'notify_type' => 'driver_misconduct',
            'icon' => 'ri-user-unfollow-line',
            'badge_color' => 'danger',
        ],
    ];

    public function resolveQuantities(?Waybill $waybill, mixed $observedQuantity, mixed $declaredOverride = null): array
    {
        $declared = $declaredOverride !== null && $declaredOverride !== ''
            ? $this->toNullableFloat($declaredOverride)
            : ($waybill !== null && $waybill->quantity !== null && $waybill->quantity !== ''
                ? (float) $waybill->quantity
                : null);

        $observed = $this->toNullableFloat($observedQuantity);
        $difference = ($declared !== null && $observed !== null)
            ? abs($declared - $observed)
            : null;

        return [
            'declared_quantity' => $declared,
            'observed_quantity' => $observed,
            'difference_quantity' => $difference,
        ];
    }

    /**
     * Central rule set used by both the API and inspection completion checks.
     * Evaluates inspection findings and returns the list of required incident modules.
     *
     * @param  array<string, mixed>  $findings
     * @return list<string>
     */
    public function getRequiredIncidents(array $findings): array
    {
        $required = [];

        $declared = $this->toNullableFloat($findings['declared_quantity'] ?? null);
        $observed = $this->toNullableFloat($findings['observed_quantity'] ?? null);

        $hasExcess = ($declared !== null && $observed !== null && $observed > $declared);
        $hasShortage = ($declared !== null && $observed !== null && $observed < $declared);

        if (!empty($findings['cargos']) && is_array($findings['cargos'])) {
            foreach ($findings['cargos'] as $c) {
                $cDeclared = $this->toNullableFloat($c['declared_quantity'] ?? null);
                $cObserved = $this->toNullableFloat($c['observed_quantity'] ?? null);
                if ($cDeclared !== null && $cObserved !== null) {
                    if ($cObserved > $cDeclared) {
                        $hasExcess = true;
                    } elseif ($cObserved < $cDeclared) {
                        $hasShortage = true;
                    }
                }
                if ($this->isYes($c['illegal_haulage'] ?? null)) {
                    $hasExcess = true;
                }
                if ($this->isYes($c['illegal_shortage'] ?? null) || $this->isNo($c['goods_match_waybill'] ?? null)) {
                    $hasShortage = true;
                }
            }
        }

        // 1. Illegal Haulage: excess observed quantity, illegal haulage = Yes, or unauthorised items = Yes
        if ($hasExcess
            || $this->isYes($findings['illegal_haulage'] ?? null)
            || $this->isYes($findings['unauthorised_items'] ?? null)
        ) {
            $required[] = self::ILLEGAL_HAULAGE;
        }

        // 2. Illegal Shortage: shortage observed quantity, or (illegal shortage = Yes / goods match waybill = No when no excess)
        if ($hasShortage
            || (!$hasExcess && ($this->isYes($findings['illegal_shortage'] ?? null) || $this->isNo($findings['goods_match_waybill'] ?? null)))
        ) {
            $required[] = self::ILLEGAL_SHORTAGE;
        }

        // 3. Route Diversion: route diversion = Yes
        if ($this->isYes($findings['route_diversion'] ?? null)) {
            $required[] = self::ROUTE_DIVERSION;
        }

        // 4. Breathaliser: breathaliser test = Failed / Positive / Unsafe
        if ($this->isFailedBreathaliser($findings['breathaliser_test'] ?? null)) {
            $required[] = self::BREATHALISER;
        }

        // 5. Driver Misconduct: driver misconduct = Yes, doors/tarpaulin secure = No, or cargo secured = No
        if ($this->isYes($findings['driver_misconduct'] ?? null)
            || $this->isNo($findings['doors_tarpaulin_secure'] ?? null)
            || $this->isNo($findings['cargo_secured'] ?? null)
        ) {
            $required[] = self::DRIVER_MISCONDUCT;
        }

        return array_values(array_unique($required));
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array<string, mixed>
     */
    public function evaluate(GoodsInspection $inspection, ?array $overrides = null): array
    {
        $waybillId = $overrides['waybill_id'] ?? $inspection->waybill_id;
        $waybill = $waybillId ? Waybill::find($waybillId) : $inspection->waybill;

        $quantities = $this->resolveQuantities(
            $waybill,
            $overrides['observed_quantity'] ?? $inspection->observed_quantity,
            $overrides['declared_quantity'] ?? $inspection->declared_quantity
        );

        $cargos = $overrides['cargos'] ?? ($inspection->relationLoaded('cargos') ? $inspection->cargos->toArray() : ($inspection->cargos()->exists() ? $inspection->cargos->toArray() : null));

        $findings = [
            'declared_quantity' => $quantities['declared_quantity'],
            'observed_quantity' => $quantities['observed_quantity'],
            'illegal_haulage' => $overrides['illegal_haulage'] ?? $inspection->illegal_haulage,
            'illegal_shortage' => $overrides['illegal_shortage'] ?? $inspection->illegal_shortage,
            'route_diversion' => $overrides['route_diversion'] ?? $inspection->route_diversion,
            'breathaliser_test' => $overrides['breathaliser_test'] ?? $inspection->breathaliser_test,
            'driver_misconduct' => $overrides['driver_misconduct'] ?? $inspection->driver_misconduct,
            'unauthorised_items' => $overrides['unauthorised_items'] ?? $inspection->unauthorised_items,
            'cargo_secured' => $overrides['cargo_secured'] ?? $inspection->cargo_secured,
            'goods_match_waybill' => $overrides['goods_match_waybill'] ?? $inspection->goods_match_waybill,
            'doors_tarpaulin_secure' => $overrides['doors_tarpaulin_secure'] ?? $inspection->doors_tarpaulin_secure,
            'visible_damage' => $overrides['visible_damage'] ?? $inspection->visible_damage,
            'cargos' => $cargos,
        ];

        $required = $this->getRequiredIncidents($findings);
        $existing = $inspection->exists ? $this->existingIncidents($inspection) : [];

        $incidents = [];
        $missing = [];

        foreach ($required as $key) {
            $meta = self::TYPE_META[$key];
            $match = $existing[$key] ?? null;
            $item = [
                'key' => $key,
                'label' => $meta['label'],
                'icon' => $meta['icon'] ?? 'ri-alert-line',
                'badge_color' => $meta['badge_color'] ?? 'warning',
                'required' => true,
                'exists' => (bool) $match,
                'incident_id' => $match['id'] ?? null,
                'edit_url' => $match['edit_url'] ?? null,
                'show_url' => $match['show_url'] ?? null,
                'date' => $match['date'] ?? null,
                'location' => $match['location'] ?? null,
                'status_badge' => $match['status_badge'] ?? null,
            ];
            $incidents[] = $item;
            if (!$item['exists']) {
                $missing[] = $key;
            }
        }

        return [
            'declared_quantity' => $quantities['declared_quantity'],
            'observed_quantity' => $quantities['observed_quantity'],
            'difference_quantity' => $quantities['difference_quantity'],
            'required' => $required,
            'missing' => $missing,
            'can_complete' => $missing === [],
            'incidents' => $incidents,
        ];
    }


    /**
     * @return array<string, array{id: string, edit_url: string, show_url: string, date: ?string, location: ?string, status_badge: ?string}>
     */
    public function existingIncidents(GoodsInspection $inspection): array
    {
        $existing = [];

        foreach (self::TYPE_META as $key => $meta) {
            $model = $meta['model'];
            $record = $model::query()->where('inspection_id', $inspection->id)->first();
            if ($record) {
                $date = $record->haulage_date ?? $record->shortage_date ?? $record->diversion_date ?? $record->test_date ?? $record->misconduct_date ?? null;
                $location = $record->haulage_location ?? $record->shortage_location ?? $record->diversion_location ?? $record->test_location ?? $record->misconduct_location ?? null;
                
                $existing[$key] = [
                    'id' => $record->id,
                    'edit_url' => route($meta['edit_route'], $record->id),
                    'show_url' => route($meta['show_route'], $record->id),
                    'date' => $date ? (is_string($date) ? $date : $date->format('Y-m-d')) : null,
                    'location' => $location,
                    'status_badge' => method_exists($record, 'getStatusBadgeAttribute') ? $record->status_badge : null,
                ];
            }
        }

        return $existing;
    }

    /**
     * Creates or retrieves a required incident module record attached to the inspection.
     *
     * @return array{record: object, created: bool, type: string, edit_url: string, label: string, notify_type: string, show_url: string}
     */
    public function createIncident(GoodsInspection $inspection, string $type): array
    {
        if (!isset(self::TYPE_META[$type])) {
            throw new InvalidArgumentException('Unknown incident type.');
        }

        $evaluation = $this->evaluate($inspection);
        if (!in_array($type, $evaluation['required'], true)) {
            throw new InvalidArgumentException(self::TYPE_META[$type]['label'] . ' is not required for this inspection.');
        }

        return DB::transaction(function () use ($inspection, $type) {
            $existing = $this->existingIncidents($inspection);
            if (isset($existing[$type])) {
                $meta = self::TYPE_META[$type];
                $record = $this->loadExistingRecord($type, $existing[$type]['id']);

                return [
                    'record' => $record,
                    'created' => false,
                    'type' => $type,
                    'label' => $meta['label'],
                    'edit_url' => $existing[$type]['edit_url'],
                    'notify_type' => $meta['notify_type'],
                    'show_url' => $existing[$type]['show_url'],
                ];
            }

            $meta = self::TYPE_META[$type];
            $record = $this->createModuleIncident($inspection, $type);
            $editUrl = route($meta['edit_route'], $record->id);
            $showUrl = route($meta['show_route'], $record->id);

            return [
                'record' => $record,
                'created' => true,
                'type' => $type,
                'label' => $meta['label'],
                'edit_url' => $editUrl,
                'notify_type' => $meta['notify_type'],
                'show_url' => $showUrl,
            ];
        });
    }

    public function applyWaybillDeclaredQuantity(array $payload): array
    {
        if (empty($payload['waybill_id'])) {
            return $payload;
        }

        $waybill = Waybill::find($payload['waybill_id']);
        if ($waybill) {
            $payload['declared_quantity'] = $waybill->quantity;
        }

        return $payload;
    }

    private function loadExistingRecord(string $type, string $id)
    {
        $meta = self::TYPE_META[$type];
        return $meta['model']::findOrFail($id);
    }

    private function createModuleIncident(GoodsInspection $inspection, string $type)
    {
        $waybill = $inspection->waybill_id ? Waybill::find($inspection->waybill_id) : null;
        $driverId = $this->resolveDriverId($inspection, $waybill);
        $createdBy = $inspection->created_by ?? Auth::id();
        $date = $inspection->inspection_date?->format('Y-m-d') ?? now()->toDateString();
        $location = $inspection->inspection_location ?: 'Inspection Checkpoint';
        $lat = $inspection->inspection_location_lat;
        $lng = $inspection->inspection_location_lng;
        $quantities = $this->resolveQuantities($waybill, $inspection->observed_quantity, $inspection->declared_quantity);
        $remarks = $inspection->remarks ?: ('Flagged in Goods Inspection #' . ($inspection->waybill_number ?? $inspection->id));

        $requiresDriver = in_array($type, [
            self::ILLEGAL_HAULAGE,
            self::ILLEGAL_SHORTAGE,
            self::BREATHALISER,
            self::DRIVER_MISCONDUCT,
        ], true);

        if ($requiresDriver && !$driverId) {
            throw new RuntimeException('A driver could not be resolved from the selected waybill or vehicle. The incident cannot be created.');
        }

        if (!$inspection->waybill_id) {
            throw new RuntimeException('A waybill is required to create this incident.');
        }

        $haulageCargos = [];
        $shortageCargos = [];
        $totalHaulageDiff = 0;
        $totalShortageDiff = 0;
        $totalDeclared = $quantities['declared_quantity'];
        $totalObserved = $quantities['observed_quantity'];

        $cargos = $inspection->relationLoaded('cargos') ? $inspection->cargos : $inspection->cargos()->get();
        if ($cargos->isNotEmpty()) {
            $totalDeclared = 0;
            $totalObserved = 0;
            foreach ($cargos as $c) {
                $dec = (float) ($c->declared_quantity ?? 0);
                $obs = (float) ($c->observed_quantity ?? 0);
                $unit = $c->quantity_unit ?? '';
                $name = $c->cargo_description ?: 'Cargo Item';
                $totalDeclared += $dec;
                $totalObserved += $obs;

                if ($obs > $dec || $this->isYes($c->illegal_haulage)) {
                    $excess = max(0, $obs - $dec);
                    $totalHaulageDiff += $excess;
                    $haulageCargos[] = "{$name} (Observed: {$obs} {$unit}, Declared: {$dec} {$unit}, Excess: +{$excess})";
                }
                if ($obs < $dec || $this->isYes($c->illegal_shortage) || $this->isNo($c->goods_match_waybill)) {
                    $short = max(0, $dec - $obs);
                    $totalShortageDiff += $short;
                    $shortageCargos[] = "{$name} (Declared: {$dec} {$unit}, Observed: {$obs} {$unit}, Shortage: -{$short})";
                }
            }
        }

        $haulageDesc = !empty($haulageCargos) ? implode('; ', $haulageCargos) : ($inspection->goods_description ?? 'Flagged during Goods Inspection');
        $shortageDesc = !empty($shortageCargos) ? implode('; ', $shortageCargos) : ($inspection->goods_description ?? 'Flagged during Goods Inspection');
        $haulageDiff = $totalHaulageDiff > 0 ? $totalHaulageDiff : $quantities['difference_quantity'];
        $shortageDiff = $totalShortageDiff > 0 ? $totalShortageDiff : ($quantities['difference_quantity'] ?? 0);

        try {
            return match ($type) {
                self::ILLEGAL_HAULAGE => IllegalHaulage::create([
                    'inspection_id' => $inspection->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $inspection->vehicle_id,
                    'waybill_id' => $inspection->waybill_id,
                    'declared_quantity' => $totalDeclared,
                    'observed_quantity' => $totalObserved,
                    'difference_quantity' => $haulageDiff,
                    'haulage_date' => $date,
                    'haulage_location' => $location,
                    'haulage_location_lat' => $lat,
                    'haulage_location_lng' => $lng,
                    'haulage_type' => 'Excess Quantity / Illegal Haulage',
                    'cargo_description' => $haulageDesc,
                    'severity' => 'moderate',
                    'action_taken' => 'Identified during Goods Inspection',
                    'remarks' => $remarks,
                    'status' => '0',
                    'created_by' => $createdBy,
                ]),
                self::ILLEGAL_SHORTAGE => IllegalShortage::create([
                    'inspection_id' => $inspection->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $inspection->vehicle_id,
                    'waybill_id' => $inspection->waybill_id,
                    'declared_quantity' => $totalDeclared,
                    'observed_quantity' => $totalObserved,
                    'shortage_date' => $date,
                    'shortage_location' => $location,
                    'shortage_location_lat' => $lat,
                    'shortage_location_lng' => $lng,
                    'shortage_type' => 'Quantity Shortage Identified During Inspection',
                    'shortage_quantity' => $shortageDiff,
                    'cargo_description' => $shortageDesc,
                    'severity' => 'moderate',
                    'action_taken' => 'Identified during Goods Inspection',
                    'remarks' => $remarks,
                    'status' => '0',
                    'created_by' => $createdBy,
                ]),

                self::ROUTE_DIVERSION => RouteDiversion::create([
                    'inspection_id' => $inspection->id,
                    'waybill_id' => $inspection->waybill_id,
                    'vehicle_id' => $inspection->vehicle_id,
                    'diversion_date' => $date,
                    'diversion_location' => $location,
                    'diversion_location_lat' => $lat,
                    'diversion_location_lng' => $lng,
                    'original_route' => $inspection->route ?? trim(($waybill?->origin ?? '') . ' → ' . ($waybill?->destination ?? '')),
                    'diverted_route' => 'Unauthorised route flagged during Goods Inspection',
                    'diversion_reason' => 'Route diversion flagged during Goods Inspection report',
                    'remarks' => $remarks,
                    'status' => '0',
                    'created_by' => $createdBy,
                ]),
                self::BREATHALISER => BreathaliserTest::create([
                    'inspection_id' => $inspection->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $inspection->vehicle_id,
                    'waybill_id' => $inspection->waybill_id,
                    'test_date' => $date,
                    'test_location' => $location,
                    'test_location_lat' => $lat,
                    'test_location_lng' => $lng,
                    'device_number' => 'Inspection Kit',
                    'test_result' => 'positive',
                    'action_taken' => 'Flagged during Goods Inspection',
                    'remarks' => $remarks,
                    'status' => '0',
                    'created_by' => $createdBy,
                ]),
                self::DRIVER_MISCONDUCT => DriverMisconduct::create([
                    'inspection_id' => $inspection->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $inspection->vehicle_id,
                    'waybill_id' => $inspection->waybill_id,
                    'misconduct_date' => $date,
                    'misconduct_location' => $location,
                    'misconduct_location_lat' => $lat,
                    'misconduct_location_lng' => $lng,
                    'misconduct_type' => 'Driver Non-compliance / Misconduct',
                    'severity' => 'moderate',
                    'action_taken' => 'Flagged during Goods Inspection',
                    'remarks' => $remarks,
                    'status' => '0',
                    'created_by' => $createdBy,
                ]),
                default => throw new InvalidArgumentException('Unknown incident type.'),
            };
        } catch (UniqueConstraintViolationException $e) {
            $meta = self::TYPE_META[$type];

            return $meta['model']::query()->where('inspection_id', $inspection->id)->firstOrFail();
        }
    }

    private function resolveDriverId(GoodsInspection $inspection, ?Waybill $waybill): ?string
    {
        if ($waybill?->driver_id) {
            return $waybill->driver_id;
        }

        if (!empty($inspection->vehicle_id)) {
            $vehicle = Vehicle::with('drivers')->find($inspection->vehicle_id);

            return $vehicle?->drivers?->first()?->id;
        }

        return null;
    }

    public function isFailedBreathaliser(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['failed', 'positive', 'unsafe'], true);
    }

    public function isYes(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['yes', '1', 'true'], true);
    }

    public function isNo(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['no', '0', 'false'], true);
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
