{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<x-layouts.auth page-title="{{ __('Driver Details') }}" sub-title="">
    {{-- ===================== HEADER ACTION BAR ===================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 topbar-card">
                <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-tile blue">
                            <i class="ri-user-line"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ __('Driver Record') }}</h5>
                            <small class="text-muted">
                                {{ __('Name') }}: <span class="fw-semibold text-primary">{{ $data->full_name ?? '—' }}</span>
                                &nbsp;•&nbsp;
                                {{ __('Phone') }}: <span class="fw-semibold">{{ $data->phone_number ?? '—' }}</span>
                            </small>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('driver.portal', ['driver_id' => $data->id]) }}" target="_blank" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold shadow-sm">
                            <i class="ri-tablet-line me-1"></i> {{ __('Open Driver ELD Screen') }}
                        </a>
                        @if($data->email)
                            <a href="{{ route('driver.resend_credentials', $data->id) }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" onclick="return confirm('Send login credentials to {{ $data->email }}?');">
                                <i class="ri-key-line me-1"></i> {{ __('Resend Credentials') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 print-content">
        {{-- ===================== LEFT COLUMN ===================== --}}
        <div class="col-xl-7">

            {{-- ============ MAIN DETAILS CARD ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">

                    {{-- Section Header --}}
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-user-settings-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Driver Details') }}</h6>
                                <small class="text-muted">{{ __('Personal, License & Employment Information') }}</small>
                            </div>
                        </div>
                        <span class="required-badge">{{ __('Read Only') }}</span>
                    </div>

                    <div class="row g-3">

                        {{-- ============ PERSONAL INFORMATION ============ --}}
                        <div class="col-12">
                            <div class="subsection-title">
                                <i class="ri-user-line"></i> {{ __('Personal Information') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-line"></i> {{ __('Driver Name') }}</label>
                                <div class="info-value">{{ $data->full_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-map-pin-line"></i> {{ __('Address') }}</label>
                                <div class="info-value">{{ $data->address ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-phone-line"></i> {{ __('Phone Number') }}</label>
                                <div class="info-value">{{ $data->phone_number ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-mail-line"></i> {{ __('Email (Portal Login)') }}</label>
                                <div class="info-value text-primary fw-medium">{{ $data->email ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-line"></i> {{ __('Date of Birth') }}</label>
                                <div class="info-value">
                                    @if ($data->date_of_birth)
                                        {{ dateFormat($data->date_of_birth) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-id-card-line"></i> {{ __('National ID / Passport Number') }}</label>
                                <div class="info-value">{{ $data->national_id_passport_number ?? '-' }}</div>
                            </div>
                        </div>

                        {{-- ============ EMPLOYMENT & LICENSE ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title cyan">
                                <i class="ri-briefcase-line"></i> {{ __('Employment & License Information') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Date Employed') }}</label>
                                <div class="info-value">
                                    @if ($data->date_employed)
                                        {{ dateFormat($data->date_employed) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-id-card-line"></i> {{ __("Driver's License Number") }}</label>
                                <div class="info-value">{{ $data->drivers_license_number ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-price-tag-3-line"></i> {{ __('License Category / Class') }}</label>
                                <div class="info-value">{{ $data->license_category_class ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-close-line"></i> {{ __('License Expiry Date') }}</label>
                                <div class="info-value">
                                    @if ($data->license_expiry_date)
                                        {{ dateFormat($data->license_expiry_date) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ============ EMERGENCY CONTACT & STATUS ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title amber">
                                <i class="ri-contacts-line"></i> {{ __('Emergency Contact & Status') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-contacts-line"></i> {{ __('Emergency Contact Name') }}</label>
                                <div class="info-value">{{ $data->emergency_contact_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-phone-fill"></i> {{ __('Emergency Contact Phone') }}</label>
                                <div class="info-value">{{ $data->emergency_contact_phone_number ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-information-line"></i> {{ __('Status') }}</label>
                                <div class="info-value">{!! $data->status_badge ?? '-' !!}</div>
                            </div>
                        </div>

                        {{-- ============ ASSIGNED ASSETS ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title slate">
                                <i class="ri-truck-line"></i> {{ __('Assigned Assets') }}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-truck-line"></i> {{ __('Assigned Vehicles') }}</label>
                                <div class="info-value">
                                    @forelse($data->vehicles as $vehicle)
                                        <a href="{{ route('vehicle.show', $vehicle->id) }}"
                                            class="chip chip-blue text-decoration-none me-1">
                                            <i class="ri-truck-line me-1"></i>{{ $vehicle->fleet_no }} ({{ $vehicle->truck_no }})
                                        </a>
                                    @empty
                                        <span class="text-muted">{{ __('No vehicle assigned') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== RIGHT COLUMN ===================== --}}
        <div class="col-xl-5">

            {{-- ============ DOCUMENTS & PHOTOGRAPH ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">

                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile cyan">
                                <i class="ri-image-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Documents & Photograph') }}</h6>
                                <small class="text-muted">{{ __('Driver photo and identification cards') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center p-2 mb-3">
                        <p class="small fw-bold mb-2 text-muted text-uppercase">{{ __('Driver Photograph') }}</p>
                        <img src="{{ $data->image() }}" alt="{{ $data->first_name ?? 'Driver' }}"
                            class="img-fluid rounded-4 shadow-sm"
                            style="height: 250px; width: 100%; object-fit: cover;">
                    </div>

                    <div class="row g-3">
                        <div class="col-6 text-center">
                            <p class="small fw-bold mb-2 text-muted text-uppercase">{{ __('Company ID Card') }}</p>
                            <img src="{{ $data->companyIdCardImage() }}" alt="Company ID"
                                class="img-fluid rounded-4 shadow-sm"
                                style="height: 130px; width: 100%; object-fit: cover;">
                        </div>
                        <div class="col-6 text-center">
                            <p class="small fw-bold mb-2 text-muted text-uppercase">{{ __('License Card') }}</p>
                            <img src="{{ $data->licenseCardImage() }}" alt="License Card"
                                class="img-fluid rounded-4 shadow-sm"
                                style="height: 130px; width: 100%; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== ELD LOGS & WAYBILL ROUTE TRIP OPERATIONS ===================== --}}
    @php
        $totalDrivenSeconds = $data->driverPlans->sum('actual_driven_seconds');
        $overtimeCount = $data->eldLogs->where('event_type', 'overtime_violation')->count();
        $totalTrips = $data->driverPlans->unique('waybill_id')->count() ?: $data->waybills->count();
        $movementAlertCount = $data->eldLogs->where('event_type', 'movement_detected')->count();
    @endphp

    <div class="row g-4 mt-2 print-content">
        <div class="col-12">
            {{-- ELD KPI Metric Overview --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="icon-tile green">
                                <i class="ri-time-line"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">{{ __('Total Driving Time') }}</small>
                                <h4 class="mb-0 fw-bold text-dark font-monospace">{{ \App\Models\DriverEldLog::formatDuration($totalDrivenSeconds) }}</h4>
                                <small class="text-success fw-semibold" style="font-size: 0.72rem;">
                                    <i class="ri-shield-check-line"></i> {{ __('Electronic Logged') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="icon-tile blue">
                                <i class="ri-route-line"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">{{ __('Assigned Waybill Trips') }}</small>
                                <h4 class="mb-0 fw-bold text-dark font-monospace">{{ $totalTrips }}</h4>
                                <small class="text-primary fw-semibold" style="font-size: 0.72rem;">
                                    <i class="ri-navigation-line"></i> {{ __('Route Operations') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="icon-tile {{ $overtimeCount > 0 ? 'red' : 'green' }}">
                                <i class="ri-alarm-warning-line"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">{{ __('Overtime Violations') }}</small>
                                <h4 class="mb-0 fw-bold {{ $overtimeCount > 0 ? 'text-danger' : 'text-dark' }} font-monospace">{{ $overtimeCount }}</h4>
                                <small class="{{ $overtimeCount > 0 ? 'text-danger' : 'text-success' }} fw-semibold" style="font-size: 0.72rem;">
                                    <i class="ri-error-warning-line"></i> {{ $overtimeCount > 0 ? __('Compliance Warning') : __('Full Compliance') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 kpi-card">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="icon-tile amber">
                                <i class="ri-radar-line"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">{{ __('Movement Warnings') }}</small>
                                <h4 class="mb-0 fw-bold text-dark font-monospace">{{ $movementAlertCount }}</h4>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    <i class="ri-speed-up-line"></i> {{ __('Unlogged Motions') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- WAYBILL & ROUTE WISE ELD BREAKDOWN --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-road-map-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Waybill & Route Operations (ELD Trip Records)') }}</h6>
                                <small class="text-muted">{{ __('Multi-driver legs, scheduled hours vs actual driving hours per route') }}</small>
                            </div>
                        </div>
                        <a href="{{ route('driver.portal', ['driver_id' => $data->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                            <i class="ri-external-link-line me-1"></i> {{ __('Live Portal') }}
                        </a>
                    </div>

                    @php
                        // Collect all driver plans and unique waybills
                        $driverPlans = $data->driverPlans;
                        $waybillIds = $driverPlans->pluck('waybill_id')->unique()->filter();
                        if ($waybillIds->isEmpty()) {
                            $waybillIds = $data->waybills->pluck('id');
                        }
                        $allWaybills = \App\Models\Waybill::with(['vehicle', 'status', 'driverPlans.driver', 'eldLogs' => fn($q) => $q->where('driver_id', $data->id)->latest()])
                            ->whereIn('id', $waybillIds)
                            ->latest()
                            ->get();
                    @endphp

                    @if($allWaybills->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="ri-route-line fs-1 text-secondary opacity-50"></i>
                            <p class="mt-2 mb-0 fw-semibold">{{ __('No waybills or route plans assigned to this driver yet.') }}</p>
                            <small>{{ __('Assigned waybill trip records and driving hours will appear here automatically.') }}</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                                        <th>{{ __('Waybill / Leg') }}</th>
                                        <th>{{ __('Origin ➔ Destination') }}</th>
                                        <th>{{ __('Assigned Vehicle') }}</th>
                                        <th>{{ __('Planned Shift') }}</th>
                                        <th>{{ __('Actual Driven') }}</th>
                                        <th>{{ __('Trip Status') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allWaybills as $wb)
                                        @php
                                            $plan = $wb->driverPlans->firstWhere('driver_id', $data->id);
                                            $plannedSec = $plan?->planned_seconds ?? 0;
                                            if ($plannedSec <= 0 && $wb->estimated_duration) {
                                                // parse duration
                                                if (is_numeric($wb->estimated_duration)) {
                                                    $plannedSec = (int) $wb->estimated_duration * 60;
                                                } elseif (preg_match('/(\d+)\s*(?:hours|hour|hrs|hr|h)/i', (string)$wb->estimated_duration, $m)) {
                                                    $plannedSec = (int) $m[1] * 3600;
                                                }
                                            }
                                            $actualSec = $plan?->actual_driven_seconds ?? 0;
                                            $isPlanOvertime = $plannedSec > 0 && $actualSec > $plannedSec;
                                            $overtimeSec = $isPlanOvertime ? ($actualSec - $plannedSec) : 0;
                                            $wbLogsCount = $wb->eldLogs->count();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    <a href="{{ route('waybill.show', $wb->id) }}" class="text-decoration-none text-primary">
                                                        Waybill #{{ $wb->waybill_number }}
                                                    </a>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    @if($plan)
                                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">
                                                            Leg {{ $plan->sequence }} of {{ $wb->driverPlans->count() }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;">Primary Driver</span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="fw-semibold text-dark">{{ $wb->origin ?? 'Origin' }}</span>
                                                    <i class="ri-arrow-right-line text-primary mx-1"></i>
                                                    <span class="fw-semibold text-dark">{{ $wb->destination ?? 'Destination' }}</span>
                                                </div>
                                                @if($wb->distance)
                                                    <small class="text-muted">{{ $wb->distance }} km &nbsp;•&nbsp; Est: {{ $wb->estimated_duration ?? '—' }}</small>
                                                @endif
                                            </td>

                                            <td>
                                                @if($wb->vehicle)
                                                    <span class="chip chip-blue py-1 px-2">
                                                        <i class="ri-truck-line me-1"></i>{{ $wb->vehicle->fleet_no }} ({{ $wb->vehicle->truck_no }})
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                <span class="font-monospace fw-semibold text-secondary">
                                                    {{ $plannedSec > 0 ? \App\Models\DriverEldLog::formatDuration($plannedSec) : '4 Hours (Std)' }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="font-monospace fw-bold {{ $isPlanOvertime ? 'text-danger' : 'text-success' }}">
                                                        {{ \App\Models\DriverEldLog::formatDigitalTime($actualSec) }}
                                                    </span>
                                                    @if($isPlanOvertime)
                                                        <small class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1" style="font-size: 0.65rem; width: fit-content;">
                                                            <i class="ri-alarm-warning-line"></i> +{{ \App\Models\DriverEldLog::formatDigitalTime($overtimeSec) }} Overtime
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                <span class="badge {{ $wb->status?->value == 2 ? 'bg-primary' : ($wb->status?->value == 3 ? 'bg-success' : 'bg-secondary') }} rounded-pill px-2 py-1">
                                                    {{ $wb->status_label }}
                                                </span>
                                            </td>

                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light border rounded-pill px-2 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#wb-collapse-{{ $wb->id }}" aria-expanded="false">
                                                    <i class="ri-list-check-2 me-1"></i> Logs ({{ $wbLogsCount }})
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Collapsible nested log events for this specific waybill --}}
                                        <tr class="collapse-row">
                                            <td colspan="7" class="p-0 border-0">
                                                <div class="collapse bg-light p-3 border-bottom" id="wb-collapse-{{ $wb->id }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">
                                                            <i class="ri-history-line me-1 text-primary"></i> {{ __('Waybill #') }}{{ $wb->waybill_number }} {{ __('Route Telematics & ELD Timeline') }}
                                                        </h6>
                                                        <small class="text-muted">{{ $wbLogsCount }} {{ __('recorded entries') }}</small>
                                                    </div>

                                                    @if($wb->eldLogs->isEmpty())
                                                        <p class="text-muted small mb-0">{{ __('No telematics logs recorded for this waybill yet.') }}</p>
                                                    @else
                                                        <div class="table-responsive bg-white rounded-3 border">
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <thead class="border-bottom text-muted small">
                                                                    <tr>
                                                                        <th>{{ __('Event') }}</th>
                                                                        <th>{{ __('Timestamp') }}</th>
                                                                        <th>{{ __('GPS Fix / Speed') }}</th>
                                                                        <th>{{ __('Driven') }}</th>
                                                                        <th>{{ __('Notes / Annotation') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($wb->eldLogs->take(10) as $wl)
                                                                        <tr class="small border-bottom border-light">
                                                                            <td>
                                                                                <span class="badge {{ $wl->event_badge_class }}" style="font-size: 0.7rem;">
                                                                                    {{ $wl->event_label }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="font-monospace text-muted">{{ $wl->created_at->format('Y-m-d H:i:s') }}</td>
                                                                            <td class="font-monospace">
                                                                                @if($wl->latitude && $wl->longitude)
                                                                                    <a href="https://www.google.com/maps?q={{ $wl->latitude }},{{ $wl->longitude }}" target="_blank" class="text-decoration-none text-info">
                                                                                        <i class="ri-map-pin-line"></i> {{ number_format($wl->latitude, 4) }}, {{ number_format($wl->longitude, 4) }}
                                                                                    </a>
                                                                                    @if($wl->speed_kmh > 0)
                                                                                        <span class="badge bg-light text-dark ms-1">{{ $wl->speed_kmh }} km/h</span>
                                                                                    @endif
                                                                                @else
                                                                                    <span class="text-muted">—</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="font-monospace text-dark">{{ \App\Models\DriverEldLog::formatDigitalTime($wl->elapsed_seconds) }}</td>
                                                                            <td class="text-secondary">{{ $wl->notes ?? '—' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- COMPREHENSIVE ELD ACTIVITY AUDIT LOG --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile cyan">
                                <i class="ri-file-list-3-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Complete ELD Activity & Telematics Log') }}</h6>
                                <small class="text-muted">{{ __('Chronological duty events, start/stop times, overtime triggers, and driver remarks') }}</small>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                            {{ $data->eldLogs->count() }} {{ __('Total Log Records') }}
                        </span>
                    </div>

                    @if($data->eldLogs->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="ri-file-shield-line fs-1 text-secondary opacity-50"></i>
                            <p class="mt-2 mb-0 fw-semibold">{{ __('No ELD logs recorded yet.') }}</p>
                            <small>{{ __('When the driver starts a shift via the Driver Portal, telematics and duty records will appear here.') }}</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="eldLogsMasterTable">
                                <thead class="table-light">
                                    <tr style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                                        <th>{{ __('Event Type') }}</th>
                                        <th>{{ __('Waybill / Route') }}</th>
                                        <th>{{ __('Timestamp') }}</th>
                                        <th>{{ __('Telematics GPS & Speed') }}</th>
                                        <th>{{ __('Elapsed Driving') }}</th>
                                        <th>{{ __('Notes & Activity Remarks') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data->eldLogs as $log)
                                        <tr>
                                            <td>
                                                <span class="badge {{ $log->event_badge_class }} py-1 px-2" style="font-size: 0.75rem;">
                                                    {{ $log->event_label }}
                                                </span>
                                            </td>

                                            <td>
                                                @if($log->waybill)
                                                    <div class="fw-semibold">
                                                        <a href="{{ route('waybill.show', $log->waybill->id) }}" class="text-decoration-none text-primary">
                                                            #{{ $log->waybill->waybill_number }}
                                                        </a>
                                                    </div>
                                                    <small class="text-muted" style="font-size: 0.72rem;">
                                                        {{ Str::limit($log->waybill->origin, 14) }} ➔ {{ Str::limit($log->waybill->destination, 14) }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td class="font-monospace text-secondary" style="font-size: 0.82rem;">
                                                <div>{{ $log->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </td>

                                            <td>
                                                @if($log->latitude && $log->longitude)
                                                    <div class="d-flex align-items-center gap-1 font-monospace" style="font-size: 0.82rem;">
                                                        <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="text-decoration-none text-primary fw-medium" title="View on Map">
                                                            <i class="ri-map-pin-2-fill text-danger me-1"></i>{{ number_format($log->latitude, 4) }}, {{ number_format($log->longitude, 4) }}
                                                        </a>
                                                    </div>
                                                    @if($log->speed_kmh > 0)
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle mt-1" style="font-size: 0.68rem;">
                                                            <i class="ri-speed-up-line"></i> {{ $log->speed_kmh }} km/h
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                <span class="font-monospace fw-semibold {{ $log->event_type === 'overtime_violation' ? 'text-danger' : 'text-dark' }}">
                                                    {{ \App\Models\DriverEldLog::formatDigitalTime($log->elapsed_seconds) }}
                                                </span>
                                                @if($log->planned_seconds > 0)
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                        Limit: {{ \App\Models\DriverEldLog::formatDuration($log->planned_seconds) }}
                                                    </small>
                                                @endif
                                            </td>

                                            <td>
                                                <span class="text-dark small">{{ $log->notes ?? '—' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== STYLES ===================== --}}
    <style>
        /* ===== TOP BAR ===== */
        .topbar-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        /* ===== ICON TILES ===== */
        .icon-tile {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        .icon-tile.blue   { background: #e0e7ff; color: #4f46e5; }
        .icon-tile.amber  { background: #fef3c7; color: #d97706; }
        .icon-tile.red    { background: #fee2e2; color: #dc2626; }
        .icon-tile.cyan   { background: #cffafe; color: #0891b2; }
        .icon-tile.green  { background: #dcfce7; color: #16a34a; }
        .icon-tile.slate  { background: #e2e8f0; color: #475569; }
        .icon-tile.dark   { background: #e2e8f0; color: #334155; }

        /* ===== SECTION HEADERS ===== */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .section-header-left {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .section-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #1e293b;
            letter-spacing: -0.01em;
        }
        .required-badge {
            background-color: #e0e7ff;
            color: #4f46e5;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        /* ===== SUBSECTION TITLES ===== */
        .subsection-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #4f46e5;
            padding: 0.5rem 0;
            border-bottom: 2px solid #e0e7ff;
            margin-top: 0.5rem;
        }
        .subsection-title i { margin-right: 0.35rem; }
        .subsection-title.amber  { color: #d97706; border-bottom-color: #fef3c7; }
        .subsection-title.red    { color: #dc2626; border-bottom-color: #fee2e2; }
        .subsection-title.cyan   { color: #0891b2; border-bottom-color: #cffafe; }
        .subsection-title.slate  { color: #475569; border-bottom-color: #e2e8f0; }
        .subsection-title.dark   { color: #334155; border-bottom-color: #cbd5e1; }

        /* ===== INFO GROUPS ===== */
        .info-group {
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
            height: 100%;
        }
        .info-group:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .info-label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .info-label i { font-size: 0.85rem; }
        .info-value {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 500;
            line-height: 1.5;
        }

        /* ===== CHIPS ===== */
        .chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .chip-blue   { background: #e0e7ff; color: #4f46e5; }
        .chip-green  { background: #dcfce7; color: #15803d; }
        .chip-red    { background: #fee2e2; color: #b91c1c; }
        .chip-amber  { background: #fef3c7; color: #b45309; }
        .chip-cyan   { background: #cffafe; color: #0e7490; }
        .chip-slate  { background: #e2e8f0; color: #334155; }

        /* ===== CARDS ===== */
        .section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: box-shadow 0.2s ease;
        }
        .section-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important;
        }

        /* ===== GRADIENT BUTTON ===== */
        .btn-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 50px;
            padding: 0.55rem 1.25rem;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
        }
        .btn-gradient-blue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .info-group { padding: 12px 14px; }
            .icon-tile { width: 40px; height: 40px; font-size: 1.15rem; }
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            body * { visibility: hidden; }
            .print-content, .print-content * { visibility: visible; }
            .print-content { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
            .btn { display: none !important; }
            @page { margin: 0.5in; }
        }
    </style>
</x-layouts.auth>
