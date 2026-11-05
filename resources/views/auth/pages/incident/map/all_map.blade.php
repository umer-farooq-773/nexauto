<x-layouts.auth page-title="{{ __('All Incidents & Driver Behavior Map') }}" sub-title="{{ __('Fleet Risk Mapping, Real-time Incident Pinpointing & Driver Conduct Analytics') }}">
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
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: var(--bg);
        }

        .all-map-wrapper {
            max-width: 1600px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* ── Top Header ── */
        .map-header {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 24px;
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
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 14px rgba(239,68,68,0.3);
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

        /* ── KPI Metrics Cards Row ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .kpi-info-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .kpi-info-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            margin-top: 2px;
        }

        .kpi-icon-pill {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .kpi-pill-red { background: #fee2e2; color: #dc2626; }
        .kpi-pill-blue { background: #e0f2fe; color: #0284c7; }
        .kpi-pill-amber { background: #fef3c7; color: #d97706; }
        .kpi-pill-green { background: #dcfce7; color: #16a34a; }
        .kpi-pill-purple { background: #f3e8ff; color: #9333ea; }

        /* ── Map Filter Toolbar (Top of Map) ── */
        .map-filter-toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }

        .filter-section-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-section-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 600;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .filter-pill:hover {
            background: #e2e8f0;
            color: var(--text);
        }
        .filter-pill.active {
            background: #0f172a;
            border-color: #0f172a;
            color: white;
            box-shadow: 0 2px 8px rgba(15,23,42,0.2);
        }
        .filter-pill.active-incident {
            background: #dc2626 !important;
            border-color: #dc2626 !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(220,38,38,0.3) !important;
        }
        .filter-pill.active-behavior {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(2,132,199,0.3) !important;
        }
        .filter-pill .pill-count {
            background: rgba(0,0,0,0.09);
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 11px;
        }
        .filter-pill.active .pill-count,
        .filter-pill.active-incident .pill-count,
        .filter-pill.active-behavior .pill-count {
            background: rgba(255,255,255,0.25);
            color: white;
        }

        .filter-type-chips {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            padding: 10px 18px;
            background: #f8fafc;
            border: 1px dashed var(--border);
            border-radius: var(--radius-md);
            margin-bottom: 14px;
        }

        .type-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            background: white;
            border: 1px solid var(--border);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .type-chip:hover {
            border-color: #94a3b8;
            color: var(--text);
        }
        .type-chip.active {
            border-color: currentColor;
            font-weight: 700;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .type-chip .type-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .search-input-group {
            position: relative;
            min-width: 220px;
        }
        .search-input-group i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-faint);
            font-size: 14px;
        }
        .search-input-field {
            width: 100%;
            padding: 7px 12px 7px 32px;
            font-size: 12.5px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            outline: none;
            transition: border-color 0.15s;
        }
        .search-input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .filter-select {
            padding: 7px 12px;
            font-size: 12.5px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            outline: none;
            cursor: pointer;
        }

        /* ── Full Width Map Canvas Container ── */
        .map-canvas-card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            min-height: 560px;
            height: 580px;
            margin-bottom: 24px;
        }

        #all-incidents-map {
            width: 100%;
            height: 100%;
        }

        /* Floating Overlays on Map */
        .map-overlay-count {
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

        .pulse-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.25);
            animation: pulseDot 2s infinite;
        }
        @keyframes pulseDot {
            0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
            70% { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
            100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
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
            border-radius: 50%;
            background: white;
            border: 1px solid var(--border);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: var(--shadow-md);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tool-round-btn:hover {
            background: var(--surface-subtle);
            color: var(--primary);
            transform: scale(1.06);
        }

        /* ── Custom Map HTML Markers ── */
        .marker-incident-container {
            position: relative;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .marker-incident-container:hover {
            transform: scale(1.22) translateY(-4px);
            z-index: 50 !important;
        }

        .marker-incident-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            opacity: 0.35;
            animation: pinPulse 2s infinite;
        }
        @keyframes pinPulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.6; }
            100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
        }

        .marker-incident-bubble {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            border: 2px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .marker-incident-tag {
            background: rgba(15, 23, 42, 0.9);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            margin-top: 4px;
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
            pointer-events: none;
        }

        /* ── MapLibre Rich Popup Styles (Fixed overlapping close button & clean spacing) ── */
        .maplibregl-popup {
            z-index: 1000 !important;
        }
        .maplibregl-popup-content {
            padding: 0 !important;
            border-radius: var(--radius-md) !important;
            box-shadow: var(--shadow-lg) !important;
            border: 1px solid var(--border) !important;
            overflow: hidden !important;
            width: 320px !important;
            max-width: 340px !important;
            font-family: 'Inter', sans-serif !important;
            z-index: 1000 !important;
        }
        .maplibregl-popup-close-button {
            top: 8px !important;
            right: 8px !important;
            width: 24px !important;
            height: 24px !important;
            border-radius: 50% !important;
            background: rgba(0, 0, 0, 0.25) !important;
            color: white !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            line-height: 1 !important;
            border: none !important;
            outline: none !important;
            cursor: pointer !important;
            transition: background 0.15s !important;
            z-index: 1001 !important;
        }
        .maplibregl-popup-close-button:hover {
            background: rgba(0, 0, 0, 0.45) !important;
        }

        .popup-card {
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
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .popup-header-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }
        .popup-header-sub {
            font-size: 11px;
            opacity: 0.9;
            margin: 0;
        }

        .popup-body {
            padding: 12px 14px;
            background: white;
            font-size: 12px;
        }
        .popup-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 7px;
        }
        .popup-label {
            width: 76px;
            color: var(--text-muted);
            font-weight: 600;
            flex-shrink: 0;
            font-size: 11px;
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
            justify-content: flex-end;
            gap: 8px;
        }
        .popup-btn {
            font-size: 11.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: background 0.15s;
        }
        .popup-btn:hover {
            background: var(--primary-dark);
            color: white;
        }
        .popup-btn-outline {
            font-size: 11.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            background: white;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .popup-btn-outline:hover {
            background: var(--surface-subtle);
            color: var(--primary);
        }

        /* ── Exact Status & Category Pill Badges (Image 3 Style) ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
            white-space: nowrap;
            border: 1px solid transparent;
            transition: all 0.15s ease;
            user-select: none;
        }

        /* Released / Resolved / Recovered / Completed / Closed (Green) */
        .status-pill.status-released,
        .status-pill.status-resolved,
        .status-pill.status-recovered,
        .status-pill.status-completed,
        .status-pill.status-closed {
            background-color: #f0fdf4 !important;
            border-color: #86efac !important;
            color: #15803d !important;
        }
        .status-pill.status-released i.status-icon,
        .status-pill.status-resolved i.status-icon,
        .status-pill.status-recovered i.status-icon,
        .status-pill.status-completed i.status-icon,
        .status-pill.status-closed i.status-icon {
            color: #16a34a !important;
        }

        /* Impounded / Detained / Pending / Open / Stuck / Accident (Red) */
        .status-pill.status-impounded,
        .status-pill.status-detained,
        .status-pill.status-pending,
        .status-pill.status-open,
        .status-pill.status-stuck {
            background-color: #fef2f2 !important;
            border-color: #fca5a5 !important;
            color: #991b1b !important;
        }
        .status-pill.status-impounded i.status-icon,
        .status-pill.status-detained i.status-icon,
        .status-pill.status-pending i.status-icon,
        .status-pill.status-open i.status-icon,
        .status-pill.status-stuck i.status-icon {
            color: #dc2626 !important;
        }

        /* In Progress / Active / Recovery In Progress / Reviewed (Amber/Yellow) */
        .status-pill.status-in_progress,
        .status-pill.status-active,
        .status-pill.status-reviewed {
            background-color: #fefce8 !important;
            border-color: #fde047 !important;
            color: #a16207 !important;
        }
        .status-pill.status-in_progress i.status-icon,
        .status-pill.status-active i.status-icon,
        .status-pill.status-reviewed i.status-icon {
            color: #d97706 !important;
        }

        /* Route Diversion Active (Blue) */
        .status-pill.status-diversion {
            background-color: #eff6ff !important;
            border-color: #93c5fd !important;
            color: #1d4ed8 !important;
        }
        .status-pill.status-diversion i.status-icon {
            color: #2563eb !important;
        }

        .status-pill .status-chevron {
            font-size: 9.5px;
            opacity: 0.65;
            margin-left: 2px;
        }

        /* Category Pill Badges (Image 2 style) */
        .category-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .category-pill.cat-incident {
            background: #fff5f5;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .category-pill.cat-behavior {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
        }

        /* ── Below Map: Incident & Driver Behavior Records List Table (Fixed Overflow Box) ── */
        .table-container-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }

        .table-card-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .table-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
        }

        .custom-data-table {
            width: 100%;
            min-width: 1020px;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }
        .custom-data-table th {
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 14px;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }
        .custom-data-table td {
            padding: 13px 14px;
            font-size: 12.5px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text);
            background: white;
        }
        .custom-data-table tbody tr {
            transition: background 0.15s ease;
        }
        .custom-data-table tbody tr:hover td {
            background: #f8fafc;
        }

        .table-icon-badge {
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

        .locate-map-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 6px;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .locate-map-btn:hover {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .view-record-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 6px;
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .view-record-btn:hover {
            background: var(--surface-subtle);
            border-color: #94a3b8;
            color: var(--text);
        }

        /* ── Modern Pagination Bar ── */
        .table-pagination-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding-top: 6px;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .page-btn:hover:not(:disabled) {
            background: var(--surface-subtle);
            border-color: #cbd5e1;
            color: var(--primary);
        }
        .page-btn.active {
            background: #0f172a;
            border-color: #0f172a;
            color: white;
            box-shadow: 0 2px 6px rgba(15,23,42,0.25);
        }
        .page-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            background: #f8fafc;
        }

        .empty-table-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-table-state i {
            font-size: 40px;
            color: #94a3b8;
            margin-bottom: 10px;
            display: inline-block;
        }
    </style>

    <div class="all-map-wrapper">
        <!-- ── Top Header Bar ── -->
        <div class="map-header">
            <div class="header-title-box">
                <div class="header-icon-badge">
                    <i class="ri-radar-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        {{ __('All Incidents & Driver Behavior Map') }}
                        <span class="badge bg-danger fs-6 ms-2 px-2.5 py-1 align-middle" id="header-total-badge">
                            {{ $totalCount }} {{ __('Events') }}
                        </span>
                    </h4>
                    <p class="text-muted small mb-0 mt-1">
                        <i class="ri-map-pin-range-line text-primary me-1"></i>
                        {{ __('Comprehensive Fleet Risk Pinpointing, Location Analytics & Real-time Record Management') }}
                    </p>
                </div>
            </div>

            <div class="header-actions">
                <a href="{{ route('waybill.index') }}" class="action-btn">
                    <i class="ri-arrow-left-line"></i> {{ __('Waybills') }}
                </a>
                <button type="button" class="action-btn" onclick="fitAllMarkers()" title="{{ __('Center & Fit All Markers') }}">
                    <i class="ri-focus-3-line"></i> {{ __('Re-fit View') }}
                </button>
                <button type="button" class="action-btn" onclick="resetFilters()" title="{{ __('Reset All Filters') }}">
                    <i class="ri-refresh-line"></i> {{ __('Reset') }}
                </button>
                <button type="button" class="action-btn btn-primary-action" id="btn-fullscreen-toggle" title="{{ __('Toggle Fullscreen') }}">
                    <i class="ri-fullscreen-line" id="fs-icon"></i>
                </button>
            </div>
        </div>

        <!-- ── KPI Summary Cards ── -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div>
                    <div class="kpi-info-label">{{ __('Total Logged Events') }}</div>
                    <div class="kpi-info-value" id="kpi-total-val">{{ $totalCount }}</div>
                </div>
                <div class="kpi-icon-pill kpi-pill-purple">
                    <i class="ri-radar-line"></i>
                </div>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-info-label">{{ __('Incident & Intervention') }}</div>
                    <div class="kpi-info-value text-danger" id="kpi-incident-val">{{ $incidentCount }}</div>
                </div>
                <div class="kpi-icon-pill kpi-pill-red">
                    <i class="ri-alarm-warning-line"></i>
                </div>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-info-label">{{ __('Driver Behaviors') }}</div>
                    <div class="kpi-info-value text-info" id="kpi-behavior-val">{{ $behaviorCount }}</div>
                </div>
                <div class="kpi-icon-pill kpi-pill-blue">
                    <i class="ri-user-star-line"></i>
                </div>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-info-label">{{ __('Pending / Active') }}</div>
                    <div class="kpi-info-value text-warning" id="kpi-pending-val">{{ $pendingCount }}</div>
                </div>
                <div class="kpi-icon-pill kpi-pill-amber">
                    <i class="ri-time-line"></i>
                </div>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-info-label">{{ __('Resolved / Closed') }}</div>
                    <div class="kpi-info-value text-success" id="kpi-resolved-val">{{ $resolvedCount }}</div>
                </div>
                <div class="kpi-icon-pill kpi-pill-green">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
            </div>
        </div>

        <!-- ── Map Filter Toolbar (On Top of Map) ── -->
        <div class="map-filter-toolbar">
            <div class="filter-section-left">
                <!-- Category Tabs -->
                <div class="filter-pill active" data-category="all">
                    <i class="ri-apps-2-line"></i> {{ __('All Records') }}
                    <span class="pill-count" id="chip-count-all">{{ $totalCount }}</span>
                </div>
                <div class="filter-pill" data-category="incident">
                    <i class="ri-alarm-warning-fill text-danger"></i> {{ __('Incident & Intervention') }}
                    <span class="pill-count" id="chip-count-incident">{{ $incidentCount }}</span>
                </div>
                <div class="filter-pill" data-category="driver_behavior">
                    <i class="ri-user-star-line text-info"></i> {{ __('Driver Behavior') }}
                    <span class="pill-count" id="chip-count-behavior">{{ $behaviorCount }}</span>
                </div>
            </div>

            <div class="filter-section-right">
                <!-- Search Input -->
                <div class="search-input-group">
                    <i class="ri-search-line"></i>
                    <input type="text" id="filter-search-input" class="search-input-field" placeholder="{{ __('Search Waybill, Vehicle, Driver…') }}">
                </div>

                <!-- Status Filter Dropdown -->
                <select id="filter-status-select" class="filter-select">
                    <option value="all">{{ __('All Statuses') }}</option>
                    <option value="pending">{{ __('Pending / Open / Stuck') }}</option>
                    <option value="in_progress">{{ __('In Progress / Active') }}</option>
                    <option value="resolved">{{ __('Resolved / Released / Closed') }}</option>
                </select>
            </div>
        </div>

        <!-- ── Incident Type Sub-Filter Chips ── -->
        <div class="filter-type-chips">
            <span class="text-muted fw-bold me-2" style="font-size: 11px; text-transform: uppercase;">{{ __('Filter by Type:') }}</span>
            <div class="type-chip active" data-type="all" style="color: #0f172a;">
                <span class="type-dot" style="background: #0f172a;"></span> {{ __('All Types') }}
            </div>
            <div class="type-chip" data-type="accident" style="color: var(--c-accident);">
                <span class="type-dot" style="background: var(--c-accident);"></span> {{ __('Accidents') }} ({{ $typeCounts['accident'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="vehicle_breakdown" style="color: var(--c-breakdown);">
                <span class="type-dot" style="background: var(--c-breakdown);"></span> {{ __('Breakdowns') }} ({{ $typeCounts['vehicle_breakdown'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="vehicle_bogged_down" style="color: var(--c-bogged);">
                <span class="type-dot" style="background: var(--c-bogged);"></span> {{ __('Bogged Down') }} ({{ $typeCounts['vehicle_bogged_down'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="impoundment" style="color: var(--c-impound);">
                <span class="type-dot" style="background: var(--c-impound);"></span> {{ __('Impoundments') }} ({{ $typeCounts['impoundment'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="illegal_detention" style="color: var(--c-detention);">
                <span class="type-dot" style="background: var(--c-detention);"></span> {{ __('Detentions') }} ({{ $typeCounts['illegal_detention'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="route_diversion" style="color: var(--c-diversion);">
                <span class="type-dot" style="background: var(--c-diversion);"></span> {{ __('Diversions') }} ({{ $typeCounts['route_diversion'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="illegal_haulage" style="color: var(--c-haulage);">
                <span class="type-dot" style="background: var(--c-haulage);"></span> {{ __('Haulage') }} ({{ $typeCounts['illegal_haulage'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="illegal_shortage" style="color: var(--c-shortage);">
                <span class="type-dot" style="background: var(--c-shortage);"></span> {{ __('Shortage') }} ({{ $typeCounts['illegal_shortage'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="breathaliser_test" style="color: var(--c-breath);">
                <span class="type-dot" style="background: var(--c-breath);"></span> {{ __('Breathaliser') }} ({{ $typeCounts['breathaliser_test'] ?? 0 }})
            </div>
            <div class="type-chip" data-type="driver_misconduct" style="color: var(--c-misconduct);">
                <span class="type-dot" style="background: var(--c-misconduct);"></span> {{ __('Misconduct') }} ({{ $typeCounts['driver_misconduct'] ?? 0 }})
            </div>
        </div>

        <!-- ── Full Width Map Canvas ── -->
        <div class="map-canvas-card" id="map-container-box">
            <!-- Map Overlay Status Pill -->
            <div class="map-overlay-count" id="map-status-pill">
                <span class="pulse-indicator"></span>
                <span id="map-count-label">{{ __('Displaying') }} <strong>{{ $totalCount }}</strong> {{ __('Mapped Pins') }}</span>
            </div>

            <!-- Floating Map Quick Tools -->
            <div class="map-quick-tools">
                <button type="button" class="tool-round-btn" onclick="fitAllMarkers()" title="{{ __('Center all pins') }}">
                    <i class="ri-fullscreen-line"></i>
                </button>
                <button type="button" class="tool-round-btn" onclick="toggleMapStyle()" title="{{ __('Toggle Map Contrast') }}">
                    <i class="ri-contrast-2-line"></i>
                </button>
            </div>

            <!-- MapLibre Canvas -->
            <div id="all-incidents-map"></div>
        </div>

        <!-- ── Below Map: Incident & Driver Behavior List Table ── -->
        <div class="table-container-card" id="records-list-section">
            <div class="table-card-header">
                <div>
                    <h5 class="table-title">
                        <i class="ri-file-list-3-line text-primary"></i>
                        {{ __('Logged Incidents & Driver Behavior Records') }}
                        <span class="badge bg-secondary rounded-pill fs-6 ms-2" id="table-filtered-count">{{ $totalCount }}</span>
                    </h5>
                    <p class="text-muted small mb-0 mt-1">
                        {{ __('Click "Locate on Map" to fly to the incident location or "View" for complete record details.') }}
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="action-btn" onclick="exportFilteredRows()" title="{{ __('Export displayed records to CSV') }}">
                        <i class="ri-download-2-line"></i> {{ __('Export CSV') }}
                    </button>
                </div>
            </div>

            <!-- Responsive Table Wrapper (strictly contained inside the box) -->
            <div class="table-responsive">
                <table class="custom-data-table" id="incidents-data-table">
                    <thead>
                        <tr>
                            <th style="width: 17%;">{{ __('Event / Type') }}</th>
                            <th style="width: 13%;">{{ __('Category') }}</th>
                            <th style="width: 9%;">{{ __('Waybill #') }}</th>
                            <th style="width: 12%;">{{ __('Asset (Fleet/Truck)') }}</th>
                            <th style="width: 11%;">{{ __('Driver') }}</th>
                            <th style="width: 15%;">{{ __('Date & Location') }}</th>
                            <th style="width: 12%;">{{ __('Details / Reason') }}</th>
                            <th style="width: 11%;">{{ __('Status') }}</th>
                            <th style="width: 10%;" class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="incidents-table-tbody">
                        @forelse($events as $index => $item)
                            <tr data-index="{{ $index }}"
                                data-category="{{ $item['category'] }}"
                                data-type="{{ $item['type_key'] }}"
                                data-status="{{ $item['status_raw'] }}"
                                data-search="{{ strtolower(($item['type_label'] ?? '') . ' ' . ($item['waybill_number'] ?? '') . ' ' . ($item['fleet_no'] ?? '') . ' ' . ($item['truck_no'] ?? '') . ' ' . ($item['driver_name'] ?? '') . ' ' . ($item['location'] ?? '') . ' ' . ($item['detail_type'] ?? '') . ' ' . ($item['description'] ?? '')) }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="table-icon-badge" style="background: {{ $item['color'] }};">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $item['type_label'] }}</div>
                                            @if($item['detail_extra'])
                                                <div class="text-muted" style="font-size: 11px;">{{ $item['detail_extra'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-pill {{ $item['category'] === 'incident' ? 'cat-incident' : 'cat-behavior' }}">
                                        {{ $item['category_label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($item['waybill_number'])
                                        <a href="{{ $item['waybill_url'] }}" class="fw-bold text-primary text-decoration-none">
                                            #{{ $item['waybill_number'] }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item['fleet_no'] }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $item['truck_no'] }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $item['driver_name'] }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item['date'] }}</div>
                                    <div class="text-muted text-truncate" style="max-width: 170px;" title="{{ $item['location'] }}">
                                        <i class="ri-map-pin-line text-danger me-1"></i>{{ $item['location'] }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate fw-medium" style="max-width: 180px;" title="{{ $item['detail_type'] }} - {{ $item['description'] }}">
                                        {{ $item['detail_type'] }}
                                    </div>
                                    @if($item['damage_cost'])
                                        <div class="text-danger fw-bold" style="font-size: 11.5px;">
                                            {{ __('Cost:') }} ${{ $item['damage_cost'] }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $item['status_pill_class'] ?? 'status-pending' }}">
                                        <i class="{{ $item['status_icon'] ?? 'fa-solid fa-circle' }} status-icon"></i>
                                        <span>{{ $item['status_label'] }}</span>
                                        {{-- <i class="fa-solid fa-chevron-down status-chevron"></i> --}}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @if($item['lat'] !== null && $item['lng'] !== null)
                                            <button type="button" class="locate-map-btn" onclick="flyToMapIndex({{ $index }})" title="{{ __('Locate Pin on Map') }}">
                                                <i class="ri-focus-2-line"></i> {{ __('Locate') }}
                                            </button>
                                        @endif
                                        <a href="{{ $item['show_url'] }}" target="_blank" class="view-record-btn" title="{{ __('View Full Details') }}">
                                            <i class="ri-external-link-line"></i> {{ __('View') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-table-state">
                                        <i class="ri-checkbox-circle-line text-success"></i>
                                        <h6>{{ __('No incidents or driver behaviors recorded.') }}</h6>
                                        <p class="small text-muted">{{ __('All fleet operations are currently clear of logged incidents.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Empty Filter State (Hidden by default) -->
            <div class="empty-table-state d-none" id="empty-filter-state">
                <i class="ri-filter-off-line"></i>
                <h6>{{ __('No matching records found') }}</h6>
                <p class="small text-muted">{{ __('Try clearing your search query or selecting a different category/status filter.') }}</p>
                <button type="button" class="action-btn mt-2" onclick="resetFilters()">
                    <i class="ri-refresh-line"></i> {{ __('Reset Filters') }}
                </button>
            </div>

            <!-- ── Dynamic Pagination Footer ── -->
            <div class="table-pagination-footer" id="pagination-footer">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">{{ __('Show') }}</span>
                    <select id="pagination-page-size" class="filter-select py-1 px-2" style="font-size: 12px;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="-1">{{ __('All') }}</option>
                    </select>
                    <span class="text-muted small">{{ __('entries') }}</span>
                    <span class="text-muted small ms-3 border-start ps-3" id="pagination-info-text">
                        {{ __('Showing 1 to 10 of :total entries', ['total' => $totalCount]) }}
                    </span>
                </div>

                <div class="d-flex align-items-center gap-1" id="pagination-controls-group">
                    <button type="button" class="page-btn" id="btn-prev-page" onclick="changePage(currentPage - 1)">
                        <i class="ri-arrow-left-s-line"></i> {{ __('Prev') }}
                    </button>
                    <div class="d-inline-flex align-items-center gap-1" id="pagination-number-buttons"></div>
                    <button type="button" class="page-btn" id="btn-next-page" onclick="changePage(currentPage + 1)">
                        {{ __('Next') }} <i class="ri-arrow-right-s-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MapLibre & Synchronized Interactive Logic ── -->
    <script>
        const ALL_EVENTS_DATA = @json($events);
        const GEOAPIFY_KEY    = @json($geoapifyApiKey);

        let map = null;
        let mapReady = false;
        let mapMarkers = [];
        let allMarkerBounds = [];

        // Active filter state
        let activeCategory = 'all'; // 'all', 'incident', 'driver_behavior'
        let activeType = 'all';     // 'all' or specific type key
        let activeStatus = 'all';   // 'all', 'pending', 'in_progress', 'resolved'
        let searchQuery = '';

        // Pagination state
        let currentPage = 1;
        let pageSize = 10;
        let currentFilteredIndices = [];

        // Map Styles
        const MAP_STYLE_OSM = {
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
            layers: [{
                id: 'osm-tiles-layer',
                type: 'raster',
                source: 'osm-tiles',
                minzoom: 0,
                maxzoom: 19
            }]
        };

        document.addEventListener('DOMContentLoaded', function () {
            initMap();
            initFilterListeners();
            initPaginationListeners();
            initFullscreen();
            applyUnifiedFilters();
        });

        /* ============================================================
           1. MAP INITIALIZATION (MapLibre GL JS)
           ============================================================ */
        function initMap() {
            map = new maplibregl.Map({
                container: 'all-incidents-map',
                style: MAP_STYLE_OSM,
                center: [73.0479, 33.6844], // Default Islamabad fallback
                zoom: 5
            });

            map.addControl(new maplibregl.NavigationControl(), 'top-right');
            map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

            map.on('load', function () {
                mapReady = true;
                renderAllIncidentMarkers();
                fitAllMarkers();
            });
        }

        /* ============================================================
           2. RENDER ALL INCIDENT & BEHAVIOR MARKERS
           ============================================================ */
        function renderAllIncidentMarkers() {
            mapMarkers = [];
            allMarkerBounds = [];

            ALL_EVENTS_DATA.forEach((item, index) => {
                if (item.lat === null || item.lng === null || isNaN(item.lat) || isNaN(item.lng)) {
                    return;
                }

                // Create custom marker DOM element
                const el = document.createElement('div');
                el.className = 'marker-incident-container';
                el.dataset.index = index;
                el.dataset.category = item.category;
                el.dataset.type = item.type_key;
                el.dataset.status = item.status_raw;

                el.innerHTML = `
                    <div class="marker-incident-pulse" style="background: ${item.color};"></div>
                    <div class="marker-incident-bubble" style="background: ${item.color};">
                        <i class="${item.icon}"></i>
                    </div>
                    <div class="marker-incident-tag">${escapeHtml(item.type_label)}</div>
                `;

                // Build rich interactive popup with clean layout & no overlap
                let extraDetailsHtml = '';
                if (item.detail_type) {
                    extraDetailsHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Detail:</span>
                            <span class="popup-value fw-semibold">${escapeHtml(item.detail_type)}</span>
                        </div>
                    `;
                }
                if (item.waybill_number) {
                    extraDetailsHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Waybill:</span>
                            <span class="popup-value"><a href="${item.waybill_url}" target="_blank" class="fw-bold text-primary">#${escapeHtml(item.waybill_number)}</a></span>
                        </div>
                    `;
                }
                if (item.fleet_no && item.fleet_no !== '—') {
                    extraDetailsHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Asset:</span>
                            <span class="popup-value fw-medium">${escapeHtml(item.fleet_no)} (${escapeHtml(item.truck_no || '-')})</span>
                        </div>
                    `;
                }
                if (item.driver_name && item.driver_name !== '—') {
                    extraDetailsHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Driver:</span>
                            <span class="popup-value">${escapeHtml(item.driver_name)}</span>
                        </div>
                    `;
                }
                if (item.damage_cost) {
                    extraDetailsHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Cost:</span>
                            <span class="popup-value text-danger fw-bold">$${escapeHtml(item.damage_cost)}</span>
                        </div>
                    `;
                }
                if (item.fatality) {
                    extraDetailsHtml += `
                        <div class="popup-row">
                            <span class="popup-label">Fatality:</span>
                            <span class="popup-value badge bg-danger">${escapeHtml(item.fatality)}</span>
                        </div>
                    `;
                }

                const statusPillHtml = `
                    <span class="status-pill ${item.status_pill_class || 'status-pending'}" style="font-size: 11px; padding: 3px 10px;">
                        <i class="${item.status_icon || 'fa-solid fa-circle'} status-icon"></i>
                        <span>${escapeHtml(item.status_label)}</span>

                    </span>
                `;

                const popupHtml = `
                    <div class="popup-card">
                        <div class="popup-header" style="background: ${item.color};">
                            <div class="popup-header-icon"><i class="${item.icon}"></i></div>
                            <div style="flex-grow: 1; min-width: 0;">
                                <h6 class="popup-header-title text-truncate">${escapeHtml(item.type_label)}</h6>
                                <p class="popup-header-sub">${escapeHtml(item.category_label)}</p>
                            </div>
                        </div>
                        <div class="popup-body">
                            <div class="popup-row" style="align-items: center;">
                                <span class="popup-label">Status:</span>
                                <span class="popup-value">${statusPillHtml}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Date:</span>
                                <span class="popup-value fw-semibold">${escapeHtml(item.date)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Location:</span>
                                <span class="popup-value">${escapeHtml(item.location)}</span>
                            </div>
                            <div class="popup-row">
                                <span class="popup-label">Coords:</span>
                                <span class="popup-value font-monospace">${item.lat.toFixed(5)}, ${item.lng.toFixed(5)}</span>
                            </div>
                            ${extraDetailsHtml}
                            <div class="popup-row mt-1">
                                <span class="popup-label">Remarks:</span>
                                <span class="popup-value text-muted" style="font-size: 11.5px;">${escapeHtml(item.description)}</span>
                            </div>
                        </div>
                        <div class="popup-footer">
                            ${item.waybill_map_url ? `
                                <a href="${item.waybill_map_url}" target="_blank" class="popup-btn-outline" title="View Waybill Route Map">
                                    <i class="ri-route-line text-primary"></i> Trip Map
                                </a>
                            ` : '<span></span>'}
                            <a href="${item.show_url}" target="_blank" class="popup-btn">
                                <i class="ri-external-link-line"></i> Full Record
                            </a>
                        </div>
                    </div>
                `;

                const popup = new maplibregl.Popup({ offset: 28 }).setHTML(popupHtml);

                const marker = new maplibregl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([item.lng, item.lat])
                    .setPopup(popup)
                    .addTo(map);

                mapMarkers.push({
                    index: index,
                    marker: marker,
                    category: item.category,
                    type_key: item.type_key,
                    status_raw: item.status_raw,
                    data: item,
                    lngLat: [item.lng, item.lat]
                });

                allMarkerBounds.push([item.lng, item.lat]);
            });
        }

        /* ============================================================
           3. FILTERING & PAGINATION LOGIC
           ============================================================ */
        function initFilterListeners() {
            // A. Category Tabs (All / Incident / Driver Behavior)
            document.querySelectorAll('.filter-pill[data-category]').forEach(pill => {
                pill.addEventListener('click', function () {
                    document.querySelectorAll('.filter-pill[data-category]').forEach(p => {
                        p.classList.remove('active', 'active-incident', 'active-behavior');
                    });

                    activeCategory = this.dataset.category;
                    if (activeCategory === 'incident') {
                        this.classList.add('active-incident');
                    } else if (activeCategory === 'driver_behavior') {
                        this.classList.add('active-behavior');
                    } else {
                        this.classList.add('active');
                    }

                    currentPage = 1;
                    applyUnifiedFilters();
                });
            });

            // B. Type Sub-Chips (Accidents, Breakdowns, etc.)
            document.querySelectorAll('.type-chip[data-type]').forEach(chip => {
                chip.addEventListener('click', function () {
                    document.querySelectorAll('.type-chip[data-type]').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    activeType = this.dataset.type;
                    currentPage = 1;
                    applyUnifiedFilters();
                });
            });

            // C. Status Filter Dropdown
            document.getElementById('filter-status-select')?.addEventListener('change', function () {
                activeStatus = this.value;
                currentPage = 1;
                applyUnifiedFilters();
            });

            // D. Search Input Field
            document.getElementById('filter-search-input')?.addEventListener('input', function () {
                searchQuery = this.value.trim().toLowerCase();
                currentPage = 1;
                applyUnifiedFilters();
            });
        }

        function initPaginationListeners() {
            document.getElementById('pagination-page-size')?.addEventListener('change', function () {
                pageSize = parseInt(this.value, 10);
                currentPage = 1;
                renderPaginatedTable();
            });
        }

        function applyUnifiedFilters() {
            currentFilteredIndices = [];
            let visibleBounds = [];

            // 1. Determine matching items
            const tableRows = document.querySelectorAll('#incidents-table-tbody tr[data-index]');
            tableRows.forEach(row => {
                const index = parseInt(row.dataset.index, 10);
                const rowCategory = row.dataset.category;
                const rowType = row.dataset.type;
                const rowStatus = row.dataset.status;
                const rowSearch = row.dataset.search || '';

                const matchesCategory = (activeCategory === 'all' || rowCategory === activeCategory);
                const matchesType = (activeType === 'all' || rowType === activeType);
                const matchesStatus = checkStatusMatch(rowStatus, activeStatus);
                const matchesSearch = (searchQuery === '' || rowSearch.includes(searchQuery));

                if (matchesCategory && matchesType && matchesStatus && matchesSearch) {
                    currentFilteredIndices.push(index);
                }
            });

            // 2. Filter Map Markers
            mapMarkers.forEach(m => {
                const isMatched = currentFilteredIndices.includes(m.index);
                m.marker.getElement().style.display = isMatched ? 'flex' : 'none';

                if (isMatched) {
                    visibleBounds.push(m.lngLat);
                }
            });

            // 3. Render Paginated Table Rows
            renderPaginatedTable();

            // 4. Update Header Counts
            const visibleCount = currentFilteredIndices.length;
            document.getElementById('table-filtered-count').textContent = visibleCount;
            document.getElementById('map-count-label').innerHTML = `{{ __('Displaying') }} <strong>${visibleCount}</strong> {{ __('Filtered Pins') }}`;

            // 5. Auto-fit map to visible bounds if needed
            if (map && mapReady && visibleBounds.length > 0) {
                if (visibleBounds.length === 1) {
                    map.flyTo({ center: visibleBounds[0], zoom: 11 });
                } else {
                    const bounds = visibleBounds.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(visibleBounds[0], visibleBounds[0]));
                    map.fitBounds(bounds, { padding: 60, maxZoom: 13 });
                }
            }
        }

        function renderPaginatedTable() {
            const tableRows = document.querySelectorAll('#incidents-table-tbody tr[data-index]');
            const totalItems = currentFilteredIndices.length;

            const emptyState = document.getElementById('empty-filter-state');
            const paginationFooter = document.getElementById('pagination-footer');

            if (totalItems === 0) {
                tableRows.forEach(r => r.style.display = 'none');
                if (emptyState) emptyState.classList.remove('d-none');
                if (paginationFooter) paginationFooter.style.display = 'none';
                return;
            }

            if (emptyState) emptyState.classList.add('d-none');
            if (paginationFooter) paginationFooter.style.display = 'flex';

            let totalPages = 1;
            let startIndex = 0;
            let endIndex = totalItems;

            if (pageSize > 0) {
                totalPages = Math.ceil(totalItems / pageSize) || 1;
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                startIndex = (currentPage - 1) * pageSize;
                endIndex = Math.min(startIndex + pageSize, totalItems);
            }

            const visibleSliceIndices = currentFilteredIndices.slice(startIndex, endIndex);

            tableRows.forEach(row => {
                const idx = parseInt(row.dataset.index, 10);
                row.style.display = visibleSliceIndices.includes(idx) ? '' : 'none';
            });

            // Update Pagination Info
            const infoText = document.getElementById('pagination-info-text');
            if (infoText) {
                infoText.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries`;
            }

            // Update Pagination Controls
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');
            const numbersContainer = document.getElementById('pagination-number-buttons');

            if (btnPrev) btnPrev.disabled = (currentPage <= 1);
            if (btnNext) btnNext.disabled = (currentPage >= totalPages);

            if (!numbersContainer) return;
            numbersContainer.innerHTML = '';

            if (totalPages <= 1) return;

            // Generate page numbers with ellipses if needed
            let pagesToShow = [];
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) pagesToShow.push(i);
            } else {
                pagesToShow.push(1);
                if (currentPage > 3) pagesToShow.push('...');

                let start = Math.max(2, currentPage - 1);
                let end = Math.min(totalPages - 1, currentPage + 1);

                for (let i = start; i <= end; i++) pagesToShow.push(i);

                if (currentPage < totalPages - 2) pagesToShow.push('...');
                pagesToShow.push(totalPages);
            }

            pagesToShow.forEach(p => {
                if (p === '...') {
                    const span = document.createElement('span');
                    span.className = 'px-1 text-muted';
                    span.textContent = '…';
                    numbersContainer.appendChild(span);
                } else {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `page-btn ${p === currentPage ? 'active' : ''}`;
                    btn.textContent = p;
                    btn.addEventListener('click', () => changePage(p));
                    numbersContainer.appendChild(btn);
                }
            });
        }

        function changePage(page) {
            currentPage = page;
            renderPaginatedTable();
            // Smoothly scroll table to top if user paged down
            document.getElementById('records-list-section')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function checkStatusMatch(statusRaw, selectedStatus) {
            if (selectedStatus === 'all') return true;

            const str = String(statusRaw).toLowerCase();
            if (selectedStatus === 'pending') {
                return ['0', 'pending', 'stuck', 'open', 'impounded', 'detained'].includes(str);
            }
            if (selectedStatus === 'in_progress') {
                return ['2', 'in_progress', 'active', 'reviewed'].includes(str);
            }
            if (selectedStatus === 'resolved') {
                return ['1', 'resolved', 'recovered', 'released', 'completed', 'closed'].includes(str);
            }
            return true;
        }

        function resetFilters() {
            activeCategory = 'all';
            activeType = 'all';
            activeStatus = 'all';
            searchQuery = '';
            currentPage = 1;

            document.querySelectorAll('.filter-pill[data-category]').forEach(p => p.classList.remove('active', 'active-incident', 'active-behavior'));
            document.querySelector('.filter-pill[data-category="all"]')?.classList.add('active');

            document.querySelectorAll('.type-chip[data-type]').forEach(c => c.classList.remove('active'));
            document.querySelector('.type-chip[data-type="all"]')?.classList.add('active');

            const statusSelect = document.getElementById('filter-status-select');
            if (statusSelect) statusSelect.value = 'all';

            const searchInput = document.getElementById('filter-search-input');
            if (searchInput) searchInput.value = '';

            applyUnifiedFilters();
            fitAllMarkers();
        }

        /* ============================================================
           4. NAVIGATION & CAMERA HELPERS
           ============================================================ */
        function fitAllMarkers() {
            if (!map || !mapReady || allMarkerBounds.length === 0) return;

            if (allMarkerBounds.length === 1) {
                map.flyTo({ center: allMarkerBounds[0], zoom: 11 });
                return;
            }

            const bounds = allMarkerBounds.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(allMarkerBounds[0], allMarkerBounds[0]));
            map.fitBounds(bounds, {
                padding: { top: 60, bottom: 60, left: 60, right: 60 },
                maxZoom: 13
            });
        }

        function flyToMapIndex(idx) {
            const markerObj = mapMarkers.find(m => m.index === idx);
            if (markerObj && markerObj.marker && map) {
                // Smoothly scroll to map
                document.getElementById('map-container-box')?.scrollIntoView({ behavior: 'smooth', block: 'center' });

                setTimeout(() => {
                    map.flyTo({
                        center: markerObj.lngLat,
                        zoom: 14,
                        essential: true
                    });
                    markerObj.marker.togglePopup();
                }, 300);
            }
        }

        function toggleMapStyle() {
            fitAllMarkers();
        }

        /* ============================================================
           5. FULLSCREEN & EXPORT
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

        function exportFilteredRows() {
            const rows = [];
            rows.push(['Type', 'Category', 'Waybill Number', 'Vehicle Fleet', 'Truck Reg', 'Driver', 'Date', 'Location', 'Latitude', 'Longitude', 'Details', 'Cost', 'Status']);

            // Use currentFilteredIndices if available, otherwise export all data
            const indicesToExport = currentFilteredIndices.length > 0
                ? currentFilteredIndices
                : ALL_EVENTS_DATA.map((_, idx) => idx);

            console.log('Exporting indices count:', indicesToExport.length);
            console.log('Total data count:', ALL_EVENTS_DATA.length);
            console.log('Sample item structure:', ALL_EVENTS_DATA[0]);

            indicesToExport.forEach(idx => {
                const item = ALL_EVENTS_DATA[idx];
                if (item) {
                    rows.push([
                        item.type_label || '-',
                        item.category_label || '-',
                        item.waybill_number ? `#${item.waybill_number}` : '-',
                        item.fleet_no || '-',
                        item.truck_no || '-',
                        item.driver_name || '-',
                        item.date || '-',
                        item.location || '-',
                        item.lat !== null && item.lat !== undefined ? item.lat : '-',
                        item.lng !== null && item.lng !== undefined ? item.lng : '-',
                        item.detail_type || '-',
                        item.damage_cost || '-',
                        item.status_label || '-'
                    ]);
                }
            });

            console.log('Total rows to export:', rows.length);
            console.log('First data row:', rows[1]);

            if (rows.length <= 1) {
                alert('No data available to export. Please ensure there are records in the table.');
                return;
            }

            const csvContent = rows
                .map(row => row.map(val => `"${String(val).replace(/"/g, '""')}"`).join(","))
                .join("\r\n");
            const csvBlob = new Blob(["\uFEFF", csvContent], { type: "text/csv;charset=utf-8" });
            const downloadUrl = URL.createObjectURL(csvBlob);
            const link = document.createElement("a");
            link.setAttribute("href", downloadUrl);
            link.setAttribute("download", `fleet_incidents_behavior_export_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(downloadUrl);
        }

        function escapeHtml(str) {
            if (str == null) return '';
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }
    </script>
</x-layouts.auth>
