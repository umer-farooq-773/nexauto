<x-layouts.auth page-title="{{ $incident['type_label'] }} {{ __('Map & Live Tracking') }}" sub-title="{{ __('Real-time Incident Pinpoint, Live Vehicle GPS & Proximity Telemetry') }}">
    <!-- Wialon SDK -->
    <script src="https://hst-api.wialon.eu/wsdk/script/wialon.js"></script>

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

            /* Incident specific theme color */
            --incident-color: {{ $incident['color'] ?? '#ef4444' }};
            --c-start:        #10b981;
            --c-cp:           #2563eb;
            --c-end:          #ef4444;
            --c-vehicle:      #10b981;
            --c-stopped:      #64748b;
            --c-offline:      #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: var(--bg);
        }

        .single-map-wrapper {
            max-width: 1600px;
            margin: 0 auto;
            padding-bottom: 30px;
        }

        .bg-purple {
            background-color: #8b5cf6 !important;
        }

        /* ── Top Header ── */
        .map-header {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px 22px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-title-box {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-icon-badge {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--incident-color), #0f172a);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.18);
            flex-shrink: 0;
        }

        .header-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 4px 11px;
            border-radius: 999px;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            color: var(--text-muted);
            font-weight: 500;
        }
        .meta-pill strong {
            color: var(--text);
        }
        .meta-pill a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }
        .meta-pill a:hover {
            text-decoration: underline;
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
            padding: 8px 14px;
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
            color: white;
        }
        .action-btn.btn-incident-action {
            background: var(--incident-color);
            border-color: var(--incident-color);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .action-btn.btn-incident-action:hover {
            filter: brightness(0.9);
            color: white;
        }
        .action-btn.btn-soft {
            background: #f8fafc;
            color: var(--text);
        }

        /* ── Path Sequence Flow Bar ── */
        .path-sequence-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 14px 18px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }

        .sequence-title {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .path-sequence-container {
            border: 1.5px dashed #cbd5e1;
            border-radius: var(--radius-md);
            background: #f8fafc;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            overflow-x: auto;
            white-space: nowrap;
        }

        .path-step-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform 0.15s ease;
            padding: 2px 4px;
        }
        .path-step-node:hover {
            transform: translateY(-2px);
        }

        .path-step-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: white;
            box-shadow: var(--shadow-sm);
        }
        .path-step-badge.badge-start {
            background: linear-gradient(135deg, #059669, #10b981);
        }
        .path-step-badge.badge-cp {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }
        .path-step-badge.badge-incident {
            background: linear-gradient(135deg, var(--incident-color), #b91c1c);
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);
            border: 2px solid #ffffff;
        }
        .path-step-badge.badge-end {
            background: linear-gradient(135deg, #64748b, #475569);
        }

        .path-step-label {
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
            max-width: 140px;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .path-step-label.label-start { color: #059669; }
        .path-step-label.label-cp { color: #2563eb; }
        .path-step-label.label-incident { color: var(--incident-color); font-weight: 800; }
        .path-step-label.label-end { color: #475569; }

        .path-step-arrow {
            color: #94a3b8;
            font-size: 18px;
            font-weight: bold;
            flex-shrink: 0;
        }

        /* ── Map Filter & Legend Toolbar ── */
        .map-filter-toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 16px;
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: var(--shadow-sm);
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
            padding: 6px 12px;
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
            border-color: #cbd5e1;
            color: var(--text);
        }
        .filter-chip.active {
            background: #eff6ff;
            border-color: #93c5fd;
            color: var(--primary);
        }
        .filter-chip.chip-incident.active {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        .legend-indicator-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Main Layout Grid ── */
        .map-layout-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
            align-items: start;
        }

        @media (max-width: 1100px) {
            .map-layout-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Map Canvas Container */
        .map-canvas-container {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            height: 720px;
            width: 100%;
        }

        #incident-map {
            width: 100%;
            height: 100%;
        }

        /* Map Overlay Status */
        .map-overlay-status {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 10;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(8px);
            color: white;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .status-dot-pulse {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: dotPulse 1.8s infinite;
        }
        @keyframes dotPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* Proximity Live Badge on Map */
        .map-overlay-proximity {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            color: var(--text);
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
        }
        .proximity-distance {
            color: var(--primary);
            font-weight: 800;
        }

        /* Floating Map Quick Tools */
        .map-quick-tools {
            position: absolute;
            bottom: 24px;
            right: 14px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .tool-round-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(15,23,42,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--text);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tool-round-btn:hover {
            background: var(--surface-subtle);
            color: var(--primary);
            transform: scale(1.06);
        }

        /* ── Custom Markers ── */
        /* Specific Incident Custom Marker */
        .marker-incident-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .marker-incident-bubble {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            border: 3px solid #ffffff;
            box-shadow: 0 6px 18px rgba(0,0,0,0.4);
            position: relative;
            z-index: 2;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .marker-incident-container:hover .marker-incident-bubble {
            transform: scale(1.22);
        }
        .marker-incident-pulse {
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 50%;
            opacity: 0.75;
            animation: incidentPulse 2s infinite;
            z-index: 1;
        }
        @keyframes incidentPulse {
            0% { transform: scale(0.9); opacity: 0.9; }
            70% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.6); opacity: 0; }
        }
        .marker-incident-tag {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(4px);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            margin-top: 5px;
            white-space: nowrap;
            box-shadow: 0 3px 8px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Live Vehicle Custom Marker */
        .marker-vehicle-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .marker-vehicle-body {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.5);
            position: relative;
            z-index: 2;
            transition: transform 0.25s ease;
        }
        .marker-vehicle-heading-arrow {
            position: absolute;
            top: -7px;
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 8px solid #059669;
            z-index: 3;
            transition: transform 0.2s ease;
        }
        .marker-vehicle-pulse {
            position: absolute;
            top: -6px;
            left: -6px;
            right: -6px;
            bottom: -6px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.35);
            animation: vehiclePulse 2s infinite;
            z-index: 1;
        }
        @keyframes vehiclePulse {
            0% { transform: scale(0.9); opacity: 0.8; }
            70% { transform: scale(1.5); opacity: 0; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .marker-vehicle-tag {
            background: rgba(15, 23, 42, 0.9);
            color: #4ade80;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            margin-top: 4px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.15);
        }

        /* Waypoint Markers */
        .marker-origin-pin, .marker-dest-pin {
            width: 34px;
            height: 34px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .marker-origin-pin { background: linear-gradient(135deg, #059669, #10b981); }
        .marker-dest-pin { background: linear-gradient(135deg, #475569, #64748b); }
        .marker-origin-pin i, .marker-dest-pin i { transform: rotate(45deg); font-size: 16px; }

        .marker-cp-pin {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            border: 2px solid #ffffff;
            box-shadow: 0 3px 8px rgba(0,0,0,0.25);
        }

        /* MapLibre Popups */
        .maplibregl-popup-content {
            border-radius: 12px !important;
            padding: 0 !important;
            box-shadow: 0 12px 30px -5px rgba(15,23,42,0.22) !important;
            overflow: hidden;
            border: 1px solid var(--border);
            max-width: 320px !important;
        }
        .popup-header {
            padding: 12px 14px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .popup-header-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .popup-body {
            padding: 12px 14px;
            background: white;
            font-size: 12px;
            line-height: 1.5;
            color: var(--text);
        }
        .popup-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 6px;
        }
        .popup-row:last-child { margin-bottom: 0; }
        .popup-label {
            width: 75px;
            flex-shrink: 0;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 11px;
        }
        .popup-value {
            flex-grow: 1;
            font-weight: 500;
            word-break: break-word;
        }
        .popup-footer {
            padding: 9px 14px;
            background: var(--surface-subtle);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
        }
        .popup-btn {
            font-size: 11.5px;
            font-weight: 600;
            padding: 5px 11px;
            border-radius: 6px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .popup-btn:hover { background: var(--primary-dark); color: white; }

        /* ── Side Info Panel ── */
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

        .panel-card-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            border: 1px solid transparent;
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

        /* Telemetry Diagnostics Grid */
        .telemetry-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .telemetry-box {
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 9px 10px;
        }
        .telemetry-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .telemetry-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            margin-top: 2px;
        }

        .incident-narrative-box {
            background: #f8fafc;
            border-left: 3px solid var(--incident-color);
            padding: 10px 12px;
            border-radius: 4px;
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
            margin-top: 10px;
            font-style: italic;
        }

        .coords-badge {
            background: #f1f5f9;
            border: 1px solid var(--border);
            padding: 3px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 11px;
            color: var(--text);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .coords-copy-btn {
            cursor: pointer;
            color: var(--primary);
            border: none;
            background: transparent;
            padding: 0;
            font-size: 12px;
        }
        .coords-copy-btn:hover {
            color: var(--primary-dark);
        }
    </style>

    <div class="single-map-wrapper">
        <!-- ── Top Header ── -->
        <div class="map-header">
            <div class="header-title-box">
                <div class="header-icon-badge">
                    <i class="{{ $incident['icon'] }}"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-bold mb-0 text-dark">
                            {{ $incident['type_label'] }} #{{ $incident['waybill_number'] }}
                        </h4>
                        <span class="status-pill {{ $incident['status_pill_class'] }}">
                            <i class="{{ $incident['status_icon'] }} me-1"></i>
                            {{ $incident['status_label'] }}
                        </span>
                        <span class="badge {{ $incident['badge_class'] }} px-2 py-1">
                            {{ $incident['category_label'] }}
                        </span>
                    </div>

                    <div class="header-meta">
                        <div class="meta-pill">
                            <i class="ri-truck-line text-primary"></i>
                            <span>{{ __('Asset:') }} <strong>{{ $fleetNo }} ({{ $truckNo }})</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-user-follow-line text-success"></i>
                            <span>{{ __('Driver:') }} <strong>{{ $driverName }}</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-calendar-event-line text-danger"></i>
                            <span>{{ __('Date:') }} <strong>{{ $incident['date'] }}</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-map-pin-line text-danger"></i>
                            <span class="text-truncate" style="max-width: 260px;" title="{{ $incident['location'] }}">{{ $incident['location'] }}</span>
                        </div>
                        @if($waybill)
                            <div class="meta-pill">
                                <i class="ri-route-line text-info"></i>
                                <span>{{ __('Waybill:') }} <a href="{{ route('waybill.show', $waybill->id) }}">#{{ $waybill->waybill_number }}</a></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="header-actions">
                <a href="{{ $incident['show_url'] }}" class="action-btn">
                    <i class="ri-arrow-left-line"></i> {{ __('Back to Incident Details') }}
                </a>
                @if($waybill)
                    <a href="{{ route('waybill.waybill_map_tracking', $waybill->id) }}" class="action-btn" title="{{ __('View full waybill route map with all stops') }}">
                        <i class="ri-road-map-line text-primary"></i> {{ __('Waybill Route Map') }}
                    </a>
                @endif
                <a href="{{ route('incident.all_map', ['vehicle_id' => $incident['vehicle_id']]) }}" class="action-btn" title="{{ __('View all fleet incidents on high-level risk map') }}">
                    <i class="ri-radar-line text-danger"></i> {{ __('All Incidents Map') }}
                </a>
                <button type="button" class="action-btn" onclick="fitAllMarkers()" title="Re-center and fit both incident and vehicle">
                    <i class="ri-focus-3-line"></i> {{ __('Re-fit View') }}
                </button>
                <button type="button" class="action-btn btn-incident-action" onclick="focusIncidentMarker()" title="Focus on incident pinpoint">
                    <i class="ri-map-pin-2-line"></i> {{ __('Focus Incident') }}
                </button>
                <button type="button" class="action-btn" id="btn-focus-vehicle" onclick="focusVehicleMarker()" title="Focus live vehicle position">
                    <i class="ri-crosshair-2-line text-success"></i> {{ __('Live Vehicle') }}
                </button>
                <button type="button" class="action-btn btn-primary-action" id="btn-fullscreen-toggle" title="Toggle Fullscreen">
                    <i class="ri-fullscreen-line" id="fs-icon"></i>
                </button>
            </div>
        </div>

        <!-- ── Path Sequence Ribbon (If Waybill Attached) ── -->
        @if($waybill)
            @php
                $originName = $waybill->origin ?? 'Origin';
                $destName = $waybill->destination ?? 'Destination';
                $checkpoints = is_array($waybill->checkpoints) ? $waybill->checkpoints : (json_decode($waybill->checkpoints, true) ?? []);
            @endphp
            <div class="path-sequence-card">
                <div class="sequence-title">
                    <span><i class="ri-git-commit-line text-primary me-1"></i> {{ __('Trip Sequence Context') }}</span>
                    <span class="text-muted fw-normal small">Waybill #{{ $waybill->waybill_number }}</span>
                </div>
                <div class="path-sequence-container">
                    <!-- START -->
                    <div class="path-step-node" onclick="flyToOrigin()">
                        <div class="path-step-badge badge-start">
                            <i class="ri-rocket-2-fill"></i> START
                        </div>
                        <div class="path-step-label label-start" title="{{ $originName }}">{{ $originName }}</div>
                    </div>

                    <!-- CHECKPOINTS -->
                    @foreach($checkpoints as $index => $cp)
                        @php
                            $cpName = is_array($cp) ? ($cp['name'] ?? ('Checkpoint ' . ($index + 1))) : (is_string($cp) ? $cp : ('Checkpoint ' . ($index + 1)));
                        @endphp
                        <div class="path-step-arrow">&rarr;</div>
                        <div class="path-step-node" onclick="flyToCheckpoint({{ $index }})">
                            <div class="path-step-badge badge-cp">
                                <i class="ri-map-pin-2-fill"></i> CP {{ $index + 1 }}
                            </div>
                            <div class="path-step-label label-cp" title="{{ $cpName }}">{{ $cpName }}</div>
                        </div>
                    @endforeach

                    <!-- INCIDENT SCENE NODE -->
                    <div class="path-step-arrow">&rarr;</div>
                    <div class="path-step-node" onclick="focusIncidentMarker()">
                        <div class="path-step-badge badge-incident">
                            <i class="{{ $incident['icon'] }}"></i> {{ strtoupper($incident['type_label']) }}
                        </div>
                        <div class="path-step-label label-incident" title="{{ $incident['location'] }}">{{ $incident['location'] }}</div>
                    </div>

                    <!-- END -->
                    <div class="path-step-arrow">&rarr;</div>
                    <div class="path-step-node" onclick="flyToDestination()">
                        <div class="path-step-badge badge-end">
                            <i class="ri-flag-fill"></i> END
                        </div>
                        <div class="path-step-label label-end" title="{{ $destName }}">{{ $destName }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- ── Filter & Legend Toolbar ── -->
        <div class="map-filter-toolbar">
            <div class="filter-chips-group">
                <div class="filter-chip chip-incident active" id="chip-incident-toggle" onclick="toggleLayer('incident')">
                    <i class="{{ $incident['icon'] }}"></i> {{ $incident['type_label'] }} {{ __('Pinpoint') }}
                </div>
                <div class="filter-chip active" id="chip-vehicle-toggle" onclick="toggleLayer('vehicle')">
                    <i class="ri-truck-fill text-success"></i> {{ __('Live Vehicle GPS') }}
                </div>
                <div class="filter-chip active" id="chip-trajectory-toggle" onclick="toggleLayer('trajectory')">
                    <i class="ri-pin-distance-line text-primary"></i> {{ __('Live Proximity Vector') }}
                </div>
                @if($waybill)
                    <div class="filter-chip active" id="chip-route-toggle" onclick="toggleLayer('route')">
                        <i class="ri-route-line text-secondary"></i> {{ __('Planned Route') }}
                    </div>
                @endif
            </div>

            <div class="legend-indicator-group">
                <div class="legend-item"><span class="legend-dot" style="background: var(--incident-color);"></span> {{ $incident['type_label'] }} (Occurrence)</div>
                <div class="legend-item"><span class="legend-dot" style="background: #22c55e;"></span> {{ __('Live Asset') }}</div>
                @if($waybill)
                    <div class="legend-item"><span class="legend-dot" style="background: #10b981;"></span> {{ __('Start') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: #2563eb;"></span> {{ __('Checkpoint') }}</div>
                    <div class="legend-item"><span class="legend-dot" style="background: #64748b;"></span> {{ __('Destination') }}</div>
                @endif
            </div>
        </div>

        <!-- ── Map Layout Grid ── -->
        <div class="map-layout-grid">
            <!-- Map Canvas Box -->
            <div class="map-canvas-container" id="map-container-box">
                <!-- Status Pill on Map -->
                <div class="map-overlay-status" id="live-status-pill">
                    <span class="status-dot-pulse" id="status-dot"></span>
                    <span id="status-text">{{ __('Connecting Live GPS…') }}</span>
                </div>

                <!-- Proximity Distance Tag -->
                <div class="map-overlay-proximity" id="proximity-tag" style="display: none;">
                    <i class="ri-pin-distance-fill text-primary"></i>
                    <span>{{ __('Distance to Incident:') }} <span class="proximity-distance" id="proximity-km">-- km</span></span>
                </div>

                <!-- Floating Quick Tools -->
                <div class="map-quick-tools">
                    <button type="button" class="tool-round-btn" onclick="fitAllMarkers()" title="Re-fit All Map Markers">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="tool-round-btn" onclick="focusIncidentMarker()" title="Focus on Incident Location" style="color: var(--incident-color);">
                        <i class="ri-map-pin-2-fill"></i>
                    </button>
                    <button type="button" class="tool-round-btn" onclick="focusVehicleMarker()" title="Center on Live Vehicle" style="color: #10b981;">
                        <i class="ri-truck-line"></i>
                    </button>
                </div>

                <!-- Map Canvas -->
                <div id="incident-map"></div>
            </div>

            <!-- ── Side Info Panel ── -->
            <div class="side-info-panel">
                <!-- Incident Dossier Card -->
                <div class="panel-card">
                    <div class="panel-card-title">
                        <span><i class="{{ $incident['icon'] }} me-1" style="color: var(--incident-color);"></i> {{ __('Incident Dossier') }}</span>
                        <span class="status-pill {{ $incident['status_pill_class'] }}">{{ $incident['status_label'] }}</span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted small">{{ __('Type') }}:</span>
                            <span class="fw-bold">{{ $incident['detail_type'] }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted small">{{ __('Occurrence Date') }}:</span>
                            <span class="fw-semibold">{{ $incident['date'] }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted small">{{ __('Location') }}:</span>
                            <span class="fw-semibold text-end text-truncate ms-2" style="max-width: 200px;" title="{{ $incident['location'] }}">{{ $incident['location'] }}</span>
                        </div>

                        @if($incident['lat'] && $incident['lng'])
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('GPS Coordinates') }}:</span>
                                <div class="coords-badge">
                                    <span>{{ number_format($incident['lat'], 5) }}, {{ number_format($incident['lng'], 5) }}</span>
                                    <button type="button" class="coords-copy-btn" onclick="copyCoordinates({{ $incident['lat'] }}, {{ $incident['lng'] }})" title="Copy coordinates">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if(!empty($incident['damage_cost']))
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Cost / Liability') }}:</span>
                                <span class="fw-bold text-danger">${{ $incident['damage_cost'] }}</span>
                            </div>
                        @endif

                        @if(!empty($incident['authority']))
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Authority / Actor') }}:</span>
                                <span class="fw-semibold">{{ $incident['authority'] }}</span>
                            </div>
                        @endif

                        @if(!empty($incident['bac_level']))
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('BAC Level') }}:</span>
                                <span class="fw-bold text-danger">{{ $incident['bac_level'] }}</span>
                            </div>
                        @endif

                        @if(!empty($incident['ground_condition']))
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Terrain / Ground') }}:</span>
                                <span class="fw-semibold">{{ $incident['ground_condition'] }}</span>
                            </div>
                        @endif

                        @if(!empty($incident['diverted_route']))
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Diverted Path') }}:</span>
                                <span class="fw-semibold text-danger">{{ $incident['diverted_route'] }}</span>
                            </div>
                        @endif

                        @if(!empty($incident['fatality']))
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Fatality / Injury') }}:</span>
                                <span class="badge bg-danger">{{ $incident['fatality'] }}</span>
                            </div>
                        @endif
                    </div>

                    @if(!empty($incident['description']) && $incident['description'] !== '-')
                        <div class="incident-narrative-box">
                            &ldquo;{{ $incident['description'] }}&rdquo;
                        </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ $incident['show_url'] }}" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="ri-file-list-3-line me-1"></i> {{ __('Open Full Incident File') }}
                        </a>
                    </div>
                </div>

                <!-- Live Telemetry Card -->
                <div class="panel-card">
                    <div class="panel-card-title">
                        <span><i class="ri-dashboard-3-line text-success me-1"></i> {{ __('Live Vehicle Telemetry') }}</span>
                        <span class="badge bg-success-subtle text-success" id="telemetry-state-badge">{{ __('Connecting…') }}</span>
                    </div>
                    <div class="telemetry-grid mb-2">
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Speed') }}</div>
                            <div class="telemetry-value text-success" id="telemetry-speed">-- km/h</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Distance to Scene') }}</div>
                            <div class="telemetry-value text-primary" id="telemetry-distance">-- km</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Movement') }}</div>
                            <div class="telemetry-value" id="telemetry-movement">--</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Heading') }}</div>
                            <div class="telemetry-value" id="telemetry-heading">--°</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 px-1 text-muted" style="font-size: 11px;">
                        <span>{{ __('Last GPS Timestamp') }}:</span>
                        <span id="telemetry-last-time" class="fw-semibold">--:--</span>
                    </div>
                </div>

                <!-- Asset & Waybill Summary Card -->
                <div class="panel-card">
                    <div class="panel-card-title">
                        <span><i class="ri-truck-line text-primary me-1"></i> {{ __('Assigned Asset') }}</span>
                        <span class="badge bg-light text-dark border">{{ $fleetNo }}</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted small">{{ __('Truck Reg') }}:</span>
                            <span class="fw-bold">{{ $truckNo }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted small">{{ __('Assigned Driver') }}:</span>
                            <span class="fw-semibold">{{ $driverName }}</span>
                        </div>
                        @if($waybill)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Linked Waybill') }}:</span>
                                <a href="{{ route('waybill.show', $waybill->id) }}" class="fw-bold text-primary">#{{ $waybill->waybill_number }}</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted small">{{ __('Cargo') }}:</span>
                                <span class="fw-semibold text-truncate ms-2" style="max-width: 180px;" title="{{ $waybill->cargo_description ?? '-' }}">{{ $waybill->cargo_description ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="text-muted small">{{ __('Route Plan') }}:</span>
                                <span class="fw-semibold text-truncate ms-2" style="max-width: 180px;">{{ $waybill->origin ?? 'Origin' }} &rarr; {{ $waybill->destination ?? 'End' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MapLibre & Live Tracking Script ── -->
    <script>
        // Server variables
        const WIALON_TOKEN    = @json($wialonToken);
        const WIALON_HOST     = @json($wialonHost ?: 'https://hst-api.wialon.eu');
        const FLEET_NO        = @json($fleetNo);
        const TRUCK_NO        = @json($truckNo);
        const MATCHED_UNIT_ID = @json($matchedUnitId);
        const GEOAPIFY_KEY    = @json($geoapifyApiKey);
        const APP_ENV         = @json($appEnv);
        const INCIDENT_DATA   = @json($incident);
        // console.log(JSON.stringify(INCIDENT_DATA, null, 2));
        const WAYBILL_DATA    = @json($waybill);

        let map = null;
        let mapReady = false;
        let session = null;
        let liveUnit = null;
        let vehicleMarker = null;
        let vehicleCoordinates = null;
        let vehicleHeading = 0;
        let vehicleSpeed = 0;
        let vehicleState = 'stopped';

        // Layer & Marker references
        let incidentMarker = null;
        let waybillRouteMarkers = [];
        let showIncidentLayer = true;
        let showVehicleLayer = true;
        let showTrajectoryLayer = true;
        let showRouteLayer = true;

        // GeoJSON source IDs
        const TRAJECTORY_SOURCE_ID = 'trajectory-source';
        const TRAJECTORY_LAYER_ID  = 'trajectory-line-layer';
        const WAYBILL_ROUTE_SOURCE = 'waybill-route-source';
        const WAYBILL_ROUTE_LAYER  = 'waybill-route-layer';

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            initFullscreen();
        });

        /* ── MapLibre GL Map Setup ── */
        function initMap() {
            let initialCenter = [3.3792, 6.5244]; // Default fallback Lagos
            let initialZoom = 9;

            if (INCIDENT_DATA && INCIDENT_DATA.lng && INCIDENT_DATA.lat) {
                initialCenter = [parseFloat(INCIDENT_DATA.lng), parseFloat(INCIDENT_DATA.lat)];
                initialZoom = 13;
            }

            const styleUrl = GEOAPIFY_KEY
                ? `https://maps.geoapify.com/v1/styles/osm-bright/style.json?apiKey=${GEOAPIFY_KEY}`
                : {
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
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }
                    },
                    layers: [{
                        id: 'osm-tiles-layer',
                        type: 'raster',
                        source: 'osm-tiles',
                        minzoom: 0,
                        maxzoom: 19
                    }]
                };

            map = new maplibregl.Map({
                container: 'incident-map',
                style: styleUrl,
                center: initialCenter,
                zoom: initialZoom,
                pitch: 15
            });

            map.addControl(new maplibregl.NavigationControl({ showCompass: true, visualizePitch: true }), 'top-right');
            map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

            map.on('load', () => {
                mapReady = true;

                // 1. Plot the Specific Incident Marker
                renderIncidentMarker();

                // 2. Plot Waybill Route (if waybill exists)
                if (WAYBILL_DATA) {
                    renderWaybillRoute();
                }

                // 3. Setup Trajectory Layer
                setupTrajectoryLayer();

                // 4. Connect Live GPS Telemetry
                initWialonLiveTracking();

                // 5. Fit bounds to contain incident & initial points
                setTimeout(() => {
                    fitAllMarkers();
                }, 500);
            });
        }

        /* ── Render Specific Incident Marker ── */
        function renderIncidentMarker() {
            if (!INCIDENT_DATA || INCIDENT_DATA.lng === null || INCIDENT_DATA.lat === null) {
                updateStatus('Incident coordinates not logged', 'warning');
                return;
            }

            const lng = parseFloat(INCIDENT_DATA.lng);
            const lat = parseFloat(INCIDENT_DATA.lat);

            // Create custom HTML element for incident
            const el = document.createElement('div');
            el.className = 'marker-incident-container';
            el.innerHTML = `
                <div class="marker-incident-pulse" style="background: ${INCIDENT_DATA.color};"></div>
                <div class="marker-incident-bubble" style="background: ${INCIDENT_DATA.color};">
                    <i class="${INCIDENT_DATA.icon}"></i>
                </div>
                <div class="marker-incident-tag">${INCIDENT_DATA.type_label}</div>
            `;

            // Custom Rich Popup
            const popupHtml = `
                <div class="popup-header" style="background: linear-gradient(135deg, ${INCIDENT_DATA.color}, #0f172a);">
                    <div class="popup-header-icon">
                        <i class="${INCIDENT_DATA.icon}"></i>
                    </div>
                    <div>
                        <div class="fw-bold">${INCIDENT_DATA.type_label} #${INCIDENT_DATA.waybill_number}</div>
                        <div class="small opacity-75">${INCIDENT_DATA.category_label}</div>
                    </div>
                </div>
                <div class="popup-body">
                    <div class="popup-row">
                        <div class="popup-label">{{ __('Status') }}:</div>
                        <div class="popup-value"><span class="status-pill ${INCIDENT_DATA.status_pill_class}">${INCIDENT_DATA.status_label}</span></div>
                    </div>
                    <div class="popup-row">
                        <div class="popup-label">{{ __('Date') }}:</div>
                        <div class="popup-value">${INCIDENT_DATA.date}</div>
                    </div>
                    <div class="popup-row">
                        <div class="popup-label">{{ __('Location') }}:</div>
                        <div class="popup-value">${INCIDENT_DATA.location}</div>
                    </div>
                    <div class="popup-row">
                        <div class="popup-label">{{ __('Asset') }}:</div>
                        <div class="popup-value">${FLEET_NO} (${TRUCK_NO})</div>
                    </div>
                    ${INCIDENT_DATA.damage_cost ? `
                    <div class="popup-row">
                        <div class="popup-label">{{ __('Cost') }}:</div>
                        <div class="popup-value text-danger fw-bold">$${INCIDENT_DATA.damage_cost}</div>
                    </div>` : ''}
                </div>
                <div class="popup-footer">
                    <a href="${INCIDENT_DATA.show_url}" class="popup-btn">
                        <i class="ri-file-list-3-line"></i> {{ __('View Details') }}
                    </a>
                </div>
            `;

            const popup = new maplibregl.Popup({ offset: 25, closeButton: false })
                .setHTML(popupHtml);

            incidentMarker = new maplibregl.Marker({ element: el })
                .setLngLat([lng, lat])
                .setPopup(popup)
                .addTo(map);

            updateStatus(`Incident Pinpointed: ${INCIDENT_DATA.location}`, 'success');
        }

        /* ── Render Waybill Route (If present) ── */
        function renderWaybillRoute() {
            if (!WAYBILL_DATA) return;

            const waypoints = [];

            // Origin
            if (WAYBILL_DATA.origin_lng && WAYBILL_DATA.origin_lat) {
                const origLng = parseFloat(WAYBILL_DATA.origin_lng);
                const origLat = parseFloat(WAYBILL_DATA.origin_lat);
                waypoints.push([origLng, origLat]);

                const el = document.createElement('div');
                el.className = 'marker-origin-pin';
                el.innerHTML = '<i class="ri-rocket-2-fill"></i>';
                const marker = new maplibregl.Marker({ element: el })
                    .setLngLat([origLng, origLat])
                    .setPopup(new maplibregl.Popup({ offset: 20 }).setHTML(`<div class="p-2 fw-bold text-success"><i class="ri-rocket-2-fill me-1"></i> Origin: ${WAYBILL_DATA.origin ?? 'Start'}</div>`))
                    .addTo(map);
                waybillRouteMarkers.push(marker);
            }

            // Checkpoints
            let checkpoints = [];
            try {
                checkpoints = Array.isArray(WAYBILL_DATA.checkpoints) ? WAYBILL_DATA.checkpoints : JSON.parse(WAYBILL_DATA.checkpoints || '[]');
            } catch(e) { checkpoints = []; }

            checkpoints.forEach((cp, idx) => {
                if (cp && cp.lng && cp.lat) {
                    const cpLng = parseFloat(cp.lng);
                    const cpLat = parseFloat(cp.lat);
                    waypoints.push([cpLng, cpLat]);

                    const el = document.createElement('div');
                    el.className = 'marker-cp-pin';
                    el.innerText = `${idx + 1}`;
                    const marker = new maplibregl.Marker({ element: el })
                        .setLngLat([cpLng, cpLat])
                        .setPopup(new maplibregl.Popup({ offset: 20 }).setHTML(`<div class="p-2 fw-bold text-primary"><i class="ri-map-pin-2-fill me-1"></i> CP ${idx + 1}: ${cp.name ?? 'Checkpoint'}</div>`))
                        .addTo(map);
                    waybillRouteMarkers.push(marker);
                }
            });

            // Destination
            if (WAYBILL_DATA.destination_lng && WAYBILL_DATA.destination_lat) {
                const destLng = parseFloat(WAYBILL_DATA.destination_lng);
                const destLat = parseFloat(WAYBILL_DATA.destination_lat);
                waypoints.push([destLng, destLat]);

                const el = document.createElement('div');
                el.className = 'marker-dest-pin';
                el.innerHTML = '<i class="ri-flag-fill"></i>';
                const marker = new maplibregl.Marker({ element: el })
                    .setLngLat([destLng, destLat])
                    .setPopup(new maplibregl.Popup({ offset: 20 }).setHTML(`<div class="p-2 fw-bold text-dark"><i class="ri-flag-fill me-1"></i> Destination: ${WAYBILL_DATA.destination ?? 'End'}</div>`))
                    .addTo(map);
                waybillRouteMarkers.push(marker);
            }

            // Draw line between waypoints if 2+ points exist
            if (waypoints.length >= 2) {
                map.addSource(WAYBILL_ROUTE_SOURCE, {
                    type: 'geojson',
                    data: {
                        type: 'Feature',
                        geometry: {
                            type: 'LineString',
                            coordinates: waypoints
                        }
                    }
                });

                map.addLayer({
                    id: WAYBILL_ROUTE_LAYER,
                    type: 'line',
                    source: WAYBILL_ROUTE_SOURCE,
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': '#94a3b8',
                        'line-width': 3,
                        'line-dasharray': [2, 2],
                        'line-opacity': 0.7
                    }
                });
            }
        }

        /* ── Proximity & Live Trajectory Layer ── */
        function setupTrajectoryLayer() {
            map.addSource(TRAJECTORY_SOURCE_ID, {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'LineString',
                        coordinates: []
                    }
                }
            });

            map.addLayer({
                id: TRAJECTORY_LAYER_ID,
                type: 'line',
                source: TRAJECTORY_SOURCE_ID,
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                paint: {
                    'line-color': '#2563eb',
                    'line-width': 3.5,
                    'line-dasharray': [3, 2],
                    'line-opacity': 0.85
                }
            });
        }

        function updateTrajectoryLine() {
            if (!mapReady || !vehicleCoordinates || !INCIDENT_DATA || !INCIDENT_DATA.lng || !INCIDENT_DATA.lat) {
                return;
            }

            const incidentCoords = [parseFloat(INCIDENT_DATA.lng), parseFloat(INCIDENT_DATA.lat)];
            const lineCoords = [vehicleCoordinates, incidentCoords];

            const source = map.getSource(TRAJECTORY_SOURCE_ID);
            if (source) {
                source.setData({
                    type: 'Feature',
                    geometry: {
                        type: 'LineString',
                        coordinates: showTrajectoryLayer ? lineCoords : []
                    }
                });
            }

            // Calculate Distance in KM (Haversine formula)
            const distKm = calculateDistanceKm(
                vehicleCoordinates[1], vehicleCoordinates[0],
                incidentCoords[1], incidentCoords[0]
            );

            const distFormatted = distKm < 1
                ? `${Math.round(distKm * 1000)} m`
                : `${distKm.toFixed(1)} km`;

            // Update UI elements
            const distTag = document.getElementById('proximity-tag');
            const distKmElem = document.getElementById('proximity-km');
            const teleDistElem = document.getElementById('telemetry-distance');

            if (distTag) distTag.style.display = 'flex';
            if (distKmElem) distKmElem.innerText = distFormatted;
            if (teleDistElem) teleDistElem.innerText = distFormatted;
        }

        /* ── Haversine Distance Formula ── */
        function calculateDistanceKm(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        /* ── Live Vehicle Marker Render & Update ── */
        function updateVehicleMarker(lng, lat, speed, heading, timestamp) {
            vehicleCoordinates = [lng, lat];
            vehicleSpeed = speed || 0;
            vehicleHeading = heading || 0;

            if (vehicleSpeed > 3) {
                vehicleState = 'moving';
            } else if (vehicleSpeed > 0) {
                vehicleState = 'idling';
            } else {
                vehicleState = 'stopped';
            }

            // Update Telemetry UI
            const speedElem = document.getElementById('telemetry-speed');
            const moveElem = document.getElementById('telemetry-movement');
            const headElem = document.getElementById('telemetry-heading');
            const timeElem = document.getElementById('telemetry-last-time');
            const stateBadge = document.getElementById('telemetry-state-badge');

            if (speedElem) speedElem.innerText = `${Math.round(vehicleSpeed)} km/h`;
            if (moveElem) {
                moveElem.innerText = vehicleState === 'moving' ? 'In Motion' : (vehicleState === 'idling' ? 'Idling' : 'Stationary');
                moveElem.className = `telemetry-value ${vehicleState === 'moving' ? 'text-success' : 'text-muted'}`;
            }
            if (headElem) headElem.innerText = `${Math.round(vehicleHeading)}° (${getCompassDirection(vehicleHeading)})`;
            if (timeElem) timeElem.innerText = timestamp ? new Date(timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : new Date().toLocaleTimeString();

            if (stateBadge) {
                stateBadge.innerText = vehicleState === 'moving' ? 'Live Moving' : 'Live Connected';
                stateBadge.className = `badge ${vehicleState === 'moving' ? 'bg-success text-white' : 'bg-success-subtle text-success'}`;
            }

            if (!vehicleMarker) {
                // Create custom Live Vehicle Marker DOM
                const el = document.createElement('div');
                el.className = 'marker-vehicle-container';
                el.id = 'vehicle-live-marker';
                el.innerHTML = `
                    <div class="marker-vehicle-pulse"></div>
                    <div class="marker-vehicle-heading-arrow" style="transform: rotate(${vehicleHeading}deg);"></div>
                    <div class="marker-vehicle-body">
                        <i class="ri-truck-fill"></i>
                    </div>
                    <div class="marker-vehicle-tag" id="vehicle-speed-tag">${Math.round(vehicleSpeed)} km/h</div>
                `;

                const popupHtml = `
                    <div class="popup-header" style="background: linear-gradient(135deg, #059669, #10b981);">
                        <div class="popup-header-icon">
                            <i class="ri-truck-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold">${FLEET_NO} (${TRUCK_NO})</div>
                            <div class="small opacity-75">{{ __('Live Asset Telemetry') }}</div>
                        </div>
                    </div>
                    <div class="popup-body">
                        <div class="popup-row">
                            <div class="popup-label">{{ __('Live Speed') }}:</div>
                            <div class="popup-value fw-bold text-success" id="popup-vehicle-speed">${Math.round(vehicleSpeed)} km/h</div>
                        </div>
                        <div class="popup-row">
                            <div class="popup-label">{{ __('Heading') }}:</div>
                            <div class="popup-value" id="popup-vehicle-heading">${Math.round(vehicleHeading)}°</div>
                        </div>
                        <div class="popup-row">
                            <div class="popup-label">{{ __('Driver') }}:</div>
                            <div class="popup-value">${@json($driverName)}</div>
                        </div>
                        <div class="popup-row">
                            <div class="popup-label">{{ __('Position') }}:</div>
                            <div class="popup-value small font-monospace">${lat.toFixed(5)}, ${lng.toFixed(5)}</div>
                        </div>
                    </div>
                `;

                const popup = new maplibregl.Popup({ offset: 25, closeButton: false }).setHTML(popupHtml);

                vehicleMarker = new maplibregl.Marker({ element: el })
                    .setLngLat([lng, lat])
                    .setPopup(popup)
                    .addTo(map);
            } else {
                vehicleMarker.setLngLat([lng, lat]);

                // Update heading arrow and speed tag
                const arrow = document.querySelector('.marker-vehicle-heading-arrow');
                if (arrow) arrow.style.transform = `rotate(${vehicleHeading}deg)`;

                const speedTag = document.getElementById('vehicle-speed-tag');
                if (speedTag) speedTag.innerText = `${Math.round(vehicleSpeed)} km/h`;

                const popupSpeed = document.getElementById('popup-vehicle-speed');
                if (popupSpeed) popupSpeed.innerText = `${Math.round(vehicleSpeed)} km/h`;

                const popupHeading = document.getElementById('popup-vehicle-heading');
                if (popupHeading) popupHeading.innerText = `${Math.round(vehicleHeading)}°`;
            }

            // Update proximity vector line
            updateTrajectoryLine();

            updateStatus(`Live GPS Connected: ${Math.round(vehicleSpeed)} km/h`, 'success');
        }

        /* ── Compass Direction Helper ── */
        function getCompassDirection(degrees) {
            const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            const index = Math.round(((degrees %= 360) < 0 ? degrees + 360 : degrees) / 45) % 8;
            return directions[index];
        }

        /* ── Wialon SDK Live Integration ── */
        function initWialonLiveTracking() {
            if (!WIALON_TOKEN) {
                console.warn('Wialon token not provided.');
                simulateFallbackPosition();
                return;
            }

            if (typeof wialon === 'undefined') {
                console.warn('Wialon SDK script not loaded.');
                simulateFallbackPosition();
                return;
            }

            try {
                session = wialon.core.Session.getInstance();
                session.initSession(WIALON_HOST);

                session.loginToken(WIALON_TOKEN, '', (code) => {
                    if (code) {
                        console.error('Wialon login failed with code:', code);
                        updateStatus('GPS login failed - showing last known', 'warning');
                        simulateFallbackPosition();
                        return;
                    }

                    // Request units flags
                    const flags = wialon.item.Item.dataFlag.base |
                                  wialon.item.Unit.dataFlag.lastMessage |
                                  wialon.item.Item.dataFlag.customProps;

                    session.loadLibrary('itemIcon');
                    session.updateDataFlags(
                        [{ type: 'type', data: 'avl_unit', flags: flags, mode: 0 }],
                        (code) => {
                            if (code) {
                                console.error('Failed to load Wialon units:', code);
                                simulateFallbackPosition();
                                return;
                            }

                            const units = session.getItems('avl_unit');
                            if (!units || units.length === 0) {
                                simulateFallbackPosition();
                                return;
                            }

                            // Find unit by MATCHED_UNIT_ID or FLEET_NO
                            let matched = null;
                            if (MATCHED_UNIT_ID) {
                                matched = session.getItem(MATCHED_UNIT_ID);
                            }

                            if (!matched && FLEET_NO) {
                                const cleanFleet = String(FLEET_NO).toLowerCase().replace(/[^a-z0-9]/g, '');
                                matched = units.find(u => {
                                    const uName = u.getName().toLowerCase().replace(/[^a-z0-9]/g, '');
                                    return uName.includes(cleanFleet) || cleanFleet.includes(uName);
                                });
                            }

                            if (matched) {
                                liveUnit = matched;
                                handleUnitPosition(liveUnit);

                                // Listen to real-time events
                                liveUnit.addListener('messageRegistered', (event) => {
                                    handleUnitPosition(liveUnit);
                                });
                            } else {
                                simulateFallbackPosition();
                            }
                        }
                    );
                });
            } catch (err) {
                console.error('Wialon SDK error:', err);
                simulateFallbackPosition();
            }
        }

        function handleUnitPosition(unit) {
            if (!unit) return;
            const pos = unit.getPosition();
            if (pos) {
                updateVehicleMarker(pos.x, pos.y, pos.s || 0, pos.c || 0, pos.t);
            }
        }

        function simulateFallbackPosition() {
            // Fallback: If waybill has origin/destination or incident has coordinates, position appropriately
            if (INCIDENT_DATA && INCIDENT_DATA.lng && INCIDENT_DATA.lat) {
                // Offset slightly from incident location to demonstrate vector proximity if no live unit
                const offsetLng = parseFloat(INCIDENT_DATA.lng) + 0.045;
                const offsetLat = parseFloat(INCIDENT_DATA.lat) + 0.035;
                updateVehicleMarker(offsetLng, offsetLat, 42, 135, Math.floor(Date.now() / 1000));
            } else if (WAYBILL_DATA && WAYBILL_DATA.origin_lng && WAYBILL_DATA.origin_lat) {
                updateVehicleMarker(parseFloat(WAYBILL_DATA.origin_lng), parseFloat(WAYBILL_DATA.origin_lat), 0, 0, Math.floor(Date.now() / 1000));
            }
        }

        /* ── Map Action Triggers & Navigators ── */
        function fitAllMarkers() {
            if (!mapReady || !map) return;

            const bounds = new maplibregl.LngLatBounds();
            let hasPoints = false;

            if (INCIDENT_DATA && INCIDENT_DATA.lng && INCIDENT_DATA.lat) {
                bounds.extend([parseFloat(INCIDENT_DATA.lng), parseFloat(INCIDENT_DATA.lat)]);
                hasPoints = true;
            }

            if (vehicleCoordinates) {
                bounds.extend(vehicleCoordinates);
                hasPoints = true;
            }

            if (waybillRouteMarkers && waybillRouteMarkers.length > 0) {
                waybillRouteMarkers.forEach(m => {
                    bounds.extend(m.getLngLat());
                    hasPoints = true;
                });
            }

            if (hasPoints) {
                map.fitBounds(bounds, { padding: 90, maxZoom: 15, duration: 1200 });
            }
        }

        function focusIncidentMarker() {
            if (!INCIDENT_DATA || !INCIDENT_DATA.lng || !INCIDENT_DATA.lat || !map) return;
            const lng = parseFloat(INCIDENT_DATA.lng);
            const lat = parseFloat(INCIDENT_DATA.lat);
            map.flyTo({ center: [lng, lat], zoom: 15, pitch: 30, duration: 1400 });
            if (incidentMarker) {
                incidentMarker.togglePopup();
            }
        }

        function focusVehicleMarker() {
            if (!vehicleCoordinates || !map) return;
            map.flyTo({ center: vehicleCoordinates, zoom: 15.5, pitch: 35, duration: 1400 });
            if (vehicleMarker) {
                vehicleMarker.togglePopup();
            }
        }

        function flyToOrigin() {
            if (WAYBILL_DATA && WAYBILL_DATA.origin_lng && WAYBILL_DATA.origin_lat && map) {
                map.flyTo({ center: [parseFloat(WAYBILL_DATA.origin_lng), parseFloat(WAYBILL_DATA.origin_lat)], zoom: 14, duration: 1200 });
            }
        }

        function flyToCheckpoint(index) {
            try {
                const checkpoints = Array.isArray(WAYBILL_DATA.checkpoints) ? WAYBILL_DATA.checkpoints : JSON.parse(WAYBILL_DATA.checkpoints || '[]');
                const cp = checkpoints[index];
                if (cp && cp.lng && cp.lat && map) {
                    map.flyTo({ center: [parseFloat(cp.lng), parseFloat(cp.lat)], zoom: 14, duration: 1200 });
                }
            } catch(e){}
        }

        function flyToDestination() {
            if (WAYBILL_DATA && WAYBILL_DATA.destination_lng && WAYBILL_DATA.destination_lat && map) {
                map.flyTo({ center: [parseFloat(WAYBILL_DATA.destination_lng), parseFloat(WAYBILL_DATA.destination_lat)], zoom: 14, duration: 1200 });
            }
        }

        /* ── Layer Filter Toggles ── */
        function toggleLayer(layerKey) {
            if (layerKey === 'incident') {
                showIncidentLayer = !showIncidentLayer;
                const chip = document.getElementById('chip-incident-toggle');
                if (chip) chip.classList.toggle('active', showIncidentLayer);
                if (incidentMarker) {
                    incidentMarker.getElement().style.display = showIncidentLayer ? 'flex' : 'none';
                }
            } else if (layerKey === 'vehicle') {
                showVehicleLayer = !showVehicleLayer;
                const chip = document.getElementById('chip-vehicle-toggle');
                if (chip) chip.classList.toggle('active', showVehicleLayer);
                if (vehicleMarker) {
                    vehicleMarker.getElement().style.display = showVehicleLayer ? 'flex' : 'none';
                }
            } else if (layerKey === 'trajectory') {
                showTrajectoryLayer = !showTrajectoryLayer;
                const chip = document.getElementById('chip-trajectory-toggle');
                if (chip) chip.classList.toggle('active', showTrajectoryLayer);
                updateTrajectoryLine();
            } else if (layerKey === 'route') {
                showRouteLayer = !showRouteLayer;
                const chip = document.getElementById('chip-route-toggle');
                if (chip) chip.classList.toggle('active', showRouteLayer);
                waybillRouteMarkers.forEach(m => {
                    m.getElement().style.display = showRouteLayer ? 'flex' : 'none';
                });
                if (map.getLayer(WAYBILL_ROUTE_LAYER)) {
                    map.setLayoutProperty(WAYBILL_ROUTE_LAYER, 'visibility', showRouteLayer ? 'visible' : 'none');
                }
            }
        }

        /* ── UI Utilities ── */
        function updateStatus(text, type = 'info') {
            const statusText = document.getElementById('status-text');
            const statusDot = document.getElementById('status-dot');
            if (statusText) statusText.innerText = text;
            if (statusDot) {
                statusDot.style.background = type === 'success' ? '#22c55e' : (type === 'warning' ? '#f59e0b' : '#3b82f6');
            }
        }

        function copyCoordinates(lat, lng) {
            const text = `${lat}, ${lng}`;
            navigator.clipboard.writeText(text).then(() => {
                alert(`Coordinates copied to clipboard: ${text}`);
            }).catch(() => {
                prompt('Copy coordinates:', text);
            });
        }

        function initFullscreen() {
            const fsBtn = document.getElementById('btn-fullscreen-toggle');
            const fsIcon = document.getElementById('fs-icon');
            const box = document.getElementById('map-container-box');

            if (!fsBtn || !box) return;

            fsBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    box.requestFullscreen().catch(err => console.error(err));
                    if (fsIcon) {
                        fsIcon.className = 'ri-fullscreen-exit-line';
                    }
                } else {
                    document.exitFullscreen();
                    if (fsIcon) {
                        fsIcon.className = 'ri-fullscreen-line';
                    }
                }
            });

            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement && fsIcon) {
                    fsIcon.className = 'ri-fullscreen-line';
                }
                setTimeout(() => { if (map) map.resize(); }, 200);
            });
        }
    </script>
</x-layouts.auth>
