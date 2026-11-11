<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NexAuto ELD — {{ $driver->full_name }} ({{ $vehicle?->fleet_no ?? 'Vehicle' }})</title>

    {{-- Progressive Web App (PWA) Meta & Manifest --}}
    <link rel="manifest" href="{{ asset('driver-manifest.json') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="NexAuto ELD">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/icon-192.png') }}">

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    {{-- JS Libraries --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @if(!empty($wialonHost))
        <script src="{{ $wialonHost }}/wsdk/script/wialon.js"></script>
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg-cockpit: #ffffff;
            --bg-page: #f8fafc;
            --border-light: #e2e8f0;
            --primary: #2563eb;
            --primary-gradient: linear-gradient(135deg, #2563eb 0%, #0284c7 100%);
            --accent-success: #16a34a;
            --accent-warning: #d97706;
            --accent-danger: #dc2626;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: var(--bg-page);
            color: var(--text-dark);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .portal-layout {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
            position: relative;
        }

        /* ── Top Clean White Cockpit Header ── */
        .cockpit-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 10px 20px;
            z-index: 1000;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        /* ── Driver Profile Avatar with Two Alphabets ── */
        .driver-avatar-wrapper {
            position: relative;
            display: inline-flex;
            flex-shrink: 0;
        }

        .driver-initials-badge {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: #ffffff;
            letter-spacing: 1px;
            user-select: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .driver-initials-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);
        }

        .avatar-status-dot {
            position: absolute;
            bottom: -1px;
            right: -1px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            background-color: #94a3b8;
        }

        .avatar-status-dot.online {
            background-color: var(--accent-success);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.25);
            animation: pulse-avatar-dot 1.8s infinite;
        }

        .avatar-status-dot.idle {
            background-color: var(--accent-warning);
            box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.25);
        }

        .avatar-status-dot.offline {
            background-color: #94a3b8;
        }

        @keyframes pulse-avatar-dot {
            0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.5); }
            70% { box-shadow: 0 0 0 7px rgba(22, 163, 74, 0); }
            100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
        }

        /* ── Center Clean HUD Timer ── */
        .timer-hud-container {
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 6px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            display: inline-flex;
            align-items: center;
            gap: 16px;
        }

        .hud-digital-clock {
            font-family: 'Chakra Petch', 'JetBrains Mono', monospace;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
            line-height: 1;
            color: #0284c7;
            text-shadow: 0 1px 2px rgba(2, 132, 199, 0.15);
            transition: color 0.3s ease;
        }

        .hud-digital-clock.overtime {
            color: #dc2626 !important;
            text-shadow: 0 0 12px rgba(220, 38, 38, 0.4) !important;
            animation: pulse-danger-timer 1.2s infinite alternate;
        }

        @keyframes pulse-danger-timer {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0.9; transform: scale(1.02); }
        }

        /* ── Duty Status Pill ── */
        .duty-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .duty-pill.driving {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .duty-pill.paused {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .duty-pill.stopped {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .duty-pill.completed {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #6ee7b7;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.15);
        }

        @keyframes riSpin {
            100% { transform: rotate(360deg); }
        }
        .ri-spin {
            display: inline-block;
            animation: riSpin 0.8s linear infinite;
        }

        /* ── Action Buttons ── */
        .btn-duty-action {
            border-radius: 24px;
            padding: 7px 18px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .btn-duty-start {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            border: 1px solid #16a34a;
            color: #ffffff;
        }
        .btn-duty-start:hover {
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.4);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-duty-pause {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: 1px solid #f59e0b;
            color: #ffffff;
        }
        .btn-duty-pause:hover {
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
            color: #ffffff;
        }

        .btn-duty-resume {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: 1px solid #0284c7;
            color: #ffffff;
        }

        /* ── Full Map Viewport ── */
        .map-viewport {
            flex: 1;
            width: 100%;
            height: 100%;
            position: relative;
            background: #e2e8f0;
        }

        #leaflet-driver-map {
            width: 100%;
            height: 100%;
            z-index: 10;
        }

        /* ── Floating Controls ── */
        .map-floating-overlay {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 500;
            display: flex;
            gap: 12px;
            max-width: 95%;
        }

        .map-control-btn {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border: 1px solid #cbd5e1;
            color: #0f172a;
            padding: 9px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .map-control-btn:hover {
            background: #ffffff;
            color: #2563eb;
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.15);
        }

        /* ── Telematics HUD Card ── */
        .telematics-hud-card {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 500;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(14px);
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: 12px 18px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
            min-width: 210px;
        }

        /* ── Movement Warning Banner ── */
        .movement-alert-banner {
            position: absolute;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 800;
            background: linear-gradient(135deg, #b91c1c, #dc2626);
            color: white;
            padding: 12px 24px;
            border-radius: 16px;
            box-shadow: 0 12px 36px rgba(220, 38, 38, 0.5);
            display: none;
            align-items: center;
            gap: 16px;
            max-width: 90%;
            border: 2px solid #fecaca;
            animation: bounce-drop 0.4s ease;
        }

        .movement-alert-banner.visible {
            display: flex !important;
        }

        @keyframes bounce-drop {
            0% { transform: translate(-50%, -30px); opacity: 0; }
            100% { transform: translate(-50%, 0); opacity: 1; }
        }

        /* ── Leaflet Custom Markers ── */
        .custom-pin {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            color: white;
            font-weight: 700;
        }
        .pin-origin { background: linear-gradient(135deg, #16a34a, #15803d); border: 2px solid white; }
        .pin-dest   { background: linear-gradient(135deg, #dc2626, #b91c1c); border: 2px solid white; }
        .pin-cp     { background: linear-gradient(135deg, #0284c7, #1d4ed8); border: 2px solid white; font-size: 11px; }

        .truck-pulse-ring {
            position: absolute;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.35);
            animation: ring-pulse 1.8s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
        }

        @keyframes ring-pulse {
            0% { transform: scale(0.6); opacity: 0.9; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        .truck-icon-circle {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #0284c7);
            border: 2px solid #ffffff;
            box-shadow: 0 4px 14px rgba(0,0,0,0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        /* ── Offcanvas Clean White Styling ── */
        .offcanvas-light {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-left: 1px solid var(--border-light) !important;
            box-shadow: -6px 0 25px rgba(0, 0, 0, 0.08) !important;
        }

        .offcanvas-header-custom {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-light);
            padding: 16px 20px;
        }

        .kpi-stat-box {
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 10px 12px;
            text-align: center;
        }

        .log-chip {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 11px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .log-chip:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            transform: translateY(-1px);
        }

        .log-timeline-item {
            position: relative;
            padding-left: 28px;
            padding-bottom: 22px;
            border-left: 2px solid #e2e8f0;
        }

        .log-timeline-item:last-child {
            border-left-color: transparent;
            padding-bottom: 0;
        }

        .log-timeline-dot {
            position: absolute;
            left: -6px;
            top: 2px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--primary);
            border: 2px solid #ffffff;
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.4);
        }

        /* ── Floating Toast Stack ── */
        .toast-stack-container {
            position: fixed;
            top: 76px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .custom-floating-toast {
            pointer-events: auto;
            min-width: 290px;
            max-width: 380px;
            background: #ffffff;
            border-radius: 14px;
            padding: 12px 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-left: 4px solid var(--primary);
            color: #0f172a;
            animation: slide-in-toast 0.3s ease;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .custom-floating-toast.toast-danger {
            border-left-color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 5px solid #dc2626;
        }

        .custom-floating-toast.toast-warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 5px solid #f59e0b;
        }

        .custom-floating-toast.toast-success {
            border-left-color: #16a34a;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 5px solid #16a34a;
        }

        @keyframes slide-in-toast {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .cockpit-header { padding: 8px 12px; }
            .hud-digital-clock { font-size: 24px; }
            .telematics-hud-card { display: none; }
            .driver-initials-badge { width: 38px; height: 38px; font-size: 15px; }
        }
    </style>
</head>
<body>

{{-- Floating Toast Stack Container --}}
<div class="toast-stack-container" id="toast-stack-container"></div>

<div class="portal-layout">

    {{-- ===================== 1. CLEAN WHITE COCKPIT HEADER ===================== --}}
    <header class="cockpit-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

            {{-- Left: Driver Identity with Two Alphabets Badge --}}
            <div class="d-flex align-items-center gap-3">
                <div class="driver-avatar-wrapper" title="{{ $driver->full_name }}">
                    <div class="driver-initials-badge">
                        {{ $initials }}
                    </div>
                    <span class="avatar-status-dot {{ $planStatus === 'active' ? 'online' : ($planStatus === 'paused' ? 'idle' : 'offline') }}" id="avatar-status-dot"></span>
                </div>

                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-dark">{{ $driver->full_name }}</h6>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">ELD Driver</span>
                    </div>
                    <div class="d-flex align-items-center gap-2" style="font-size: 11px;">
                        <span><i class="ri-id-card-line me-1"></i>{{ $driver->drivers_license_number ?: 'DL-Pending' }}</span>
                        <span>•</span>
                        <span class="text-primary fw-semibold"><i class="ri-truck-line me-1"></i>{{ $vehicle?->fleet_no ?? ($vehicle?->truck_no ?? 'Fleet Vehicle') }}</span>
                    </div>
                </div>
            </div>

            {{-- Center: Route Pill & Digital Countdown Timer HUD --}}
            <div class="d-flex flex-column align-items-center text-center">
                @if($waybill)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-light text-dark border border-secondary border-opacity-25" style="font-size: 11px;">
                            <i class="ri-route-line text-primary me-1"></i>Waybill #<span id="waybill-number-display">{{ $waybill->waybill_number }}</span>
                        </span>
                        <span id="waybill-status-pill" class="badge {{ match($waybill->status?->value) { 2 => 'bg-primary text-white', 3 => 'bg-success text-white', 4 => 'bg-danger text-white', 5 => 'bg-secondary text-white', default => 'bg-secondary-subtle text-secondary' } }}" style="font-size: 10px;">
                            @if($isDelivered)
                                <i class="ri-checkbox-circle-fill me-1"></i>{{ $waybill->status_label }}
                            @else
                                {{ $waybill->status_label }}
                            @endif
                        </span>
                        <span class="text-muted d-none d-lg-inline" style="font-size: 11px;">
                            {{ Str::limit($waybill->origin, 14) }} <i class="ri-arrow-right-line text-primary"></i> {{ Str::limit($waybill->destination, 14) }}
                        </span>
                    </div>
                @endif

                <div class="timer-hud-container">
                    <div class="d-flex flex-column align-items-center">
                        <div id="countdown-timer-display" class="hud-digital-clock {{ $isOvertime ? 'overtime' : '' }}">
                            {{ $isOvertime ? '+' . \App\Models\DriverEldLog::formatDigitalTime($overtimeSeconds) : \App\Models\DriverEldLog::formatDigitalTime($remainingSeconds) }}
                        </div>
                        <small id="countdown-caption" class="text-uppercase fw-bold {{ $isWaybillCompleted ? 'text-success' : ($isOvertime ? 'text-danger' : 'text-primary') }}" style="font-size: 9px; letter-spacing: 0.8px;">
                            @if($isDelivered)
                                ✓ WAYBILL DELIVERED
                            @elseif($isWaybillCompleted)
                                ✓ TRIP COMPLETED
                            @elseif($isOvertime)
                                🚨 LIMIT EXCEEDED (OVERTIME)
                            @else
                                REMAINING TIME DOWN
                            @endif
                        </small>
                    </div>

                    <div class="border-start border-secondary border-opacity-25 ps-3 text-start d-none d-sm-block">
                        <div class="text-muted" style="font-size: 10px;">SHIFT ALLOTMENT</div>
                        <div id="shift-allotment-display" class="fw-bold text-dark font-monospace" style="font-size: 13px;">
                            {{ \App\Models\DriverEldLog::formatDuration($plannedSeconds) }}
                        </div>
                        <div id="cockpit-overtime-pill" class="text-danger fw-bold font-monospace {{ $isOvertime ? '' : 'd-none' }}" style="font-size: 11px;">
                            Extra: +{{ \App\Models\DriverEldLog::formatDigitalTime($overtimeSeconds) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Duty Status Pill, Control Actions & Navigation --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Duty Status Pill --}}
                <div id="duty-status-badge" class="duty-pill {{ $isWaybillCompleted ? 'completed' : ($planStatus === 'active' ? 'driving' : ($planStatus === 'paused' ? 'paused' : 'stopped')) }}">
                    <span id="duty-status-text">
                        @if($isDelivered)
                            <i class="ri-checkbox-circle-fill text-success me-1"></i> TRIP DELIVERED
                        @elseif($isWaybillCompleted)
                            <i class="ri-checkbox-circle-fill text-success me-1"></i> TRIP COMPLETED
                        @elseif($planStatus === 'active')
                            DRIVING (ON DUTY)
                        @elseif($planStatus === 'paused')
                            RESTING (PAUSED)
                        @else
                            DUTY NOT STARTED
                        @endif
                    </span>
                </div>

                {{-- Action Buttons (HIDDEN when waybill is delivered/completed) --}}
                <button id="btn-start-duty" class="btn btn-duty-action btn-duty-start {{ ($planStatus === 'active' || $isWaybillCompleted) ? 'd-none' : '' }}">
                    <i class="ri-play-circle-line fs-5"></i>
                    <span>START DUTY</span>
                </button>

                <button id="btn-pause-duty" class="btn btn-duty-action btn-duty-pause {{ ($planStatus === 'active' && !$isWaybillCompleted) ? '' : 'd-none' }}">
                    <i class="ri-pause-circle-line fs-5"></i>
                    <span>PAUSE REST</span>
                </button>

                <button id="btn-resume-duty" class="btn btn-duty-action btn-duty-resume d-none">
                    <i class="ri-play-line fs-5"></i>
                    <span>RESUME</span>
                </button>

                <button id="btn-end-duty" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold {{ ($planStatus === 'active' && !$isWaybillCompleted) ? '' : 'd-none' }}">
                    <i class="ri-stop-circle-line me-1"></i> End Shift
                </button>

                @if($isWaybillCompleted)
                    <div id="delivered-status-indicator" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold d-flex align-items-center gap-1 shadow-sm" style="font-size: 11px;">
                        <i class="ri-checkbox-circle-fill fs-6 text-success"></i>
                        <span>{{ $isDelivered ? 'Delivered' : 'Completed' }}</span>
                    </div>
                @endif

                {{-- Multi-Driver Leg Selector (if multi-driver) --}}
                @if($totalDrivers > 1)
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 11px;">
                            <i class="ri-group-line me-1"></i> Leg {{ $driverSequence }}/{{ $totalDrivers }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow p-2" style="font-size: 12px; min-width: 250px;">
                            <li class="dropdown-header text-uppercase text-muted fw-bold" style="font-size: 10px;">Waybill Driver Plan</li>
                            @foreach($allDriverPlans as $plan)
                                <li class="p-2 border-bottom border-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold {{ $plan->driver_id === $driver->id ? 'text-primary' : 'text-dark' }}">
                                            {{ $plan->sequence }}. {{ $plan->driver?->full_name }}
                                            @if($plan->driver_id === $driver->id) <span class="badge bg-primary" style="font-size: 9px;">You</span> @endif
                                        </div>
                                        <small class="text-muted">{{ \App\Models\DriverEldLog::formatDuration($plan->planned_seconds) }}</small>
                                    </div>
                                    <span class="badge {{ $plan->status === 'completed' ? 'bg-success' : ($plan->status === 'active' ? 'bg-primary' : 'bg-secondary') }}" style="font-size: 9px;">
                                        {{ ucfirst($plan->status) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Real-time Sync Button --}}
                <button type="button" id="btn-sync-state" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm" title="Sync Real-Time Data (Status, Waybill, Shifts)" style="font-size: 11px;">
                    <i class="ri-refresh-line" id="sync-btn-icon"></i>
                    <span>Sync</span>
                </button>

                {{-- Real-time Driver Log Trigger --}}
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#driverLogOffcanvas">
                    <i class="ri-file-list-3-line"></i>
                    <span class="d-none d-sm-inline">Driver Log</span>
                    <span id="log-count-badge" class="badge bg-primary text-white ms-1" style="font-size: 9px;">{{ $eldLogs->count() }}</span>
                </button>

                {{-- Driver History --}}
                <a href="{{ route('driver.history') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm" style="font-size: 11px;" title="View Trip History & Activity Logs">
                    <i class="ri-history-line"></i>
                    <span class="d-none d-sm-inline">History</span>
                </a>

                {{-- PWA Install Button (Sharp SVG Download Icon) --}}
                <button id="pwa-install-btn" class="btn btn-sm btn-success rounded-pill px-3 py-1 d-flex align-items-center gap-1 fw-bold shadow-sm" title="Install NexAuto ELD App" style="font-size: 11px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    <span>Install App</span>
                </button>

                {{-- Sign Out --}}
                <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Sign Out">
                        <i class="ri-logout-box-r-line"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ===================== 2. FULL MAP VIEWPORT ===================== --}}
    <main class="map-viewport">

        {{-- Telematics Floating HUD Card --}}
        <div class="telematics-hud-card">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small text-uppercase fw-bold" style="font-size: 10px;">Live Telematics</span>
                <span id="telematics-status-pill" class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9px;">
                    <i class="ri-satellite-line me-1"></i>Active GPS
                </span>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <span class="fs-3 fw-bold text-dark font-monospace" id="hud-speed">0</span>
                <span class="text-muted small">km/h</span>
            </div>
            <div class="text-muted" style="font-size: 11px;">
                <span>Lat: <span id="hud-lat" class="font-monospace text-dark">{{ $waybill?->origin_lat ? number_format($waybill->origin_lat, 4) : '—' }}</span></span>
                <span class="mx-1">•</span>
                <span>Lng: <span id="hud-lng" class="font-monospace text-dark">{{ $waybill?->origin_lng ? number_format($waybill->origin_lng, 4) : '—' }}</span></span>
            </div>
        </div>

        {{-- Loud Visual Movement Alert Banner --}}
        <div id="movement-alert-banner" class="movement-alert-banner">
            <i class="ri-alarm-warning-fill fs-2"></i>
            <div>
                <div class="fw-bold fs-6">VEHICLE IN MOTION — DUTY TIMER STOPPED!</div>
                <small>ELD regulations require active logging while vehicle is moving. Please start your timer now.</small>
            </div>
            <button id="banner-start-timer-btn" class="btn btn-light text-danger fw-bold rounded-pill px-3 py-1 shadow-sm">
                START TIMER
            </button>
        </div>

        {{-- Leaflet Map Canvas --}}
        <div id="leaflet-driver-map"></div>

        {{-- Floating Overlay Controls --}}
        <div class="map-floating-overlay">
            <button id="btn-center-vehicle" class="map-control-btn" title="Center Map on Vehicle">
                <i class="ri-crosshair-2-line text-primary fs-5"></i>
                <span>Vehicle Fix</span>
            </button>
            <button id="btn-fit-route" class="map-control-btn" title="View Entire Waybill Track">
                <i class="ri-route-line text-info fs-5"></i>
                <span>Fit Route</span>
            </button>
            <button id="btn-toggle-tiles" class="map-control-btn" title="Toggle Map Style">
                <i class="ri-earth-line text-warning fs-5"></i>
                <span id="tiles-label">Satellite</span>
            </button>
        </div>
    </main>

</div>

@if(!$isWaybillCompleted)
{{-- ===================== 3. INITIAL LOGIN "START" ALERT MODAL ===================== --}}
<div class="modal fade" id="initialStartModal" tabindex="-1" aria-labelledby="initialStartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white border shadow-lg rounded-4">
            <div class="modal-header border-bottom pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="ri-timer-flash-line fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="initialStartModalLabel">Ready to Start Trip?</h5>
                        <small class="text-muted">ELD Driver Duty Activation</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-secondary mb-3">
                    Welcome, <strong class="text-dark">{{ $driver->full_name }}</strong>. You are currently scheduled for:
                </p>
                <div class="bg-light p-3 rounded-3 mb-3 border">
                    @if($waybill)
                        <div class="fw-bold text-dark mb-1">Waybill #{{ $waybill->waybill_number }}</div>
                        <div class="text-muted small mb-2">
                            <span>{{ $waybill->origin }}</span>
                            <i class="ri-arrow-right-line mx-1 text-primary"></i>
                            <span>{{ $waybill->destination }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary small">
                            <span>Assigned Fleet: <strong class="text-primary">{{ $vehicle?->fleet_no ?? 'Fleet Vehicle' }}</strong></span>
                            <span>Shift Limit: <strong class="text-success">{{ \App\Models\DriverEldLog::formatDuration($plannedSeconds) }}</strong></span>
                        </div>
                    @else
                        <div class="text-muted small">No active waybill currently assigned. Starting duty will log general driving time.</div>
                    @endif
                </div>
                <div class="alert alert-info border-0 rounded-3 small mb-0 d-flex align-items-center gap-2">
                    <i class="ri-information-line fs-5"></i>
                    <span>Click <strong>Start Duty</strong> to begin your countdown timer. If your vehicle moves without starting, an alert will be triggered.</span>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Review Map First
                </button>
                <button type="button" id="modal-start-trip-btn" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                    <i class="ri-play-fill me-1"></i> START DUTY NOW
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ===================== 4. TIME LIMIT EXCEEDED VIOLATION MODAL ===================== --}}
<div class="modal fade" id="overtimeViolationModal" tabindex="-1" aria-labelledby="overtimeViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white border border-danger shadow-lg rounded-4">
            <div class="modal-header border-bottom border-danger border-opacity-25 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger">
                        <i class="ri-error-warning-line fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0" id="overtimeViolationModalLabel">Driving Time Limit Exceeded!</h5>
                        <small class="text-muted">Safety &amp; Compliance Notification</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <i class="ri-alarm-warning-fill text-danger" style="font-size: 54px;"></i>
                    <h5 class="fw-bold text-dark mt-2">Maximum Driving Limit Reached</h5>
                    <p class="text-secondary small">
                        You have exceeded your scheduled driving allotment of <strong>{{ \App\Models\DriverEldLog::formatDuration($plannedSeconds) }}</strong> on this trip.
                    </p>
                </div>

                <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 small mb-3">
                    <i class="ri-broadcast-line me-1"></i>
                    <strong>Automated Alerts Dispatched:</strong> An urgent overtime notification has been transmitted to Fleet Management, Client Director, and logged to your ELD record.
                </div>

                <div class="bg-light p-3 rounded-3 text-secondary small border">
                    <div class="fw-bold text-dark mb-1">Required Next Steps:</div>
                    <ul class="mb-0 ps-3">
                        <li>Halt the vehicle at the nearest safe rest area.</li>
                        <li>Switch to relief driver or enter mandatory rest period.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Acknowledge
                </button>
                <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#driverLogOffcanvas" data-bs-dismiss="modal">
                    <i class="ri-file-list-3-line me-1"></i> View Log Record
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== 5. PWA INSTALL GUIDE MODAL ===================== --}}
<div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white border border-success border-opacity-50 shadow-lg rounded-4">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="pwaInstallModalLabel">Install NexAuto ELD App</h5>
                        <small class="text-muted">Progressive Web App (PWA)</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 text-secondary small">
                {{-- InPrivate / Incognito Warning Alert --}}
                <div class="alert alert-warning border border-warning border-opacity-50 rounded-3 small mb-3 d-flex align-items-start gap-2">
                    <i class="ri-alert-line fs-5 flex-shrink-0 text-warning mt-1"></i>
                    <div>
                        <strong class="text-dark d-block mb-1">Notice for InPrivate / Incognito Browsing:</strong>
                        <span>If you are browsing in an <strong>InPrivate / Incognito tab</strong>, Microsoft Edge &amp; Chrome block PWA installation by design for privacy. Open this link in a <strong>normal (regular) browser tab</strong> to install!</span>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 border mb-3">
                    <div class="fw-bold text-primary mb-2"><i class="ri-edge-line me-1"></i> Microsoft Edge (Windows):</div>
                    <p class="mb-2">1. Look at the right end of your address bar (URL bar) — click the <strong>"App available. Install NexAuto ELD"</strong> icon <i class="ri-computer-line text-primary"></i>.</p>
                    <p class="mb-0">2. Or click the Edge menu <strong>`...`</strong> (top right) ➔ <strong>Apps</strong> ➔ <strong>Install this site as an app</strong>.</p>
                </div>

                <div class="bg-light p-3 rounded-3 border mb-3">
                    <div class="fw-bold text-success mb-2"><i class="ri-chrome-line me-1"></i> Google Chrome (Desktop &amp; Android):</div>
                    <p class="mb-0">Click the <strong>Install</strong> icon in the address bar or tap the 3-dots menu ➔ <strong>Add to Home screen</strong> / <strong>Install App</strong>.</p>
                </div>

                <div class="bg-light p-3 rounded-3 border">
                    <div class="fw-bold text-info mb-2"><i class="ri-apple-line me-1"></i> iPhone / iPad (Safari):</div>
                    <p class="mb-0">Tap the <strong>Share</strong> button <i class="ri-share-box-line text-primary"></i> at the bottom, then scroll and select <strong>Add to Home Screen</strong>.</p>
                </div>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <button type="button" id="copy-portal-link-btn" class="btn btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="ri-file-copy-line me-1"></i> Copy Link
                </button>
                <div class="d-flex gap-2">
                    <button type="button" id="trigger-prompt-direct-btn" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="ri-download-cloud-line me-1"></i> Direct Install
                    </button>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== 6. REAL-TIME DRIVER LOG OFFCANVAS (CLEAN WHITE THEME) ===================== --}}
<div class="offcanvas offcanvas-end offcanvas-light" tabindex="-1" id="driverLogOffcanvas" aria-labelledby="driverLogOffcanvasLabel" style="width: 440px; max-width: 90vw;">
    <div class="offcanvas-header offcanvas-header-custom">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                <i class="ri-file-list-3-line fs-4"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0" id="driverLogOffcanvasLabel">ELD Driver Activity Log</h5>
                <small class="text-muted">Real-time Duty &amp; Telematics Record</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-3">
        {{-- Stats Grid (4 Cards including Driver Extra Time / Overtime) --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-sm-3">
                <div class="kpi-stat-box">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 9px; letter-spacing: 0.5px;">TOTAL DRIVEN</small>
                    <span id="log-stat-driven" class="fw-bold text-success font-monospace" style="font-size: 13px;">
                        {{ \App\Models\DriverEldLog::formatDigitalTime($actualDrivenSeconds) }}
                    </span>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="kpi-stat-box">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 9px; letter-spacing: 0.5px;">SHIFT LIMIT</small>
                    <span class="fw-bold text-primary font-monospace" style="font-size: 13px;">
                        {{ \App\Models\DriverEldLog::formatDuration($plannedSeconds) }}
                    </span>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="kpi-stat-box">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 9px; letter-spacing: 0.5px;">EXTRA TIME</small>
                    <span id="log-stat-overtime" class="fw-bold {{ $isOvertime ? 'text-danger' : 'text-muted' }} font-monospace" style="font-size: 13px;">
                        {{ $isOvertime ? '+' . \App\Models\DriverEldLog::formatDigitalTime($overtimeSeconds) : '00:00:00' }}
                    </span>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="kpi-stat-box">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 9px; letter-spacing: 0.5px;">STATUS</small>
                    <span id="log-stat-balance" class="fw-bold {{ $isOvertime ? 'text-danger' : 'text-success' }} font-monospace" style="font-size: 12px;">
                        {{ $isOvertime ? 'Overtime' : 'Compliant' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick Remark Tag Pills --}}
        <div class="mb-3">
            <small class="text-muted d-block mb-1 fw-semibold" style="font-size: 10px; text-transform: uppercase;">Quick Log Tags (Click to send):</small>
            <div class="d-flex flex-wrap gap-1">
                <span class="log-chip" onclick="quickSendRemark('⛽ Fuel Stop')">⛽ Fuel Stop</span>
                <span class="log-chip" onclick="quickSendRemark('👮 Police / Weight Check')">👮 Inspection</span>
                <span class="log-chip" onclick="quickSendRemark('☕ Rest Break')">☕ Break</span>
                <span class="log-chip" onclick="quickSendRemark('⚠️ Heavy Traffic Delay')">⚠️ Traffic</span>
                <span class="log-chip" onclick="quickSendRemark('🔧 Vehicle Check Passed')">🔧 Vehicle Check</span>
            </div>
        </div>

        {{-- Add Driver Comment / Remark Form --}}
        <form id="add-remark-form" class="mb-4 p-2 bg-light rounded-3 border">
            <label class="form-label text-dark fw-semibold small mb-1" for="driver-remark-input">
                <i class="ri-chat-new-line text-primary me-1"></i> Add Driver Remark / Note
            </label>
            <div class="input-group">
                <input type="text" id="driver-remark-input" class="form-control form-control-sm bg-white text-dark border-secondary border-opacity-25" placeholder="Type fuel stop, police check, delay note..." required>
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold">
                    <i class="ri-send-plane-fill me-1"></i> Send
                </button>
            </div>
        </form>

        {{-- Real-Time Events Timeline --}}
        <h6 class="text-uppercase text-dark fw-bold mb-3 d-flex align-items-center justify-content-between" style="font-size: 11px; letter-spacing: 0.5px;">
            <span><i class="ri-time-line text-primary me-1"></i> Activity History</span>
            <small class="text-muted" id="timeline-count">{{ $eldLogs->count() }} entries</small>
        </h6>

        <div id="eld-timeline-container">
            @forelse($eldLogs as $log)
                <div class="log-timeline-item">
                    <div class="log-timeline-dot"></div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge {{ $log->event_badge_class }}" style="font-size: 10px;">{{ $log->event_label }}</span>
                        <small class="text-primary font-monospace fw-bold" style="font-size: 11px;">{{ $log->created_at->format('H:i:s') }}</small>
                    </div>
                    @if($log->notes)
                        <div class="text-dark small mb-1 fw-medium">{{ $log->notes }}</div>
                    @endif
                    @if($log->latitude && $log->longitude)
                        <div class="text-muted font-monospace" style="font-size: 10px;">
                            <i class="ri-map-pin-line text-primary"></i> GPS: {{ number_format($log->latitude, 4) }}, {{ number_format($log->longitude, 4) }}
                            @if($log->speed_kmh > 0) • {{ $log->speed_kmh }} km/h @endif
                        </div>
                    @endif
                </div>
            @empty
                <div id="no-logs-msg" class="text-center text-muted py-4 small">
                    No activity recorded yet for this shift. Click Start Duty to begin logging.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===================== 7. CLIENT-SIDE LOGIC & REAL-TIME WEBSOCKET/TELEMATICS ===================== --}}
<script>
(function() {
    'use strict';

    // ── Application Configuration & State ──
    const CONFIG = {
        driverId:        @json($driver->id),
        driverName:      @json($driver->full_name),
        waybillId:       @json($waybill?->id),
        vehicleId:       @json($vehicle?->id),
        wialonUnitId:    @json($vehicle?->gps_unit_id),
        wialonHost:      @json($wialonHost),
        wialonToken:     @json($wialonToken),
        plannedSeconds:  {{ (int) $plannedSeconds }},
        originLat:       {{ $waybill?->origin_lat ? (float)$waybill->origin_lat : 'null' }},
        originLng:       {{ $waybill?->origin_lng ? (float)$waybill->origin_lng : 'null' }},
        destLat:         {{ $waybill?->destination_lat ? (float)$waybill->destination_lat : 'null' }},
        destLng:         {{ $waybill?->destination_lng ? (float)$waybill->destination_lng : 'null' }},
        originName:      @json($waybill?->origin ?? 'Origin'),
        destName:        @json($waybill?->destination ?? 'Destination'),
        isDelivered:        {{ $isDelivered ? 'true' : 'false' }},
        isWaybillCompleted: {{ $isWaybillCompleted ? 'true' : 'false' }},
        checkpoints:     @json($checkpoints),
        routeGeometry:   @json($routeGeometry),
        csrfToken:       document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        routes: {
            startDuty:       '{{ route('driver.start_duty') }}',
            pauseDuty:       '{{ route('driver.pause_duty') }}',
            resumeDuty:      '{{ route('driver.resume_duty') }}',
            endDuty:         '{{ route('driver.end_duty') }}',
            syncTimer:       '{{ route('driver.sync_timer') }}',
            movementAlert:   '{{ route('driver.movement_alert') }}',
            addRemark:       '{{ route('driver.add_remark') }}',
            getLogs:         '{{ route('driver.get_logs') }}',
            liveTelematics:  '{{ route('driver.live_telematics') }}',
            syncState:       '{{ route('driver.sync_state') }}'
        }
    };

    let timerState = {
        isRunning:      {{ ($planStatus === 'active' && !$isWaybillCompleted) ? 'true' : 'false' }},
        elapsedSeconds: {{ (int) $actualDrivenSeconds }},
        intervalId:     null,
        isOvertime:     {{ $isOvertime ? 'true' : 'false' }},
        // If already in overtime on page load (e.g. after logout/refresh), mark as already notified
        // so the overtime violation modal does NOT re-pop every time the page loads or the driver logs out
        lastNotifiedOvertime: {{ $isOvertime ? 'true' : 'false' }}
    };

    let vehicleState = {
        lat:     CONFIG.originLat || 33.6844,
        lng:     CONFIG.originLng || 73.0479,
        speed:   0,
        heading: 0,
        marker:  null,
        hasFix:  false
    };

    let map = null;
    let routeLine = null;
    let checkpointMarkers = [];
    let currentTileLayer = null;
    let isSatellite = false;
    let movementWarningActive = false;
    let audioCtx = null;
    let deferredPwaPrompt = null;

    // ── Helper: AudioContext Initialization & Chimes ──
    function initAudio() {
        if (!audioCtx) {
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch(e) {}
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    function playChime(frequencies = [880], duration = 180, type = 'sine') {
        initAudio();
        if (!audioCtx) return;
        try {
            frequencies.forEach((freq, idx) => {
                setTimeout(() => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = type;
                    osc.frequency.value = freq;
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    gain.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + duration / 1000);
                    setTimeout(() => osc.stop(), duration);
                }, idx * 120);
            });
        } catch(e) {}
    }

    // ── Helper: Browser Notification & Floating Toast ──
    function requestBrowserNotifications() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    function showFloatingToast(title, message, type = 'info') {
        const container = document.getElementById('toast-stack-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `custom-floating-toast toast-${type}`;

        let icon = 'ri-information-line text-info';
        if (type === 'danger') icon = 'ri-error-warning-fill text-danger';
        else if (type === 'warning') icon = 'ri-alarm-warning-fill text-warning';
        else if (type === 'success') icon = 'ri-checkbox-circle-fill text-success';

        toast.innerHTML = `
            <i class="${icon} fs-4"></i>
            <div class="flex-grow-1">
                <div class="fw-bold mb-1" style="font-size: 13px;">${title}</div>
                <div class="small" style="color: #475569; font-size: 11px;">${message}</div>
            </div>
            <button type="button" class="btn-close btn-sm" aria-label="Close"></button>
        `;

        toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(60px)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        // Send HTML5 desktop notification if permitted
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                new Notification(title, {
                    body: message,
                    icon: '/assets/img/icon-192.png',
                    tag: 'nexauto-eld-' + Date.now()
                });
            } catch(e) {}
        }
    }

    function formatTime(totalSecs) {
        totalSecs = Math.max(0, Math.floor(totalSecs));
        const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
        const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
        const secs = String(totalSecs % 60).padStart(2, '0');
        return `${hrs}:${mins}:${secs}`;
    }

    // ── Timer Logic & Overtime Extra Time Update ──
    function updateTimerUI() {
        const display = document.getElementById('countdown-timer-display');
        const caption = document.getElementById('countdown-caption');
        const logStatDriven = document.getElementById('log-stat-driven');
        const logStatOvertime = document.getElementById('log-stat-overtime');
        const logStatBalance = document.getElementById('log-stat-balance');
        const cockpitOvertimePill = document.getElementById('cockpit-overtime-pill');

        if (logStatDriven) logStatDriven.textContent = formatTime(timerState.elapsedSeconds);

        const remaining = CONFIG.plannedSeconds - timerState.elapsedSeconds;

        if (remaining <= 0) {
            timerState.isOvertime = true;
            const overtimeSecs = Math.abs(remaining);
            const overtimeFormatted = '+' + formatTime(overtimeSecs);

            if (display) {
                display.textContent = overtimeFormatted;
                display.classList.add('overtime');
            }
            if (caption) {
                caption.textContent = '🚨 LIMIT EXCEEDED (OVERTIME)';
                caption.className = 'text-uppercase fw-bold text-danger';
            }
            if (logStatOvertime) {
                logStatOvertime.textContent = overtimeFormatted;
                logStatOvertime.className = 'fw-bold text-danger font-monospace';
            }
            if (logStatBalance) {
                logStatBalance.textContent = 'Overtime';
                logStatBalance.className = 'fw-bold text-danger font-monospace';
            }
            if (cockpitOvertimePill) {
                cockpitOvertimePill.textContent = 'Extra: ' + overtimeFormatted;
                cockpitOvertimePill.classList.remove('d-none');
            }

            // Trigger Overtime Violation Modal & Alarm only during an active timer tick
            // (not on page load/refresh or after logout — lastNotifiedOvertime guards this)
            if (!timerState.lastNotifiedOvertime && timerState.isRunning) {
                timerState.lastNotifiedOvertime = true;
                playChime([520, 440, 520, 440], 250, 'sawtooth');
                showFloatingToast('🚨 Overtime Violation', 'You have exceeded your scheduled driving limit!', 'danger');
                const modalEl = document.getElementById('overtimeViolationModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            }
        } else {
            timerState.isOvertime = false;
            if (display) {
                display.textContent = formatTime(remaining);
                display.classList.remove('overtime');
            }
            if (caption) {
                caption.textContent = 'REMAINING TIME DOWN';
                caption.className = 'text-uppercase fw-bold text-primary';
            }
            if (logStatOvertime) {
                logStatOvertime.textContent = '00:00:00';
                logStatOvertime.className = 'fw-bold text-muted font-monospace';
            }
            if (logStatBalance) {
                logStatBalance.textContent = 'Compliant';
                logStatBalance.className = 'fw-bold text-success font-monospace';
            }
            if (cockpitOvertimePill) {
                cockpitOvertimePill.classList.add('d-none');
            }
        }
    }

    function startTimerTick() {
        if (timerState.intervalId) clearInterval(timerState.intervalId);
        timerState.isRunning = true;

        timerState.intervalId = setInterval(function() {
            timerState.elapsedSeconds++;
            updateTimerUI();

            // Periodic sync with server every 15 seconds
            if (timerState.elapsedSeconds % 15 === 0) {
                syncTimerWithServer();
            }
        }, 1000);

        setDutyStatus('active');
        dismissMovementAlert();
    }

    function pauseTimerTick() {
        if (timerState.intervalId) clearInterval(timerState.intervalId);
        timerState.isRunning = false;
        setDutyStatus('paused');
        syncTimerWithServer();
    }

    function setDutyStatus(status) {
        const badge = document.getElementById('duty-status-badge');
        const text = document.getElementById('duty-status-text');
        const dot = document.getElementById('avatar-status-dot');
        const btnStart = document.getElementById('btn-start-duty');
        const btnPause = document.getElementById('btn-pause-duty');
        const btnResume = document.getElementById('btn-resume-duty');
        const btnEnd = document.getElementById('btn-end-duty');

        if (CONFIG.isWaybillCompleted || CONFIG.isDelivered) {
            if (badge) badge.className = 'duty-pill completed';
            if (text) text.innerHTML = '<i class="ri-checkbox-circle-fill text-success me-1"></i> ' + (CONFIG.isDelivered ? 'TRIP DELIVERED' : 'TRIP COMPLETED');
            if (dot) dot.className = 'avatar-status-dot offline';
            btnStart?.classList.add('d-none');
            btnPause?.classList.add('d-none');
            btnResume?.classList.add('d-none');
            btnEnd?.classList.add('d-none');
            return;
        }

        if (status === 'active') {
            if (badge) badge.className = 'duty-pill driving';
            if (text) text.textContent = 'DRIVING (ON DUTY)';
            if (dot) dot.className = 'avatar-status-dot online';
            btnStart?.classList.add('d-none');
            btnPause?.classList.remove('d-none');
            btnResume?.classList.add('d-none');
            btnEnd?.classList.remove('d-none');
        } else if (status === 'paused') {
            if (badge) badge.className = 'duty-pill paused';
            if (text) text.textContent = 'RESTING (PAUSED)';
            if (dot) dot.className = 'avatar-status-dot idle';
            btnStart?.classList.add('d-none');
            btnPause?.classList.add('d-none');
            btnResume?.classList.remove('d-none');
            btnEnd?.classList.remove('d-none');
        } else {
            if (badge) badge.className = 'duty-pill stopped';
            if (text) text.textContent = 'DUTY NOT STARTED';
            if (dot) dot.className = 'avatar-status-dot offline';
            btnStart?.classList.remove('d-none');
            btnPause?.classList.add('d-none');
            btnResume?.classList.add('d-none');
            btnEnd?.classList.add('d-none');
        }
    }

    function syncTimerWithServer() {
        fetch(CONFIG.routes.syncTimer, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({
                driver_id: CONFIG.driverId,
                waybill_id: CONFIG.waybillId,
                elapsed_seconds: timerState.elapsedSeconds,
                planned_seconds: CONFIG.plannedSeconds,
                latitude: vehicleState.lat,
                longitude: vehicleState.lng,
                speed_kmh: vehicleState.speed
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.overtime_notification_sent) {
                showFloatingToast('🚨 Overtime Alert', 'Dispatch notification transmitted to Fleet Management & Client Director.', 'danger');
                refreshLogsList();
            }
        })
        .catch(() => {});
    }

    // ── Movement Detection Alert ──
    function triggerMovementAlert(speed) {
        if (timerState.isRunning || movementWarningActive) return;
        movementWarningActive = true;
        playChime([880, 880, 880], 200, 'square');

        const banner = document.getElementById('movement-alert-banner');
        if (banner) banner.classList.add('visible');

        showFloatingToast('⚠️ Vehicle In Motion', 'Your vehicle is moving! Please start your duty timer.', 'warning');

        fetch(CONFIG.routes.movementAlert, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({
                driver_id: CONFIG.driverId,
                waybill_id: CONFIG.waybillId,
                speed_kmh: speed,
                latitude: vehicleState.lat,
                longitude: vehicleState.lng
            })
        }).then(() => refreshLogsList());
    }

    function dismissMovementAlert() {
        movementWarningActive = false;
        const banner = document.getElementById('movement-alert-banner');
        if (banner) banner.classList.remove('visible');
    }

    // ── Leaflet Map Setup ──
    function initLeafletMap() {
        const defaultCenter = [
            CONFIG.originLat || 33.6844,
            CONFIG.originLng || 73.0479
        ];

        map = L.map('leaflet-driver-map', {
            center: defaultCenter,
            zoom: 12,
            zoomControl: false
        });

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        setTileLayer(false);
        drawWaybillRoute();
        initVehicleMarker();
    }

    function setTileLayer(satellite) {
        if (currentTileLayer) map.removeLayer(currentTileLayer);

        if (satellite) {
            currentTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: '&copy; Esri'
            });
            const label = document.getElementById('tiles-label');
            if (label) label.textContent = 'Streets';
        } else {
            currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            });
            const label = document.getElementById('tiles-label');
            if (label) label.textContent = 'Satellite';
        }

        currentTileLayer.addTo(map);
        isSatellite = satellite;
    }

    function drawWaybillRoute() {
        const bounds = L.latLngBounds();

        // Origin Pin
        if (CONFIG.originLat && CONFIG.originLng) {
            const originIcon = L.divIcon({
                className: '',
                html: `<div class="custom-pin pin-origin" style="width:34px;height:34px;"><i class="ri-play-fill fs-5"></i></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });
            L.marker([CONFIG.originLat, CONFIG.originLng], { icon: originIcon })
                .addTo(map)
                .bindPopup(`<strong>Trip Origin:</strong><br>${CONFIG.originName}`);
            bounds.extend([CONFIG.originLat, CONFIG.originLng]);
        }

        // Destination Pin
        if (CONFIG.destLat && CONFIG.destLng) {
            const destIcon = L.divIcon({
                className: '',
                html: `<div class="custom-pin pin-dest" style="width:34px;height:34px;"><i class="ri-flag-2-fill fs-5"></i></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });
            L.marker([CONFIG.destLat, CONFIG.destLng], { icon: destIcon })
                .addTo(map)
                .bindPopup(`<strong>Trip Destination:</strong><br>${CONFIG.destName}`);
            bounds.extend([CONFIG.destLat, CONFIG.destLng]);
        }

        // Checkpoints
        if (Array.isArray(CONFIG.checkpoints)) {
            CONFIG.checkpoints.forEach((cp, idx) => {
                const lat = parseFloat(cp.lat || cp.latitude);
                const lng = parseFloat(cp.lng || cp.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const cpIcon = L.divIcon({
                        className: '',
                        html: `<div class="custom-pin pin-cp" style="width:28px;height:28px;">${idx + 1}</div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });
                    const marker = L.marker([lat, lng], { icon: cpIcon })
                        .addTo(map)
                        .bindPopup(`<strong>Checkpoint #${idx + 1}:</strong><br>${cp.name || cp.address || 'Waypoint'}`);
                    checkpointMarkers.push(marker);
                    bounds.extend([lat, lng]);
                }
            });
        }

        // Route Polyline
        let points = [];
        if (Array.isArray(CONFIG.routeGeometry) && CONFIG.routeGeometry.length > 0) {
            points = CONFIG.routeGeometry.map(pt => Array.isArray(pt) ? [pt[0], pt[1]] : [pt.lat, pt.lng]);
        } else if (CONFIG.originLat && CONFIG.destLat) {
            points = [
                [CONFIG.originLat, CONFIG.originLng],
                ...checkpointMarkers.map(m => m.getLatLng()),
                [CONFIG.destLat, CONFIG.destLng]
            ];
        }

        if (points.length >= 2) {
            routeLine = L.polyline(points, {
                color: '#0284c7',
                weight: 5,
                opacity: 0.9,
                lineJoin: 'round',
                dashArray: '8, 6'
            }).addTo(map);

            bounds.extend(routeLine.getBounds());
            map.fitBounds(bounds, { padding: [50, 50] });
        } else if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    function initVehicleMarker() {
        const truckIcon = L.divIcon({
            className: '',
            html: `
                <div class="position-relative d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                    <div class="truck-pulse-ring"></div>
                    <div class="truck-icon-circle" id="truck-heading-circle">
                        <i class="ri-truck-fill"></i>
                    </div>
                </div>
            `,
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        vehicleState.marker = L.marker([vehicleState.lat, vehicleState.lng], { icon: truckIcon, zIndexOffset: 1000 }).addTo(map);
        vehicleState.marker.bindPopup(`<strong>${CONFIG.driverName}</strong><br>Fleet Vehicle Live GPS`);
    }

    function updateVehiclePosition(lat, lng, speed, heading) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        speed = parseFloat(speed) || 0;
        heading = parseInt(heading) || 0;

        if (isNaN(lat) || isNaN(lng)) return;

        vehicleState.lat = lat;
        vehicleState.lng = lng;
        vehicleState.speed = speed;
        vehicleState.heading = heading;
        vehicleState.hasFix = true;

        if (vehicleState.marker) {
            vehicleState.marker.setLatLng([lat, lng]);
            const circle = document.getElementById('truck-heading-circle');
            if (circle && heading > 0) {
                circle.style.transform = `rotate(${heading}deg)`;
            }
        }

        // Update telematics HUD
        const hudSpeed = document.getElementById('hud-speed');
        const hudLat = document.getElementById('hud-lat');
        const hudLng = document.getElementById('hud-lng');
        if (hudSpeed) hudSpeed.textContent = Math.round(speed);
        if (hudLat) hudLat.textContent = lat.toFixed(4);
        if (hudLng) hudLng.textContent = lng.toFixed(4);

        // Movement check: If vehicle is moving (> 3 km/h) and timer is NOT running
        if (speed > 3 && !timerState.isRunning) {
            triggerMovementAlert(speed);
        } else if (speed <= 1 && movementWarningActive) {
            dismissMovementAlert();
        }
    }

    // ── Telematics Service (Wialon WSDK + Backend Polling) ──
    function initWialonTracking() {
        if (typeof wialon === 'undefined' || !CONFIG.wialonToken) {
            startFallbackPolling();
            return;
        }

        try {
            const session = wialon.core.Session.getInstance();
            session.initSession(CONFIG.wialonHost);
            session.loginToken(CONFIG.wialonToken, '', function(code) {
                if (code) { startFallbackPolling(); return; }

                session.updateDataFlags(
                    [{ type: 'type', data: 'avl_unit', flags: wialon.item.Item.dataFlag.base | wialon.item.Unit.dataFlag.lastMessage, mode: 0 }],
                    function(loadCode) {
                        if (loadCode) { startFallbackPolling(); return; }

                        const units = session.getItems('avl_unit');
                        if (!units || !units.length) { startFallbackPolling(); return; }

                        let matchedUnit = null;
                        if (CONFIG.wialonUnitId) {
                            matchedUnit = units.find(u => String(u.getId()) === String(CONFIG.wialonUnitId));
                        }
                        if (!matchedUnit && units.length > 0) {
                            matchedUnit = units[0];
                        }

                        if (matchedUnit) {
                            const pos = matchedUnit.getPosition();
                            if (pos) updateVehiclePosition(pos.y, pos.x, pos.s, pos.c);

                            matchedUnit.addListener('changePosition', function() {
                                const newPos = matchedUnit.getPosition();
                                if (newPos) updateVehiclePosition(newPos.y, newPos.x, newPos.s, newPos.c);
                            });
                        } else {
                            startFallbackPolling();
                        }
                    }
                );
            });
        } catch(e) {
            startFallbackPolling();
        }
    }

    function startFallbackPolling() {
        setInterval(function() {
            if (!CONFIG.vehicleId) return;

            fetch(`${CONFIG.routes.liveTelematics}?vehicle_id=${CONFIG.vehicleId}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        updateVehiclePosition(data.latitude, data.longitude, data.speed_kmh, data.heading);
                    }
                })
                .catch(() => {});
        }, 5000);
    }

    // ── DOM Actions & Event Listeners ──
    function doStartDuty() {
        initAudio();
        requestBrowserNotifications();
        startTimerTick();
        playChime([440, 880], 200, 'sine');

        fetch(CONFIG.routes.startDuty, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
            body: JSON.stringify({
                driver_id: CONFIG.driverId,
                waybill_id: CONFIG.waybillId,
                latitude: vehicleState.lat,
                longitude: vehicleState.lng,
                speed_kmh: vehicleState.speed
            })
        })
        .then(r => r.json())
        .then(data => {
            showFloatingToast('Duty Started', 'Shift timer is running. Drive safely!', 'success');
            refreshLogsList();
        })
        .catch(() => {});
    }

    // ── Driver Remark Submission ──
    function submitDriverRemark(remarkText) {
        if (!remarkText || !remarkText.trim()) return;
        remarkText = remarkText.trim();
        initAudio();

        fetch(CONFIG.routes.addRemark, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({
                driver_id: CONFIG.driverId,
                waybill_id: CONFIG.waybillId,
                remark: remarkText,
                notes: remarkText,
                latitude: vehicleState.lat,
                longitude: vehicleState.lng
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                const input = document.getElementById('driver-remark-input');
                if (input) input.value = '';
                playChime([880], 120, 'sine');
                showFloatingToast('Remark Logged', remarkText, 'info');
                refreshLogsList();
            } else {
                showFloatingToast('Error', data.message || 'Could not save remark.', 'danger');
            }
        })
        .catch(() => {
            showFloatingToast('Network Error', 'Failed to communicate with server.', 'danger');
        });
    }

    // Quick tag pill click handler (instant send)
    window.quickSendRemark = function(tag) {
        submitDriverRemark(tag);
    };

    function setupDOMEvents() {
        // Start duty
        document.getElementById('btn-start-duty')?.addEventListener('click', () => doStartDuty());
        document.getElementById('modal-start-trip-btn')?.addEventListener('click', () => {
            const modalEl = document.getElementById('initialStartModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            sessionStorage.setItem('startModalDismissed_' + (CONFIG.waybillId || 'general'), '1');
            doStartDuty();
        });
        document.getElementById('banner-start-timer-btn')?.addEventListener('click', () => doStartDuty());

        // Pause duty
        document.getElementById('btn-pause-duty')?.addEventListener('click', function() {
            initAudio();
            pauseTimerTick();
            playChime([660, 440], 200, 'sine');
            showFloatingToast('Duty Paused', 'Shift timer paused for rest break.', 'warning');

            fetch(CONFIG.routes.pauseDuty, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
                body: JSON.stringify({
                    driver_id: CONFIG.driverId,
                    waybill_id: CONFIG.waybillId,
                    elapsed_seconds: timerState.elapsedSeconds,
                    latitude: vehicleState.lat,
                    longitude: vehicleState.lng
                })
            }).then(() => refreshLogsList());
        });

        // Resume duty
        document.getElementById('btn-resume-duty')?.addEventListener('click', function() {
            initAudio();
            startTimerTick();
            playChime([440, 660, 880], 180, 'sine');
            showFloatingToast('Duty Resumed', 'Shift timer resumed.', 'success');

            fetch(CONFIG.routes.resumeDuty, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
                body: JSON.stringify({
                    driver_id: CONFIG.driverId,
                    waybill_id: CONFIG.waybillId,
                    latitude: vehicleState.lat,
                    longitude: vehicleState.lng
                })
            }).then(() => refreshLogsList());
        });

        // End duty
        document.getElementById('btn-end-duty')?.addEventListener('click', function() {
            if (!confirm('Are you sure you want to complete and end your shift?')) return;
            initAudio();
            if (timerState.intervalId) clearInterval(timerState.intervalId);
            timerState.isRunning = false;
            setDutyStatus('stopped');

            fetch(CONFIG.routes.endDuty, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
                body: JSON.stringify({
                    driver_id: CONFIG.driverId,
                    waybill_id: CONFIG.waybillId,
                    elapsed_seconds: timerState.elapsedSeconds,
                    latitude: vehicleState.lat,
                    longitude: vehicleState.lng
                })
            })
            .then(r => r.json())
            .then(data => {
                showFloatingToast('Duty Completed', 'Your shift has ended and was logged to ELD records.', 'success');
                refreshLogsList();
            });
        });

        // Add Remark Form Submit
        document.getElementById('add-remark-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('driver-remark-input');
            const remark = input?.value;
            submitDriverRemark(remark);
        });

        // Center on vehicle
        document.getElementById('btn-center-vehicle')?.addEventListener('click', function() {
            if (vehicleState.marker) {
                map.setView(vehicleState.marker.getLatLng(), 15, { animate: true });
                vehicleState.marker.openPopup();
            }
        });

        // Fit route
        document.getElementById('btn-fit-route')?.addEventListener('click', function() {
            if (routeLine) {
                map.fitBounds(routeLine.getBounds(), { padding: [50, 50], animate: true });
            }
        });

        // Toggle map tiles
        document.getElementById('btn-toggle-tiles')?.addEventListener('click', function() {
            setTileLayer(!isSatellite);
        });

        // Sync Real-Time Data Button
        document.getElementById('btn-sync-state')?.addEventListener('click', function() {
            syncRealTimeState(true);
        });

        // PWA Install Button
        document.getElementById('pwa-install-btn')?.addEventListener('click', function() {
            handlePwaInstall();
        });

        document.getElementById('trigger-prompt-direct-btn')?.addEventListener('click', function() {
            if (deferredPwaPrompt) {
                const modalEl = document.getElementById('pwaInstallModal');
                if (modalEl) {
                    const inst = bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                }
                deferredPwaPrompt.prompt();
                deferredPwaPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        showFloatingToast('PWA Installed', 'NexAuto ELD installed!', 'success');
                        deferredPwaPrompt = null;
                    }
                });
            } else {
                showFloatingToast('Notice', 'InPrivate/Incognito blocks auto-prompt. Please open in a regular Edge window or click Edge Menu (...) > Apps > Install.', 'info');
            }
        });

        // Copy portal link button
        document.getElementById('copy-portal-link-btn')?.addEventListener('click', function() {
            const url = window.location.origin + '/driver-panel';
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    showFloatingToast('Link Copied', 'URL copied! Paste in a regular (non-InPrivate) browser tab to install.', 'success');
                });
            } else {
                prompt('Copy this URL and open in a regular window:', url);
            }
        });
    }

    function handlePwaInstall() {
        if (deferredPwaPrompt) {
            deferredPwaPrompt.prompt();
            deferredPwaPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    showFloatingToast('PWA Installed', 'NexAuto ELD has been installed as an app!', 'success');
                    deferredPwaPrompt = null;
                }
            });
        } else if (window.matchMedia('(display-mode: standalone)').matches) {
            showFloatingToast('Already Installed', 'NexAuto ELD is already installed and running standalone.', 'info');
        } else {
            const modalEl = document.getElementById('pwaInstallModal');
            if (modalEl) {
                // Remove any dangling backdrops first to prevent darkening
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
    }

    // ── Real-Time Portal State Sync (Waybill status, Duration & Driver Plan) ──
    function syncRealTimeState(manual = false) {
        const icon = document.getElementById('sync-btn-icon');
        if (manual && icon) {
            icon.classList.add('ri-spin');
        }

        fetch(`${CONFIG.routes.syncState}?driver_id=${CONFIG.driverId}&waybill_id=${CONFIG.waybillId || ''}`)
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) return;

                // 1. Update Waybill Status
                if (data.waybill) {
                    const statusPill = document.getElementById('waybill-status-pill');
                    if (statusPill) {
                        statusPill.textContent = data.waybill.status_label;
                        statusPill.className = 'badge ' + (data.waybill.status_badge || 'bg-secondary text-white');
                    }

                    const numDisplay = document.getElementById('waybill-number-display');
                    if (numDisplay && data.waybill.waybill_number) {
                        numDisplay.textContent = data.waybill.waybill_number;
                    }

                    // If waybill is Delivered (3) or Completed (4/6)
                    if (data.waybill.is_delivered || data.waybill.is_completed) {
                        CONFIG.isWaybillCompleted = true;
                        CONFIG.isDelivered = data.waybill.is_delivered;

                        if (timerState.isRunning) {
                            if (timerState.intervalId) clearInterval(timerState.intervalId);
                            timerState.isRunning = false;
                        }

                        const dutyStatusBadge = document.getElementById('duty-status-badge');
                        const dutyStatusText = document.getElementById('duty-status-text');
                        if (dutyStatusBadge) {
                            dutyStatusBadge.className = 'duty-pill completed';
                        }
                        if (dutyStatusText) {
                            dutyStatusText.innerHTML = '<i class="ri-checkbox-circle-fill text-success me-1"></i> ' + (data.waybill.is_delivered ? 'TRIP DELIVERED' : 'TRIP COMPLETED');
                        }

                        // Hide action buttons
                        document.getElementById('btn-start-duty')?.classList.add('d-none');
                        document.getElementById('btn-pause-duty')?.classList.add('d-none');
                        document.getElementById('btn-resume-duty')?.classList.add('d-none');
                        document.getElementById('btn-end-duty')?.classList.add('d-none');

                        // Ensure delivered indicator badge exists
                        let indicator = document.getElementById('delivered-status-indicator');
                        if (!indicator && dutyStatusBadge) {
                            indicator = document.createElement('div');
                            indicator.id = 'delivered-status-indicator';
                            indicator.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold d-flex align-items-center gap-1 shadow-sm';
                            indicator.style.fontSize = '11px';
                            indicator.innerHTML = '<i class="ri-checkbox-circle-fill fs-6 text-success"></i> <span>' + (data.waybill.is_delivered ? 'Delivered' : 'Completed') + '</span>';
                            dutyStatusBadge.insertAdjacentElement('afterend', indicator);
                        }

                        // Close start modal if open
                        const modalEl = document.getElementById('initialStartModal');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }

                        // Update countdown caption
                        const countdownCaption = document.getElementById('countdown-caption');
                        if (countdownCaption) {
                            countdownCaption.textContent = data.waybill.is_delivered ? '✓ WAYBILL DELIVERED' : '✓ TRIP COMPLETED';
                            countdownCaption.className = 'text-uppercase fw-bold text-success';
                        }

                        const logStatBalance = document.getElementById('log-stat-balance');
                        if (logStatBalance) {
                            logStatBalance.textContent = data.waybill.status_label || 'Delivered';
                            logStatBalance.className = 'fw-bold text-success font-monospace';
                        }
                    }
                }

                // 2. Update Driver Plan Allotment & Timer
                if (data.driver_plan && Number.isFinite(data.driver_plan.planned_seconds)) {
                    if (data.driver_plan.planned_seconds > 0 && data.driver_plan.planned_seconds !== CONFIG.plannedSeconds) {
                        CONFIG.plannedSeconds = data.driver_plan.planned_seconds;
                        const allotmentDisplay = document.getElementById('shift-allotment-display');
                        if (allotmentDisplay) {
                            allotmentDisplay.textContent = data.driver_plan.formatted_planned || formatTime(CONFIG.plannedSeconds);
                        }
                        const logStatLimit = document.getElementById('log-stat-limit');
                        if (logStatLimit) {
                            logStatLimit.textContent = data.driver_plan.formatted_planned || formatTime(CONFIG.plannedSeconds);
                        }
                        updateTimerUI();
                    }
                }

                // 3. Refresh activity logs
                refreshLogsList();

                if (manual) {
                    showFloatingToast('🔄 Synced', `Waybill status: ${data.waybill ? data.waybill.status_label : 'Active'}`, 'success');
                }
            })
            .catch(() => {
                if (manual) {
                    showFloatingToast('Sync Error', 'Could not refresh real-time data from server.', 'secondary');
                }
            })
            .finally(() => {
                if (icon) {
                    setTimeout(() => icon.classList.remove('ri-spin'), 600);
                }
            });
    }

    function refreshLogsList() {
        fetch(`${CONFIG.routes.getLogs}?driver_id=${CONFIG.driverId}&waybill_id=${CONFIG.waybillId || ''}`)
            .then(r => r.json())
            .then(data => {
                if (!data || !data.logs) return;
                const container = document.getElementById('eld-timeline-container');
                const countBadge = document.getElementById('log-count-badge');
                const timelineCount = document.getElementById('timeline-count');

                if (countBadge) countBadge.textContent = data.logs.length;
                if (timelineCount) timelineCount.textContent = `${data.logs.length} entries`;

                if (!container) return;
                if (data.logs.length === 0) {
                    container.innerHTML = `<div id="no-logs-msg" class="text-center text-muted py-4 small">No activity recorded yet for this shift.</div>`;
                    return;
                }

                container.innerHTML = data.logs.map(l => {
                    const badgeClass = l.badge_class || l.badge || 'bg-primary';
                    const eventLabel = l.event_label || l.label || 'Duty Log';
                    const timeString = l.time || l.created_at || '';
                    const notesText = l.notes || '';

                    return `
                        <div class="log-timeline-item">
                            <div class="log-timeline-dot"></div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge ${badgeClass}" style="font-size: 10px;">${eventLabel}</span>
                                <small class="text-primary font-monospace fw-bold" style="font-size: 11px;">${timeString}</small>
                            </div>
                            ${notesText ? `<div class="text-dark small mb-1 fw-medium">${notesText}</div>` : ''}
                            ${l.latitude && l.longitude ? `
                                <div class="text-muted font-monospace" style="font-size: 10px;">
                                    <i class="ri-map-pin-line text-primary"></i> GPS: ${Number(l.latitude).toFixed(4)}, ${Number(l.longitude).toFixed(4)}
                                    ${l.speed_kmh > 0 ? `• ${l.speed_kmh} km/h` : ''}
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');
            })
            .catch(() => {});
    }

    // ── PWA Setup & Events ──
    function setupPWA() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/driver-sw.js').catch(() => {});
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPwaPrompt = e;
            const btn = document.getElementById('pwa-install-btn');
            if (btn) {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                btn.title = "Click to install app now!";
            }
        });

        window.addEventListener('appinstalled', () => {
            showFloatingToast('PWA Installed', 'NexAuto ELD successfully added to your device.', 'success');
            deferredPwaPrompt = null;
        });
    }

    // ── Initialization on Page Load ──
    document.addEventListener('DOMContentLoaded', function() {
        initLeafletMap();
        setupDOMEvents();
        setupPWA();
        updateTimerUI();

        if (timerState.isRunning) {
            startTimerTick();
        } else if (!CONFIG.isWaybillCompleted && !CONFIG.isDelivered) {
            // Auto-open modal on fresh login only if not completed/delivered, and not already dismissed in this session
            const dismissedKey = 'startModalDismissed_' + (CONFIG.waybillId || 'general');
            if (!sessionStorage.getItem(dismissedKey)) {
                const modalEl = document.getElementById('initialStartModal');
                if (modalEl) {
                    const startModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    startModal.show();
                }
            }
        }

        const startModalEl = document.getElementById('initialStartModal');
        if (startModalEl) {
            startModalEl.addEventListener('hidden.bs.modal', function () {
                const dismissedKey = 'startModalDismissed_' + (CONFIG.waybillId || 'general');
                sessionStorage.setItem(dismissedKey, '1');
            });
        }

        // Global backdrop cleaner to prevent screen darkening on repeated modal triggers
        document.addEventListener('hidden.bs.modal', function () {
            if (document.querySelectorAll('.modal.show').length === 0) {
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        });

        initWialonTracking();

        // Background real-time sync every 15 seconds (updates waybill status, plan, and logs)
        setInterval(() => syncRealTimeState(false), 15000);
    });

})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
