<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Models\DriverDetail;
use App\Models\DriverEldLog;
use App\Models\Status;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Waybill;
use App\Models\WaybillDriverPlan;
use App\Notifications\DriverOvertimeNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function authorizeDriverAction(DriverDetail $driver): void
    {
        $user = auth()->user();
        if ($user->driverDetail?->id !== $driver->id
            && !$user->hasAnyRole([Constants::SUPERADMIN, Constants::ADMIN])) {
            abort(403, 'You are not authorized to update this driver duty timer.');
        }
    }

    /**
     * Single-screen PWA Driver ELD & Navigation Portal
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $driver = null;

        if ($user->driverDetail) {
            $driver = $user->driverDetail;
        } elseif ($request->filled('driver_id') && $user->hasAnyRole([Constants::SUPERADMIN, Constants::ADMIN, Constants::CHAMPS_MANAGEMENT, Constants::CLIENT_DIRECTORS])) {
            $driver = DriverDetail::find($request->input('driver_id'));
        }

        if (!$driver) {
            // If admin or preview without driver profile, pick the first active driver
            if ($user->hasAnyRole([Constants::SUPERADMIN, Constants::ADMIN, Constants::CHAMPS_MANAGEMENT])) {
                $driver = DriverDetail::where('status', 1)->first() ?? DriverDetail::first();
            }
        }

        if (!$driver) {
            return view('driver.no-profile');
        }

        // Find active waybill assigned to this driver
        $waybill = Waybill::with(['vehicle', 'driverPlans.driver', 'status', 'route_trip'])
            ->whereHas('status', function ($q) {
                $q->whereIn('value', Waybill::ACTIVE_STATUS_VALUES);
            })
            ->where(function ($q) use ($driver) {
                $q->where('driver_id', $driver->id)
                  ->orWhereHas('driverPlans', function ($planQ) use ($driver) {
                      $planQ->where('driver_id', $driver->id);
                  });
            })
            ->orderByRaw("CASE WHEN status_id IN (SELECT id FROM statuses WHERE value = 2) THEN 0 ELSE 1 END")
            ->latest('updated_at')
            ->first();

        // If no active waybill found, check for latest completed or awaiting waybill
        if (!$waybill) {
            $waybill = Waybill::with(['vehicle', 'driverPlans.driver', 'status', 'route_trip'])
                ->where(function ($q) use ($driver) {
                    $q->where('driver_id', $driver->id)
                      ->orWhereHas('driverPlans', function ($planQ) use ($driver) {
                          $planQ->where('driver_id', $driver->id);
                      });
                })
                ->latest('updated_at')
                ->first();
        }

        // Multi-driver plan resolution
        $currentPlan = null;
        $plannedSeconds = 14400; // 4 hours default
        $actualDrivenSeconds = 0;
        $planStatus = 'pending';
        $driverSequence = 1;
        $totalDrivers = 1;
        $allDriverPlans = collect();

        if ($waybill) {
            $allDriverPlans = $waybill->driverPlans()->with('driver')->orderBy('sequence')->get();
            $totalDrivers = $allDriverPlans->count() ?: 1;
            $currentPlan = $allDriverPlans->firstWhere('driver_id', $driver->id);

            if ($currentPlan) {
                $driverSequence = $currentPlan->sequence;
                $planStatus = $currentPlan->status;
                $actualDrivenSeconds = (int) $currentPlan->actual_driven_seconds;

                if ($currentPlan->planned_seconds !== null && $currentPlan->planned_seconds > 0) {
                    $plannedSeconds = (int) $currentPlan->planned_seconds;
                } elseif ($waybill->estimated_duration) {
                    $parsed = $this->parseDurationToSeconds($waybill->estimated_duration);
                    if ($parsed > 0) {
                        // If multi-driver, distribute or allocate based on sequence (4h for 1st, 2h for 2nd)
                        if ($totalDrivers > 1) {
                            $plannedSeconds = $driverSequence === 1 ? min($parsed, 14400) : 7200;
                        } else {
                            $plannedSeconds = $parsed;
                        }
                    } else {
                        $plannedSeconds = $driverSequence === 1 ? 14400 : 7200;
                    }
                } else {
                    $plannedSeconds = $driverSequence === 1 ? 14400 : 7200;
                }
            } elseif ($waybill->estimated_duration) {
                $parsed = $this->parseDurationToSeconds($waybill->estimated_duration);
                $plannedSeconds = $parsed > 0 ? $parsed : 14400;
            }
        }

        if ($plannedSeconds <= 0) {
            $plannedSeconds = 14400; // Fallback to 4 hours default shift
        }

        // Resolve completion before applying login state recovery below.
        $statusValue = $waybill?->status?->value !== null ? (int) $waybill->status->value : null;
        $isDelivered = $statusValue === 3;
        $isWaybillCompleted = in_array($statusValue, [3, 4, 6], true) || $planStatus === 'completed';

        // Recover state for this driver's current waybill. A driver may have
        // earlier events on another waybill; those must not block auto-start on
        // a newly assigned trip or leak the previous trip's elapsed time.
        $latestDutyEvent = DriverEldLog::where('driver_id', $driver->id)
            ->when($waybill, fn ($q) => $q->where('waybill_id', $waybill->id))
            ->whereIn('event_type', ['start_duty', 'resume_duty', 'pause_duty', 'end_duty'])
            ->latest('created_at')->first();
        if ($latestDutyEvent) {
            if (in_array($latestDutyEvent->event_type, ['start_duty', 'resume_duty'], true)) {
                $actualDrivenSeconds = (int) $latestDutyEvent->elapsed_seconds + now()->diffInSeconds($latestDutyEvent->created_at, true);
                $planStatus = 'active';
            } elseif ($latestDutyEvent->event_type === 'pause_duty') {
                $actualDrivenSeconds = (int) $latestDutyEvent->elapsed_seconds;
                $planStatus = 'paused';
            } else {
                $actualDrivenSeconds = (int) $latestDutyEvent->elapsed_seconds;
                $planStatus = 'completed';
            }
        }

        // Start automatically the first time this assigned driver opens the
        // portal for this waybill. Prior trip history is intentionally ignored.
        if (!$latestDutyEvent && $user->driverDetail?->id === $driver->id && !$isWaybillCompleted) {
            $this->recordDutyStart($driver, $waybill, $currentPlan);
            $planStatus = 'active';
        }

        $fullName = trim($driver->full_name ?? '');
        $nameParts = preg_split('/\s+/', $fullName);
        if (count($nameParts) >= 2 && !empty($nameParts[1])) {
            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        } else {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $fullName);
            $initials = strtoupper(substr($cleanName ?: 'DR', 0, 2));
        }

        $vehicle = $waybill?->vehicle ?? $driver->vehicles->first();

        // Check if waybill or plan is completed/delivered
        // Calculate initial remaining seconds
        $remainingSeconds = max(0, $plannedSeconds - $actualDrivenSeconds);
        $isOvertime = $actualDrivenSeconds > $plannedSeconds;
        $overtimeSeconds = $isOvertime ? ($actualDrivenSeconds - $plannedSeconds) : 0;

        // Fetch recent ELD logs for this driver
        $eldLogs = DriverEldLog::where('driver_id', $driver->id)
            ->when($waybill, fn ($q) => $q->where('waybill_id', $waybill->id))
            ->latest()
            ->take(30)
            ->get();

        $wialonHost = rtrim(config('services.wialon.host', 'https://hst-api.wialon.eu'), '/');
        $wialonToken = config('services.wialon.token', '');

        // Pre-parse checkpoints and route geometry
        $checkpoints = is_array($waybill?->checkpoints) ? $waybill->checkpoints : (json_decode($waybill?->checkpoints ?? '[]', true) ?: []);
        $routeGeometry = is_array($waybill?->route_geometry) ? $waybill->route_geometry : (json_decode($waybill?->route_geometry ?? '[]', true) ?: []);

        return view('driver.portal', compact(
            'driver',
            'waybill',
            'vehicle',
            'currentPlan',
            'plannedSeconds',
            'actualDrivenSeconds',
            'remainingSeconds',
            'planStatus',
            'driverSequence',
            'totalDrivers',
            'allDriverPlans',
            'initials',
            'isOvertime',
            'overtimeSeconds',
            'isDelivered',
            'isWaybillCompleted',
            'eldLogs',
            'wialonHost',
            'wialonToken',
            'checkpoints',
            'routeGeometry'
        ));
    }

    /**
     * Start duty and countdown timer
     */
    public function startDuty(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'  => ['required'],
            'waybill_id' => ['nullable'],
            'latitude'   => ['nullable', 'numeric'],
            'longitude'  => ['nullable', 'numeric'],
            'speed_kmh'  => ['nullable', 'numeric'],
        ]);

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $this->authorizeDriverAction($driver);
        $waybillId = $request->input('waybill_id');
        $vehicleId = null;

        if ($waybillId) {
            $waybill = Waybill::find($waybillId);
            $vehicleId = $waybill?->vehicle_id;

            // Update waybill driver plan to active
            $plan = WaybillDriverPlan::where('waybill_id', $waybillId)
                ->where('driver_id', $driver->id)
                ->first();

            if ($plan) {
                $plan->update([
                    'status'     => 'active',
                    'started_at' => $plan->started_at ?? now(),
                ]);
            }

            // If waybill status is pending/awaiting (0 or 1), mark as In Transit (2)
            if ($waybill && in_array((int)$waybill->status?->value, [0, 1])) {
                $inTransitStatus = Status::where('value', 2)->first();
                if ($inTransitStatus) {
                    $waybill->update(['status_id' => $inTransitStatus->id]);
                }
            }
        }

        // Log ELD start duty event
        $log = DriverEldLog::create([
            'driver_id'        => $driver->id,
            'waybill_id'       => $waybillId,
            'vehicle_id'       => $vehicleId,
            'event_type'       => 'start_duty',
            'status'           => 'driving',
            'latitude'         => $request->input('latitude'),
            'longitude'        => $request->input('longitude'),
            'speed_kmh'        => $request->input('speed_kmh', 0),
            'elapsed_seconds'  => 0,
            'notes'            => 'Driver started shift countdown timer via ELD Portal.',
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Duty timer started successfully.',
            'started_at' => now()->toIso8601String(),
            'status'     => 'active',
            'log'        => [
                'event_label' => $log->event_label,
                'created_at'  => $log->created_at->format('H:i:s'),
                'badge'       => $log->event_badge_class,
            ]
        ]);
    }

    private function recordDutyStart(DriverDetail $driver, ?Waybill $waybill, ?WaybillDriverPlan $plan): void
    {
        if ($plan) {
            $plan->update(['status' => 'active', 'started_at' => $plan->started_at ?? now()]);
        }
        DriverEldLog::create([
            'driver_id' => $driver->id,
            'waybill_id' => $waybill?->id,
            'vehicle_id' => $waybill?->vehicle_id,
            'event_type' => 'start_duty',
            'status' => 'driving',
            'elapsed_seconds' => 0,
            'notes' => 'Duty started automatically when driver logged into the ELD portal.',
        ]);
    }

    /**
     * Pause duty / Take a rest break
     */
    public function pauseDuty(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'        => ['required'],
            'waybill_id'       => ['nullable'],
            'elapsed_seconds'  => ['nullable', 'integer'],
            'latitude'         => ['nullable', 'numeric'],
            'longitude'        => ['nullable', 'numeric'],
        ]);

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $this->authorizeDriverAction($driver);
        $waybillId = $request->input('waybill_id');
        $elapsed = (int) $request->input('elapsed_seconds', 0);

        if ($waybillId) {
            $plan = WaybillDriverPlan::where('waybill_id', $waybillId)
                ->where('driver_id', $driver->id)
                ->first();

            if ($plan) {
                $plan->update(['actual_driven_seconds' => $elapsed]);
            }
        }

        $log = DriverEldLog::create([
            'driver_id'        => $driver->id,
            'waybill_id'       => $waybillId,
            'event_type'       => 'pause_duty',
            'status'           => 'on_duty',
            'latitude'         => $request->input('latitude'),
            'longitude'        => $request->input('longitude'),
            'elapsed_seconds'  => $elapsed,
            'notes'            => 'Driver paused timer for rest break.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Duty paused.',
            'status'  => 'paused',
        ]);
    }

    /**
     * Resume driving duty
     */
    public function resumeDuty(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'   => ['required'],
            'waybill_id'  => ['nullable'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
        ]);

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $this->authorizeDriverAction($driver);
        $waybillId = $request->input('waybill_id');
        $elapsed = 0;

        if ($waybillId) {
            $plan = WaybillDriverPlan::where('waybill_id', $waybillId)
                ->where('driver_id', $driver->id)
                ->first();

            if ($plan) {
                $plan->update(['status' => 'active']);
                $elapsed = (int) $plan->actual_driven_seconds;
            }
        }

        $log = DriverEldLog::create([
            'driver_id'   => $driver->id,
            'waybill_id'  => $waybillId,
            'event_type'  => 'resume_duty',
            'status'      => 'driving',
            'elapsed_seconds' => $elapsed,
            'latitude'    => $request->input('latitude'),
            'longitude'   => $request->input('longitude'),
            'notes'       => 'Driver resumed duty timer.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Duty resumed.',
            'status'  => 'active',
        ]);
    }

    /**
     * End duty / Hand over to relief driver
     */
    public function endDuty(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'        => ['required'],
            'waybill_id'       => ['nullable'],
            'elapsed_seconds'  => ['nullable', 'integer'],
            'latitude'         => ['nullable', 'numeric'],
            'longitude'        => ['nullable', 'numeric'],
        ]);

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $this->authorizeDriverAction($driver);
        $waybillId = $request->input('waybill_id');
        $elapsed = (int) $request->input('elapsed_seconds', 0);
        $nextDriver = null;

        if ($waybillId) {
            $plan = WaybillDriverPlan::where('waybill_id', $waybillId)
                ->where('driver_id', $driver->id)
                ->first();

            if ($plan) {
                $plan->update([
                    'status'                => 'completed',
                    'actual_driven_seconds' => $elapsed > 0 ? $elapsed : $plan->actual_driven_seconds,
                    'ended_at'              => now(),
                ]);

                // Find next driver in plan sequence
                $nextPlan = WaybillDriverPlan::with('driver')
                    ->where('waybill_id', $waybillId)
                    ->where('sequence', '>', $plan->sequence)
                    ->orderBy('sequence')
                    ->first();

                if ($nextPlan) {
                    $nextDriver = [
                        'name'             => $nextPlan->driver?->full_name,
                        'sequence'         => $nextPlan->sequence,
                        'planned_hours'    => $nextPlan->planned_seconds ? round($nextPlan->planned_seconds / 3600, 1) : null,
                    ];
                }
            }
        }

        $log = DriverEldLog::create([
            'driver_id'        => $driver->id,
            'waybill_id'       => $waybillId,
            'event_type'       => 'end_duty',
            'status'           => 'off_duty',
            'latitude'         => $request->input('latitude'),
            'longitude'        => $request->input('longitude'),
            'elapsed_seconds'  => $elapsed,
            'notes'            => 'Driver ended duty shift.',
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Duty completed successfully.',
            'status'      => 'completed',
            'next_driver' => $nextDriver,
        ]);
    }

    /**
     * Periodic Timer Sync & Overtime Limit Exceeded Evaluation
     */
    public function syncTimer(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'        => ['required'],
            'waybill_id'       => ['nullable'],
            'elapsed_seconds'  => ['required', 'integer'],
            'planned_seconds'  => ['required', 'integer'],
            'latitude'         => ['nullable', 'numeric'],
            'longitude'        => ['nullable', 'numeric'],
            'speed_kmh'        => ['nullable', 'numeric'],
        ]);

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $this->authorizeDriverAction($driver);
        $waybillId = $request->input('waybill_id');
        $elapsed = (int) $request->input('elapsed_seconds');
        $planned = (int) $request->input('planned_seconds');

        // Persist actual driven seconds on the waybill driver plan
        if ($waybillId) {
            WaybillDriverPlan::where('waybill_id', $waybillId)
                ->where('driver_id', $driver->id)
                ->update(['actual_driven_seconds' => $elapsed]);
        }

        $isOvertime = $planned > 0 && $elapsed > $planned;
        $overtimeNotificationSent = false;

        if ($isOvertime) {
            // Check if violation has already been notified recently (past 30 mins)
            $recentViolation = DriverEldLog::where('driver_id', $driver->id)
                ->where('waybill_id', $waybillId)
                ->where('event_type', 'overtime_violation')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();

            if (!$recentViolation) {
                // Log overtime violation
                DriverEldLog::create([
                    'driver_id'        => $driver->id,
                    'waybill_id'       => $waybillId,
                    'event_type'       => 'overtime_violation',
                    'status'           => 'violation',
                    'latitude'         => $request->input('latitude'),
                    'longitude'        => $request->input('longitude'),
                    'speed_kmh'        => $request->input('speed_kmh', 0),
                    'elapsed_seconds'  => $elapsed,
                    'planned_seconds'  => $planned,
                    'notes'            => 'Driver exceeded scheduled driving limit of ' . DriverEldLog::formatDuration($planned) . '.',
                ]);

                // Dispatch notification to Admin, Client Director, Driver
                $waybill = Waybill::with('vehicle')->find($waybillId);
                $waybillNo = $waybill?->waybill_number ?? 'N/A';
                $vehicleFleet = $waybill?->vehicle?->fleet_no ?? $waybill?->vehicle?->truck_no ?? 'Fleet Vehicle';
                $targetUrl = route('waybill.show', $waybillId ?: 1);

                $recipients = User::role([
                    Constants::SUPERADMIN,
                    Constants::ADMIN,
                    Constants::CLIENT_DIRECTORS,
                ])->get();

                if ($driver->user) {
                    $recipients->push($driver->user);
                }

                $notification = new DriverOvertimeNotification(
                    driverName: $driver->full_name,
                    waybillNumber: $waybillNo,
                    vehicleFleet: $vehicleFleet,
                    allocatedSeconds: $planned,
                    actualDrivenSeconds: $elapsed,
                    targetUrl: $targetUrl
                );

                foreach ($recipients->unique('id') as $recipient) {
                    try {
                        $recipient->notify($notification);
                    } catch (\Throwable $th) {
                        Log::warning('Overtime notification error for user ' . $recipient->id . ': ' . $th->getMessage());
                    }
                }

                $overtimeNotificationSent = true;
            }
        }

        return response()->json([
            'success'                   => true,
            'is_overtime'               => $isOvertime,
            'elapsed_seconds'           => $elapsed,
            'remaining_seconds'         => max(0, $planned - $elapsed),
            'overtime_seconds'          => $isOvertime ? ($elapsed - $planned) : 0,
            'overtime_notification_sent'=> $overtimeNotificationSent,
        ]);
    }

    /**
     * Vehicle movement detected while timer was NOT active
     */
    public function movementAlert(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'   => ['required'],
            'waybill_id'  => ['nullable'],
            'speed_kmh'   => ['nullable', 'numeric'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
        ]);

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $this->authorizeDriverAction($driver);
        $waybillId = $request->input('waybill_id');
        $speed = (float) $request->input('speed_kmh', 0);

        // Throttle movement alerts: at most 1 log per 3 minutes
        $recent = DriverEldLog::where('driver_id', $driver->id)
            ->where('event_type', 'movement_detected')
            ->where('created_at', '>=', now()->subMinutes(3))
            ->exists();

        if (!$recent) {
            DriverEldLog::create([
                'driver_id'   => $driver->id,
                'waybill_id'  => $waybillId,
                'event_type'  => 'movement_detected',
                'status'      => 'info',
                'speed_kmh'   => $speed,
                'latitude'    => $request->input('latitude'),
                'longitude'   => $request->input('longitude'),
                'notes'       => "Vehicle in motion ({$speed} km/h) without active duty timer.",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Movement event recorded.',
        ]);
    }

    /**
     * Add manual remark / annotation to ELD log
     */
    public function addRemark(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id'   => ['required'],
            'waybill_id'  => ['nullable'],
            'remark'      => ['nullable', 'string', 'max:500'],
            'notes'       => ['nullable', 'string', 'max:500'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
        ]);

        $notes = trim((string) ($request->input('remark') ?: $request->input('notes')));
        if (empty($notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Remark text cannot be empty.',
            ], 422);
        }

        $driver = DriverDetail::findOrFail($request->input('driver_id'));
        $waybillId = $request->input('waybill_id');

        $log = DriverEldLog::create([
            'driver_id'   => $driver->id,
            'waybill_id'  => $waybillId,
            'event_type'  => 'manual_remark',
            'status'      => 'info',
            'latitude'    => $request->input('latitude'),
            'longitude'   => $request->input('longitude'),
            'notes'       => $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Remark added successfully.',
            'log'     => [
                'id'          => $log->id,
                'event_type'  => $log->event_type,
                'event_label' => $log->event_label,
                'label'       => $log->event_label,
                'badge'       => $log->event_badge_class,
                'badge_class' => $log->event_badge_class,
                'notes'       => $log->notes,
                'time'        => $log->created_at->format('H:i:s'),
                'created_at'  => $log->created_at->format('H:i:s'),
                'date'        => $log->created_at->format('d M Y'),
            ]
        ]);
    }

    /**
     * Fetch real-time ELD logs for modal/drawer
     */
    public function getLogs(Request $request): JsonResponse
    {
        $driverId = $request->input('driver_id');
        $waybillId = $request->input('waybill_id');

        $logs = DriverEldLog::where('driver_id', $driverId)
            ->when($waybillId, fn ($q) => $q->where('waybill_id', $waybillId))
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($log) => [
                'id'          => $log->id,
                'event_type'  => $log->event_type,
                'event_label' => $log->event_label,
                'label'       => $log->event_label,
                'badge'       => $log->event_badge_class,
                'badge_class' => $log->event_badge_class,
                'status'      => $log->status,
                'latitude'    => $log->latitude,
                'longitude'   => $log->longitude,
                'speed_kmh'   => $log->speed_kmh,
                'elapsed'     => $log->elapsed_seconds ? gmdate('H:i:s', $log->elapsed_seconds) : '—',
                'notes'       => $log->notes,
                'time'        => $log->created_at->format('H:i:s'),
                'created_at'  => $log->created_at->format('H:i:s'),
                'date'        => $log->created_at->format('d M Y'),
            ]);

        return response()->json([
            'success' => true,
            'logs'    => $logs,
        ]);
    }

    /**
     * Live telematics endpoint for fallback polling
     */
    public function liveTelematics(Request $request): JsonResponse
    {
        $vehicleId = $request->input('vehicle_id');
        if (!$vehicleId) {
            return response()->json(['success' => false, 'message' => 'No vehicle id provided']);
        }

        $vehicle = Vehicle::with('vehicleMakeModel')->find($vehicleId);
        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Vehicle not found'], 404);
        }

        $loc = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('vehicle_current_locations')) {
            try {
                $loc = DB::table('vehicle_current_locations')
                    ->where('vehicle_id', $vehicleId)
                    ->latest('last_received_at')
                    ->first();
            } catch (\Throwable $e) {
                Log::warning('Driver live telematics location lookup failed', [
                    'vehicle_id' => $vehicleId,
                    'error' => $e->getMessage(),
                ]);
                $loc = null;
            }
        }

        if ($loc) {
            return response()->json([
                'success'   => true,
                'fleet_no'  => $vehicle?->fleet_no ?? $vehicle?->truck_no,
                'plate_no'  => $vehicle?->truck_no ?? $vehicle?->plate_number,
                'model'     => $vehicle?->vehicleMakeModel?->name,
                'latitude'  => (float) $loc->latitude,
                'longitude' => (float) $loc->longitude,
                'speed_kmh' => (float) $loc->speed_kmh,
                'heading'   => (int) ($loc->heading ?? 0),
                'ignition'  => (bool) ($loc->ignition ?? false),
                'engine'    => $loc->engine_status ?: ((bool) ($loc->ignition ?? false) ? 'Running' : 'Off'),
                'gps_status'=> 'Active',
                'last_received_at' => $loc->last_received_at ?? null,
                'time'      => Carbon::parse($loc->gps_time ?? now())->toIso8601String(),
            ]);
        }

        // Return vehicle default details
        return response()->json([
            'success'   => true,
            'fleet_no'  => $vehicle?->fleet_no ?? $vehicle?->truck_no,
            'plate_no'  => $vehicle?->truck_no ?? $vehicle?->plate_number,
            'model'     => $vehicle?->vehicleMakeModel?->name,
            'gps_status'=> $vehicle?->gps_status ?? 'Active',
            'latitude'  => null,
            'longitude' => null,
            'speed_kmh' => 0,
            'heading'   => 0,
            'ignition'  => false,
            'engine'    => 'Off',
        ]);
    }

    /**
     * Real-time Portal Sync State (Waybill status, Driver plan, Vehicle & Duration)
     */
    public function syncState(Request $request): JsonResponse
    {
        $driverId = $request->input('driver_id');
        $waybillId = $request->input('waybill_id');

        $driver = DriverDetail::find($driverId);
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $waybill = null;
        if ($waybillId) {
            $waybill = Waybill::with(['status', 'vehicle'])->find($waybillId);
        }

        if (!$waybill) {
            $waybill = $driver->driverPlans()
                ->whereHas('waybill', function ($q) {
                    $q->whereHas('status', fn ($sq) => $sq->whereIn('value', [0, 1, 2, 5]));
                })
                ->latest()
                ->first()
                ?->waybill;
        }

        if (!$waybill) {
            $waybill = $driver->driverPlans()->latest()->first()?->waybill;
        }

        if ($waybill) {
            $waybill->loadMissing(['status', 'vehicle']);
        }

        $plan = null;
        if ($waybill) {
            $plan = WaybillDriverPlan::where('waybill_id', $waybill->id)
                ->where('driver_id', $driver->id)
                ->first();
        }

        $statusValue = $waybill?->status?->value !== null ? (int) $waybill->status->value : null;
        $isDelivered = $statusValue === 3;
        $isCompleted = in_array($statusValue, [3, 4, 6], true);

        $badgeClass = match ($statusValue) {
            0 => 'bg-warning-subtle text-warning border border-warning-subtle',
            1 => 'bg-info-subtle text-info border border-info-subtle',
            2 => 'bg-primary text-white',
            3 => 'bg-success text-white',
            4 => 'bg-danger text-white',
            5 => 'bg-secondary text-white',
            6 => 'bg-dark text-white',
            default => 'bg-secondary-subtle text-secondary',
        };

        return response()->json([
            'success'     => true,
            'server_time' => now()->format('H:i:s'),
            'waybill'     => $waybill ? [
                'id'             => $waybill->id,
                'waybill_number' => $waybill->waybill_number,
                'status_id'      => $waybill->status_id,
                'status_value'   => $statusValue,
                'status_label'   => $waybill->status_label,
                'status_badge'   => $badgeClass,
                'is_delivered'   => $isDelivered,
                'is_completed'   => $isCompleted,
                'origin'         => $waybill->origin,
                'destination'    => $waybill->destination,
            ] : null,
            'driver_plan' => $plan ? [
                'id'                    => $plan->id,
                'status'                => $plan->status,
                'planned_seconds'       => (int) $plan->planned_seconds,
                'actual_driven_seconds' => (int) $plan->actual_driven_seconds,
                'formatted_planned'     => DriverEldLog::formatDuration($plan->planned_seconds),
            ] : null,
            'vehicle' => $waybill?->vehicle ? [
                'id'       => $waybill->vehicle->id,
                'fleet_no' => $waybill->vehicle->fleet_no ?? $waybill->vehicle->truck_no,
            ] : null,
        ]);
    }

    /** Save the driver's current duty state when they log out. */
    public function logoutDuty(): JsonResponse
    {
        $driver = auth()->user()->driverDetail;
        if (!$driver) {
            return response()->json(['success' => true]);
        }

        $plan = $driver->driverPlans()->where('status', 'active')->latest()->first();
        if ($plan) {
            $latestDutyEvent = DriverEldLog::where('driver_id', $driver->id)
                ->where('waybill_id', $plan->waybill_id)
                ->whereIn('event_type', ['start_duty', 'resume_duty', 'pause_duty', 'end_duty'])
                ->latest('created_at')->first();
            if (in_array($latestDutyEvent?->event_type, ['pause_duty', 'end_duty'], true)) {
                $plan->update(['actual_driven_seconds' => (int) $latestDutyEvent->elapsed_seconds]);
                return response()->json(['success' => true]);
            }

            $elapsed = (int) $plan->actual_driven_seconds;
            $last = DriverEldLog::where('driver_id', $driver->id)->where('waybill_id', $plan->waybill_id)
                ->whereIn('event_type', ['start_duty', 'resume_duty'])->latest()->first();
            if ($last) $elapsed = (int) $last->elapsed_seconds + now()->diffInSeconds($last->created_at, true);
            // `paused` belongs to the ELD event log; the plan enum has no paused value.
            $plan->update(['actual_driven_seconds' => $elapsed]);
            DriverEldLog::create([
                'driver_id' => $driver->id, 'waybill_id' => $plan->waybill_id,
                'event_type' => 'pause_duty', 'status' => 'on_duty', 'elapsed_seconds' => $elapsed,
                'notes' => 'Duty timer saved and paused when driver logged out.',
            ]);
        }
        return response()->json(['success' => true]);
    }

    /**
     * Parse human duration string (e.g. '14 Hours 33 Mins', '4h', '180') into seconds
     */
    protected function parseDurationToSeconds($duration): int
    {
        if (empty($duration)) {
            return 0;
        }

        if (is_numeric($duration)) {
            $num = (int) $duration;
            // If greater than 1000, probably already seconds; if less, assume minutes
            return $num > 1000 ? $num : $num * 60;
        }

        $str = strtolower((string) $duration);
        $totalSeconds = 0;

        if (preg_match('/(\d+)\s*(?:hours|hour|hrs|hr|h)/i', $str, $matches)) {
            $totalSeconds += (int) $matches[1] * 3600;
        }

        if (preg_match('/(\d+)\s*(?:minutes|minute|mins|min|m)/i', $str, $matches)) {
            $totalSeconds += (int) $matches[1] * 60;
        }

        if ($totalSeconds > 0) {
            return $totalSeconds;
        }

        if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $str, $matches)) {
            $hours = (int) $matches[1];
            $mins = (int) $matches[2];
            $secs = isset($matches[3]) ? (int) $matches[3] : 0;
            return ($hours * 3600) + ($mins * 60) + $secs;
        }

        return 0;
    }

    /**
     * Driver History Page — shows all past trips, vehicles driven, and ELD activity
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $driver = $user->driverDetail;

        if (!$driver) {
            if ($user->hasAnyRole([Constants::SUPERADMIN, Constants::ADMIN, Constants::CHAMPS_MANAGEMENT])) {
                $driver = DriverDetail::where('status', 1)->first() ?? DriverDetail::first();
            }
        }

        if (!$driver) {
            return redirect()->route('driver.portal');
        }

        // All vehicles this driver has ever driven (from ELD logs + driver plans)
        $vehicleIds = DriverEldLog::where('driver_id', $driver->id)
            ->whereNotNull('vehicle_id')
            ->distinct()
            ->pluck('vehicle_id')
            ->merge(
                WaybillDriverPlan::where('driver_id', $driver->id)
                    ->whereHas('waybill', fn($q) => $q->whereNotNull('vehicle_id'))
                    ->with('waybill:id,vehicle_id')
                    ->get()
                    ->pluck('waybill.vehicle_id')
                    ->filter()
            )
            ->unique()
            ->values();

        $vehicles = Vehicle::whereIn('id', $vehicleIds)->get(['id', 'fleet_no', 'truck_no']);

        // All waybill trips for this driver
        $tripsQuery = WaybillDriverPlan::where('driver_id', $driver->id)
            ->with(['waybill.vehicle', 'waybill.status']);

        // Apply filters
        if ($request->filled('vehicle_id')) {
            $tripsQuery->whereHas('waybill', fn($q) => $q->where('vehicle_id', $request->input('vehicle_id')));
        }
        if ($request->filled('status')) {
            $tripsQuery->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $tripsQuery->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $tripsQuery->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $trips = $tripsQuery->latest('created_at')->paginate(15);

        // Summary statistics
        $totalTrips = WaybillDriverPlan::where('driver_id', $driver->id)->count();
        $completedTrips = WaybillDriverPlan::where('driver_id', $driver->id)->where('status', 'completed')->count();
        $totalDrivingSeconds = (int) WaybillDriverPlan::where('driver_id', $driver->id)->sum('actual_driven_seconds');
        $totalVehicles = $vehicleIds->count();
        $overtimeCount = DriverEldLog::where('driver_id', $driver->id)->where('event_type', 'overtime_violation')->count();

        // Driver initials for avatar
        $fullName = trim($driver->full_name ?? '');
        $nameParts = preg_split('/\s+/', $fullName);
        if (count($nameParts) >= 2 && !empty($nameParts[1])) {
            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        } else {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $fullName);
            $initials = strtoupper(substr($cleanName ?: 'DR', 0, 2));
        }

        return view('driver.history', compact(
            'driver',
            'vehicles',
            'trips',
            'totalTrips',
            'completedTrips',
            'totalDrivingSeconds',
            'totalVehicles',
            'overtimeCount',
            'initials'
        ));
    }

    /**
     * AJAX endpoint for driver history activity log
     */
    public function historyLogs(Request $request): JsonResponse
    {
        $user = auth()->user();
        $driver = $user->driverDetail;

        if (!$driver) {
            if ($user->hasAnyRole([Constants::SUPERADMIN, Constants::ADMIN, Constants::CHAMPS_MANAGEMENT])) {
                $driver = DriverDetail::where('status', 1)->first() ?? DriverDetail::first();
            }
        }

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'No driver profile'], 404);
        }

        $query = DriverEldLog::where('driver_id', $driver->id)
            ->with(['waybill:id,waybill_number', 'vehicle:id,fleet_no,truck_no']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }
        if ($request->filled('waybill_id')) {
            $query->where('waybill_id', $request->input('waybill_id'));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $logs = $query->latest()
            ->paginate($request->input('per_page', 30))
            ->through(fn ($log) => [
                'id'          => $log->id,
                'event_type'  => $log->event_type,
                'event_label' => $log->event_label,
                'badge_class' => $log->event_badge_class,
                'status'      => $log->status,
                'waybill_no'  => $log->waybill?->waybill_number,
                'vehicle'     => $log->vehicle ? ($log->vehicle->fleet_no ?? $log->vehicle->truck_no) : null,
                'latitude'    => $log->latitude,
                'longitude'   => $log->longitude,
                'speed_kmh'   => $log->speed_kmh,
                'elapsed'     => $log->elapsed_seconds ? gmdate('H:i:s', $log->elapsed_seconds) : null,
                'planned'     => $log->planned_seconds ? DriverEldLog::formatDuration($log->planned_seconds) : null,
                'notes'       => $log->notes,
                'time'        => $log->created_at->format('H:i:s'),
                'date'        => $log->created_at->format('d M Y'),
                'created_at'  => $log->created_at->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'logs'    => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }
}

