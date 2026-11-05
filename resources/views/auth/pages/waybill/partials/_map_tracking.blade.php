<x-layouts.auth page-title="{{ __('Waybill Map & Live Tracking') }}" sub-title="Interactive Route, Incident Pinpointing & Vehicle Tracking">
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

            /* Category Colors */
            --c-start:        #10b981;
            --c-cp:           #2563eb;
            --c-end:          #ef4444;
            --c-accident:     #dc2626;
            --c-breakdown:    #f97316;
            --c-bogged:       #d97706;
            --c-impound:      #8b5cf6;
            --c-detention:    #e11d48;
            --c-diversion:    #0284c7;
            --c-haulage:      #ea580c;
            --c-breath:       #be123c;
            --c-misconduct:   #991b1b;
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
            max-width: 1560px;
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
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
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
            margin-top: 4px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 3px 10px;
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
        }
        .action-btn.btn-danger-soft {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        .action-btn.btn-danger-soft:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }
        .action-btn.btn-danger-soft:hover i {
            color: white !important;
        }

        /* ── Path Sequence Flow Bar (Image 1 Style) ── */
        .path-sequence-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }

        .sequence-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .path-sequence-container {
            border: 1.5px dashed #cbd5e1;
            border-radius: var(--radius-md);
            background: #f8fafc;
            padding: 14px 18px;
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
            padding: 7px 16px;
            border-radius: 999px;
            font-size: 12.5px;
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
        .path-step-badge.badge-end {
            background: linear-gradient(135deg, #dc2626, #ef4444);
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
        .path-step-label.label-end { color: #dc2626; }

        .path-step-arrow {
            color: #94a3b8;
            font-size: 20px;
            font-weight: bold;
            flex-shrink: 0;
        }

        /* ── Map Filters & Legend Toolbar ── */
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

        /* ── Map Container ── */
        .map-layout-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
        }
        @media (max-width: 1100px) {
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
            min-height: 640px;
            height: calc(100vh - 340px);
        }

        #waybill-map {
            width: 100%;
            height: 100%;
        }

        /* Floating Overlays on Map */
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
            bottom: 24px;
            right: 14px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .tool-round-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: all 0.15s ease;
        }
        .tool-round-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }

        /* ── Map Markers Styling ── */
        /* Start Marker */
        .marker-origin-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }
        .marker-origin-pin {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: transform 0.2s ease;
        }
        .marker-origin-pin i {
            transform: rotate(45deg);
            font-size: 18px;
        }
        .marker-origin-label {
            background: #059669;
            color: white;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
            margin-top: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            letter-spacing: 0.5px;
        }

        /* Checkpoint Marker */
        .marker-cp-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }
        .marker-cp-pin {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            transition: transform 0.2s ease;
        }
        .marker-cp-label {
            background: #1d4ed8;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 999px;
            margin-top: 3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            white-space: nowrap;
        }

        /* Destination Marker */
        .marker-dest-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }
        .marker-dest-pin {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #b91c1c, #ef4444);
            color: white;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: transform 0.2s ease;
        }
        .marker-dest-pin i {
            transform: rotate(45deg);
            font-size: 18px;
        }
        .marker-dest-label {
            background: #b91c1c;
            color: white;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
            margin-top: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            letter-spacing: 0.5px;
        }

        /* Incident & Driver Behavior Custom Markers */
        .marker-incident-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .marker-incident-bubble {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 17px;
            border: 2.5px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.35);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 2;
        }
        .marker-incident-container:hover .marker-incident-bubble {
            transform: scale(1.18);
        }
        .marker-incident-pulse {
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            opacity: 0.7;
            animation: incidentPulse 2s infinite;
            z-index: 1;
        }
        @keyframes incidentPulse {
            0% { transform: scale(0.9); opacity: 0.8; }
            70% { transform: scale(1.5); opacity: 0; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .marker-incident-tag {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
            margin-top: 4px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.15);
        }

        /* Live Vehicle Marker */
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
            border-color: #94a3b8;
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
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
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
            min-width: 290px;
            max-width: 360px;
        }
        .maplibregl-popup-close-button {
            top: 8px;
            right: 8px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(0,0,0,0.15);
            color: white;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            cursor: pointer;
            transition: background 0.15s;
            top: 8px !important;
            right: 8px !important;
            width: 24px !important;
            height: 24px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            line-height: 1 !important;
        }
        .maplibregl-popup-close-button:hover {
            background: rgba(0,0,0,0.45);
            color: white;
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

        /* ── Exact Status & Category Pill Badges (Image 3 Style) ── */
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
        .status-pill.status-released,
        .status-pill.status-resolved,
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
        .status-pill .status-chevron {
            font-size: 9px;
            opacity: 0.65;
            margin-left: 2px;
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
            margin-bottom: 8px;
        }
        .popup-row:last-child {
            margin-bottom: 0;
        }
        .popup-label {
            width: 80px;
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
            justify-content: flex-end;
        }
        .popup-btn {
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.15s;
        }
        .popup-btn:hover {
            background: var(--primary-dark);
            color: white;
        }

        /* ── Side Drawer / Info Panel ── */
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

        .incident-list-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 380px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .incident-item-card {
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .incident-item-card:hover {
            background: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
            transform: translateX(2px);
        }

        .incident-item-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .incident-item-icon {
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
        .incident-item-title {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .incident-item-meta {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .incident-item-action {
            flex-shrink: 0;
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
    </style>

    <div class="map-wrapper">
        <!-- ── Top Header ── -->
        <div class="map-header">
            <div class="header-title-box">
                <div class="header-icon-badge">
                    <i class="ri-route-line"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        {{ __('Waybill') }} #{{ $waybill->waybill_number }}
                        <span class="badge bg-primary fs-6 ms-2 px-2 py-1 align-middle">
                            {{ $waybill->status?->badge_label ?? __('Active') }}
                        </span>
                    </h4>
                    <div class="header-meta">
                        <div class="meta-pill">
                            <i class="ri-truck-line text-primary"></i>
                            <span>{{ __('Asset:') }} <strong>{{ $waybill->vehicle?->fleet_no ?? '-' }} ({{ $waybill->vehicle?->truck_no ?? '-' }})</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-user-follow-line text-success"></i>
                            <span>{{ __('Driver:') }} <strong>{{ $waybill->driver?->full_name ?? '-' }}</strong></span>
                        </div>
                        <div class="meta-pill">
                            <i class="ri-map-pin-range-line text-danger"></i>
                            <span>{{ $waybill->origin ?? __('Origin') }} &rarr; {{ $waybill->destination ?? __('Destination') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-actions">
                <a href="{{ route('waybill.show', $waybill->id) }}" class="action-btn">
                    <i class="ri-arrow-left-line"></i> {{ __('Back to Waybill') }}
                </a>
                <a href="{{ route('incident.all_map', ['vehicle_id' => $waybill->vehicle_id]) }}" class="action-btn btn-danger-soft" title="{{ __('View incidents and driver behavior for this vehicle') }}">
                    <i class="ri-radar-line text-danger"></i> {{ __('All Incidents Map') }}
                </a>
                <button type="button" class="action-btn" id="btn-fit-all" title="Fit all markers and route">
                    <i class="ri-focus-3-line"></i> {{ __('Re-fit View') }}
                </button>
                <button type="button" class="action-btn" id="btn-focus-vehicle" title="Locate live vehicle">
                    <i class="ri-crosshair-2-line text-success"></i> {{ __('Live Vehicle') }}
                </button>
                <button type="button" class="action-btn btn-primary-action" id="btn-fullscreen-toggle" title="Toggle Fullscreen">
                    <i class="ri-fullscreen-line" id="fs-icon"></i>
                </button>
            </div>
        </div>

        <!-- ── Path Sequence Flow Bar (Template Image 1) ── -->
        @php
            $originName = $waybill->origin ?? 'Origin';
            $destName = $waybill->destination ?? 'Destination';
            $checkpoints = is_array($waybill->checkpoints) ? $waybill->checkpoints : (json_decode($waybill->checkpoints, true) ?? []);
        @endphp
        <div class="path-sequence-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="sequence-title mb-0">
                    <i class="ri-git-commit-line text-primary"></i> {{ __('Path Sequence') }}
                </div>
                <a href="{{ route('incident.all_map', ['vehicle_id' => $waybill->vehicle_id]) }}" class="action-btn btn-danger-soft py-1 px-3" style="font-size: 11.5px; border-radius: 999px;">
                    <i class="ri-radar-line text-danger"></i> {{ __('View All Incidents Map') }}
                </a>
            </div>
            <div class="path-sequence-container" id="path-sequence-flow">
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

        <!-- ── Filter & Legend Toolbar ── -->
        <div class="map-filter-toolbar">
            <div class="filter-chips-group">
                <div class="filter-chip active" data-layer-filter="all">
                    <i class="ri-apps-2-line"></i> {{ __('All Layers') }}
                </div>
                <div class="filter-chip active" data-layer-filter="route">
                    <i class="ri-route-line"></i> {{ __('Route & Path') }}
                </div>
                <div class="filter-chip active" data-layer-filter="vehicle">
                    <i class="ri-truck-fill text-success"></i> {{ __('Live Vehicle') }}
                </div>
                <div class="filter-chip active" data-layer-filter="incidents">
                    <i class="ri-alarm-warning-fill text-danger"></i> {{ __('Incidents') }}
                    <span class="chip-count">{{ count(array_filter($mapIncidents, fn($i) => $i['category'] === 'incident')) }}</span>
                </div>
                <div class="filter-chip active" data-layer-filter="driver_behavior">
                    <i class="ri-user-star-line text-warning"></i> {{ __('Driver Behavior') }}
                    <span class="chip-count">{{ count(array_filter($mapIncidents, fn($i) => $i['category'] === 'driver_behavior')) }}</span>
                </div>
            </div>

            <div class="legend-indicator-group">
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-start);"></span> {{ __('Start') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-cp);"></span> {{ __('Checkpoints') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-end);"></span> {{ __('End') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-accident);"></span> {{ __('Accident') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-breakdown);"></span> {{ __('Breakdown') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-bogged);"></span> {{ __('Bogged Down') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: var(--c-diversion);"></span> {{ __('Diversion') }}</div>
                <div class="legend-item"><span class="legend-dot" style="background: #22c55e;"></span> {{ __('Live Unit') }}</div>
            </div>
        </div>

        <!-- ── Main Map Canvas & Sidebar Grid ── -->
        <div class="map-layout-grid">
            <div class="map-canvas-container" id="map-container-box">
                <!-- Status Pill on Map -->
                <div class="map-overlay-status" id="live-status-pill">
                    <span class="status-dot-pulse" id="status-dot"></span>
                    <span id="status-text">{{ __('Connecting Live GPS…') }}</span>
                </div>

                <!-- Floating Quick Tools -->
                <div class="map-quick-tools">
                    <button type="button" class="tool-round-btn" onclick="fitAllMarkers()" title="Center all markers">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="tool-round-btn" onclick="focusVehicleMarker()" title="Center on Vehicle">
                        <i class="ri-truck-line"></i>
                    </button>
                    <button type="button" class="tool-round-btn" onclick="toggleLayer('incidents')" title="Toggle Incident Pins">
                        <i class="ri-error-warning-line text-danger"></i>
                    </button>
                </div>

                <!-- Map Canvas -->
                <div id="waybill-map"></div>
            </div>

            <!-- ── Side Panel: Incident Drawer & Live Telemetry ── -->
            <div class="side-info-panel">
                <!-- Waybill Overview -->
                <div class="panel-card">
                    <div class="panel-card-title">
                        <span><i class="ri-file-list-3-line text-primary me-1"></i> {{ __('Waybill Overview') }}</span>
                        <span class="badge bg-light text-dark border">{{ $waybill->waybill_number }}</span>
                    </div>
                    <div class="telemetry-grid mb-2">
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Cargo') }}</div>
                            <div class="telemetry-value text-truncate" title="{{ $waybill->cargo_description ?? '-' }}">{{ $waybill->cargo_description ?? '-' }}</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Weight') }}</div>
                            <div class="telemetry-value">{{ $waybill->cargo_weight_kg ? number_format((float)$waybill->cargo_weight_kg) . ' kg' : '-' }}</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Dispatch Date') }}</div>
                            <div class="telemetry-value">{{ $waybill->dispatch_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Distance / ETA') }}</div>
                            <div class="telemetry-value">{{ $waybill->distance ? $waybill->distance . ' km' : '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Live Telemetry Card -->
                <div class="panel-card">
                    <div class="panel-card-title">
                        <span><i class="ri-dashboard-3-line text-success me-1"></i> {{ __('Live Telemetry') }}</span>
                        <span class="badge bg-success-subtle text-success" id="telemetry-state-badge">{{ __('Active Unit') }}</span>
                    </div>
                    <div class="telemetry-grid">
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Speed') }}</div>
                            <div class="telemetry-value text-success" id="telemetry-speed">-- km/h</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Movement') }}</div>
                            <div class="telemetry-value" id="telemetry-movement">Connecting…</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Heading') }}</div>
                            <div class="telemetry-value" id="telemetry-heading">--°</div>
                        </div>
                        <div class="telemetry-box">
                            <div class="telemetry-label">{{ __('Last Updated') }}</div>
                            <div class="telemetry-value" id="telemetry-last-time">--:--</div>
                        </div>
                    </div>
                </div>

                <!-- Logged Incidents List Drawer -->
                <div class="panel-card">
                    <div class="panel-card-title">
                        <span><i class="ri-alert-line text-danger me-1"></i> {{ __('Waybill Incidents') }}</span>
                        <span class="badge bg-danger rounded-pill">{{ count($mapIncidents) }}</span>
                    </div>
                    <div class="incident-list-container" id="incidents-list-box">
                        @forelse($mapIncidents as $idx => $inc)
                            <div class="incident-item-card" onclick="flyToIncident({{ $idx }})">
                                <div class="incident-item-left">
                                    <div class="incident-item-icon" style="background: {{ $inc['color'] }};">
                                        <i class="{{ $inc['icon'] }}"></i>
                                    </div>
                                    <div style="overflow: hidden;">
                                        <div class="incident-item-title">{{ $inc['type_label'] }}</div>
                                        <div class="incident-item-meta">
                                            <span>{{ $inc['date'] }}</span> &bull; <span class="text-truncate">{{ $inc['location'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="incident-item-action">
                                    <span class="status-pill {{ $inc['status_color'] == 'success' ? 'status-resolved' : ($inc['status_color'] == 'warning' ? 'status-in_progress' : 'status-impounded') }}">
                                        <span>{{ $inc['status_label'] }}</span>
                                        <i class="fa-solid fa-chevron-down status-chevron"></i>
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="ri-checkbox-circle-line text-success fs-3 mb-1"></i>
                                <p class="mb-0 small">{{ __('No incidents recorded for this trip.') }}</p>
                            </div>
                        @endforelse
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
        const MATCHED_UNIT_ID = @json($matchedUnitId);
        const GEOAPIFY_KEY    = @json($geoapifyApiKey);
        const APP_ENV         = @json($appEnv);
        const WAYBILL_DATA    = @json($waybill);
        const INCIDENTS_DATA  = @json($mapIncidents);

        let map = null;
        let mapReady = false;
        let session = null;
        let liveUnit = null;
        let vehicleMarker = null;
        let vehicleCoordinates = null;
        let vehicleHeading = 0;
        let vehicleSpeed = 0;
        let vehicleState = 'stopped'; // 'moving', 'stopped', 'offline'

        // Layer Markers Group
        let routeMarkers = [];
        let incidentMarkers = [];
        let routeGeoJsonLayerId = 'waybill-route-line';
        let routeGlowLayerId = 'waybill-route-glow';
        let allBounds = [];

        // Layer visibility state
        let layerVisibility = {
            route: true,
            vehicle: true,
            incidents: true,
            driver_behavior: true
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
            initFilterChips();
            initFullscreen();

            if (WIALON_TOKEN && (MATCHED_UNIT_ID || FLEET_NO)) {
                initWialonSession();
            } else {
                setVehicleStatusPill('offline', 'Wialon GPS not linked');
            }
        });

        /* ============================================================
           1. MAP INITIALIZATION (MapLibre GL JS)
           ============================================================ */
        function initMap() {
            map = new maplibregl.Map({
                container: 'waybill-map',
                style: MAP_STYLE,
                center: [73.0479, 33.6844], // Default Islamabad fallback
                zoom: 6
            });

            map.addControl(new maplibregl.NavigationControl(), 'top-right');
            map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

            map.on('load', function () {
                mapReady = true;
                renderWaybillRouteAndCheckpoints();
                renderIncidentMarkers();
                fitAllMarkers();
            });
        }

        /* ============================================================
           2. RENDER ROUTE & CHECKPOINTS (START, CP 1..N, END)
           ============================================================ */
        function renderWaybillRouteAndCheckpoints() {
            allBounds = [];
            const originLat = parseFloat(WAYBILL_DATA.origin_lat);
            const originLng = parseFloat(WAYBILL_DATA.origin_lng);
            const originName = WAYBILL_DATA.origin || 'Origin';

            const destLat = parseFloat(WAYBILL_DATA.destination_lat);
            const destLng = parseFloat(WAYBILL_DATA.destination_lng);
            const destName = WAYBILL_DATA.destination || 'Destination';

            let waypointsCoords = [];

            // A. START MARKER
            if (!isNaN(originLat) && !isNaN(originLng)) {
                const startEl = document.createElement('div');
                startEl.className = 'marker-origin-container';
                startEl.innerHTML = `
                    <div class="marker-origin-pin"><i class="ri-rocket-2-fill"></i></div>
                    <div class="marker-origin-label">START</div>
                `;

                const startPopup = new maplibregl.Popup({ offset: 25 }).setHTML(`
                    <div class="popup-card">
                        <div class="popup-header" style="background: linear-gradient(135deg, #059669, #10b981);">
                            <div class="popup-header-icon"><i class="ri-rocket-2-fill"></i></div>
                            <div>
                                <h6 class="popup-header-title">START (Origin)</h6>
                                <p class="popup-header-sub">Dispatch Location</p>
                            </div>
                        </div>
                        <div class="popup-body">
                            <div class="popup-row">
                                <span class="popup-label">Location:</span>
                                <span class="popup-value fw-bold text-success">${escapeHtml(originName)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Coordinates:</span>
                                <span class="popup-value font-monospace">${originLat.toFixed(5)}, ${originLng.toFixed(5)}</span>
                            </div>
                        </div>
                    </div>
                `);

                const marker = new maplibregl.Marker({ element: startEl, anchor: 'bottom' })
                    .setLngLat([originLng, originLat])
                    .setPopup(startPopup)
                    .addTo(map);

                routeMarkers.push({ marker: marker, type: 'start', lngLat: [originLng, originLat] });
                allBounds.push([originLng, originLat]);
                waypointsCoords.push({ lat: originLat, lng: originLng });
            }

            // B. CHECKPOINT MARKERS
            let checkpoints = WAYBILL_DATA.checkpoints;
            if (typeof checkpoints === 'string') {
                try { checkpoints = JSON.parse(checkpoints); } catch (e) { checkpoints = []; }
            }
            if (Array.isArray(checkpoints)) {
                checkpoints.forEach((cp, idx) => {
                    const cpLat = parseFloat(cp.lat);
                    const cpLng = parseFloat(cp.lng);
                    const cpName = cp.name || `Checkpoint ${idx + 1}`;

                    if (!isNaN(cpLat) && !isNaN(cpLng)) {
                        const cpEl = document.createElement('div');
                        cpEl.className = 'marker-cp-container';
                        cpEl.innerHTML = `
                            <div class="marker-cp-pin">${idx + 1}</div>
                            <div class="marker-cp-label">CP ${idx + 1}</div>
                        `;

                        const cpPopup = new maplibregl.Popup({ offset: 20 }).setHTML(`
                            <div class="popup-card">
                                <div class="popup-header" style="background: linear-gradient(135deg, #1d4ed8, #3b82f6);">
                                    <div class="popup-header-icon"><i class="ri-map-pin-2-fill"></i></div>
                                    <div>
                                        <h6 class="popup-header-title">CHECKPOINT ${idx + 1}</h6>
                                        <p class="popup-header-sub">Route Inspection Point</p>
                                    </div>
                                </div>
                                <div class="popup-body">
                                    <div class="popup-row">
                                        <span class="popup-label">Name:</span>
                                        <span class="popup-value fw-bold text-primary">${escapeHtml(cpName)}</span>
                                    </div>
                                    <div class="popup-row">
                                        <span class="popup-label">Coordinates:</span>
                                        <span class="popup-value font-monospace">${cpLat.toFixed(5)}, ${cpLng.toFixed(5)}</span>
                                    </div>
                                </div>
                            </div>
                        `);

                        const marker = new maplibregl.Marker({ element: cpEl, anchor: 'bottom' })
                            .setLngLat([cpLng, cpLat])
                            .setPopup(cpPopup)
                            .addTo(map);

                        routeMarkers.push({ marker: marker, type: 'cp', index: idx, lngLat: [cpLng, cpLat] });
                        allBounds.push([cpLng, cpLat]);
                        waypointsCoords.push({ lat: cpLat, lng: cpLng });
                    }
                });
            }

            // C. END / DESTINATION MARKER
            if (!isNaN(destLat) && !isNaN(destLng)) {
                const destEl = document.createElement('div');
                destEl.className = 'marker-dest-container';
                destEl.innerHTML = `
                    <div class="marker-dest-pin"><i class="ri-flag-fill"></i></div>
                    <div class="marker-dest-label">END</div>
                `;

                const destPopup = new maplibregl.Popup({ offset: 25 }).setHTML(`
                    <div class="popup-card">
                        <div class="popup-header" style="background: linear-gradient(135deg, #b91c1c, #ef4444);">
                            <div class="popup-header-icon"><i class="ri-flag-fill"></i></div>
                            <div>
                                <h6 class="popup-header-title">END (Destination)</h6>
                                <p class="popup-header-sub">Final Delivery Location</p>
                            </div>
                        </div>
                        <div class="popup-body">
                            <div class="popup-row">
                                <span class="popup-label">Location:</span>
                                <span class="popup-value fw-bold text-danger">${escapeHtml(destName)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Coordinates:</span>
                                <span class="popup-value font-monospace">${destLat.toFixed(5)}, ${destLng.toFixed(5)}</span>
                            </div>
                        </div>
                    </div>
                `);

                const marker = new maplibregl.Marker({ element: destEl, anchor: 'bottom' })
                    .setLngLat([destLng, destLat])
                    .setPopup(destPopup)
                    .addTo(map);

                routeMarkers.push({ marker: marker, type: 'end', lngLat: [destLng, destLat] });
                allBounds.push([destLng, destLat]);
                waypointsCoords.push({ lat: destLat, lng: destLng });
            }

            // D. ROUTE LINE (GeoJSON Polyline)
            drawRoutePolyline(waypointsCoords);
        }

        function drawRoutePolyline(waypoints) {
            let geometryCoordinates = [];

            // 1. Check if waybill has saved route_geometry
            if (WAYBILL_DATA.route_geometry) {
                let geom = WAYBILL_DATA.route_geometry;
                if (typeof geom === 'string') {
                    try { geom = JSON.parse(geom); } catch (e) {}
                }
                if (Array.isArray(geom) && geom.length > 0) {
                    // Normalize [ [lng, lat], ... ]
                    if (Array.isArray(geom[0]) && geom[0].length >= 2) {
                        geometryCoordinates = geom;
                    }
                }
            }

            // 2. If no saved geometry, use straight line between waypoints or fetch routing
            if (geometryCoordinates.length === 0 && waypoints.length >= 2) {
                // Fetch dynamic Geoapify route if available
                if (typeof $ !== 'undefined' && GEOAPIFY_KEY) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route("geoapify.routing") }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            waypoints: waypoints
                        },
                        success: function (res) {
                            if (res && res.coordinates && res.coordinates.length > 0) {
                                renderGeoJsonLine(res.coordinates);
                            } else {
                                renderGeoJsonLine(waypoints.map(w => [w.lng, w.lat]));
                            }
                        },
                        error: function () {
                            renderGeoJsonLine(waypoints.map(w => [w.lng, w.lat]));
                        }
                    });
                    return;
                } else {
                    geometryCoordinates = waypoints.map(w => [w.lng, w.lat]);
                }
            }

            if (geometryCoordinates.length > 0) {
                renderGeoJsonLine(geometryCoordinates);
            }
        }

        function renderGeoJsonLine(coordinates) {
            if (!map || !mapReady) return;

            if (map.getSource('route-source')) {
                map.getSource('route-source').setData({
                    type: 'Feature',
                    geometry: { type: 'LineString', coordinates: coordinates }
                });
                return;
            }

            map.addSource('route-source', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: { type: 'LineString', coordinates: coordinates }
                }
            });

            // Outer Glow Layer
            map.addLayer({
                id: routeGlowLayerId,
                type: 'line',
                source: 'route-source',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: {
                    'line-color': '#60a5fa',
                    'line-width': 10,
                    'line-opacity': 0.45
                }
            });

            // Main Line Layer
            map.addLayer({
                id: routeGeoJsonLayerId,
                type: 'line',
                source: 'route-source',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: {
                    'line-color': '#2563eb',
                    'line-width': 5,
                    'line-opacity': 0.95
                }
            });
        }

        /* ============================================================
           3. RENDER INCIDENT & DRIVER BEHAVIOR MARKERS (PINPOINT)
           ============================================================ */
        function renderIncidentMarkers() {
            incidentMarkers = [];

            INCIDENTS_DATA.forEach((inc, index) => {
                if (inc.lat === null || inc.lng === null || isNaN(inc.lat) || isNaN(inc.lng)) {
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
                            <span class="popup-label">Cost:</span>
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
                        <i class="fa-solid fa-chevron-down status-chevron"></i>
                    </span>
                `;

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
                            <a href="${inc.show_url}" target="_blank" class="popup-btn">
                                <i class="ri-external-link-line"></i> View Record
                            </a>
                        </div>
                    </div>
                `;

                const popup = new maplibregl.Popup({ offset: 28 }).setHTML(popupHtml);

                const marker = new maplibregl.Marker({ element: markerEl, anchor: 'center' })
                    .setLngLat([inc.lng, inc.lat])
                    .setPopup(popup)
                    .addTo(map);

                incidentMarkers.push({
                    index: index,
                    marker: marker,
                    category: inc.category,
                    type_key: inc.type_key,
                    data: inc,
                    lngLat: [inc.lng, inc.lat]
                });

                allBounds.push([inc.lng, inc.lat]);
            });
        }

        /* ============================================================
           4. LIVE VEHICLE TRACKING (WIALON INTEGRATION & REALTIME)
           ============================================================ */
        function initWialonSession() {
            setVehicleStatusPill('loading', 'Connecting to Wialon…');
            try {
                session = wialon.core.Session.getInstance();
                session.initSession(String(WIALON_HOST).replace(/\/+$/, ''));
                session.loginToken(WIALON_TOKEN, '', function (code) {
                    if (code) {
                        setVehicleStatusPill('offline', 'Wialon Login Failed');
                        return;
                    }
                    setVehicleStatusPill('online', 'Connected to Live Fleet');
                    loadWialonVehicle();
                });
            } catch (err) {
                console.error('Wialon session error:', err);
                setVehicleStatusPill('offline', 'GPS Service Unavailable');
            }
        }

        function loadWialonVehicle() {
            const flags = wialon.item.Item.dataFlag.base |
                          wialon.item.Unit.dataFlag.lastMessage |
                          (wialon.item.Unit.dataFlag.position || 0);

            session.updateDataFlags(
                [{ type: 'type', data: 'avl_unit', flags: flags, mode: 0 }],
                function (code) {
                    if (code) {
                        setVehicleStatusPill('offline', 'Unable to load vehicle data');
                        return;
                    }

                    const units = session.getItems('avl_unit');
                    if (!units || !units.length) {
                        setVehicleStatusPill('warning', 'No GPS units found');
                        return;
                    }

                    // Find matched unit
                    if (MATCHED_UNIT_ID) {
                        liveUnit = units.find(u => String(u.getId()) === String(MATCHED_UNIT_ID));
                    }
                    if (!liveUnit && FLEET_NO) {
                        liveUnit = units.find(u => (u.getName() || '').toLowerCase().includes(String(FLEET_NO).toLowerCase()));
                    }
                    if (!liveUnit && units.length > 0) {
                        liveUnit = units[0]; // fallback
                    }

                    if (liveUnit) {
                        updateLiveVehicleState(liveUnit);
                        // Subscribe for live events
                        session.addListener('messageRegistered', function (evt) {
                            if (evt && evt.getData() && String(evt.getData().id) === String(liveUnit.getId())) {
                                updateLiveVehicleState(liveUnit);
                            }
                        });
                        // Periodic fallback poll
                        setInterval(() => {
                            updateLiveVehicleState(liveUnit);
                        }, 5000);
                    } else {
                        setVehicleStatusPill('warning', `Fleet unit "${FLEET_NO}" not online`);
                    }
                }
            );
        }

        function updateLiveVehicleState(unit) {
            if (!unit) return;

            const pos = unit.getPosition();
            const lastMsg = unit.getLastMessage();

            if (pos && typeof pos.x === 'number' && typeof pos.y === 'number') {
                const lng = pos.x;
                const lat = pos.y;
                const speed = Math.round(pos.s || 0);
                const heading = Math.round(pos.c || 0);
                const time = pos.t ? new Date(pos.t * 1000).toLocaleTimeString() : new Date().toLocaleTimeString();

                vehicleCoordinates = [lng, lat];
                vehicleHeading = heading;
                vehicleSpeed = speed;
                vehicleState = speed > 0 ? 'moving' : 'stopped';

                // Update UI Telemetry
                document.getElementById('telemetry-speed').textContent = `${speed} km/h`;
                document.getElementById('telemetry-speed').className = speed > 0 ? 'telemetry-value text-success' : 'telemetry-value text-muted';
                document.getElementById('telemetry-movement').textContent = speed > 0 ? `Moving (${speed} km/h)` : 'Stopped / Parked';
                document.getElementById('telemetry-heading').textContent = `${heading}°`;
                document.getElementById('telemetry-last-time').textContent = time;

                setVehicleStatusPill('online', `Tracking Live &bull; ${speed} km/h`);

                renderOrUpdateVehicleMarker(lat, lng, speed, heading, time);
            }
        }

        function renderOrUpdateVehicleMarker(lat, lng, speed, heading, time) {
            if (!map || !mapReady) return;

            const fleetName = WAYBILL_DATA.vehicle ? `${WAYBILL_DATA.vehicle.fleet_no} (${WAYBILL_DATA.vehicle.truck_no || ''})` : (FLEET_NO || 'Vehicle');
            const driverName = WAYBILL_DATA.driver ? WAYBILL_DATA.driver.full_name : 'Assigned Driver';

            if (!vehicleMarker) {
                const el = document.createElement('div');
                el.className = 'marker-vehicle-container';
                el.id = 'live-vehicle-marker-el';
                el.innerHTML = `
                    <div class="vehicle-pulse-ring"></div>
                    <div class="vehicle-heading-arrow" style="transform: translateX(-50%) rotate(${heading}deg);"></div>
                    <div class="marker-vehicle-body ${speed > 0 ? 'moving' : 'stopped'}">
                        <i class="ri-truck-fill"></i>
                    </div>
                    <div class="marker-vehicle-tag">${escapeHtml(fleetName)}</div>
                `;

                const vehiclePopup = new maplibregl.Popup({ offset: 28 }).setHTML(`
                    <div class="popup-card">
                        <div class="popup-header" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
                            <div class="popup-header-icon" style="color: #22c55e;"><i class="ri-truck-fill"></i></div>
                            <div>
                                <h6 class="popup-header-title text-white">${escapeHtml(fleetName)}</h6>
                                <p class="popup-header-sub"><i class="ri-user-line me-1"></i> ${escapeHtml(driverName)}</p>
                            </div>
                        </div>
                        <div class="popup-body">
                            <div class="popup-row">
                                <span class="popup-label">Speed:</span>
                                <span class="popup-value fw-bold text-success" id="popup-vehicle-speed">${speed} km/h</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Status:</span>
                                <span class="popup-value fw-semibold" id="popup-vehicle-status">${speed > 0 ? 'In Motion' : 'Stationary'}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Heading:</span>
                                <span class="popup-value" id="popup-vehicle-heading">${heading}°</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Last Ping:</span>
                                <span class="popup-value text-muted" id="popup-vehicle-time">${escapeHtml(time)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Coordinates:</span>
                                <span class="popup-value font-monospace">${lat.toFixed(5)}, ${lng.toFixed(5)}</span>
                            </div>
                        </div>
                        <div class="popup-footer">
                            <a href="{{ route('map.view') }}?vehicle=${encodeURIComponent(WAYBILL_DATA.vehicle ? WAYBILL_DATA.vehicle.fleet_no : '')}" target="_blank" class="popup-btn">
                                <i class="ri-external-link-line"></i> View on Fleet Map
                            </a>
                        </div>
                    </div>
                `);

                vehicleMarker = new maplibregl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([lng, lat])
                    .setPopup(vehiclePopup)
                    .addTo(map);

                allBounds.push([lng, lat]);
            } else {
                vehicleMarker.setLngLat([lng, lat]);
                const el = document.getElementById('live-vehicle-marker-el');
                if (el) {
                    const arrow = el.querySelector('.vehicle-heading-arrow');
                    if (arrow) arrow.style.transform = `translateX(-50%) rotate(${heading}deg)`;
                    const body = el.querySelector('.marker-vehicle-body');
                    if (body) body.className = `marker-vehicle-body ${speed > 0 ? 'moving' : 'stopped'}`;
                }

                // Update popup contents if open
                const speedSpan = document.getElementById('popup-vehicle-speed');
                if (speedSpan) speedSpan.textContent = `${speed} km/h`;
                const statusSpan = document.getElementById('popup-vehicle-status');
                if (statusSpan) statusSpan.textContent = speed > 0 ? 'In Motion' : 'Stationary';
                const timeSpan = document.getElementById('popup-vehicle-time');
                if (timeSpan) timeSpan.textContent = time;
            }
        }

        function setVehicleStatusPill(state, text) {
            const pill = document.getElementById('live-status-pill');
            const dot = document.getElementById('status-dot');
            const label = document.getElementById('status-text');
            if (!pill || !dot || !label) return;

            label.innerHTML = text;
            if (state === 'online') {
                dot.style.background = '#10b981';
                dot.className = 'status-dot-pulse';
            } else if (state === 'loading') {
                dot.style.background = '#f59e0b';
                dot.className = 'status-dot-pulse';
            } else {
                dot.style.background = '#94a3b8';
                dot.className = '';
            }
        }

        /* ============================================================
           5. NAVIGATION & CAMERA HELPERS
           ============================================================ */
        function fitAllMarkers() {
            if (!map || !mapReady || allBounds.length === 0) return;

            if (allBounds.length === 1) {
                map.flyTo({ center: allBounds[0], zoom: 12 });
                return;
            }

            const bounds = allBounds.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(allBounds[0], allBounds[0]));
            map.fitBounds(bounds, {
                padding: { top: 60, bottom: 60, left: 60, right: 60 },
                maxZoom: 14
            });
        }

        function flyToOrigin() {
            const lat = parseFloat(WAYBILL_DATA.origin_lat);
            const lng = parseFloat(WAYBILL_DATA.origin_lng);
            if (!isNaN(lat) && !isNaN(lng) && map) {
                map.flyTo({ center: [lng, lat], zoom: 13, essential: true });
                const startObj = routeMarkers.find(m => m.type === 'start');
                if (startObj && startObj.marker) startObj.marker.togglePopup();
            }
        }

        function flyToCheckpoint(idx) {
            const cpObj = routeMarkers.find(m => m.type === 'cp' && m.index === idx);
            if (cpObj && cpObj.marker && map) {
                map.flyTo({ center: cpObj.lngLat, zoom: 13, essential: true });
                cpObj.marker.togglePopup();
            }
        }

        function flyToDestination() {
            const lat = parseFloat(WAYBILL_DATA.destination_lat);
            const lng = parseFloat(WAYBILL_DATA.destination_lng);
            if (!isNaN(lat) && !isNaN(lng) && map) {
                map.flyTo({ center: [lng, lat], zoom: 13, essential: true });
                const endObj = routeMarkers.find(m => m.type === 'end');
                if (endObj && endObj.marker) endObj.marker.togglePopup();
            }
        }

        function flyToIncident(idx) {
            const incObj = incidentMarkers.find(m => m.index === idx);
            if (incObj && incObj.marker && map) {
                map.flyTo({ center: incObj.lngLat, zoom: 14, essential: true });
                incObj.marker.togglePopup();
            }
        }

        function focusVehicleMarker() {
            if (vehicleCoordinates && map) {
                map.flyTo({ center: vehicleCoordinates, zoom: 14, essential: true });
                if (vehicleMarker) vehicleMarker.togglePopup();
            } else {
                alert('Vehicle position is currently updating or offline.');
            }
        }

        /* ============================================================
           6. FILTER CHIPS & LAYER TOGGLES
           ============================================================ */
        function initFilterChips() {
            const chips = document.querySelectorAll('[data-layer-filter]');
            chips.forEach(chip => {
                chip.addEventListener('click', function () {
                    const filter = this.dataset.layerFilter;

                    if (filter === 'all') {
                        const setActive = !this.classList.contains('active');
                        chips.forEach(c => c.classList.toggle('active', setActive));
                        Object.keys(layerVisibility).forEach(k => layerVisibility[k] = setActive);
                        applyLayerVisibility();
                        return;
                    }

                    this.classList.toggle('active');
                    layerVisibility[filter] = this.classList.contains('active');
                    applyLayerVisibility();
                });
            });

            document.getElementById('btn-fit-all')?.addEventListener('click', fitAllMarkers);
            document.getElementById('btn-focus-vehicle')?.addEventListener('click', focusVehicleMarker);
        }

        function applyLayerVisibility() {
            // A. Route line & glow
            if (map && mapReady) {
                const routeVisible = layerVisibility.route ? 'visible' : 'none';
                if (map.getLayer(routeGeoJsonLayerId)) map.setLayoutProperty(routeGeoJsonLayerId, 'visibility', routeVisible);
                if (map.getLayer(routeGlowLayerId)) map.setLayoutProperty(routeGlowLayerId, 'visibility', routeVisible);
            }

            // B. Route markers
            routeMarkers.forEach(m => {
                m.marker.getElement().style.display = layerVisibility.route ? 'flex' : 'none';
            });

            // C. Live Vehicle marker
            if (vehicleMarker) {
                vehicleMarker.getElement().style.display = layerVisibility.vehicle ? 'flex' : 'none';
            }

            // D. Incidents & Driver Behavior markers
            incidentMarkers.forEach(inc => {
                const show = (inc.category === 'incident' && layerVisibility.incidents) ||
                             (inc.category === 'driver_behavior' && layerVisibility.driver_behavior);
                inc.marker.getElement().style.display = show ? 'flex' : 'none';
            });
        }

        function toggleLayer(category) {
            layerVisibility[category] = !layerVisibility[category];
            const chip = document.querySelector(`[data-layer-filter="${category}"]`);
            if (chip) chip.classList.toggle('active', layerVisibility[category]);
            applyLayerVisibility();
        }

        /* ============================================================
           7. FULLSCREEN TOGGLE
           ============================================================ */
        function initFullscreen() {
            const fsBtn = document.getElementById('btn-fullscreen-toggle');
            const mapBox = document.getElementById('map-container-box');
            if (!fsBtn || !mapBox) return;

            fsBtn.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    mapBox.requestFullscreen().catch(err => {
                        console.error('Error attempting to enable fullscreen:', err);
                    });
                } else {
                    document.exitFullscreen();
                }
            });

            document.addEventListener('fullscreenchange', function () {
                const icon = document.getElementById('fs-icon');
                if (document.fullscreenElement) {
                    if (icon) icon.className = 'ri-fullscreen-exit-line';
                } else {
                    if (icon) icon.className = 'ri-fullscreen-line';
                }
                if (map) setTimeout(() => map.resize(), 200);
            });
        }

        function escapeHtml(str) {
            if (str == null) return '';
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }
    </script>
</x-layouts.auth>
