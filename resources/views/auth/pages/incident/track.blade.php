<x-layouts.auth page-title="{{ __('Track Incidents & Live Fleet Map') }}" sub-title="{{ __('Fleet Risk Mapping, Real-time Multi-Incident Pinpointing & Vehicle Tracking') }}">
    @php
        $wialonHostUrl = rtrim($wialonHost ?: config('services.wialon.host', 'https://hst-api.wialon.eu'), '/');
    @endphp

    <!-- Wialon Web SDK -->
    <script src="{{ $wialonHostUrl }}/wsdk/script/wialon.js"></script>

    <!-- MapLibre GL JS -->
    <link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

    <!-- RemixIcon & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:        #2563eb;
            --primary-dark:   #1d4ed8;
            --primary-soft:   #eff6ff;
            --surface:        #ffffff;
            --surface-subtle: #f8fafc;
            --bg:             #f1f5f9;
            --border:         #e2e8f0;
            --text:           #0f172a;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --radius-lg:      16px;
            --radius-md:      12px;
            --radius-sm:      8px;
            --shadow-sm:      0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:      0 4px 14px rgba(15,23,42,0.08);
            --shadow-lg:      0 10px 25px -3px rgba(15,23,42,0.12);

            /* Category Colors */
            --c-accident:     #ef4444;
            --c-breakdown:    #f97316;
            --c-bogged:       #d97706;
            --c-impound:      #8b5cf6;
            --c-detention:    #dc2626;
            --c-diversion:    #0284c7;
            --c-haulage:      #ea580c;
            --c-shortage:     #d97706;
            --c-breath:       #e11d48;
            --c-misconduct:   #b91c1c;
            --c-vehicle:      #10b981;
            --c-stopped:      #64748b;
            --c-offline:      #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: var(--bg);
        }

        .map-wrapper {
            max-width: 1680px;
            margin: 0 auto;
            padding-bottom: 24px;
        }

        /* ── Top Header ── */
        .map-header {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 14px 20px;
            margin-bottom: 12px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .header-title-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(37,99,235,0.25);
        }

        .header-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 3px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            padding: 2px 10px;
            border-radius: 999px;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            color: var(--text-muted);
            font-weight: 500;
        }
        .meta-pill strong {
            color: var(--text);
        }

        .header-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            font-size: 13px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .action-btn:hover {
            background: var(--surface-subtle);
            border-color: #cbd5e1;
            color: var(--primary);
        }
        .action-btn.btn-primary-action {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .action-btn.btn-primary-action:hover {
            background: var(--primary-dark);
        }

        /* ── DEDICATED ON-PAGE FILTER BAR (SEAMLESS, INSTANT) ── */
        .map-interactive-filter-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 12px 16px;
            margin-bottom: 12px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filter-controls-row {
            display: grid;
            grid-template-columns: 2fr 1.3fr 1.3fr 1.3fr 1.2fr 1.2fr auto;
            gap: 10px;
            align-items: center;
        }

        @media (max-width: 1400px) {
            .filter-controls-row {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }

        .filter-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .filter-input-group i.search-icon {
            position: absolute;
            left: 11px;
            color: var(--text-faint);
            font-size: 14px;
            pointer-events: none;
        }
        .filter-search-input {
            width: 100%;
            height: 38px;
            padding: 6px 12px 6px 34px;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: var(--surface-subtle);
            font-size: 13px;
            color: var(--text);
            outline: none;
            transition: all 0.15s ease;
        }
        .filter-search-input:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }

        .filter-select-input {
            width: 100%;
            height: 38px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: var(--surface-subtle);
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            outline: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .filter-select-input:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }

        .btn-reset-filters {
            height: 38px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #dc2626;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-reset-filters:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(220,38,38,0.25);
        }

        /* Sub-row with layer chips & legend */
        .filter-sub-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 8px;
            border-top: 1px dashed var(--border);
        }

        .filter-chips-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .filter-chip:hover {
            background: #e2e8f0;
            color: var(--text);
        }
        .filter-chip.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        }
        .filter-chip .chip-count {
            background: rgba(0,0,0,0.12);
            padding: 1px 6px;
            border-radius: 999px;
            font-size: 10.5px;
        }
        .filter-chip.active .chip-count {
            background: rgba(255,255,255,0.25);
            color: white;
        }

        .legend-indicator-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 11.5px;
            color: var(--text-muted);
        }
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Map Grid & Canvas ── */
        .map-layout-grid {
            display: grid;
            grid-template-columns: 1fr 370px;
            gap: 14px;
            transition: grid-template-columns 0.3s ease;
        }
        .map-layout-grid.drawer-collapsed {
            grid-template-columns: 1fr 0px;
        }
        .map-layout-grid.drawer-collapsed .side-info-panel {
            display: none;
        }

        @media (max-width: 1180px) {
            .map-layout-grid {
                grid-template-columns: 1fr;
            }
        }

        .map-canvas-container {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            min-height: 680px;
            height: calc(100vh - 290px);
        }

        #incident-fleet-map {
            width: 100%;
            height: 100%;
        }

        /* Floating Status Overlays */
        .map-overlay-status {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 10;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border);
            padding: 6px 14px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            font-weight: 600;
            box-shadow: var(--shadow-md);
        }
        .status-dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.25);
            animation: pulseDot 2s infinite;
        }
        @keyframes pulseDot {
            0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.5); }
            70% { box-shadow: 0 0 0 7px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }

        .map-quick-tools {
            position: absolute;
            bottom: 20px;
            right: 14px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .tool-round-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: all 0.15s ease;
        }
        .tool-round-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        /* ── FLASHING & PULSING INCIDENT MARKERS ── */
        .marker-incident-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .marker-incident-bubble {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            border: 2.5px solid #ffffff;
            box-shadow: 0 4px 14px rgba(0,0,0,0.38);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 2;
        }
        .marker-incident-container:hover .marker-incident-bubble {
            transform: scale(1.22);
        }
        .marker-incident-pulse {
            position: absolute;
            border-radius: 50%;
            opacity: 0.85;
            width:38px;
            height:38px;
            animation: incidentPulse 1.8s infinite cubic-bezier(0.24, 0, 0.38, 1);
            z-index: 1;
        }
        @keyframes incidentPulse {
            0% { transform: scale(0.85); opacity: 0.95; }
            60% { transform: scale(1.7); opacity: 0; }
            100% { transform: scale(1.7); opacity: 0; }
        }
        .marker-incident-tag {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(4px);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
            margin-top: 4px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.18);
        }

        /* ── LIVE VEHICLE FLEET MARKER ── */
        .marker-vehicle-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .marker-vehicle-body {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #1e293b;
            border: 3px solid #22c55e;
            color: #22c55e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        .marker-vehicle-body.moving {
            border-color: #22c55e;
            color: #22c55e;
        }
        .marker-vehicle-body.stopped {
            border-color: #64748b;
            color: #94a3b8;
        }
        .marker-vehicle-body.slow {
            border-color: #f59e0b;
            color: #f59e0b;
        }
        .vehicle-heading-arrow {
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 9px solid #22c55e;
            z-index: 3;
        }
        .vehicle-pulse-ring {
            position: absolute;
            width:38px;
            height:38px;
            border-radius: 50%;
            border: 2px solid #22c55e;
            opacity: 0.8;
            animation: vehiclePing 1.8s infinite;
            z-index: 1;
        }
        @keyframes vehiclePing {
            0% { transform: scale(0.9); opacity: 0.9; }
            80% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.6); opacity: 0; }
        }
        .marker-vehicle-tag {
            background: #0f172a;
            color: #38bdf8;
            font-size: 10.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            margin-top: 4px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            border: 1px solid rgba(56, 189, 248, 0.4);
        }

        /* ── Rich MapLibre Popup Styling ── */
        .maplibregl-popup-content {
            padding: 0;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            font-family: 'Inter', sans-serif;
            min-width: 300px;
            max-width: 380px;
        }
        .maplibregl-popup-close-button {
            top: 8px !important;
            right: 8px !important;
            width: 24px !important;
            height: 24px !important;
            border-radius: 50% !important;
            background: rgba(0,0,0,0.2) !important;
            color: white !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            line-height: 1 !important;
            border: 0 !important;
            cursor: pointer !important;
            transition: background 0.15s !important;
        }
        .maplibregl-popup-close-button:hover {
            background: rgba(0,0,0,0.5) !important;
        }

        .popup-card {
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        .popup-header {
            padding: 12px 36px 12px 14px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }
        .popup-header-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .popup-header-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }
        .popup-header-sub {
            font-size: 11px;
            opacity: 0.9;
            margin: 2px 0 0;
        }
        .popup-body {
            padding: 14px;
            background: white;
            font-size: 12.5px;
            line-height: 1.5;
            color: var(--text);
        }
        .popup-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 7px;
        }
        .popup-row:last-child {
            margin-bottom: 0;
        }
        .popup-label {
            width: 82px;
            flex-shrink: 0;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 11.5px;
        }
        .popup-value {
            flex-grow: 1;
            font-weight: 500;
            word-break: break-word;
        }
        .popup-footer {
            padding: 10px 14px;
            background: var(--surface-subtle);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .popup-btn {
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 6px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }
        .popup-btn:hover {
            background: var(--primary-dark);
            color: white;
        }

        /* ── Status Pills ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.4;
            white-space: nowrap;
            border: 1px solid transparent;
            user-select: none;
        }
        .status-pill.status-resolved,
        .status-pill.status-released,
        .status-pill.status-recovered,
        .status-pill.status-completed,
        .status-pill.status-closed {
            background-color: #f0fdf4 !important;
            border-color: #86efac !important;
            color: #15803d !important;
        }
        .status-pill.status-impounded,
        .status-pill.status-detained,
        .status-pill.status-pending,
        .status-pill.status-open,
        .status-pill.status-stuck {
            background-color: #fef2f2 !important;
            border-color: #fca5a5 !important;
            color: #991b1b !important;
        }
        .status-pill.status-in_progress,
        .status-pill.status-active,
        .status-pill.status-reviewed {
            background-color: #fefce8 !important;
            border-color: #fde047 !important;
            color: #a16207 !important;
        }
        .status-pill.status-diversion {
            background-color: #eff6ff !important;
            border-color: #93c5fd !important;
            color: #1d4ed8 !important;
        }

        /* ── Side Info Drawer ── */
        .side-info-panel {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .panel-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px;
            box-shadow: var(--shadow-sm);
        }

        .panel-nav-tabs {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--surface-subtle);
            border-radius: 8px;
            padding: 3px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
        }
        .panel-nav-tab {
            flex: 1;
            text-align: center;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            color: var(--text-muted);
            border: none;
            background: transparent;
            transition: all 0.15s ease;
        }
        .panel-nav-tab.active {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .drawer-search-box {
            position: relative;
            margin-bottom: 10px;
        }
        .drawer-search-box input {
            width: 100%;
            padding: 7px 10px 7px 32px;
            border-radius: 6px;
            border: 1px solid var(--border);
            font-size: 12.5px;
            background: var(--surface-subtle);
            outline: none;
        }
        .drawer-search-box input:focus {
            border-color: var(--primary);
            background: white;
        }
        .drawer-search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-faint);
            font-size: 13px;
        }

        .drawer-list-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 480px;
            overflow-y: auto;
            padding-right: 2px;
        }

        .drawer-item-card {
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 9px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .drawer-item-card:hover {
            background: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
            transform: translateX(2px);
        }

        .drawer-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
    </style>

    <div class="map-wrapper">
        <!-- ── Top Header ── -->
        <div class="map-header">
            <div class="header-title-box">
                <div class="header-icon-badge">
                    <i class="ri-radar-line"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark" style="font-size: 18px">
                        {{ __('Track Incidents & Fleet Live Map') }}
                        <span class="badge bg-primary fs-6 ms-2 px-2 py-1 align-middle" id="total-badge-count">
                            {{ $totalIncidents }} {{ __('Incidents') }} &bull; {{ $totalVehicles }} {{ __('Vehicles') }}
                        </span>
                    </h4>
                    <div class="header-meta">
                        <div class="meta-pill">
                            <i class="ri-alarm-warning-line text-danger"></i>
                            <span>{{ __('Active Incidents:') }} <strong id="kpi-incidents-count">{{ $incidentCount }}</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-user-star-line text-warning"></i>
                            <span>{{ __('Driver Conduct:') }} <strong id="kpi-behavior-count">{{ $behaviorCount }}</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-truck-line text-success"></i>
                            <span>{{ __('Fleet Assets:') }} <strong id="kpi-vehicles-count">{{ $totalVehicles }}</strong></span>
                        </div>
                        <div class="meta-pill" id="filter-status-indicator" style="display: none; background: #eff6ff; border-color: #93c5fd; color: #1d4ed8;">
                            <i class="ri-filter-3-fill text-primary"></i>
                            <span id="filter-status-text">Filters Active</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-actions">
                <button type="button" class="action-btn" id="btn-fit-all" title="Fit all incidents and vehicles in view">
                    <i class="ri-focus-3-line"></i> {{ __('Fit All') }}
                </button>
                <button type="button" class="action-btn" id="btn-focus-incidents" title="Focus on Incidents">
                    <i class="ri-alarm-warning-line text-danger"></i> {{ __('Incidents') }}
                </button>
                <button type="button" class="action-btn" id="btn-focus-vehicles" title="Focus on Fleet Vehicles">
                    <i class="ri-truck-line text-success"></i> {{ __('Vehicles') }}
                </button>
                <button type="button" class="action-btn" id="btn-toggle-drawer" title="Toggle List Drawer">
                    <i class="ri-layout-right-line"></i> {{ __('List') }}
                </button>
                <button type="button" class="action-btn btn-primary-action" id="btn-fullscreen-toggle" title="Toggle Fullscreen">
                    <i class="ri-fullscreen-line" id="fs-icon"></i>
                </button>
            </div>
        </div>

        <!-- ── DEDICATED SEAMLESS FILTER CONTROL BAR (DIRECTLY ON MAP AREA) ── -->
        <div class="map-interactive-filter-bar">
            <!-- Row 1: Direct Filter Selects & Keyword Search -->
            <div class="filter-controls-row">
                <!-- 1. Search Keyword -->
                <div class="filter-input-group">
                    <i class="ri-search-line search-icon"></i>
                    <input type="text" id="filter-keyword" class="filter-search-input" placeholder="Search waybill #, vehicle, location, driver, incident…" autocomplete="off">
                </div>

                <!-- 2. Waybill Filter -->
                <div>
                    <select id="filter-waybill" class="filter-select-input" title="Filter by Waybill Number">
                        <option value="">-- All Waybills ({{ count($filterOptions['waybills']) }}) --</option>
                        @foreach($filterOptions['waybills'] as $wb)
                            <option value="{{ $wb }}">{{ $wb }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Vehicle / Fleet Filter -->
                <div>
                    <select id="filter-fleet" class="filter-select-input" title="Filter by Fleet Asset">
                        <option value="">-- All Vehicles ({{ count($filterOptions['fleets']) }}) --</option>
                        @foreach($filterOptions['fleets'] as $fl)
                            <option value="{{ $fl }}">{{ $fl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Incident Type Filter -->
                <div>
                    <select id="filter-type" class="filter-select-input" title="Filter by Incident Type">
                        <option value="">-- All Incident Types --</option>
                        <option value="accident">Accident ({{ $filterOptions['typeCounts']['accident'] ?? 0 }})</option>
                        <option value="vehicle_breakdown">Vehicle Breakdown ({{ $filterOptions['typeCounts']['vehicle_breakdown'] ?? 0 }})</option>
                        <option value="vehicle_bogged_down">Bogged Down ({{ $filterOptions['typeCounts']['vehicle_bogged_down'] ?? 0 }})</option>
                        <option value="impoundment">Impoundment ({{ $filterOptions['typeCounts']['impoundment'] ?? 0 }})</option>
                        <option value="illegal_detention">Illegal Detention ({{ $filterOptions['typeCounts']['illegal_detention'] ?? 0 }})</option>
                        <option value="route_diversion">Route Diversion ({{ $filterOptions['typeCounts']['route_diversion'] ?? 0 }})</option>
                        <option value="illegal_haulage">Illegal Haulage ({{ $filterOptions['typeCounts']['illegal_haulage'] ?? 0 }})</option>
                        <option value="illegal_shortage">Illegal Shortage ({{ $filterOptions['typeCounts']['illegal_shortage'] ?? 0 }})</option>
                        <option value="breathaliser_test">Breathaliser Test ({{ $filterOptions['typeCounts']['breathaliser_test'] ?? 0 }})</option>
                        <option value="driver_misconduct">Driver Misconduct ({{ $filterOptions['typeCounts']['driver_misconduct'] ?? 0 }})</option>
                    </select>
                </div>

                <!-- 5. Status Filter -->
                <div>
                    <select id="filter-status" class="filter-select-input" title="Filter by Status">
                        <option value="">-- All Statuses --</option>
                        <option value="pending">Pending / Stuck / Open</option>
                        <option value="in_progress">In Progress / Active</option>
                        <option value="resolved">Resolved / Released / Closed</option>
                    </select>
                </div>

                <!-- 6. Location Filter -->
                <div>
                    <select id="filter-location" class="filter-select-input" title="Filter by Location">
                        <option value="">-- All Locations ({{ count($filterOptions['locations']) }}) --</option>
                        @foreach($filterOptions['locations'] as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 7. Reset Filters Button -->
                <div>
                    <button type="button" id="btn-reset-filters" class="btn-reset-filters" title="Reset all search and filters">
                        <i class="ri-refresh-line"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Row 2: Layer Toggles & Color Legend -->
            <div class="filter-sub-row">
                <div class="filter-chips-group">
                    <div class="filter-chip active" data-layer="all">
                        <i class="ri-apps-2-line"></i> {{ __('All Layers') }}
                    </div>
                    <div class="filter-chip active" data-layer="incidents">
                        <i class="ri-alarm-warning-fill text-danger"></i> {{ __('Incidents') }}
                        <span class="chip-count" id="chip-count-incidents">{{ $incidentCount }}</span>
                    </div>
                    <div class="filter-chip active" data-layer="driver_behavior">
                        <i class="ri-user-star-line text-warning"></i> {{ __('Driver Inspection') }}
                        <span class="chip-count" id="chip-count-behavior">{{ $behaviorCount }}</span>
                    </div>
                    <div class="filter-chip active" data-layer="vehicles">
                        <i class="ri-truck-fill text-success"></i> {{ __('Live Fleet') }}
                        <span class="chip-count" id="chip-count-vehicles">{{ $totalVehicles }}</span>
                    </div>
                </div>

                <div class="legend-indicator-group">
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-accident);"></span> {{ __('Accident') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-breakdown);"></span> {{ __('Breakdown') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-bogged);"></span> {{ __('Bogged') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-impound);"></span> {{ __('Impound') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-detention);"></span> {{ __('Detention') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-diversion);"></span> {{ __('Diversion') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: var(--c-vehicle);"></span> {{ __('Live Unit') }}</div>
                </div>
            </div>
        </div>

        <!-- ── Main Map Canvas & Sidebar Grid ── -->
        <div class="map-layout-grid" id="map-grid-container">
            <div class="map-canvas-container" id="map-container-box">
                <!-- Status Pill on Map -->
                <div class="map-overlay-status" id="live-status-pill">
                    <span class="status-dot-pulse" id="status-dot"></span>
                    <span id="status-text">{{ __('Connecting Live GPS…') }}</span>
                </div>

                <!-- Floating Quick Tools -->
                <div class="map-quick-tools">
                    <button type="button" class="tool-round-btn" onclick="fitAllMarkers()" title="Center All Markers">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="tool-round-btn" onclick="focusVehicles()" title="Locate Fleet Vehicles">
                        <i class="ri-truck-line text-success"></i>
                    </button>
                    <button type="button" class="tool-round-btn" onclick="focusIncidents()" title="Locate Incidents">
                        <i class="ri-alarm-warning-line text-danger"></i>
                    </button>
                </div>

                <!-- Map Canvas -->
                <div id="incident-fleet-map"></div>
            </div>

            <!-- ── Side Panel: Incident Drawer & Live Telemetry ── -->
            <div class="side-info-panel" id="side-info-panel">
                <div class="panel-card h-100 d-flex flex-column">
                    <!-- Nav Tabs: Incidents vs Vehicles -->
                    <div class="panel-nav-tabs">
                        <button type="button" class="panel-nav-tab active" data-tab="incidents" onclick="switchDrawerTab('incidents')">
                            <i class="ri-alarm-warning-line me-1"></i> {{ __('Incidents') }} (<span id="drawer-inc-count">{{ $totalIncidents }}</span>)
                        </button>
                        <button type="button" class="panel-nav-tab" data-tab="vehicles" onclick="switchDrawerTab('vehicles')">
                            <i class="ri-truck-line me-1"></i> {{ __('Fleet') }} (<span id="drawer-veh-count">{{ $totalVehicles }}</span>)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="drawer-search-box">
                        <i class="ri-search-line"></i>
                        <input type="text" id="drawer-search-input" placeholder="Search in list…" oninput="filterDrawerList(this.value)" autocomplete="off">
                    </div>

                    <!-- Incidents List Tab Content -->
                    <div class="drawer-list-container flex-grow-1" id="drawer-incidents-list">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Vehicles List Tab Content -->
                    <div class="drawer-list-container flex-grow-1" id="drawer-vehicles-list" style="display: none;">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MapLibre & Live Tracking Script ── -->
    <script>
        // Server variables
        const WIALON_TOKEN     = @json($wialonToken);
        const WIALON_HOST      = @json($wialonHost ?: 'https://hst-api.wialon.eu');
        const GEOAPIFY_KEY     = @json($geoapifyApiKey);
        const APP_ENV          = @json($appEnv);
        const ALL_INCIDENTS    = @json($incidents);
        const ALL_VEHICLES     = @json($fleetVehicles);

        let map = null;
        let mapReady = false;
        let session = null;

        // Active filtered datasets
        let activeIncidents = [...ALL_INCIDENTS];
        let activeVehicles = [...ALL_VEHICLES];

        // Marker collections
        let incidentMarkerObjects = [];
        let vehicleMarkerObjects = {}; // key: vehicle.id or unitId -> { marker, el, data, lngLat }

        // Layer visibility state
        let layerVisibility = {
            incidents: true,
            driver_behavior: true,
            vehicles: true
        };

        // OpenStreetMap raster style for MapLibre GL
        const MAP_STYLE = {
            version: 8,
            sources: {
                'osm-tiles': {
                    type: 'raster',
                    tiles: [
                        'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'
                    ],
                    tileSize: 256,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }
            },
            layers: [
                {
                    id: 'osm-tiles-layer',
                    type: 'raster',
                    source: 'osm-tiles',
                    minzoom: 0,
                    maxzoom: 19
                }
            ]
        };

        document.addEventListener('DOMContentLoaded', function () {
            initMap();
            initFilterEvents();
            initFullscreen();
            renderDrawerLists();

            if (WIALON_TOKEN) {
                initWialonSession();
            } else {
                setVehicleStatusPill('warning', 'Using System Telemetry');
            }
        });

        /* ============================================================
           1. MAP INITIALIZATION (MapLibre GL JS)
           ============================================================ */
        function initMap() {
            map = new maplibregl.Map({
                container: 'incident-fleet-map',
                style: MAP_STYLE,
                center: [69.3451, 30.3753], // Pakistan/Regional center default
                zoom: 5.5
            });

            map.addControl(new maplibregl.NavigationControl(), 'top-right');
            map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

            map.on('load', function () {
                mapReady = true;
                renderIncidentMarkers();
                renderVehicleMarkers();
                fitAllMarkers();
            });
        }

        /* ============================================================
           2. RENDER FLASHING INCIDENT MARKERS
           ============================================================ */
        function renderIncidentMarkers() {
            // Remove existing incident markers
            incidentMarkerObjects.forEach(obj => obj.marker.remove());
            incidentMarkerObjects = [];

            activeIncidents.forEach((inc, index) => {
                if (inc.lat === null || inc.lng === null || isNaN(inc.lat) || isNaN(inc.lng)) {
                    return;
                }

                // Check layer visibility
                if (!layerVisibility[inc.category] && inc.category !== 'all') {
                    return;
                }

                const markerEl = document.createElement('div');
                markerEl.className = 'marker-incident-container';
                markerEl.dataset.category = inc.category;
                markerEl.dataset.typeKey = inc.type_key;

                markerEl.innerHTML = `
                    <div class="marker-incident-pulse" style="background: ${inc.color};"></div>
                    <div class="marker-incident-bubble" style="background: ${inc.color};">
                        <i class="${inc.icon}"></i>
                    </div>
                    <div class="marker-incident-tag">${escapeHtml(inc.type_label)}</div>
                `;

                // Build rich interactive popup
                let extraRowHtml = '';
                if (inc.waybill_number) {
                    extraRowHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Waybill:</span>
                            <span class="popup-value fw-bold text-primary">#${escapeHtml(inc.waybill_number)}</span>
                        </div>
                    `;
                }
                if (inc.fleet_no && inc.fleet_no !== '—') {
                    extraRowHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Vehicle:</span>
                            <span class="popup-value fw-semibold">${escapeHtml(inc.fleet_no)} (${escapeHtml(inc.truck_no || '')})</span>
                        </div>
                    `;
                }
                if (inc.driver_name && inc.driver_name !== '—') {
                    extraRowHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Driver:</span>
                            <span class="popup-value">${escapeHtml(inc.driver_name)}</span>
                        </div>
                    `;
                }
                if (inc.detail_type) {
                    extraRowHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Detail:</span>
                            <span class="popup-value fw-semibold">${escapeHtml(inc.detail_type)}</span>
                        </div>
                    `;
                }
                if (inc.damage_cost) {
                    extraRowHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Damage Cost:</span>
                            <span class="popup-value text-danger fw-bold">$${escapeHtml(inc.damage_cost)}</span>
                        </div>
                    `;
                }
                if (inc.fatality) {
                    extraRowHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Fatality:</span>
                            <span class="popup-value badge bg-danger">${escapeHtml(inc.fatality)}</span>
                        </div>
                    `;
                }

                const statusPillClass = inc.status_color === 'success' ? 'status-resolved' : (inc.status_color === 'warning' ? 'status-in_progress' : 'status-impounded');
                const statusPillHtml = `
                    <span class="status-pill ${statusPillClass}" style="font-size: 11px; padding: 3px 10px;">
                        <span>${escapeHtml(inc.status_label)}</span>
                    </span>
                `;

                let waybillLinkHtml = '';
                if (inc.waybill_map_url) {
                    waybillLinkHtml = `
                        <a href="${inc.waybill_map_url}" target="_blank" class="action-btn py-1 px-2" style="font-size: 11.5px;">
                            <i class="ri-route-line text-primary"></i> Track Waybill
                        </a>
                    `;
                }

                const popupHtml = `
                    <div class="popup-card">
                        <div class="popup-header" style="background: ${inc.color};">
                            <div class="popup-header-icon"><i class="${inc.icon}"></i></div>
                            <div style="flex-grow: 1; min-width: 0;">
                                <h6 class="popup-header-title text-truncate">${escapeHtml(inc.type_label)}</h6>
                                <p class="popup-header-sub">${escapeHtml(inc.category_label)}</p>
                            </div>
                        </div>
                        <div class="popup-body">
                            <div class="popup-row" style="align-items: center;">
                                <span class="popup-label">Status:</span>
                                <span class="popup-value">${statusPillHtml}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Date:</span>
                                <span class="popup-value">${escapeHtml(inc.date)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Location:</span>
                                <span class="popup-value fw-medium">${escapeHtml(inc.location)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Coordinates:</span>
                                <span class="popup-value font-monospace">${inc.lat.toFixed(5)}, ${inc.lng.toFixed(5)}</span>
                            </div>
                            ${extraRowHtml}
                            <div class="popup-row mt-1">
                                <span class="popup-label">Description:</span>
                                <span class="popup-value text-muted" style="font-size: 12px;">${escapeHtml(inc.description)}</span>
                            </div>
                        </div>
                        <div class="popup-footer">
                            <div>${waybillLinkHtml}</div>
                            <a href="${inc.show_url}" target="_blank" class="popup-btn">
                                <i class="ri-external-link-line"></i> View Incident Record
                            </a>
                        </div>
                    </div>
                `;

                const popup = new maplibregl.Popup({ offset: 28 }).setHTML(popupHtml);

                const marker = new maplibregl.Marker({ element: markerEl, anchor: 'center' })
                    .setLngLat([inc.lng, inc.lat])
                    .setPopup(popup)
                    .addTo(map);

                incidentMarkerObjects.push({
                    id: inc.id,
                    index: index,
                    marker: marker,
                    category: inc.category,
                    type_key: inc.type_key,
                    data: inc,
                    lngLat: [inc.lng, inc.lat]
                });
            });
        }

        /* ============================================================
           3. RENDER ALL FLEET VEHICLE MARKERS
           ============================================================ */
        function renderVehicleMarkers() {
            // Remove existing vehicle markers
            Object.values(vehicleMarkerObjects).forEach(v => v.marker.remove());
            vehicleMarkerObjects = {};

            if (!layerVisibility.vehicles) {
                return;
            }

            activeVehicles.forEach(veh => {
                if (veh.lat === null || veh.lng === null || isNaN(veh.lat) || isNaN(veh.lng)) {
                    return;
                }

                createOrUpdateVehicleMarker(veh.id, veh.lat, veh.lng, 0, 0, 'Ready', veh.fleet_no, veh.truck_no, veh.driver_name, veh.waybill_number, veh.waybill_map_url, veh.vehicle_show_url);
            });
        }

        function createOrUpdateVehicleMarker(key, lat, lng, speed, heading, timeStr, fleetNo, truckNo, driverName, waybillNo, waybillMapUrl, vehShowUrl) {
            if (!layerVisibility.vehicles || !map || !mapReady) {
                return;
            }

            const stateClass = speed > 50 ? 'moving' : (speed > 0 ? 'slow' : 'stopped');
            const stateLabel = speed > 0 ? `Moving (${speed} km/h)` : (timeStr === 'Ready' ? 'Standing / Assigned' : 'Stopped / Parked');

            if (!vehicleMarkerObjects[key]) {
                const el = document.createElement('div');
                el.className = 'marker-vehicle-container';
                el.dataset.vehicleKey = key;

                el.innerHTML = `
                    <div class="vehicle-pulse-ring" style="border-color: ${speed > 0 ? '#22c55e' : '#94a3b8'};"></div>
                    <div class="marker-vehicle-body ${stateClass}">
                        <div class="vehicle-heading-arrow" style="transform: translateX(-50%) rotate(${heading}deg); border-bottom-color: ${speed > 0 ? '#22c55e' : '#94a3b8'};"></div>
                        <i class="ri-truck-fill"></i>
                    </div>
                    <div class="marker-vehicle-tag">${escapeHtml(fleetNo)}</div>
                `;

                let waybillRowHtml = '';
                if (waybillNo) {
                    waybillRowHtml = `
                        <div class="popup-row">
                            <span class="popup-label">Waybill:</span>
                            <span class="popup-value fw-bold text-primary">#${escapeHtml(waybillNo)}</span>
                        </div>
                    `;
                }

                let waybillActionBtn = '';
                if (waybillMapUrl) {
                    waybillActionBtn = `
                        <a href="${waybillMapUrl}" target="_blank" class="action-btn py-1 px-2" style="font-size: 11.5px;">
                            <i class="ri-route-line text-primary"></i> Track Waybill
                        </a>
                    `;
                }

                const popupHtml = `
                    <div class="popup-card">
                        <div class="popup-header" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                            <div class="popup-header-icon" style="background: rgba(34, 197, 94, 0.2); color: #22c55e;">
                                <i class="ri-truck-fill"></i>
                            </div>
                            <div style="flex-grow: 1; min-width: 0;">
                                <h6 class="popup-header-title text-truncate">${escapeHtml(fleetNo)} ${truckNo ? `(${escapeHtml(truckNo)})` : ''}</h6>
                                <p class="popup-header-sub text-success"><i class="ri-radar-fill"></i> Live Vehicle Tracking</p>
                            </div>
                        </div>
                        <div class="popup-body">
                            <div class="popup-row">
                                <span class="popup-label">Status:</span>
                                <span class="popup-value fw-bold ${speed > 0 ? 'text-success' : 'text-muted'}">${speed} km/h &bull; ${stateLabel}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Driver:</span>
                                <span class="popup-value">${escapeHtml(driverName)}</span>
                            </div>
                            ${waybillRowHtml}
                            <div class="popup-row">
                                <span class="popup-label">Heading:</span>
                                <span class="popup-value font-monospace">${heading}°</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Last GPS:</span>
                                <span class="popup-value">${escapeHtml(timeStr)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Position:</span>
                                <span class="popup-value font-monospace">${lat.toFixed(5)}, ${lng.toFixed(5)}</span>
                            </div>
                        </div>
                        <div class="popup-footer">
                            <div>${waybillActionBtn}</div>
                            <a href="${vehShowUrl}" target="_blank" class="popup-btn">
                                <i class="ri-external-link-line"></i> View Vehicle
                            </a>
                        </div>
                    </div>
                `;

                const popup = new maplibregl.Popup({ offset: 28 }).setHTML(popupHtml);

                const marker = new maplibregl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([lng, lat])
                    .setPopup(popup)
                    .addTo(map);

                vehicleMarkerObjects[key] = {
                    key: key,
                    marker: marker,
                    el: el,
                    data: { key, fleetNo, truckNo, driverName, speed, heading, lat, lng, timeStr },
                    lngLat: [lng, lat]
                };
            } else {
                // Update position & rotation
                const item = vehicleMarkerObjects[key];
                item.marker.setLngLat([lng, lat]);
                item.lngLat = [lng, lat];
                item.data.speed = speed;
                item.data.heading = heading;

                const bodyEl = item.el.querySelector('.marker-vehicle-body');
                if (bodyEl) {
                    bodyEl.className = `marker-vehicle-body ${stateClass}`;
                }
                const arrowEl = item.el.querySelector('.vehicle-heading-arrow');
                if (arrowEl) {
                    arrowEl.style.transform = `translateX(-50%) rotate(${heading}deg)`;
                    arrowEl.style.borderBottomColor = speed > 0 ? '#22c55e' : '#94a3b8';
                }
            }
        }

        /* ============================================================
           4. WIALON LIVE REAL-TIME TELEMETRY PUSH & POLL
           ============================================================ */
        function initWialonSession() {
            setVehicleStatusPill('loading', 'Connecting Wialon GPS…');
            try {
                session = wialon.core.Session.getInstance();
                session.initSession(String(WIALON_HOST).replace(/\/+$/, ''));
                session.loginToken(WIALON_TOKEN, '', function (code) {
                    if (code) {
                        setVehicleStatusPill('warning', 'Live Fleet GPS Ready');
                        return;
                    }
                    setVehicleStatusPill('online', 'Connected to Live Fleet');
                    loadWialonFleetUnits();
                });
            } catch (err) {
                console.error('Wialon session error:', err);
                setVehicleStatusPill('warning', 'Using Fleet Telemetry');
            }
        }

        function loadWialonFleetUnits() {
            const flags = wialon.item.Item.dataFlag.base |
                          wialon.item.Unit.dataFlag.lastMessage |
                          (wialon.item.Unit.dataFlag.position || 0);

            session.updateDataFlags(
                [{ type: 'type', data: 'avl_unit', flags: flags, mode: 0 }],
                function (code) {
                    if (code) return;

                    const units = session.getItems('avl_unit');
                    if (!units || !units.length) return;

                    setVehicleStatusPill('online', `Tracking ${units.length} Fleet GPS Units`);

                    units.forEach(unit => updateLiveUnitPosition(unit));

                    session.addListener('messageRegistered', function (evt) {
                        if (evt && evt.getData()) {
                            const unitId = String(evt.getData().id);
                            const unit = session.getItem(unitId);
                            if (unit) updateLiveUnitPosition(unit);
                        }
                    });

                    setInterval(() => {
                        const allUnits = session.getItems('avl_unit');
                        if (allUnits && allUnits.length) {
                            allUnits.forEach(u => updateLiveUnitPosition(u));
                        }
                    }, 5000);
                }
            );
        }

        function updateLiveUnitPosition(unit) {
            if (!unit || !map || !mapReady) return;

            const pos = unit.getPosition();
            if (!pos || typeof pos.x !== 'number' || typeof pos.y !== 'number') return;

            const unitId = String(unit.getId());
            const unitName = unit.getName() || 'Fleet Asset';
            const lng = pos.x;
            const lat = pos.y;
            const speed = Math.round(pos.s || 0);
            const heading = Math.round(pos.c || 0);
            const timeStr = pos.t ? new Date(pos.t * 1000).toLocaleTimeString() : new Date().toLocaleTimeString();

            // Match with DB vehicle
            const matchedDbVeh = ALL_VEHICLES.find(v =>
                (v.gps_unit_id && String(v.gps_unit_id) === unitId) ||
                (v.fleet_no && unitName.toLowerCase().includes(v.fleet_no.toLowerCase()))
            );

            const vehicleId = matchedDbVeh ? matchedDbVeh.id : unitId;
            const fleetNo = matchedDbVeh ? matchedDbVeh.fleet_no : unitName;
            const truckNo = matchedDbVeh ? matchedDbVeh.truck_no : '';
            const driverName = matchedDbVeh ? matchedDbVeh.driver_name : '—';
            const waybillNo = matchedDbVeh?.waybill_number ? matchedDbVeh.waybill_number : null;
            const waybillMapUrl = matchedDbVeh?.waybill_map_url ? matchedDbVeh.waybill_map_url : null;
            const vehShowUrl = matchedDbVeh?.vehicle_show_url ? matchedDbVeh.vehicle_show_url : '#';

            // Also update vehicle's known coordinates in active memory
            if (matchedDbVeh) {
                matchedDbVeh.lat = lat;
                matchedDbVeh.lng = lng;
            }

            // Check if this vehicle is included in current active filtered vehicles
            const isIncluded = activeVehicles.some(v => v.id === vehicleId || (v.fleet_no && v.fleet_no.toLowerCase() === fleetNo.toLowerCase()));
            if (isIncluded && layerVisibility.vehicles) {
                createOrUpdateVehicleMarker(vehicleId, lat, lng, speed, heading, timeStr, fleetNo, truckNo, driverName, waybillNo, waybillMapUrl, vehShowUrl);
            }
        }

        /* ============================================================
           5. INSTANT RESPONSIVE FILTERING (VEHICLES + INCIDENTS)
           ============================================================ */
        function matchesWaybill(itemWbNo, itemWbId, targetWb) {
            if (!targetWb) return true;
            if (!itemWbNo && !itemWbId) return false;
            const t = String(targetWb).trim().toLowerCase();
            const tClean = t.replace(/^wb[-_]?/i, '');

            if (itemWbId && String(itemWbId).toLowerCase() === t) return true;
            if (itemWbNo) {
                const i = String(itemWbNo).trim().toLowerCase();
                const iClean = i.replace(/^wb[-_]?/i, '');
                if (i === t || iClean === tClean) return true;
                if (iClean.length >= 2 && (iClean.includes(tClean) || tClean.includes(iClean))) return true;
                if (i.includes(t) || t.includes(i)) return true;
            }
            return false;
        }

        function matchesFleet(itemFleetNo, itemVehId, itemTruckNo, targetFleet) {
            if (!targetFleet) return true;
            const t = String(targetFleet).trim().toLowerCase();
            if (itemVehId && String(itemVehId).toLowerCase() === t) return true;
            if (itemFleetNo) {
                const f = String(itemFleetNo).trim().toLowerCase();
                if (f === t || f.includes(t) || t.includes(f)) return true;
            }
            if (itemTruckNo) {
                const trk = String(itemTruckNo).trim().toLowerCase();
                if (trk === t || trk.includes(t) || t.includes(trk)) return true;
            }
            return false;
        }

        function matchesLocation(locStr, targetLoc) {
            if (!targetLoc) return true;
            if (!locStr) return false;
            return String(locStr).toLowerCase().includes(String(targetLoc).toLowerCase().trim());
        }

        function matchesStatus(statusRaw, targetStatus) {
            if (!targetStatus) return true;
            const s = String(statusRaw || '').toLowerCase();
            if (targetStatus === 'resolved') {
                return ['1', 'resolved', 'recovered', 'released', 'completed', 'closed'].includes(s);
            }
            if (targetStatus === 'in_progress') {
                return ['2', 'in_progress', 'active', 'reviewed', 'recovery in progress'].includes(s);
            }
            if (targetStatus === 'pending') {
                return ['0', 'pending', 'stuck', 'open', 'impounded', 'detained', 'active diversion', 'stuck / pending'].includes(s);
            }
            return true;
        }

        function executeFilters() {
            const keyword = (document.getElementById('filter-keyword').value || '').trim().toLowerCase();
            const waybill = (document.getElementById('filter-waybill').value || '').trim();
            const fleet = (document.getElementById('filter-fleet').value || '').trim();
            const type = (document.getElementById('filter-type').value || '').trim();
            const status = (document.getElementById('filter-status').value || '').trim();
            const location = (document.getElementById('filter-location').value || '').trim();

            const hasActiveFilters = !!(keyword || waybill || fleet || type || status || location);

            // Update UI status indicator pill
            const statusIndicator = document.getElementById('filter-status-indicator');
            if (statusIndicator) {
                statusIndicator.style.display = hasActiveFilters ? 'inline-flex' : 'none';
            }

            // A. Filter Incidents
            activeIncidents = ALL_INCIDENTS.filter(item => {
                if (status && !matchesStatus(item.status_raw, status)) return false;
                if (type && item.type_key !== type) return false;
                if (location && !matchesLocation(item.location, location)) return false;
                if (waybill && !matchesWaybill(item.waybill_number, item.waybill_id, waybill)) return false;
                if (fleet && !matchesFleet(item.fleet_no, item.vehicle_id, item.truck_no, fleet)) return false;

                if (keyword) {
                    const matchWb = matchesWaybill(item.waybill_number, item.waybill_id, keyword);
                    const matchFleet_ = matchesFleet(item.fleet_no, item.vehicle_id, item.truck_no, keyword);
                    const matchLoc = matchesLocation(item.location, keyword);
                    const matchDesc = (item.description || '').toLowerCase().includes(keyword);
                    const matchDriver = (item.driver_name || '').toLowerCase().includes(keyword);
                    const matchType = (item.type_label || '').toLowerCase().includes(keyword);
                    const matchDetail = (item.detail_type || '').toLowerCase().includes(keyword);
                    if (!matchWb && !matchFleet_ && !matchLoc && !matchDesc && !matchDriver && !matchType && !matchDetail) {
                        return false;
                    }
                }

                return true;
            });

            // B. Filter Vehicles
            activeVehicles = ALL_VEHICLES.filter(v => {
                // If Fleet is explicitly selected
                if (fleet && !matchesFleet(v.fleet_no, v.id, v.truck_no, fleet)) {
                    return false;
                }

                // If Waybill is explicitly selected
                if (waybill) {
                    const directWbMatch = matchesWaybill(v.waybill_number, v.waybill_id, waybill) ||
                        (v.all_waybill_numbers && v.all_waybill_numbers.some(wb => matchesWaybill(wb, null, waybill))) ||
                        (v.all_waybill_ids && v.all_waybill_ids.some(id => matchesWaybill(null, id, waybill)));

                    const incidentLinkMatch = activeIncidents.some(i => (i.vehicle_id === v.id || (i.fleet_no && i.fleet_no === v.fleet_no)));

                    if (!directWbMatch && !incidentLinkMatch) {
                        return false;
                    }
                }

                // If Incident Type is selected, show vehicles involved in active filtered incidents
                if (type) {
                    const involvedInType = activeIncidents.some(i => (i.vehicle_id === v.id || (i.fleet_no && i.fleet_no === v.fleet_no)));
                    if (!involvedInType) return false;
                }

                // If Status is selected, show vehicles involved in active filtered incidents
                if (status) {
                    const involvedInStatus = activeIncidents.some(i => (i.vehicle_id === v.id || (i.fleet_no && i.fleet_no === v.fleet_no)));
                    if (!involvedInStatus) return false;
                }

                // If Location is selected
                if (location) {
                    const matchOrigin = matchesLocation(v.waybill_origin, location);
                    const matchDest = matchesLocation(v.waybill_dest, location);
                    const matchAllOrigins = v.all_origins && v.all_origins.some(o => matchesLocation(o, location));
                    const matchAllDest = v.all_destinations && v.all_destinations.some(d => matchesLocation(d, location));
                    const matchIncidentLoc = activeIncidents.some(i => (i.vehicle_id === v.id || (i.fleet_no && i.fleet_no === v.fleet_no)));

                    if (!matchOrigin && !matchDest && !matchAllOrigins && !matchAllDest && !matchIncidentLoc) {
                        return false;
                    }
                }

                // If Keyword search is present
                if (keyword) {
                    const matchFleet_ = matchesFleet(v.fleet_no, v.id, v.truck_no, keyword);
                    const matchWb = matchesWaybill(v.waybill_number, v.waybill_id, keyword) ||
                        (v.all_waybill_numbers && v.all_waybill_numbers.some(wb => matchesWaybill(wb, null, keyword)));
                    const matchDriver = (v.driver_name || '').toLowerCase().includes(keyword);
                    const matchOrigin = matchesLocation(v.waybill_origin, keyword);
                    const matchDest = matchesLocation(v.waybill_dest, keyword);
                    const matchIncident = activeIncidents.some(i => (i.vehicle_id === v.id || (i.fleet_no && i.fleet_no === v.fleet_no)));

                    if (!matchFleet_ && !matchWb && !matchDriver && !matchOrigin && !matchDest && !matchIncident) {
                        return false;
                    }
                }

                return true;
            });

            // Re-render markers & drawer lists
            renderIncidentMarkers();
            renderVehicleMarkers();
            renderDrawerLists();
            fitAllMarkers();

            // Update Counts in UI
            const totalCountEl = document.getElementById('total-badge-count');
            if (totalCountEl) totalCountEl.textContent = `${activeIncidents.length} Incidents • ${activeVehicles.length} Vehicles`;

            const incCountEl = document.getElementById('drawer-inc-count');
            if (incCountEl) incCountEl.textContent = activeIncidents.length;

            const vehCountEl = document.getElementById('drawer-veh-count');
            if (vehCountEl) vehCountEl.textContent = activeVehicles.length;

            const kpiInc = document.getElementById('kpi-incidents-count');
            if (kpiInc) kpiInc.textContent = activeIncidents.filter(i => i.category === 'incident').length;

            const kpiBeh = document.getElementById('kpi-behavior-count');
            if (kpiBeh) kpiBeh.textContent = activeIncidents.filter(i => i.category === 'driver_behavior').length;

            const kpiVeh = document.getElementById('kpi-vehicles-count');
            if (kpiVeh) kpiVeh.textContent = activeVehicles.length;
        }

        function resetAllFilters() {
            document.getElementById('filter-keyword').value = '';
            document.getElementById('filter-waybill').value = '';
            document.getElementById('filter-fleet').value = '';
            document.getElementById('filter-type').value = '';
            document.getElementById('filter-status').value = '';
            document.getElementById('filter-location').value = '';
            document.getElementById('drawer-search-input').value = '';

            // Reset layer chips
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.add('active'));
            layerVisibility.incidents = true;
            layerVisibility.driver_behavior = true;
            layerVisibility.vehicles = true;

            activeIncidents = [...ALL_INCIDENTS];
            activeVehicles = [...ALL_VEHICLES];

            executeFilters();
        }

        function initFilterEvents() {
            // Instant event listeners for all filter controls
            document.getElementById('filter-keyword').addEventListener('input', executeFilters);
            document.getElementById('filter-waybill').addEventListener('change', executeFilters);
            document.getElementById('filter-fleet').addEventListener('change', executeFilters);
            document.getElementById('filter-type').addEventListener('change', executeFilters);
            document.getElementById('filter-status').addEventListener('change', executeFilters);
            document.getElementById('filter-location').addEventListener('change', executeFilters);

            // Reset button
            document.getElementById('btn-reset-filters').addEventListener('click', resetAllFilters);

            // Layer chips toggle
            document.querySelectorAll('.filter-chip').forEach(chip => {
                chip.addEventListener('click', function () {
                    const layer = this.dataset.layer;
                    if (layer === 'all') {
                        const willActivate = !this.classList.contains('active');
                        document.querySelectorAll('.filter-chip').forEach(c => {
                            if (willActivate) c.classList.add('active');
                            else c.classList.remove('active');
                        });
                        layerVisibility.incidents = willActivate;
                        layerVisibility.driver_behavior = willActivate;
                        layerVisibility.vehicles = willActivate;
                    } else {
                        this.classList.toggle('active');
                        const isActive = this.classList.contains('active');
                        if (layer === 'incidents') layerVisibility.incidents = isActive;
                        if (layer === 'driver_behavior') layerVisibility.driver_behavior = isActive;
                        if (layer === 'vehicles') layerVisibility.vehicles = isActive;
                    }

                    renderIncidentMarkers();
                    renderVehicleMarkers();
                    renderDrawerLists();
                    fitAllMarkers();
                });
            });

            // Toggle side list drawer
            document.getElementById('btn-toggle-drawer').addEventListener('click', function () {
                const grid = document.getElementById('map-grid-container');
                grid.classList.toggle('drawer-collapsed');
                setTimeout(() => { if (map) map.resize(); }, 350);
            });

            // Focus buttons
            document.getElementById('btn-fit-all').addEventListener('click', fitAllMarkers);
            document.getElementById('btn-focus-incidents').addEventListener('click', focusIncidents);
            document.getElementById('btn-focus-vehicles').addEventListener('click', focusVehicles);
        }

        /* ============================================================
           6. SIDE DRAWER LIST RENDERING & INTERACTION
           ============================================================ */
        function renderDrawerLists() {
            // A. Incidents List
            const incListContainer = document.getElementById('drawer-incidents-list');
            if (activeIncidents.length === 0) {
                incListContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="ri-checkbox-circle-line text-success fs-2 mb-1"></i>
                        <p class="small mb-0">No matching incidents found.</p>
                    </div>
                `;
            } else {
                incListContainer.innerHTML = activeIncidents.map((inc, idx) => `
                    <div class="drawer-item-card" onclick="flyToIncidentById('${inc.id}')">
                        <div class="d-flex align-items-center gap-2 min-w-0" style="overflow: hidden;">
                            <div class="drawer-item-icon" style="background: ${inc.color};">
                                <i class="${inc.icon}"></i>
                            </div>
                            <div style="overflow: hidden;">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 12.5px;">${escapeHtml(inc.type_label)}</div>
                                <div class="text-muted text-truncate" style="font-size: 11px;">
                                    ${escapeHtml(inc.date)} &bull; ${escapeHtml(inc.location)}
                                </div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    ${inc.waybill_number ? `<span class="badge bg-light text-primary border" style="font-size: 10px;">#${escapeHtml(inc.waybill_number)}</span>` : ''}
                                    ${inc.fleet_no && inc.fleet_no !== '—' ? `<span class="badge bg-light text-dark border" style="font-size: 10px;">${escapeHtml(inc.fleet_no)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <span class="status-pill ${inc.status_color === 'success' ? 'status-resolved' : (inc.status_color === 'warning' ? 'status-in_progress' : 'status-impounded')}" style="font-size: 10.5px; padding: 2px 8px;">
                            ${escapeHtml(inc.status_label)}
                        </span>
                    </div>
                `).join('');
            }

            // B. Vehicles List
            const vehListContainer = document.getElementById('drawer-vehicles-list');
            if (activeVehicles.length === 0) {
                vehListContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="ri-truck-line text-muted fs-2 mb-1"></i>
                        <p class="small mb-0">No matching vehicles found.</p>
                    </div>
                `;
            } else {
                vehListContainer.innerHTML = activeVehicles.map(v => `
                    <div class="drawer-item-card" onclick="flyToVehicleById('${v.id}')">
                        <div class="d-flex align-items-center gap-2 min-w-0" style="overflow: hidden;">
                            <div class="drawer-item-icon" style="background: #1e293b; color: #22c55e;">
                                <i class="ri-truck-fill"></i>
                            </div>
                            <div style="overflow: hidden;">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 12.5px;">${escapeHtml(v.fleet_no)} (${escapeHtml(v.truck_no || 'N/A')})</div>
                                <div class="text-muted text-truncate" style="font-size: 11px;">
                                    Driver: ${escapeHtml(v.driver_name || '—')}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border" style="font-size: 10.5px;">
                            ${escapeHtml(v.waybill_number ? '#' + v.waybill_number : 'Available')}
                        </span>
                    </div>
                `).join('');
            }
        }

        function switchDrawerTab(tabName) {
            document.querySelectorAll('.panel-nav-tab').forEach(tab => {
                if (tab.dataset.tab === tabName) tab.classList.add('active');
                else tab.classList.remove('active');
            });

            if (tabName === 'incidents') {
                document.getElementById('drawer-incidents-list').style.display = 'flex';
                document.getElementById('drawer-vehicles-list').style.display = 'none';
            } else {
                document.getElementById('drawer-incidents-list').style.display = 'none';
                document.getElementById('drawer-vehicles-list').style.display = 'flex';
            }
        }

        function filterDrawerList(query) {
            const q = query.trim().toLowerCase();
            document.querySelectorAll('#drawer-incidents-list .drawer-item-card, #drawer-vehicles-list .drawer-item-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(q) ? 'flex' : 'none';
            });
        }

        function flyToIncidentById(id) {
            const target = incidentMarkerObjects.find(obj => String(obj.id) === String(id));
            if (target && map) {
                map.flyTo({ center: target.lngLat, zoom: 14, speed: 1.4 });
                target.marker.togglePopup();
            }
        }

        function flyToVehicleById(id) {
            const veh = vehicleMarkerObjects[id];
            if (veh && map) {
                map.flyTo({ center: veh.lngLat, zoom: 15, speed: 1.4 });
                veh.marker.togglePopup();
            } else {
                const targetVeh = ALL_VEHICLES.find(v => String(v.id) === String(id));
                const name = targetVeh ? targetVeh.fleet_no : 'Selected Vehicle';
                alert(`Vehicle "${name}" is waiting for GPS signal.`);
            }
        }

        /* ============================================================
           7. ZOOM & FIT CONTROLS
           ============================================================ */
        function fitAllMarkers() {
            if (!map || !mapReady) return;

            const bounds = new maplibregl.LngLatBounds();
            let count = 0;

            if (layerVisibility.incidents || layerVisibility.driver_behavior) {
                incidentMarkerObjects.forEach(obj => {
                    bounds.extend(obj.lngLat);
                    count++;
                });
            }

            if (layerVisibility.vehicles) {
                Object.values(vehicleMarkerObjects).forEach(v => {
                    bounds.extend(v.lngLat);
                    count++;
                });
            }

            if (count === 1) {
                const center = bounds.getCenter();
                map.flyTo({ center: [center.lng, center.lat], zoom: 13.5, duration: 1000 });
            } else if (count > 1) {
                map.fitBounds(bounds, { padding: 60, maxZoom: 14, duration: 1000 });
            }
        }

        function focusIncidents() {
            if (!map || !mapReady) return;
            const bounds = new maplibregl.LngLatBounds();
            let count = 0;
            incidentMarkerObjects.forEach(obj => {
                bounds.extend(obj.lngLat);
                count++;
            });
            if (count > 0) {
                map.fitBounds(bounds, { padding: 60, maxZoom: 14, duration: 1000 });
            } else {
                alert('No incidents matching current filter.');
            }
        }

        function focusVehicles() {
            if (!map || !mapReady) return;
            const bounds = new maplibregl.LngLatBounds();
            let count = 0;
            Object.values(vehicleMarkerObjects).forEach(v => {
                bounds.extend(v.lngLat);
                count++;
            });
            if (count > 0) {
                map.fitBounds(bounds, { padding: 60, maxZoom: 15, duration: 1000 });
            } else {
                alert('No active vehicle markers found on the map.');
            }
        }

        function setVehicleStatusPill(state, text) {
            const dot = document.getElementById('status-dot');
            const txt = document.getElementById('status-text');

            if (txt) txt.textContent = text;
            if (dot) {
                if (state === 'online') {
                    dot.style.background = '#10b981';
                } else if (state === 'warning') {
                    dot.style.background = '#f59e0b';
                } else {
                    dot.style.background = '#ef4444';
                }
            }
        }

        function initFullscreen() {
            const btn = document.getElementById('btn-fullscreen-toggle');
            const icon = document.getElementById('fs-icon');
            if (!btn) return;

            btn.addEventListener('click', function () {
                const elem = document.getElementById('map-container-box');
                if (!document.fullscreenElement) {
                    elem.requestFullscreen().then(() => {
                        if (icon) icon.className = 'ri-fullscreen-exit-line';
                        setTimeout(() => { if (map) map.resize(); }, 200);
                    }).catch(err => console.error(err));
                } else {
                    document.exitFullscreen().then(() => {
                        if (icon) icon.className = 'ri-fullscreen-line';
                        setTimeout(() => { if (map) map.resize(); }, 200);
                    }).catch(err => console.error(err));
                }
            });
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</x-layouts.auth>
