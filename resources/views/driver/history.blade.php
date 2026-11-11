<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexAuto ELD — Driver History</title>

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg-page: #f1f5f9;
            --bg-card: #ffffff;
            --border-light: #e2e8f0;
            --primary: #2563eb;
            --primary-gradient: linear-gradient(135deg, #2563eb 0%, #0284c7 100%);
            --accent-success: #16a34a;
            --accent-warning: #d97706;
            --accent-danger: #dc2626;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        html, body {
            margin: 0; padding: 0;
            min-height: 100vh;
            background: var(--bg-page);
            color: var(--text-dark);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* ── Sticky Header ── */
        .history-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 12px 24px;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .driver-initials-badge {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--primary-gradient);
            border: 2px solid #fff;
            box-shadow: 0 4px 12px rgba(37,99,235,0.25);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 700; font-size: 16px; color: #fff;
            letter-spacing: 1px; flex-shrink: 0;
        }

        /* ── Summary Stats ── */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 10px;
        }
        .stat-icon.trips  { background: #eff6ff; color: #2563eb; }
        .stat-icon.done   { background: #f0fdf4; color: #16a34a; }
        .stat-icon.hours  { background: #fefce8; color: #d97706; }
        .stat-icon.cars   { background: #f0f9ff; color: #0284c7; }
        .stat-icon.alert  { background: #fef2f2; color: #dc2626; }
        .stat-value {
            font-family: 'Chakra Petch', sans-serif;
            font-size: 28px; font-weight: 700; color: var(--text-dark);
            line-height: 1;
        }
        .stat-label {
            font-size: 12px; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;
        }

        /* ── Filter Sidebar ── */
        .filter-sidebar {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 20px;
        }
        .filter-sidebar .form-label {
            font-size: 12px; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .filter-sidebar .form-control,
        .filter-sidebar .form-select {
            border-radius: 10px; border: 1px solid var(--border-light);
            font-size: 13px; padding: 8px 12px;
        }
        .filter-sidebar .form-control:focus,
        .filter-sidebar .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }

        /* ── Trip Cards ── */
        .trip-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 20px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
            overflow: hidden;
        }
        .trip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.07);
        }
        .trip-card::before {
            content: '';
            position: absolute; top: 0; left: 0;
            width: 4px; height: 100%;
            border-radius: 16px 0 0 16px;
        }
        .trip-card.status-completed::before { background: var(--accent-success); }
        .trip-card.status-active::before    { background: var(--primary); }
        .trip-card.status-pending::before   { background: var(--text-muted); }

        .trip-route-line {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600; color: var(--text-dark);
        }
        .trip-route-line .route-dot {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
        }
        .trip-route-line .route-dot.origin { background: var(--accent-success); }
        .trip-route-line .route-dot.dest   { background: var(--accent-danger); }
        .trip-route-line .route-arrow {
            color: var(--text-muted); font-size: 12px;
        }

        .trip-meta { font-size: 12px; color: var(--text-muted); }
        .trip-meta i { font-size: 13px; }

        .time-bar {
            height: 6px; border-radius: 3px; background: #e2e8f0;
            overflow: hidden; position: relative;
        }
        .time-bar-fill {
            height: 100%; border-radius: 3px;
            transition: width 0.3s ease;
        }

        /* ── Activity Log Section ── */
        .activity-log-section {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 20px;
        }

        .log-timeline-item {
            position: relative;
            padding-left: 32px;
            padding-bottom: 20px;
            border-left: 2px solid #e2e8f0;
            margin-left: 6px;
        }
        .log-timeline-item:last-child {
            border-left-color: transparent;
            padding-bottom: 0;
        }
        .log-timeline-dot {
            position: absolute; left: -7px; top: 2px;
            width: 12px; height: 12px; border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 6px rgba(37,99,235,0.3);
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 56px; opacity: 0.3; }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .filter-sidebar { margin-bottom: 16px; }
        }
        @media (max-width: 575.98px) {
            .stat-card { padding: 14px 10px; }
            .stat-value { font-size: 22px; }
            .trip-card { padding: 14px; }
        }

        html,
        body {
            max-width: 100%;
            overflow-x: clip;
        }

        .history-header > .d-flex,
        .history-header > .d-flex > div,
        .trip-card,
        .activity-log-section,
        .filter-sidebar {
            min-width: 0;
        }

        .history-tabs {
            flex-wrap: nowrap;
        }

        .history-tabs .nav-link {
            white-space: nowrap;
        }

        .trip-route-line {
            min-width: 0;
            flex-wrap: wrap;
            row-gap: 6px;
        }

        .trip-route-line .text-truncate {
            min-width: 0;
            max-width: min(35vw, 140px) !important;
        }

        .log-timeline-item > .d-flex.justify-content-between {
            flex-wrap: wrap;
            gap: 8px;
        }

        .log-timeline-item > .d-flex.justify-content-between > :first-child {
            min-width: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .log-timeline-item > .d-flex.justify-content-between > :last-child {
            flex: 0 0 auto;
            text-align: left !important;
        }

        @media (max-width: 767.98px) {
            .history-header {
                padding: 10px 12px;
            }

            .history-header > .d-flex {
                align-items: stretch !important;
                gap: 10px !important;
            }

            .history-header > .d-flex > div:first-child {
                gap: 10px !important;
            }

            .history-header .driver-initials-badge {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .history-tabs {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
            }

            .history-tabs .nav-item,
            .history-tabs .nav-link {
                width: 100%;
            }

            .history-tabs .nav-link {
                padding: 9px 8px;
                font-size: 12px;
            }

            .container-fluid {
                --bs-gutter-x: 1rem;
            }

            .filter-sidebar {
                padding: 14px;
            }

            .filter-sidebar form {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .filter-sidebar form > .mb-3 {
                min-width: 0;
                margin-bottom: 0 !important;
            }

            .filter-sidebar form .form-control,
            .filter-sidebar form .form-select {
                min-width: 0;
                min-height: 42px;
                padding: 8px;
                font-size: 12px;
            }

            .filter-sidebar form > .d-grid {
                grid-column: 1 / -1;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px !important;
            }

            .filter-sidebar form .btn {
                min-height: 40px;
                padding: 8px 6px;
                font-size: 12px;
            }

            #activityPane .filter-sidebar {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            #activityPane .filter-sidebar > h6,
            #activityPane .filter-sidebar > .d-grid {
                grid-column: 1 / -1;
            }

            #activityPane .filter-sidebar > .d-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            #activityPane .filter-sidebar > .mb-3 {
                min-width: 0;
                margin-bottom: 0 !important;
            }

            #activityPane .filter-sidebar .form-control,
            #activityPane .filter-sidebar .form-select {
                min-width: 0;
            }

            .trip-card .row > * {
                min-width: 0;
            }

            .activity-log-section {
                padding: 14px;
            }

            .activity-log-section > .d-flex {
                align-items: flex-start !important;
                gap: 8px;
            }

            .activity-log-section h6 {
                min-width: 0;
                line-height: 1.5;
            }

            .activity-log-section #btn-load-logs {
                flex: 0 0 auto;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 4px 0;
            }

            .pagination .page-link {
                padding: 7px 10px;
            }
        }

        @media (max-width: 359.98px) {
            .container-fluid {
                --bs-gutter-x: 0.75rem;
            }

            .history-header {
                padding-right: 8px;
                padding-left: 8px;
            }

            .history-tabs .nav-link {
                font-size: 11px;
            }

            .stat-card {
                padding: 12px 8px;
            }

            .stat-label {
                font-size: 10px;
            }

            .filter-sidebar {
                padding: 12px;
            }

            .trip-card {
                padding: 12px;
            }
        }

        /* ── Pagination ── */
        .pagination .page-link {
            border-radius: 10px; margin: 0 2px;
            border: 1px solid var(--border-light);
            color: var(--text-dark); font-size: 13px;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary); border-color: var(--primary);
        }

        /* ── Tab Pills ── */
        .history-tabs .nav-link {
            border-radius: 12px; padding: 8px 18px;
            font-size: 13px; font-weight: 600;
            color: var(--text-muted); border: 1px solid transparent;
        }
        .history-tabs .nav-link.active {
            background: var(--primary); color: #fff;
            border-color: var(--primary);
        }
        .history-tabs .nav-link:not(.active):hover {
            background: #eff6ff; color: var(--primary);
        }

        /* Loading spinner */
        .loading-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(255,255,255,0.7); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .loading-overlay.show { display: flex; }
    </style>
</head>
<body>

{{-- ===================== HEADER ===================== --}}
<header class="history-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        {{-- Left: Back + Driver Identity --}}
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('driver.portal') }}" class="btn btn-sm btn-outline-secondary rounded-circle p-2" title="Back to Portal">
                <i class="ri-arrow-left-line fw-bold"></i>
            </a>
            <div class="driver-initials-badge">{{ $initials }}</div>
            <div>
                <h6 class="mb-0 fw-bold" style="font-size: 15px;">{{ $driver->full_name }}</h6>
                <span class="text-muted" style="font-size: 12px;">
                    <i class="ri-history-line me-1"></i>Driver History & Activity Log
                </span>
            </div>
        </div>

        {{-- Right: Nav Tabs --}}
        <ul class="nav history-tabs" id="historyTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="trips-tab" data-bs-toggle="tab" data-bs-target="#tripsPane" type="button">
                    <i class="ri-route-line me-1"></i>Trips
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activityPane" type="button">
                    <i class="ri-time-line me-1"></i>Activity Log
                </button>
            </li>
        </ul>
    </div>
</header>

{{-- ===================== SUMMARY STATS ===================== --}}
<div class="container-fluid px-3 px-md-4 mt-3">
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon trips"><i class="ri-route-line"></i></div>
                <div class="stat-value">{{ $totalTrips }}</div>
                <div class="stat-label">Total Trips</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon done"><i class="ri-checkbox-circle-line"></i></div>
                <div class="stat-value">{{ $completedTrips }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon hours"><i class="ri-timer-line"></i></div>
                <div class="stat-value">{{ $totalDrivingSeconds >= 3600 ? round($totalDrivingSeconds / 3600, 1) . 'h' : round($totalDrivingSeconds / 60) . 'm' }}</div>
                <div class="stat-label">Driving Time</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon cars"><i class="ri-truck-line"></i></div>
                <div class="stat-value">{{ $totalVehicles }}</div>
                <div class="stat-label">Vehicles Driven</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon alert"><i class="ri-alarm-warning-line"></i></div>
                <div class="stat-value">{{ $overtimeCount }}</div>
                <div class="stat-label">Overtime Alerts</div>
            </div>
        </div>
    </div>

    {{-- ===================== TAB CONTENT ===================== --}}
    <div class="tab-content">

        {{-- ──────── TRIPS TAB ──────── --}}
        <div class="tab-pane fade show active" id="tripsPane">
            <div class="row g-3">
                {{-- Filter Sidebar --}}
                <div class="col-lg-3">
                    <div class="filter-sidebar position-lg-sticky" style="top: 90px;">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 14px;">
                            <i class="ri-filter-3-line text-primary"></i> Filters
                        </h6>
                        <form method="GET" action="{{ route('driver.history') }}" id="filterForm">
                            <div class="mb-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vehicle</label>
                                <select name="vehicle_id" class="form-select">
                                    <option value="">All Vehicles</option>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                                            {{ $v->fleet_no ?? $v->truck_no ?? 'Vehicle #'.$v->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Plan Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-semibold">
                                    <i class="ri-search-line me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('driver.history') }}" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                                    <i class="ri-refresh-line me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Trip Cards --}}
                <div class="col-lg-9">
                    @if($trips->count() > 0)
                        <div class="d-flex flex-column gap-3">
                            @foreach($trips as $trip)
                                @php
                                    $wb = $trip->waybill;
                                    $vehicle = $wb?->vehicle;
                                    $statusVal = $wb?->status?->value !== null ? (int) $wb->status->value : null;
                                    $statusLabel = $wb?->status?->name ?? 'Unknown';
                                    $plannedSec = (int) $trip->planned_seconds;
                                    $actualSec = (int) $trip->actual_driven_seconds;
                                    $isOvertime = $plannedSec > 0 && $actualSec > $plannedSec;
                                    $pct = $plannedSec > 0 ? min(100, round(($actualSec / $plannedSec) * 100)) : 0;
                                    $barColor = $isOvertime ? 'var(--accent-danger)' : ($pct >= 80 ? 'var(--accent-warning)' : 'var(--accent-success)');
                                    $statusBadge = match($statusVal) {
                                        0 => 'bg-warning-subtle text-warning',
                                        1 => 'bg-info-subtle text-info',
                                        2 => 'bg-primary text-white',
                                        3 => 'bg-success text-white',
                                        4 => 'bg-danger text-white',
                                        5 => 'bg-secondary text-white',
                                        6 => 'bg-dark text-white',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                    $cardClass = match($trip->status) {
                                        'completed' => 'status-completed',
                                        'active'    => 'status-active',
                                        default     => 'status-pending',
                                    };
                                @endphp
                                <div class="trip-card {{ $cardClass }}">
                                    <div class="row align-items-center g-3">
                                        {{-- Waybill & Route --}}
                                        <div class="col-md-5">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 12px;">
                                                    <i class="ri-file-text-line me-1"></i>#{{ $wb?->waybill_number ?? '—' }}
                                                </span>
                                                <span class="badge {{ $statusBadge }}" style="font-size: 11px;">{{ $statusLabel }}</span>
                                                <span class="badge {{ $trip->status === 'completed' ? 'bg-success' : ($trip->status === 'active' ? 'bg-primary' : 'bg-secondary') }} text-white" style="font-size: 10px;">
                                                    {{ ucfirst($trip->status) }}
                                                </span>
                                            </div>
                                            @if($wb?->origin || $wb?->destination)
                                                <div class="trip-route-line mb-1">
                                                    <span class="route-dot origin"></span>
                                                    <span class="text-truncate" style="max-width: 140px;" title="{{ $wb->origin }}">{{ Str::limit($wb->origin ?? '—', 22) }}</span>
                                                    <span class="route-arrow"><i class="ri-arrow-right-s-line"></i></span>
                                                    <span class="route-dot dest"></span>
                                                    <span class="text-truncate" style="max-width: 140px;" title="{{ $wb->destination }}">{{ Str::limit($wb->destination ?? '—', 22) }}</span>
                                                </div>
                                            @endif
                                            <div class="trip-meta d-flex flex-wrap gap-3 mt-2">
                                                <span><i class="ri-calendar-line me-1"></i>{{ $trip->created_at?->format('d M Y') }}</span>
                                                @if($vehicle)
                                                    <span><i class="ri-truck-line me-1"></i>{{ $vehicle->fleet_no ?? $vehicle->truck_no }}</span>
                                                @endif
                                                <span><i class="ri-user-line me-1"></i>Leg {{ $trip->sequence }}</span>
                                            </div>
                                        </div>

                                        {{-- Time Breakdown --}}
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted fw-semibold">Driving Time</small>
                                                @if($isOvertime)
                                                    <small class="text-danger fw-bold"><i class="ri-alarm-warning-line"></i> Overtime</small>
                                                @endif
                                            </div>
                                            <div class="time-bar mb-2">
                                                <div class="time-bar-fill" style="width: {{ $pct }}%; background: {{ $barColor }};"></div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <strong class="text-dark">{{ \App\Models\DriverEldLog::formatDigitalTime($actualSec) }}</strong> driven
                                                </small>
                                                <small class="text-muted">
                                                    of {{ \App\Models\DriverEldLog::formatDuration($plannedSec) }} planned
                                                </small>
                                            </div>
                                        </div>

                                        {{-- Duration & Dates --}}
                                        <div class="col-md-3 text-md-end">
                                            @if($trip->started_at)
                                                <div class="trip-meta mb-1">
                                                    <i class="ri-play-circle-line text-success me-1"></i>
                                                    Started: {{ $trip->started_at->format('H:i · d M') }}
                                                </div>
                                            @endif
                                            @if($trip->ended_at)
                                                <div class="trip-meta mb-1">
                                                    <i class="ri-stop-circle-line text-danger me-1"></i>
                                                    Ended: {{ $trip->ended_at->format('H:i · d M') }}
                                                </div>
                                            @endif
                                            @if($isOvertime)
                                                <div class="trip-meta">
                                                    <span class="badge bg-danger-subtle text-danger" style="font-size: 11px;">
                                                        <i class="ri-timer-line me-1"></i>+{{ \App\Models\DriverEldLog::formatDuration($actualSec - $plannedSec) }} extra
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($trips->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $trips->withQueryString()->links() }}
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <i class="ri-road-map-line d-block mb-3"></i>
                            <h5 class="fw-bold text-muted">No Trips Found</h5>
                            <p class="text-muted mb-0">No trip records match your current filters.<br>Try adjusting the date range or vehicle selection.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ──────── ACTIVITY LOG TAB ──────── --}}
        <div class="tab-pane fade" id="activityPane">
            <div class="row g-3">
                {{-- Activity Filters --}}
                <div class="col-lg-3">
                    <div class="filter-sidebar position-lg-sticky" style="top: 90px;">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 14px;">
                            <i class="ri-filter-3-line text-primary"></i> Activity Filters
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" id="log-date-from" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" id="log-date-to" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vehicle</label>
                            <select id="log-vehicle" class="form-select">
                                <option value="">All Vehicles</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->fleet_no ?? $v->truck_no ?? 'Vehicle #'.$v->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Event Type</label>
                            <select id="log-event-type" class="form-select">
                                <option value="">All Events</option>
                                <option value="start_duty">Duty Started</option>
                                <option value="pause_duty">Duty Paused</option>
                                <option value="resume_duty">Duty Resumed</option>
                                <option value="end_duty">Duty Completed</option>
                                <option value="overtime_violation">Overtime Violation</option>
                                <option value="movement_detected">Movement Alert</option>
                                <option value="manual_remark">Driver Remark</option>
                                <option value="checkpoint_passed">Checkpoint</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="btn-apply-log-filter" class="btn btn-primary btn-sm rounded-pill fw-semibold">
                                <i class="ri-search-line me-1"></i> Search Logs
                            </button>
                            <button type="button" id="btn-clear-log-filter" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold">
                                <i class="ri-refresh-line me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Activity Log Timeline --}}
                <div class="col-lg-9">
                    <div class="activity-log-section">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0" style="font-size: 14px;">
                                <i class="ri-time-line text-primary me-1"></i> Activity Timeline
                                <span id="log-total-count" class="badge bg-primary-subtle text-primary ms-2" style="font-size: 11px;"></span>
                            </h6>
                            <button type="button" id="btn-load-logs" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 11px;">
                                <i class="ri-refresh-line me-1"></i> Refresh
                            </button>
                        </div>

                        <div id="activity-log-container">
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                <p class="mb-0" style="font-size: 13px;">Loading activity logs...</p>
                            </div>
                        </div>

                        {{-- Load More --}}
                        <div id="load-more-container" class="text-center mt-3" style="display: none;">
                            <button type="button" id="btn-load-more" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-semibold" style="font-size: 12px;">
                                <i class="ri-arrow-down-line me-1"></i> Load More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Footer spacing --}}
    <div style="height: 40px;"></div>
</div>

{{-- ===================== JS ===================== --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    'use strict';

    const HISTORY_LOGS_URL = @json(route('driver.history_logs'));
    let currentPage = 1;
    let isLoading = false;

    // ── Load Activity Logs via AJAX ──
    function loadActivityLogs(page = 1, append = false) {
        if (isLoading) return;
        isLoading = true;

        const params = new URLSearchParams();
        params.set('page', page);
        params.set('per_page', 30);

        const dateFrom = document.getElementById('log-date-from')?.value;
        const dateTo = document.getElementById('log-date-to')?.value;
        const vehicleId = document.getElementById('log-vehicle')?.value;
        const eventType = document.getElementById('log-event-type')?.value;

        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (vehicleId) params.set('vehicle_id', vehicleId);
        if (eventType) params.set('event_type', eventType);

        const container = document.getElementById('activity-log-container');
        if (!append) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <p class="mb-0" style="font-size: 13px;">Loading activity logs...</p>
                </div>`;
        }

        fetch(HISTORY_LOGS_URL + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(data => {
            isLoading = false;
            if (!data.success) {
                container.innerHTML = '<div class="empty-state"><i class="ri-error-warning-line d-block mb-2"></i><p>Failed to load logs</p></div>';
                return;
            }

            const logs = data.logs || [];
            const pagination = data.pagination || {};

            document.getElementById('log-total-count').textContent = pagination.total + ' entries';

            if (logs.length === 0 && !append) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="ri-inbox-line d-block mb-3"></i>
                        <h6 class="fw-bold text-muted">No Activity Found</h6>
                        <p class="text-muted mb-0" style="font-size: 13px;">No ELD events match your filters.</p>
                    </div>`;
                document.getElementById('load-more-container').style.display = 'none';
                return;
            }

            let html = '';
            logs.forEach((log, i) => {
                const dotColor = getDotColor(log.event_type);
                html += `
                    <div class="log-timeline-item">
                        <div class="log-timeline-dot" style="background: ${dotColor};"></div>
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <span class="badge ${log.badge_class} me-1" style="font-size: 10px;">${log.event_label}</span>
                                ${log.waybill_no ? `<span class="badge bg-primary-subtle text-primary" style="font-size: 10px;">#${log.waybill_no}</span>` : ''}
                                ${log.vehicle ? `<span class="badge bg-light text-dark border" style="font-size: 10px;"><i class="ri-truck-line me-1"></i>${log.vehicle}</span>` : ''}
                            </div>
                            <div class="text-end">
                                <small class="text-muted fw-semibold">${log.time}</small>
                                <br><small class="text-muted" style="font-size: 10px;">${log.date}</small>
                            </div>
                        </div>
                        ${log.notes ? `<p class="mb-1 text-muted" style="font-size: 12px;">${escapeHtml(log.notes)}</p>` : ''}
                        <div class="d-flex gap-3 flex-wrap" style="font-size: 11px;">
                            ${log.elapsed ? `<span class="text-muted"><i class="ri-timer-line me-1"></i>Elapsed: <strong>${log.elapsed}</strong></span>` : ''}
                            ${log.planned ? `<span class="text-muted"><i class="ri-time-line me-1"></i>Planned: <strong>${log.planned}</strong></span>` : ''}
                            ${log.speed_kmh ? `<span class="text-muted"><i class="ri-speed-line me-1"></i>${log.speed_kmh} km/h</span>` : ''}
                            ${(log.latitude && log.longitude) ? `<span class="text-muted"><i class="ri-map-pin-line me-1"></i>${Number(log.latitude).toFixed(4)}, ${Number(log.longitude).toFixed(4)}</span>` : ''}
                        </div>
                    </div>`;
            });

            if (append) {
                container.insertAdjacentHTML('beforeend', html);
            } else {
                container.innerHTML = html;
            }

            currentPage = pagination.current_page;
            if (pagination.current_page < pagination.last_page) {
                document.getElementById('load-more-container').style.display = 'block';
            } else {
                document.getElementById('load-more-container').style.display = 'none';
            }
        })
        .catch(err => {
            isLoading = false;
            console.error('Failed to load logs:', err);
            if (!append) {
                container.innerHTML = '<div class="empty-state"><i class="ri-error-warning-line d-block mb-2"></i><p class="text-muted">Failed to load logs</p></div>';
            }
        });
    }

    function getDotColor(eventType) {
        const map = {
            'start_duty': '#16a34a', 'resume_duty': '#16a34a',
            'pause_duty': '#d97706', 'rest': '#d97706',
            'end_duty': '#64748b',
            'overtime_violation': '#dc2626',
            'movement_detected': '#0284c7',
            'checkpoint_passed': '#2563eb',
            'manual_remark': '#0f172a',
        };
        return map[eventType] || '#94a3b8';
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // ── Event Listeners ──
    document.getElementById('btn-apply-log-filter')?.addEventListener('click', () => loadActivityLogs(1, false));
    document.getElementById('btn-clear-log-filter')?.addEventListener('click', () => {
        document.getElementById('log-date-from').value = '';
        document.getElementById('log-date-to').value = '';
        document.getElementById('log-vehicle').value = '';
        document.getElementById('log-event-type').value = '';
        loadActivityLogs(1, false);
    });
    document.getElementById('btn-load-logs')?.addEventListener('click', () => loadActivityLogs(1, false));
    document.getElementById('btn-load-more')?.addEventListener('click', () => loadActivityLogs(currentPage + 1, true));

    // Load logs when Activity tab is shown
    document.getElementById('activity-tab')?.addEventListener('shown.bs.tab', function() {
        loadActivityLogs(1, false);
    });

    // Also preload if activity tab is active on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('activity-tab')?.classList.contains('active')) {
            loadActivityLogs(1, false);
        }
    });

})();
</script>
</body>
</html>
