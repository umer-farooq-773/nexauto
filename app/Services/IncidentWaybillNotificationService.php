<?php

namespace App\Services;

use App\Constants\Constants;
use App\Models\Accident;
use App\Models\BreathaliserTest;
use App\Models\DriverMisconduct;
use App\Models\IllegalDetention;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\Impoundment;
use App\Models\RouteDiversion;
use App\Models\User;
use App\Models\VehicleBoggedDown;
use App\Models\GoodsInspection;
use App\Models\VehicleBreakdown;
use App\Models\Waybill;
use App\Notifications\GoodsInspectionCompletedNotification;
use App\Notifications\IncidentWaybillCreatedNotification;
use App\Services\InspectionIncidentRequirementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class IncidentWaybillNotificationService
{
    public function notifyGoodsInspectionCompleted(GoodsInspection $inspection): void
    {
        $inspection->loadMissing(['waybill.vehicle', 'waybill.driver.user', 'vehicle.drivers.user']);

        $recipients = $this->resolveInspectionRecipients($inspection);
        if ($recipients->isEmpty()) {
            return;
        }

        $requirementService = app(InspectionIncidentRequirementService::class);
        $existingIncidents = $requirementService->existingIncidents($inspection);
        $linkedIncidents = [];

        foreach ($existingIncidents as $key => $data) {
            $meta = InspectionIncidentRequirementService::TYPE_META[$key] ?? null;
            if ($meta) {
                $linkedIncidents[] = [
                    'key' => $key,
                    'label' => $meta['label'],
                    'show_url' => $data['show_url'] ?? $data['edit_url'] ?? null,
                    'edit_url' => $data['edit_url'] ?? null,
                    'incident_id' => $data['id'] ?? null,
                ];
            }
        }

        $notification = new GoodsInspectionCompletedNotification($inspection, $linkedIncidents);

        foreach ($recipients as $recipient) {
            try {
                $recipient->notifyNow($notification, ['database']);
            } catch (\Throwable $exception) {
                $this->safeLogWarning('Goods inspection database notification failed', [
                    'recipient_id' => $recipient->id ?? null,
                    'inspection_id' => $inspection->id,
                    'message' => $exception->getMessage(),
                ]);
            }

            if (empty($recipient->email)) {
                continue;
            }

            try {
                $recipient->notifyNow($notification, ['mail']);
            } catch (\Throwable $exception) {
                $this->safeLogWarning('Goods inspection mail notification failed', [
                    'recipient_id' => $recipient->id ?? null,
                    'inspection_id' => $inspection->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    protected function resolveInspectionRecipients(GoodsInspection $inspection)
    {
        $roleRecipients = User::role([
            Constants::SUPERADMIN,
            Constants::ADMIN,
            Constants::CHAMPS_MANAGEMENT,
            Constants::CLIENT_MANAGEMENT,
            Constants::CLIENT_DIRECTORS,
        ])->get();

        $driverUser = $inspection->waybill?->driver?->user 
            ?? $inspection->vehicle?->drivers?->first()?->user;

        return $roleRecipients
            ->merge(auth()->check() ? [auth()->user()] : [])
            ->merge($driverUser ? [$driverUser] : [])
            ->filter(fn ($user) => $user && !empty($user->email))
            ->unique('id')
            ->values();
    }
    public function notifyForWaybill(Waybill $waybill): void
    {
        if (empty($waybill->vehicle_id)) {
            return;
        }

        $waybill->loadMissing(['vehicle', 'driver.user']);

        $recipients = $this->resolveRecipients($waybill);
        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($this->findIncidentTargets($waybill) as $incident) {
            $this->sendIncidentNotification($recipients, $waybill, $incident);
        }
    }

public function notifyForIncident(Waybill $waybill, string $incidentType, string $incidentLabel, string $routeName, array $additionalData = []): void // $routeName can be route name or full URL
    {
        if (empty($waybill->vehicle_id)) {
            return;
        }

        $waybill->loadMissing(['vehicle', 'driver.user']);

        $recipients = $this->resolveRecipients($waybill);
        if ($recipients->isEmpty()) {
            return;
        }

        $this->sendIncidentNotification($recipients, $waybill, [
            'type' => $incidentType,
            'label' => $incidentLabel,
'url' => filter_var($routeName, FILTER_VALIDATE_URL) ? $routeName : route($routeName),
            'id' => null,
            'additional_data' => $additionalData,
        ]);
    }

    protected function sendIncidentNotification($recipients, Waybill $waybill, array $incident): void
    {
        foreach ($recipients as $recipient) {
            $notification = new IncidentWaybillCreatedNotification(
                $waybill,
                $incident['type'],
                $incident['label'],
                $incident['url'],
                $incident['id'] ?? null,
                $incident['additional_data'] ?? []
            );

            try {
                $recipient->notifyNow($notification, ['database']);
            } catch (\Throwable $exception) {
                $this->safeLogWarning('Incident waybill database notification failed', [
                    'recipient_id' => $recipient->id ?? null,
                    'waybill_id' => $waybill->id,
                    'incident_type' => $incident['type'],
                    'message' => $exception->getMessage(),
                ]);
            }

            if (empty($recipient->email)) {
                continue;
            }

            try {
                $recipient->notifyNow($notification, ['mail']);
            } catch (\Throwable $exception) {
                $this->safeLogWarning('Incident waybill mail notification failed', [
                    'recipient_id' => $recipient->id ?? null,
                    'waybill_id' => $waybill->id,
                    'incident_type' => $incident['type'],
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    protected function safeLogWarning(string $message, array $context = []): void
    {
        try {
            Log::warning($message, $context);
        } catch (\Throwable $exception) {
            // Logging must never break the notification flow.
        }
    }

    protected function resolveRecipients(Waybill $waybill)
    {
        $roleRecipients = User::role([
            Constants::SUPERADMIN,
            Constants::ADMIN,
            Constants::CHAMPS_MANAGEMENT,
            Constants::CLIENT_MANAGEMENT,
            Constants::CLIENT_DIRECTORS,
        ])->get();

        return $roleRecipients
            // ->filter(fn ($user) => $user)
            ->merge(auth()->check() ? [auth()->user()] : [])
            ->merge($waybill->driver?->user ? [$waybill->driver->user] : [])
            ->filter(fn ($user) => $user && !empty($user->email))
            ->unique('id')
            ->values();
    }

    protected function findIncidentTargets(Waybill $waybill): array
    {
        $incidentMap = [
            'impoundment' => [
                'model' => Impoundment::class,
                'label' => 'Impoundment',
                'route' => 'impoundment.index',
            ],
            'vehicle_breakdown' => [
                'model' => VehicleBreakdown::class,
                'label' => 'Vehicle Breakdown',
                'route' => 'vehicle_breakdown.index',
                'query' => fn (Builder $query) => $query->where('incident_type', 'breakdown'),
            ],
            'vehicle_bogged_down' => [
                'model' => VehicleBoggedDown::class,
                'label' => 'Vehicle Bogged Down',
                'route' => 'vehicle_bogged_down.index',
            ],
            'illegal_detention' => [
                'model' => IllegalDetention::class,
                'label' => 'Illegal Detention',
                'route' => 'illegal_detention.index',
            ],
            'illegal_haulage' => [
                'model' => IllegalHaulage::class,
                'label' => 'Illegal Haulage',
                'route' => 'illegal_haulage.index',
            ],
            'illegal_shortage' => [
                'model' => IllegalShortage::class,
                'label' => 'Illegal Shortage',
                'route' => 'illegal_shortage.index',
            ],
            'breathaliser_test' => [
                'model' => BreathaliserTest::class,
                'label' => 'Breathaliser Test',
                'route' => 'breathaliser_test.index',
            ],
            'accident' => [
                'model' => Accident::class,
                'label' => 'Accident',
                'route' => 'accident.index',
            ],
            'route_diversion' => [
                'model' => RouteDiversion::class,
                'label' => 'Route Diversion',
                'route' => 'route_diversion.index',
            ],
            'driver_misconduct' => [
                'model' => DriverMisconduct::class,
                'label' => 'Driver Misconduct',
                'route' => 'driver_misconduct.index',
            ],
        ];

        $matches = [];

        foreach ($incidentMap as $type => $config) {
            /** @var \Illuminate\Database\Eloquent\Model|null $record */
            $record = $config['model']::query()
                ->where('vehicle_id', $waybill->vehicle_id)
                ->when(
                    isset($config['query']),
                    fn (Builder $query) => $config['query']($query)
                )
                ->latest()
                ->first();

            if (!$record) {
                continue;
            }

            // Build URL with incident_id for focused view
            // The 'route' key stores the index route (e.g. 'impoundment.index').
            // Strip the trailing '.index' segment to get the base prefix, then append '.show'.
            $baseRoute  = preg_replace('/\.index$/', '', $config['route']);
            $focusedUrl = route($baseRoute . '.show', $record->id);

            $matches[] = [
                'type' => $type,
                'label' => $config['label'],
                'url' => $focusedUrl,
                'id' => $record->id,
            ];
        }

        return $matches;
    }
}
