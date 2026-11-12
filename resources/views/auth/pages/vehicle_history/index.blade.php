<x-layouts.auth page-title="{{ __('Vehicle Track Player') }}" sub-title="{{ __('Historical GPS Route Playback, Trip Analytics & Real-time Telemetry') }}">
    
    <x-slot:actionButton>
        <!-- Export Report Dropdown in Header -->
        <div class="export-dropdown" id="export-history-dropdown-header" style="position: relative; display: inline-block;">
            <button type="button" class="btn btn-success d-flex align-items-center gap-2" id="btn-export-toggle-header" onclick="toggleExportMenu(event)" title="{{ __('Export & Download Vehicle History Report') }}">
                <i class="ri-download-2-line" style="font-size: 16px;"></i>
                <span>{{ __('Export Report') }}</span>
                <i class="ri-arrow-down-s-line" style="font-size: 16px;"></i>
            </button>
            <div class="export-menu" id="export-menu-list-header" style="display: none; position: absolute; right: 0; top: calc(100% + 4px); background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -3px rgba(15,23,42,0.12); min-width: 250px; z-index: 1050; padding: 6px 0; list-style: none; margin: 0;">
                <a href="javascript:void(0)" class="export-menu-item-header" onclick="exportVehicleHistory('pdf')" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-size: 13px; font-weight: 500; color: #0f172a; text-decoration: none; cursor: pointer; transition: background 0.12s ease;">
                    <i class="ri-file-pdf-2-fill" style="font-size: 20px; color: #ef4444;"></i>
                    <div>
                        <div style="font-weight: 600;">{{ __('PDF Document') }}</div>
                        <small style="color: #64748b; font-size: 11px;">{{ __('Printable Report (.pdf)') }}</small>
                    </div>
                </a>
                <a href="javascript:void(0)" class="export-menu-item-header" onclick="exportVehicleHistory('xlsx')" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-size: 13px; font-weight: 500; color: #0f172a; text-decoration: none; cursor: pointer; transition: background 0.12s ease;">
                    <i class="ri-file-excel-2-fill" style="font-size: 20px; color: #10b981;"></i>
                    <div>
                        <div style="font-weight: 600;">{{ __('Excel Spreadsheet') }}</div>
                        <small style="color: #64748b; font-size: 11px;">{{ __('Multi-sheet (.xlsx)') }}</small>
                    </div>
                </a>
                <a href="javascript:void(0)" class="export-menu-item-header" onclick="exportVehicleHistory('csv')" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-size: 13px; font-weight: 500; color: #0f172a; text-decoration: none; cursor: pointer; transition: background 0.12s ease;">
                    <i class="ri-file-text-fill" style="font-size: 20px; color: #3b82f6;"></i>
                    <div>
                        <div style="font-weight: 600;">{{ __('CSV Data File') }}</div>
                        <small style="color: #64748b; font-size: 11px;">{{ __('Trips Table (.csv)') }}</small>
                    </div>
                </a>
            </div>
        </div>
        
        <style>
            .export-menu-item-header:hover {
                background-color: #eff6ff !important;
                color: #2563eb !important;
            }
        </style>
    </x-slot:actionButton>
    
    <!-- MapLibre GL JS -->
    <link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

    <!-- Choices.js (lightweight searchable select, no dependencies) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>

    <!-- RemixIcon & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:        #2563eb;
            --primary-dark:   #1d4ed8;
            --primary-soft:   #eff6ff;
            --surface:        #ffffff;
            --surface-subtle: #f8fafc;
            --bg:             #f1f5f9;
            --border:         #e2e8f0;
            --border-dark:    #cbd5e1;
            --text:           #0f172a;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --radius-lg:      16px;
            --radius-md:      12px;
            --radius-sm:      8px;
            --shadow-sm:      0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:      0 4px 14px rgba(15,23,42,0.08);
            --shadow-lg:      0 10px 25px -3px rgba(15,23,42,0.12);
            --shadow-xl:      0 20px 25px -5px rgba(0, 0, 0, 0.2);

            --c-speed-stop:   #64748b;
            --c-speed-slow:   #f59e0b;
            --c-speed-med:    #10b981;
            --c-speed-fast:   #2563eb;
            --c-speed-max:    #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: var(--bg);
        }

        .track-player-wrapper {
            max-width: 1720px;
            margin: 0 auto;
            padding-bottom: 24px;
        }

        /* ── TOP CONFIGURATION TOOLBAR ── */
        .track-config-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 14px;
            box-shadow: var(--shadow-sm);
        }

        .config-row-main {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
        }

        .config-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .config-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .config-input, .config-select {
            height: 38px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--surface-subtle);
            color: var(--text);
            outline: none;
            transition: all 0.15s ease;
        }
        .config-input:focus, .config-select:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }

        .vehicle-select-box {
            min-width: 260px;
            flex-grow: 1;
        }

        /* ── SEARCHABLE VEHICLE SELECT (Choices.js) ──
           `color-scheme: light` + explicit !important colors are required
           here: on wide/desktop screens some browsers apply native OS dark
           form-control styling (black background, white/invisible text) to
           the real <input> Choices renders for search unless every layer
           is pinned explicitly. This is what caused the "black dropdown
           on large screens" bug. */
        .vehicle-select-box {
            color-scheme: light;
        }
        .vehicle-select-box select#select-vehicle {
            display: none !important;
        }
        .vehicle-select-box .choices {
            margin-bottom: 0 !important;
            color-scheme: light;
        }
        .vehicle-select-box .choices__inner {
            min-height: 38px !important;
            padding: 0 8px 0 12px !important;
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius-sm) !important;
            border: 1px solid var(--border) !important;
            background-color: var(--surface-subtle) !important;
            color: var(--text) !important;
            transition: all 0.15s ease;
        }
        .vehicle-select-box .choices__list--single {
            padding: 0;
        }
        .vehicle-select-box .choices__list--single .choices__item {
            color: var(--text) !important;
        }
        .vehicle-select-box.is-focused .choices__inner,
        .vehicle-select-box .choices.is-focused .choices__inner,
        .vehicle-select-box .choices.is-open .choices__inner {
            border-color: var(--primary) !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .vehicle-select-box .choices__list--dropdown,
        .vehicle-select-box .choices__list[aria-expanded] {
            border-color: var(--border-dark) !important;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-md);
            background-color: #ffffff !important;
            color: var(--text) !important;
            z-index: 40;
            font-size: 13px;
        }
        .vehicle-select-box .choices__input {
            background-color: #ffffff !important;
            color: var(--text) !important;
            font-size: 13px;
            margin-bottom: 0;
        }
        .vehicle-select-box .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: var(--primary-soft) !important;
            color: var(--primary-dark) !important;
        }
        .vehicle-select-box .choices__list--dropdown .choices__item {
            padding: 8px 12px;
            background-color: #ffffff;
            color: var(--text);
        }
        .vehicle-select-box .choices__list--dropdown .choices__item--selectable:hover {
            background-color: var(--primary-soft) !important;
        }
        .vehicle-select-box .choices__placeholder {
            color: var(--text-faint) !important;
            opacity: 1 !important;
        }

        .preset-btn-group {
            display: inline-flex;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 3px;
            gap: 3px;
            height: 38px;
            align-items: center;
        }

        .preset-btn {
            border: none;
            background: transparent;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .preset-btn:hover {
            color: var(--text);
            background: rgba(0,0,0,0.04);
        }
        .preset-btn.active {
            background: var(--surface);
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .config-checkbox-group {
            display: flex;
            align-items: center;
            gap: 16px;
            height: 38px;
            padding: 0 6px;
        }

        .custom-check {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text);
            cursor: pointer;
            user-select: none;
        }
        .custom-check input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* ── COMPACT CONFIG CONTROLS ── */
        .config-item-compact {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .config-label-compact {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .config-select-compact {
            height: 32px;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--surface-subtle);
            color: var(--text);
            outline: none;
            transition: all 0.15s ease;
            min-width: 100px;
        }
        .config-select-compact:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }

        /* ── MODERN TOGGLE SWITCH ── */
        .modern-toggle-switch {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        .modern-toggle-switch input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: relative;
            width: 42px;
            height: 22px;
            background: #cbd5e1;
            border-radius: 22px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: white;
            top: 2px;
            left: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .modern-toggle-switch input[type="checkbox"]:checked + .toggle-slider {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
        .modern-toggle-switch input[type="checkbox"]:checked + .toggle-slider::before {
            transform: translateX(20px);
        }
        .toggle-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
        }
        .modern-toggle-switch:hover .toggle-slider {
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
        }

        .btn-show-track {
            height: 38px;
            padding: 0 20px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37,99,235,0.25);
            transition: all 0.15s ease;
        }
        .btn-show-track:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            box-shadow: 0 6px 16px rgba(37,99,235,0.35);
            transform: translateY(-1px);
        }
        .btn-show-track:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-fullscreen-toggle {
            height: 38px;
            padding: 0 14px;
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-fullscreen-toggle:hover {
            background: var(--surface-subtle);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ── EXPORT DROPDOWN ── */
        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .btn-export-dropdown {
            height: 38px;
            padding: 0 14px;
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-export-dropdown:hover {
            background: var(--surface-subtle);
            border-color: #10b981;
            color: #10b981;
        }

        .export-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 4px);
            background: #ffffff;
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            z-index: 100;
            padding: 6px 0;
            list-style: none;
            margin: 0;
        }
        .export-menu.show {
            display: block;
            animation: fadeInExportMenu 0.15s ease-out;
        }
        @keyframes fadeInExportMenu {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .export-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            text-decoration: none;
            cursor: pointer;
            transition: background 0.12s ease;
        }
        .export-menu-item:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }
        .export-menu-item i {
            font-size: 18px;
        }
        .export-menu-item .icon-pdf { color: #ef4444; }
        .export-menu-item .icon-xlsx { color: #10b981; }
        .export-menu-item .icon-csv { color: #3b82f6; }

        /* Fullscreen View Styles */
        .track-player-wrapper:fullscreen,
        .track-player-wrapper:-webkit-full-screen,
        .track-player-wrapper:-moz-full-screen,
        .track-player-wrapper:-ms-fullscreen {
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            padding: 10px 14px !important;
            background: #0f172a !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            z-index: 99999 !important;
            box-sizing: border-box !important;
        }
        .track-player-wrapper:fullscreen .track-config-panel,
        .track-player-wrapper:-webkit-full-screen .track-config-panel,
        .track-player-wrapper:-moz-full-screen .track-config-panel,
        .track-player-wrapper:-ms-fullscreen .track-config-panel {
            background: rgba(30, 41, 59, 0.95) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
            padding: 8px 14px !important;
            margin-bottom: 8px !important;
            flex-shrink: 0 !important;
        }
        .track-player-wrapper:fullscreen .config-label,
        .track-player-wrapper:-webkit-full-screen .config-label,
        .track-player-wrapper:-moz-full-screen .config-label,
        .track-player-wrapper:-ms-fullscreen .config-label,
        .track-player-wrapper:fullscreen .custom-check,
        .track-player-wrapper:-webkit-full-screen .custom-check,
        .track-player-wrapper:-moz-full-screen .custom-check,
        .track-player-wrapper:-ms-fullscreen .custom-check {
            color: #cbd5e1 !important;
        }
        .track-player-wrapper:fullscreen .config-input,
        .track-player-wrapper:fullscreen .config-select,
        .track-player-wrapper:-webkit-full-screen .config-input,
        .track-player-wrapper:-webkit-full-screen .config-select,
        .track-player-wrapper:-moz-full-screen .config-input,
        .track-player-wrapper:-moz-full-screen .config-select,
        .track-player-wrapper:-ms-fullscreen .config-input,
        .track-player-wrapper:-ms-fullscreen .config-select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .preset-btn-group,
        .track-player-wrapper:-webkit-full-screen .preset-btn-group {
            background: #0f172a !important;
            border-color: #334155 !important;
        }
        .track-player-wrapper:fullscreen .preset-btn,
        .track-player-wrapper:-webkit-full-screen .preset-btn {
            color: #94a3b8 !important;
        }
        .track-player-wrapper:fullscreen .preset-btn:hover,
        .track-player-wrapper:-webkit-full-screen .preset-btn:hover {
            color: #f8fafc !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .track-player-wrapper:fullscreen .preset-btn.active,
        .track-player-wrapper:-webkit-full-screen .preset-btn.active {
            background: var(--primary) !important;
            color: #ffffff !important;
        }
        .track-player-wrapper:fullscreen .btn-fullscreen-toggle,
        .track-player-wrapper:-webkit-full-screen .btn-fullscreen-toggle {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .btn-fullscreen-toggle:hover,
        .track-player-wrapper:-webkit-full-screen .btn-fullscreen-toggle:hover {
            background: #334155 !important;
            color: #38bdf8 !important;
            border-color: #38bdf8 !important;
        }
        .track-player-wrapper:fullscreen .vehicle-select-box .choices__inner,
        .track-player-wrapper:-webkit-full-screen .vehicle-select-box .choices__inner {
            background: #ffffff !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .vehicle-select-box .choices__list--dropdown,
        .track-player-wrapper:-webkit-full-screen .vehicle-select-box .choices__list--dropdown {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .track-stage-grid,
        .track-player-wrapper:-webkit-full-screen .track-stage-grid,
        .track-player-wrapper:-moz-full-screen .track-stage-grid,
        .track-player-wrapper:-ms-fullscreen .track-stage-grid {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            height: 100% !important;
            display: grid !important;
            grid-template-columns: 360px 1fr !important;
            gap: 10px !important;
        }
        .track-player-wrapper:fullscreen .map-canvas-wrapper,
        .track-player-wrapper:-webkit-full-screen .map-canvas-wrapper,
        .track-player-wrapper:-moz-full-screen .map-canvas-wrapper,
        .track-player-wrapper:-ms-fullscreen .map-canvas-wrapper {
            height: 100% !important;
            min-height: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        .track-player-wrapper:fullscreen #track-player-map,
        .track-player-wrapper:-webkit-full-screen #track-player-map,
        .track-player-wrapper:-moz-full-screen #track-player-map,
        .track-player-wrapper:-ms-fullscreen #track-player-map {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        .track-player-wrapper:fullscreen .track-sidebar,
        .track-player-wrapper:-webkit-full-screen .track-sidebar,
        .track-player-wrapper:-moz-full-screen .track-sidebar,
        .track-player-wrapper:-ms-fullscreen .track-sidebar {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            gap: 10px !important;
        }
        .track-player-wrapper:fullscreen .sidebar-card,
        .track-player-wrapper:-webkit-full-screen .sidebar-card,
        .track-player-wrapper:-moz-full-screen .sidebar-card,
        .track-player-wrapper:-ms-fullscreen .sidebar-card {
            background: rgba(30, 41, 59, 0.95) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .sidebar-tabs,
        .track-player-wrapper:-webkit-full-screen .sidebar-tabs {
            border-bottom-color: rgba(255, 255, 255, 0.12) !important;
        }
        .track-player-wrapper:fullscreen .sidebar-tab-btn,
        .track-player-wrapper:-webkit-full-screen .sidebar-tab-btn {
            color: #94a3b8 !important;
        }
        .track-player-wrapper:fullscreen .sidebar-tab-btn.active,
        .track-player-wrapper:-webkit-full-screen .sidebar-tab-btn.active {
            color: #38bdf8 !important;
            border-bottom-color: #38bdf8 !important;
            background: rgba(56, 189, 248, 0.08) !important;
        }
        .track-player-wrapper:fullscreen .trip-item-card,
        .track-player-wrapper:-webkit-full-screen .trip-item-card {
            background: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .trip-item-card:hover,
        .track-player-wrapper:-webkit-full-screen .trip-item-card:hover {
            background: #1e293b !important;
            border-color: #38bdf8 !important;
        }
        .track-player-wrapper:fullscreen .trip-item-card.active,
        .track-player-wrapper:-webkit-full-screen .trip-item-card.active {
            background: rgba(37, 99, 235, 0.22) !important;
            border-color: #38bdf8 !important;
        }
        .track-player-wrapper:fullscreen .trip-item-name,
        .track-player-wrapper:-webkit-full-screen .trip-item-name {
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .trip-meta-time,
        .track-player-wrapper:-webkit-full-screen .trip-meta-time {
            color: #94a3b8 !important;
        }
        .track-player-wrapper:fullscreen .trip-meta-chip,
        .track-player-wrapper:-webkit-full-screen .trip-meta-chip {
            background: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #cbd5e1 !important;
        }
        .track-player-wrapper:fullscreen .playback-controller-bar,
        .track-player-wrapper:-webkit-full-screen .playback-controller-bar,
        .track-player-wrapper:-moz-full-screen .playback-controller-bar,
        .track-player-wrapper:-ms-fullscreen .playback-controller-bar {
            background: rgba(15, 23, 42, 0.96) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5) !important;
            color: #f8fafc !important;
            flex-shrink: 0 !important;
            padding: 10px 18px !important;
        }
        .track-player-wrapper:fullscreen .play-btn,
        .track-player-wrapper:-webkit-full-screen .play-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .play-btn:hover,
        .track-player-wrapper:-webkit-full-screen .play-btn:hover {
            background: #334155 !important;
            border-color: #38bdf8 !important;
            color: #38bdf8 !important;
        }
        .track-player-wrapper:fullscreen .play-btn.btn-play-pause,
        .track-player-wrapper:-webkit-full-screen .play-btn.btn-play-pause {
            background: #2563eb !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4) !important;
        }
        .track-player-wrapper:fullscreen .play-btn.btn-play-pause.bg-danger,
        .track-player-wrapper:-webkit-full-screen .play-btn.btn-play-pause.bg-danger {
            background: #ef4444 !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4) !important;
        }
        .track-player-wrapper:fullscreen .speed-btn-group,
        .track-player-wrapper:-webkit-full-screen .speed-btn-group {
            background: #0f172a !important;
            border-color: #334155 !important;
        }
        .track-player-wrapper:fullscreen .speed-btn,
        .track-player-wrapper:-webkit-full-screen .speed-btn {
            color: #94a3b8 !important;
        }
        .track-player-wrapper:fullscreen .speed-btn:hover,
        .track-player-wrapper:-webkit-full-screen .speed-btn:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .track-player-wrapper:fullscreen .speed-btn.active,
        .track-player-wrapper:-webkit-full-screen .speed-btn.active {
            background: #2563eb !important;
            color: #ffffff !important;
        }
        .track-player-wrapper:fullscreen .digital-clock-box,
        .track-player-wrapper:-webkit-full-screen .digital-clock-box {
            background: #0f172a !important;
            border-color: #334155 !important;
        }
        .track-player-wrapper:fullscreen .digital-time,
        .track-player-wrapper:-webkit-full-screen .digital-time {
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .digital-date,
        .track-player-wrapper:-webkit-full-screen .digital-date {
            color: #94a3b8 !important;
        }
        .track-player-wrapper:fullscreen .timeline-track-wrap,
        .track-player-wrapper:-webkit-full-screen .timeline-track-wrap {
            background: #334155 !important;
        }
        .track-player-wrapper:fullscreen .timeline-progress-bar,
        .track-player-wrapper:-webkit-full-screen .timeline-progress-bar {
            background: linear-gradient(90deg, #38bdf8, #2563eb) !important;
        }
        .track-player-wrapper:fullscreen .timeline-handle,
        .track-player-wrapper:-webkit-full-screen .timeline-handle {
            background: #ffffff !important;
            border: 3px solid #38bdf8 !important;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.6) !important;
        }
        .track-player-wrapper:fullscreen .timeline-time-labels,
        .track-player-wrapper:-webkit-full-screen .timeline-time-labels {
            color: #94a3b8 !important;
        }
        .track-player-wrapper:fullscreen .timeline-time-labels #timeline-duration-label,
        .track-player-wrapper:-webkit-full-screen .timeline-time-labels #timeline-duration-label {
            color: #38bdf8 !important;
        }

        .track-player-wrapper:fullscreen .kpi-mini-value,
        .track-player-wrapper:-webkit-full-screen .kpi-mini-value {
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .veh-hero-title,
        .track-player-wrapper:-webkit-full-screen .veh-hero-title{
            color: #38bdf8 !important;
        }
        .track-player-wrapper:fullscreen .kpi-mini-card,
        .track-player-wrapper:-webkit-full-screen .kpi-mini-card {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        .track-player-wrapper:fullscreen .btn-export-dropdown,
        .track-player-wrapper:-webkit-full-screen .btn-export-dropdown {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .export-menu,
        .track-player-wrapper:-webkit-full-screen .export-menu {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        .track-player-wrapper:fullscreen .export-menu-item,
        .track-player-wrapper:-webkit-full-screen .export-menu-item {
            color: #f8fafc !important;
        }
        .track-player-wrapper:fullscreen .export-menu-item:hover,
        .track-player-wrapper:-webkit-full-screen .export-menu-item:hover {
            background: #334155 !important;
        }

        /* ── MAIN STAGE: SIDEBAR + MAP CONTAINER ── */
        .track-stage-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 14px;
            position: relative;
            height: calc(100vh - 240px);
            min-height: 560px;
        }

        @media (max-width: 1180px) {
            .track-stage-grid {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 0;
            }
        }

        /* ── LEFT SIDEBAR (VEHICLE DETAILS & TRIPS) ── */
        .track-sidebar {
            display: flex;
            flex-direction: column;
            gap: 12px;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        .track-sidebar .sidebar-card:last-child {
            flex: 1 1 0%;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            box-shadow: var(--shadow-sm);
        }

        .veh-hero-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .veh-hero-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(37,99,235,0.2);
        }
        .veh-hero-title {
            font-size: 15px;
            font-weight: 800;
            color: #0f6dcb;
            margin: 0;
        }
        .veh-hero-sub {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        /* KPI Quick Stats in Sidebar */
        .kpi-grid-sidebar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
        }
        .kpi-mini-card {
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 8px 10px;
        }
        .kpi-mini-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .kpi-mini-value {
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
            font-family: 'JetBrains Mono', monospace;
            margin-top: 2px;
        }

        /* Tabs for Trips & Stops */
        .sidebar-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 10px;
        }
        .sidebar-tab-btn {
            flex: 1;
            padding: 8px;
            font-size: 12.5px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.15s ease;
            text-align: center;
        }
        .sidebar-tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: rgba(37,99,235,0.03);
        }

        .trip-list-container, .stop-list-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1 1 0%;
            min-height: 0;
            max-height: none;
            overflow-y: auto;
            padding-right: 4px;
        }

        .trip-item-card {
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
            border-left: 4px solid var(--primary);
        }
        .trip-item-card:hover {
            background: #fff;
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
            transform: translateX(2px);
        }
        .trip-item-card.active {
            background: #eff6ff;
            border-color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary);
        }

        .trip-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .trip-item-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .trip-item-distance {
            font-size: 13px;
            font-weight: 800;
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
        }
        .trip-item-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 6px;
        }
        .trip-meta-time {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .trip-meta-time i {
            flex-shrink: 0;
            color: var(--text-faint);
        }
        .trip-meta-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .trip-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 2px 9px;
            white-space: nowrap;
        }
        .trip-meta-chip i {
            color: var(--text-faint);
            font-size: 11px;
        }

        /* ── MAP CANVAS & HUD OVERLAYS ── */
        .map-canvas-wrapper {
            position: relative;
            background: #e5e7eb;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            height: 100%;
            min-height: 520px;
            display: flex;
            flex-direction: column;
        }

        #track-player-map {
            width: 100%;
            flex: 1 1 0%;
            min-height: 0;
            height: 100%;
        }

        /* Floating HUD Tools Top-Right */
        .map-floating-tools {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 10;
            display: flex;
            gap: 8px;
        }
        .tool-btn {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12.5px;
            font-weight: 600;
            box-shadow: var(--shadow-md);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .tool-btn:hover {
            background: var(--surface-subtle);
            color: var(--primary);
        }

        /* Floating HUD Status Top-Center */
        .map-status-pill {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 10;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            color: white;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-md);
        }
        .status-dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.35);
        }

        /* Floating Telemetry HUD (Right Side) */
        .telemetry-hud-card {
            position: absolute;
            top: 56px;
            right: 14px;
            z-index: 10;
            width: 340px;
            max-height: min(72vh, 620px);
            overflow-y: auto;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-xl);
            padding: 14px;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        /* Telemetry toggle button for small screens */
        .telemetry-toggle-btn {
            position: absolute;
            top: 56px;
            right: 14px;
            z-index: 11;
            display: none;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 10px 12px;
            color: #f8fafc;
            cursor: pointer;
            box-shadow: var(--shadow-xl);
        }

        .telemetry-toggle-btn i {
            font-size: 20px;
        }

        /* Hide telemetry panel on small screens by default */
        @media (max-width: 1024px) {
            .telemetry-hud-card {
                display: none;
            }
            
            .telemetry-hud-card.show-mobile {
                display: block;
                width: 260px;
            }
            
            .telemetry-toggle-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .telemetry-hud-card.show-mobile {
                width: calc(100% - 28px);
                right: 14px;
                left: 14px;
                max-height: 50vh;
                overflow-y: auto;
            }
        }

        .hud-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }
        .hud-header h6 {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hud-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .hud-cell {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 6px;
            padding: 6px 8px;
        }
        .hud-cell-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .hud-cell-value {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 1px;
        }

        .hud-sensors-section {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 8px;
        }
        .hud-sensor-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
        }
        .hud-sensor-name {
            color: #94a3b8;
            font-size: 11.5px;
        }
        .hud-sensor-val {
            font-weight: 700;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
        }

        /* ── BOTTOM WIALON-STYLE PLAYBACK CONTROL BAR ── */
        .playback-controller-bar {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 10px 18px;
            z-index: 20;
            box-shadow: 0 -4px 14px rgba(15,23,42,0.05);
            flex-shrink: 0;
            position: relative;
        }

        .playback-row-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .playback-btn-cluster {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }

        .play-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .play-btn:hover {
            background: var(--surface-subtle);
            border-color: var(--primary);
            color: var(--primary);
        }
        .play-btn.btn-play-pause {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border: none;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .play-btn.btn-play-pause:hover {
            background: var(--primary-dark);
            box-shadow: 0 6px 16px rgba(37,99,235,0.4);
            transform: scale(1.04);
        }

        .playback-speed-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .speed-btn-group {
            display: inline-flex;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 2px;
            gap: 2px;
        }
        .speed-btn {
            border: none;
            background: transparent;
            font-size: 11.5px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            padding: 3px 8px;
            border-radius: 6px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .speed-btn:hover {
            color: var(--text);
        }
        .speed-btn.active {
            background: var(--primary);
            color: white;
        }

        /* Large Digital Playback Clock */
        .digital-clock-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 3px 12px;
            flex-shrink: 0;
        }
        .digital-time {
            font-size: 16px;
            font-weight: 800;
            color: var(--text);
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: -0.02em;
        }
        .digital-date {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* Interactive Scrubbing Timeline */
        .timeline-container {
            position: relative;
            padding: 4px 0 2px 0;
            user-select: none;
        }
        .timeline-track-wrap {
            position: relative;
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            cursor: pointer;
            overflow: visible;
        }
        .timeline-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            border-radius: 999px;
            width: 0%;
            pointer-events: none;
        }
        .timeline-handle {
            position: absolute;
            top: 50%;
            left: 0%;
            width: 18px;
            height: 18px;
            background: #ffffff;
            border: 3px solid var(--primary);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            cursor: grab;
            transition: transform 0.1s ease;
            z-index: 5;
        }
        .timeline-handle:active {
            cursor: grabbing;
            transform: translate(-50%, -50%) scale(1.15);
        }

        .timeline-time-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
            font-size: 11px;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-muted);
        }

        /* Responsive layout adjustments for controls */
        @media (max-width: 992px) {
            .playback-row-controls {
                flex-wrap: wrap;
                gap: 8px;
            }
            .speed-btn-group .speed-btn {
                padding: 3px 5px;
                font-size: 10.5px;
            }
            .playback-controller-bar {
                padding: 8px 12px;
            }
        }
        @media (max-width: 640px) {
            .playback-row-controls {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: space-between;
            }
            .playback-speed-wrap {
                width: 100%;
                justify-content: center;
                order: 3;
            }
            .digital-clock-box {
                padding: 2px 8px;
            }
            .digital-time {
                font-size: 14px;
            }
        }

        /* ── CUSTOM VEHICLE & ANNOTATION MARKERS ── */
        /* NOTE: no CSS transition on transform here — MapLibre positions this
           element via its own inline transform on every render/zoom/pan frame.
           A competing CSS transition on the same property is what was causing
           the marker to visibly slide/drift instead of sticking to its point. */
        .marker-vehicle-pin {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 14px rgba(37,99,235,0.45);
            border: 2px solid #ffffff;
            position: relative;
        }
        .marker-heading-arrow {
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 8px solid #2563eb;
            transition: transform 0.15s linear;
        }

        .marker-annotation-pin {
            padding: 4px 8px;
            border-radius: 999px;
            color: white;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            border: 1.5px solid #ffffff;
            cursor: pointer;
            white-space: nowrap;
        }
    </style>

    <div class="track-player-wrapper">
        <!-- ── 1. TRACK CONFIGURATION TOOLBAR (REDESIGNED) ── -->
        <div class="track-config-panel">
            <!-- Top Row: Main Controls -->
            <div class="config-row-main" style="margin-bottom: 16px; align-items: flex-end;">
                <!-- Vehicle Selector -->
                <div class="config-item vehicle-select-box">
                    <label class="config-label"><i class="ri-truck-line"></i> {{ __('Vehicle / Unit') }}</label>
                    <select id="select-vehicle" class="config-select">
                        <option value="" selected>-- {{ __('Select Vehicle') }} --</option>
                        @foreach($vehicles as $veh)
                            <option value="{{ $veh['id'] }}"
                                data-unit-id="{{ $veh['unit_id'] }}"
                                data-fleet="{{ $veh['fleet_no'] }}"
                                data-truck="{{ $veh['truck_no'] }}"
                                data-driver="{{ $veh['driver_name'] }}"
                                data-model="{{ $veh['make_model'] }}"
                                data-last-gps="{{ $veh['last_gps_time'] ?? '' }}"
                                data-last-speed="{{ $veh['last_speed'] ?? 0 }}"
                                {{ (!empty($preselectedVehicleId) && $veh['id'] == $preselectedVehicleId) ? 'selected' : '' }}>
                                {{ $veh['fleet_no'] }} &bull; {{ $veh['truck_no'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Time Range Section -->
                <div class="config-item" style="flex-grow: 1;">
                    <label class="config-label"><i class="ri-calendar-event-line"></i> {{ __('Time Range') }}</label>
                    <div style="display: flex; gap: 10px; align-items: flex-end;">
                        <!-- Quick Presets -->
                        <div class="preset-btn-group">
                            <button type="button" class="preset-btn active" data-preset="today">{{ __('Today') }}</button>
                            <button type="button" class="preset-btn" data-preset="yesterday">{{ __('Yesterday') }}</button>
                            <button type="button" class="preset-btn" data-preset="week">{{ __('Week') }}</button>
                            <button type="button" class="preset-btn" data-preset="month">{{ __('Month') }}</button>
                        </div>
                        
                        <!-- From Datetime -->
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <small style="font-size: 10px; color: var(--text-muted); font-weight: 600;">{{ __('FROM') }}</small>
                            <input type="datetime-local" id="input-from" class="config-input" value="{{ $defaultFrom }}" style="height: 38px; font-size: 12px;">
                        </div>

                        <!-- To Datetime -->
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <small style="font-size: 10px; color: var(--text-muted); font-weight: 600;">{{ __('TO') }}</small>
                            <input type="datetime-local" id="input-to" class="config-input" value="{{ $defaultTo }}" style="height: 38px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <!-- Primary Action Button -->
                <div class="config-item" style="margin-left: auto;">
                    <label class="config-label" style="opacity: 0; pointer-events: none; height: 18px;">.</label>
                    <button type="button" id="btn-show-track" class="btn-show-track" style="height: 38px; padding: 0 28px; font-size: 14px;">
                        <i class="ri-play-circle-line" style="font-size: 18px;"></i>
                        <span id="btn-show-track-text">{{ __('Show Track') }}</span>
                    </button>
                </div>
            </div>

            <!-- Bottom Row: Advanced Options -->
            <div class="config-row-secondary" style="display: flex; align-items: center; gap: 14px; padding-top: 14px; border-top: 1px solid var(--border);">
                <!-- Track Color Mode -->
                <div class="config-item-compact">
                    <label class="config-label-compact"><i class="ri-palette-line"></i> {{ __('Color') }}</label>
                    <select id="select-track-color" class="config-select-compact">
                        <option value="trips">{{ __('By trips') }}</option>
                        <option value="single">{{ __('Single') }}</option>
                        <option value="orange">{{ __('Orange') }}</option>
                        <option value="green">{{ __('Green') }}</option>
                        <option value="speed">{{ __('Speed') }}</option>
                    </select>
                </div>

                <!-- Line Thickness -->
                <div class="config-item-compact">
                    <label class="config-label-compact"><i class="ri-ruler-line"></i> {{ __('Thickness') }}</label>
                    <select id="select-thickness" class="config-select-compact">
                        <option value="4">4px</option>
                        <option value="6">6px</option>
                        <option value="7" selected>7px</option>
                        <option value="9">9px</option>
                        <option value="12">12px</option>
                    </select>
                </div>

                <!-- Modern Toggle Switches for Checkboxes -->
                <div style="display: flex; align-items: center; gap: 20px; margin-left: 20px; padding-left: 20px; border-left: 1px solid var(--border);">
                    <label class="modern-toggle-switch" title="Display trip start, trip end, stops, and speed peaks">
                        <input type="checkbox" id="check-annotations" checked>
                        <span class="toggle-slider"></span>
                        <span class="toggle-label">{{ __('Annotations') }}</span>
                    </label>
                    <label class="modern-toggle-switch" title="Automatically segment historical path into distinct trips">
                        <input type="checkbox" id="check-trip-detector" checked>
                        <span class="toggle-slider"></span>
                        <span class="toggle-label">{{ __('Trip Detector') }}</span>
                    </label>
                </div>

                <!-- Full View Button -->
                <div style="margin-left: auto;">
                    <button type="button" id="btn-fullscreen-top" class="btn-fullscreen-toggle" onclick="toggleFullscreen()" title="{{ __('Toggle Fullscreen / Full View') }}" style="height: 36px; padding: 0 16px; font-size: 13px;">
                        <i class="ri-fullscreen-line" id="fs-main-icon" style="font-size: 16px;"></i>
                        <span id="fs-main-text">{{ __('Full View') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── 2. MAIN STAGE: SIDEBAR & MAP ── -->
        <div class="track-stage-grid">
            <!-- Left Sidebar (Vehicle & Trips) -->
            <div class="track-sidebar">
                <!-- Vehicle Info Card -->
                <div class="sidebar-card">
                    <div class="veh-hero-box">
                        <div class="veh-hero-icon">
                            <i class="ri-truck-fill"></i>
                        </div>
                        <div>
                            <h5 class="veh-hero-title" id="side-veh-fleet">—</h5>
                            <p class="veh-hero-sub" id="side-veh-plate">—</p>
                        </div>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="kpi-grid-sidebar">
                        <div class="kpi-mini-card">
                            <div class="kpi-mini-label">{{ __('Mileage') }}</div>
                            <div class="kpi-mini-value text-primary" id="side-mileage">0.00 km</div>
                        </div>
                        <div class="kpi-mini-card">
                            <div class="kpi-mini-label">{{ __('Duration') }}</div>
                            <div class="kpi-mini-value" id="side-duration">0h 0m</div>
                        </div>
                        <div class="kpi-mini-card">
                            <div class="kpi-mini-label">{{ __('Moving Time') }}</div>
                            <div class="kpi-mini-value text-success" id="side-moving-time">0h 0m</div>
                        </div>
                        <div class="kpi-mini-card">
                            <div class="kpi-mini-label">{{ __('Max Speed') }}</div>
                            <div class="kpi-mini-value text-danger" id="side-max-speed">0 km/h</div>
                        </div>
                    </div>
                </div>

                <!-- Trips & Stops Panel -->
                <div class="sidebar-card flex-grow-1 d-flex flex-column">
                    <div class="sidebar-tabs">
                        <button type="button" class="sidebar-tab-btn active" id="tab-btn-trips" onclick="switchSidebarTab('trips')">
                            <i class="ri-route-line me-1"></i> {{ __('Trips') }} (<span id="trip-badge-count">0</span>)
                        </button>
                        <button type="button" class="sidebar-tab-btn" id="tab-btn-stops" onclick="switchSidebarTab('stops')">
                            <i class="ri-parking-box-line me-1"></i> {{ __('Stops') }} (<span id="stop-badge-count">0</span>)
                        </button>
                    </div>

                    <!-- Trips List -->
                    <div id="trip-list-wrap" class="trip-list-container">
                        <div class="text-center py-4 text-muted">
                            <i class="ri-information-line fs-4 d-block mb-1"></i>
                            <span>{{ __('Click "Show Track" to load trips') }}</span>
                        </div>
                    </div>

                    <!-- Stops List -->
                    <div id="stop-list-wrap" class="stop-list-container" style="display: none;">
                        <div class="text-center py-4 text-muted">
                            <i class="ri-information-line fs-4 d-block mb-1"></i>
                            <span>{{ __('No stops recorded') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Canvas & HUD Overlays -->
            <div class="map-canvas-wrapper">
                <!-- Top Map Quick Tools -->
                <div class="map-floating-tools">
                    <button type="button" class="tool-btn" onclick="fitTrackBounds()" title="Fit complete track in view">
                        <i class="ri-focus-3-line"></i> {{ __('Fit Track') }}
                    </button>
                    <button type="button" class="tool-btn active" id="telemetry-hud-toggle" onclick="toggleTelemetryHud()" title="Toggle Telemetry HUD" aria-controls="telemetry-hud" aria-pressed="true">
                        <i class="ri-dashboard-3-line"></i> {{ __('Telemetry') }}
                    </button>
                    <button type="button" class="tool-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen View">
                        <i class="ri-fullscreen-line" id="fs-map-icon"></i> <span id="fs-map-text">{{ __('Full View') }}</span>
                    </button>
                </div>

                <!-- Status Pill Top Right -->
                <div class="map-status-pill" id="map-status-pill">
                    <span class="status-dot-pulse" id="status-dot"></span>
                    <span id="map-status-text">{{ __('Ready') }}</span>
                </div>

                <!-- Toggle button for telemetry on small screens -->
                <button class="telemetry-toggle-btn" id="telemetry-toggle-btn" onclick="toggleTelemetryHUD()" title="Toggle Telemetry">
                    <i class="ri-radar-line"></i>
                </button>

                <!-- Floating Telemetry & Sensors HUD (Right Side) -->
                <div class="telemetry-hud-card" id="telemetry-hud">
                    <div class="hud-header">
                        <div>
                            <h6><i class="ri-radar-fill text-primary"></i> {{ __('Vehicle Telemetry') }}</h6>
                            <div class="small text-white mt-1" id="hud-vehicle-name">{{ __('Select a vehicle') }}</div>
                            <div class="small text-white-50" id="hud-vehicle-meta">—</div>
                        </div>
                        <span class="badge bg-primary" id="hud-status-badge">{{ __('STANDBY') }}</span>
                    </div>

                    <div class="hud-grid">
                        <div class="hud-cell">
                            <div class="hud-cell-label">{{ __('Speed') }}</div>
                            <div class="hud-cell-value" id="hud-speed">0 <span class="fs-6 font-normal">km/h</span></div>
                        </div>
                        <div class="hud-cell">
                            <div class="hud-cell-label">{{ __('Course') }}</div>
                            <div class="hud-cell-value" id="hud-course">0°</div>
                        </div>
                        <div class="hud-cell">
                            <div class="hud-cell-label">{{ __('Altitude') }}</div>
                            <div class="hud-cell-value" id="hud-alt">0 <span class="fs-6 font-normal">m</span></div>
                        </div>
                        <div class="hud-cell">
                            <div class="hud-cell-label">{{ __('Satellites') }}</div>
                            <div class="hud-cell-value" id="hud-sats">0</div>
                        </div>
                        <div class="hud-cell">
                            <div class="hud-cell-label">{{ __('Coordinates') }}</div>
                            <div class="hud-cell-value" id="hud-coordinates">—</div>
                        </div>
                        <div class="hud-cell">
                            <div class="hud-cell-label">{{ __('GPS Time') }}</div>
                            <div class="hud-cell-value" id="hud-gps-time">—</div>
                        </div>
                    </div>

                    <div class="hud-sensors-section" id="hud-sensors-container">
                        <div class="text-muted text-center py-1">{{ __('Load a track to view sensor readings') }}</div>
                    </div>
                </div>

                <!-- Map Container -->
                <div id="track-player-map"></div>

                <!-- ── 3. BOTTOM WIALON-STYLE PLAYBACK CONTROL BAR ── -->
                <div class="playback-controller-bar">
                    <div class="playback-row-controls">
                        <!-- Left: Playback buttons -->
                        <div class="playback-btn-cluster">
                            <button type="button" class="play-btn" onclick="seekTrack('first')" title="Jump to start">
                                <i class="ri-skip-back-fill"></i>
                            </button>
                            <button type="button" class="play-btn" onclick="seekTrack('prev')" title="Previous position">
                                <i class="ri-rewind-fill"></i>
                            </button>
                            <button type="button" class="play-btn btn-play-pause" id="btn-play-pause" onclick="togglePlayback()" title="Play / Pause">
                                <i class="ri-play-fill" id="play-pause-icon"></i>
                            </button>
                            <button type="button" class="play-btn" onclick="seekTrack('next')" title="Next position">
                                <i class="ri-speed-fill"></i>
                            </button>
                            <button type="button" class="play-btn" onclick="seekTrack('last')" title="Jump to end">
                                <i class="ri-skip-forward-fill"></i>
                            </button>
                        </div>

                        <!-- Center: Playback Speed -->
                        <div class="playback-speed-wrap d-flex align-items-center gap-2">
                            <span class="text-muted fw-bold fs-6">{{ __('Speed:') }}</span>
                            <div class="speed-btn-group">
                                <button type="button" class="speed-btn" data-speed="0.5" onclick="setPlaybackSpeed(0.5)">0.5x</button>
                                <button type="button" class="speed-btn active" data-speed="1" onclick="setPlaybackSpeed(1)">1x</button>
                                <button type="button" class="speed-btn" data-speed="2" onclick="setPlaybackSpeed(2)">2x</button>
                                <button type="button" class="speed-btn" data-speed="4" onclick="setPlaybackSpeed(4)">4x</button>
                                <button type="button" class="speed-btn" data-speed="8" onclick="setPlaybackSpeed(8)">8x</button>
                                <button type="button" class="speed-btn" data-speed="16" onclick="setPlaybackSpeed(16)">16x</button>
                            </div>
                        </div>

                        <!-- Right: Digital Clock Display -->
                        <div class="digital-clock-box">
                            <i class="ri-time-line text-primary fs-5"></i>
                            <div>
                                <div class="digital-time" id="digital-clock-time">00:00:00</div>
                                <div class="digital-date" id="digital-clock-date">-- --- ----</div>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Scrubbing Timeline -->
                    <div class="timeline-container">
                        <div class="timeline-track-wrap" id="timeline-track-wrap">
                            <div class="timeline-progress-bar" id="timeline-progress-bar"></div>
                            <div class="timeline-handle" id="timeline-handle"></div>
                        </div>
                        <div class="timeline-time-labels">
                            <span id="timeline-start-label">00:00:00</span>
                            <span id="timeline-duration-label" class="text-primary font-bold">Total: 0h 0m</span>
                            <span id="timeline-end-label">00:00:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* ============================================================
           WIALON-STYLE TRACK PLAYER ENGINE & STATE
           ============================================================ */

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
                    attribution: '&copy; OpenStreetMap contributors'
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

        // State variables
        let map = null;
        let mapReady = false;
        let trackData = null;
        let vehicleMarker = null;
        let annotationMarkers = [];
        let vehicleChoices = null;

        // Playback engine state
        let isPlaying = false;
        let playbackSpeed = 1;
        let currentPointIndex = 0;
        let lastFrameTimestamp = null;
        let animationFrameId = null;

        document.addEventListener('DOMContentLoaded', function () {
            initVehicleSearchableSelect();
            initMap();
            initToolbarEvents();
            updateVehicleSidebarInfo();
            // Don't automatically trigger track load - user must select vehicle and click "Show Track"
            // const initialVehicle = document.getElementById('select-vehicle').value;
            // if (initialVehicle) {
            //     loadTrackData();
            // }
        });

        /* ============================================================
           0. SEARCHABLE VEHICLE SELECT (Choices.js)
           ============================================================ */
        let vehicleChoicesData = [];

        function initVehicleSearchableSelect() {
            const el = document.getElementById('select-vehicle');
            if (!el || typeof Choices === 'undefined') return;

            // Snapshot the server-rendered <option> list before Choices takes
            // over, so we can run our own guaranteed case-insensitive filter
            // against it (rather than relying on the library's fuzzy search,
            // which was matching "AOU" but not "aou").
            // Skip the placeholder option (empty value) from searchable choices
            vehicleChoicesData = Array.from(el.options)
                .filter(opt => opt.value !== '') // Exclude placeholder
                .map(opt => ({
                    value: opt.value,
                    label: opt.textContent.trim(),
                    selected: false, // Never auto-select
                    customProperties: { searchText: opt.textContent.trim().toLowerCase() }
                }));

            vehicleChoices = new Choices(el, {
                searchEnabled: true,
                searchChoices: false, // we filter manually below — see 'search' listener
                searchFloor: 0,
                searchResultLimit: 50,
                searchPlaceholderValue: '{{ __("Search fleet no, truck no...") }}',
                noResultsText: '{{ __("No vehicles found") }}',
                itemSelectText: '',
                shouldSort: false,
                placeholder: true,
                placeholderValue: '-- {{ __("Select Vehicle") }} --',
                position: 'bottom',
                classNames: { containerOuter: 'choices' }
            });

            // Force clear selection to show placeholder on initial load
            // Only if no preselected vehicle from URL
            if (!el.value || el.value === '') {
                vehicleChoices.removeActiveItems();
            }

            // Manual, always-case-insensitive substring search.
            el.addEventListener('search', function (e) {
                const term = (e.detail && e.detail.value ? e.detail.value : '').toLowerCase().trim();
                const filtered = term
                    ? vehicleChoicesData.filter(v => v.customProperties.searchText.includes(term))
                    : vehicleChoicesData;

                vehicleChoices.clearChoices();
                vehicleChoices.setChoices(filtered.length ? filtered : [], 'value', 'label', true);
            });

            // Restore full list when the search box is cleared/dropdown reopened
            el.addEventListener('showDropdown', function () {
                if (vehicleChoices && vehicleChoices.input && !vehicleChoices.input.value) {
                    vehicleChoices.clearChoices();
                    vehicleChoices.setChoices(vehicleChoicesData, 'value', 'label', true);
                }
            });
        }

        /* ============================================================
           1. MAP INITIALIZATION (MapLibre GL JS)
           ============================================================ */
        function initMap() {
            map = new maplibregl.Map({
                container: 'track-player-map',
                style: MAP_STYLE,
                center: [73.0479, 33.6844], // Default Islamabad/Regional center
                zoom: 6
            });

            // Zoom/rotate controls moved to bottom-right so they never sit
            // underneath the status pill / telemetry HUD anchored top-right.
            map.addControl(new maplibregl.NavigationControl(), 'bottom-right');
            map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

            map.on('load', function () {
                mapReady = true;

                // Create empty GeoJSON source for track lines
                map.addSource('track-lines-source', {
                    type: 'geojson',
                    data: {
                        type: 'FeatureCollection',
                        features: []
                    }
                });

                // Line layer
                map.addLayer({
                    id: 'track-lines-layer',
                    type: 'line',
                    source: 'track-lines-source',
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': ['get', 'color'],
                        'line-width': ['get', 'thickness'],
                        'line-opacity': 0.85
                    }
                });

                // Vehicle marker DOM element
                const markerEl = document.createElement('div');
                markerEl.className = 'marker-vehicle-pin';
                markerEl.id = 'player-vehicle-marker';
                markerEl.innerHTML = `
                    <div class="marker-heading-arrow" id="marker-arrow"></div>
                    <i class="ri-truck-fill"></i>
                `;

                vehicleMarker = new maplibregl.Marker({
                    element: markerEl,
                    anchor: 'center',
                    rotationAlignment: 'map'
                });
            });
        }

        /* ============================================================
           2. TOOLBAR & PRESET HANDLING
           ============================================================ */
        function initToolbarEvents() {
            // Vehicle select change (Choices.js keeps the underlying <select>
            // in sync and still dispatches a native 'change' event on it)
            document.getElementById('select-vehicle').addEventListener('change', function () {
                updateVehicleSidebarInfo();
                // Don't auto-load track - user should click "Show Track" button
                // loadTrackData();
            });

            // Preset buttons (Today, Yesterday, Week, Month)
            document.querySelectorAll('.preset-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const preset = this.dataset.preset;
                    applyDatePreset(preset);
                });
            });

            // Show Track button
            document.getElementById('btn-show-track').addEventListener('click', function () {
                loadTrackData();
            });

            // Settings change that only require re-drawing without API call
            document.getElementById('select-track-color').addEventListener('change', function () {
                if (trackData && trackData.points && trackData.points.length) {
                    renderTrackPolylines();
                }
            });

            document.getElementById('select-thickness').addEventListener('change', function () {
                if (trackData && trackData.points && trackData.points.length) {
                    renderTrackPolylines();
                }
            });

            document.getElementById('check-annotations').addEventListener('change', function () {
                if (trackData) {
                    renderAnnotations();
                }
            });

            // Timeline scrubbing events
            initTimelineScrubbing();
        }

        function applyDatePreset(preset) {
            const now = new Date();
            let from = new Date();
            let to = new Date();

            if (preset === 'today') {
                from.setHours(0, 0, 0, 0);
            } else if (preset === 'yesterday') {
                from.setDate(now.getDate() - 1);
                from.setHours(0, 0, 0, 0);
                to.setDate(now.getDate() - 1);
                to.setHours(23, 59, 59, 999);
            } else if (preset === 'week') {
                from.setDate(now.getDate() - 7);
                from.setHours(0, 0, 0, 0);
            } else if (preset === 'month') {
                from.setDate(now.getDate() - 30);
                from.setHours(0, 0, 0, 0);
            }

            document.getElementById('input-from').value = formatDateTimeLocal(from);
            document.getElementById('input-to').value = formatDateTimeLocal(to);
        }

        function formatDateTimeLocal(d) {
            const pad = num => String(num).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }

        function updateVehicleSidebarInfo() {
            const select = document.getElementById('select-vehicle');
            const selectedOpt = select.options[select.selectedIndex];
            
            // Handle case when no vehicle is selected (placeholder selected)
            if (!selectedOpt || !selectedOpt.value) {
                document.getElementById('side-veh-fleet').textContent = 'No Vehicle Selected';
                document.getElementById('side-veh-plate').textContent = 'Please select a vehicle from the dropdown';
                return;
            }

            document.getElementById('side-veh-fleet').textContent = selectedOpt.dataset.fleet || 'Asset';
            document.getElementById('side-veh-plate').textContent = (selectedOpt.dataset.truck || '') + (selectedOpt.dataset.driver ? ' • ' + selectedOpt.dataset.driver : '');
        }

        /* ============================================================
           3. LOAD HISTORICAL TRACK DATA VIA AJAX
           ============================================================ */
        function loadTrackData() {
            const vehicleId = document.getElementById('select-vehicle').value;
            const from = document.getElementById('input-from').value;
            const to = document.getElementById('input-to').value;
            const tripDetector = document.getElementById('check-trip-detector').checked ? 1 : 0;
            const showAnnotations = document.getElementById('check-annotations').checked ? 1 : 0;

            if (!vehicleId) {
                alert('Please select a vehicle.');
                return;
            }

            // UI loading state
            pausePlayback();
            const btn = document.getElementById('btn-show-track');
            const btnText = document.getElementById('btn-show-track-text');
            btn.disabled = true;
            btnText.textContent = 'Loading...';
            setMapStatus('loading', 'Loading Track Data from Wialon...');

            const url = `{{ route('vehicle_history.track_data') }}?vehicle_id=${encodeURIComponent(vehicleId)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&trip_detector=${tripDetector}&show_annotations=${showAnnotations}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btnText.textContent = 'Show Track';

                    if (!data.success) {
                        setMapStatus('warning', data.message || 'Error loading track data');
                        alert(data.message || 'Could not retrieve track data.');
                        return;
                    }

                    trackData = data;
                    handleTrackDataLoaded(data);
                })
                .catch(err => {
                    console.error('Track load error:', err);
                    btn.disabled = false;
                    btnText.textContent = 'Show Track';
                    setMapStatus('warning', 'Network Error');
                    alert('Failed to connect to server. Please try again.');
                });
        }

        function handleTrackDataLoaded(data) {
            const vehicle = data.vehicle || {};
            document.getElementById('hud-vehicle-name').textContent = vehicle.fleet_no || vehicle.unit_name || vehicle.unit_id || 'Vehicle';
            document.getElementById('hud-vehicle-meta').textContent = [vehicle.make_model, vehicle.truck_no, vehicle.driver_name]
                .filter(value => value && value !== '—' && value !== 'N/A')
                .join(' • ') || '—';

            // 1. Update Sidebar KPIs
            document.getElementById('side-mileage').textContent = (data.summary.total_mileage_km || 0).toFixed(2) + ' km';
            document.getElementById('side-duration').textContent = data.summary.total_duration_fmt || '0h 0m';
            document.getElementById('side-moving-time').textContent = data.summary.moving_duration_fmt || '0h 0m';
            document.getElementById('side-max-speed').textContent = (data.summary.max_speed || 0) + ' km/h';

            // 2. Populate Trips & Stops lists
            renderTripsList(data.trips || []);
            renderStopsList(data.stops || []);

            // 3. Render Polyline(s) on Map
            renderTrackPolylines();

            // 4. Render Annotations Pins
            renderAnnotations();

            // 5. Update Timeline
            if (data.points && data.points.length > 0) {
                currentPointIndex = 0;
                document.getElementById('timeline-start-label').textContent = data.points[0].time;
                document.getElementById('timeline-end-label').textContent = data.points[data.points.length - 1].time;
                document.getElementById('timeline-duration-label').textContent = 'Total: ' + data.summary.total_duration_fmt;

                // Put vehicle marker at first point
                updatePlaybackState(0);
                fitTrackBounds();
                setMapStatus('online', `${data.points.length} GPS Points Loaded`);
            } else {
                setMapStatus('warning', 'No GPS Points in Period');
                if (vehicleMarker) vehicleMarker.remove();
            }
        }

        /* ============================================================
           4. RENDER POLYLINES & MAP ANNOTATIONS
           ============================================================ */
        function renderTrackPolylines() {
            if (!mapReady || !trackData || !trackData.points || !trackData.points.length) {
                return;
            }

            const colorMode = document.getElementById('select-track-color').value;
            const thickness = parseInt(document.getElementById('select-thickness').value) || 7;
            const features = [];

            if (colorMode === 'trips' && trackData.trips && trackData.trips.length > 0) {
                // Segment track by trips
                trackData.trips.forEach(trip => {
                    const tripPts = trackData.points.slice(trip.start_idx, trip.end_idx + 1);
                    if (tripPts.length >= 2) {
                        features.push({
                            type: 'Feature',
                            properties: {
                                color: trip.color || '#2563eb',
                                thickness: thickness,
                                tripId: trip.id
                            },
                            geometry: {
                                type: 'LineString',
                                coordinates: tripPts.map(p => [p.lng, p.lat])
                            }
                        });
                    }
                });
            } else {
                // Single line mode or custom colors
                let singleColor = '#2563eb';
                if (colorMode === 'orange') singleColor = '#ea580c';
                if (colorMode === 'green') singleColor = '#10b981';

                features.push({
                    type: 'Feature',
                    properties: {
                        color: singleColor,
                        thickness: thickness
                    },
                    geometry: {
                        type: 'LineString',
                        coordinates: trackData.points.map(p => [p.lng, p.lat])
                    }
                });
            }

            map.getSource('track-lines-source').setData({
                type: 'FeatureCollection',
                features: features
            });
        }

        function renderAnnotations() {
            // Remove previous annotation markers
            annotationMarkers.forEach(m => m.remove());
            annotationMarkers = [];

            const showAnnotations = document.getElementById('check-annotations').checked;
            if (!showAnnotations || !trackData || !trackData.annotations || !trackData.annotations.length) {
                return;
            }

            trackData.annotations.forEach(ann => {
                const el = document.createElement('div');
                el.className = 'marker-annotation-pin';
                el.style.background = ann.color || '#2563eb';
                el.innerHTML = `<i class="${ann.icon}"></i> <span>${escapeHtml(ann.title)}</span>`;

                const popupHtml = `
                    <div style="padding: 6px; font-size: 12px;">
                        <strong style="color: ${ann.color};">${escapeHtml(ann.title)}</strong>
                        <div style="margin-top: 2px; color: #475569;">${escapeHtml(ann.description)}</div>
                        <div style="margin-top: 4px; font-family: monospace; font-size: 11px; color: #64748b;">${ann.lat.toFixed(5)}, ${ann.lng.toFixed(5)}</div>
                    </div>
                `;
                const popup = new maplibregl.Popup({ offset: 16 }).setHTML(popupHtml);

                const m = new maplibregl.Marker({ element: el, anchor: 'bottom' })
                    .setLngLat([ann.lng, ann.lat])
                    .setPopup(popup)
                    .addTo(map);

                annotationMarkers.push(m);
            });
        }

        function fitTrackBounds() {
            if (!map || !trackData || !trackData.points || !trackData.points.length) {
                return;
            }

            const bounds = new maplibregl.LngLatBounds();
            trackData.points.forEach(p => bounds.extend([p.lng, p.lat]));
            map.fitBounds(bounds, { padding: 60, maxZoom: 16, duration: 800 });
        }

        /* ============================================================
           5. TRIPS & STOPS LIST RENDERING
           ============================================================ */
        function renderTripsList(trips) {
            const container = document.getElementById('trip-list-wrap');
            document.getElementById('trip-badge-count').textContent = trips.length;

            if (!trips.length) {
                container.innerHTML = `<div class="text-center py-4 text-muted"><span>No separate trips detected</span></div>`;
                return;
            }

            let html = '';
            trips.forEach((t, i) => {
                html += `
                    <div class="trip-item-card ${i === 0 ? 'active' : ''}" style="border-left-color: ${t.color};" onclick="selectTrip(${i})" id="trip-card-${i}">
                        <div class="trip-item-header">
                            <span class="trip-item-name">
                                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${t.color};"></span>
                                ${escapeHtml(t.name)}
                            </span>
                            <span class="trip-item-distance">${t.distance_km.toFixed(1)} km</span>
                        </div>
                        <div class="trip-item-meta">
                            <span class="trip-meta-time"><i class="ri-time-line"></i> ${t.start_time_fmt} &ndash; ${t.end_time_fmt}</span>
                            <div class="trip-meta-stats">
                                <span class="trip-meta-chip"><i class="ri-timer-line"></i> ${t.duration_fmt}</span>
                                <span class="trip-meta-chip"><i class="ri-dashboard-3-line"></i> ${t.avg_speed} km/h avg</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function renderStopsList(stops) {
            const container = document.getElementById('stop-list-wrap');
            document.getElementById('stop-badge-count').textContent = stops.length;

            if (!stops.length) {
                container.innerHTML = `<div class="text-center py-4 text-muted"><span>No significant stops recorded</span></div>`;
                return;
            }

            let html = '';
            stops.forEach((s, i) => {
                html += `
                    <div class="trip-item-card" style="border-left-color: #f59e0b;" onclick="zoomToCoordinate(${s.lng}, ${s.lat})">
                        <div class="trip-item-header">
                            <span class="trip-item-name text-warning">
                                <i class="ri-parking-box-fill"></i> Stop ${i + 1}
                            </span>
                            <span class="badge bg-warning text-dark">${s.duration_fmt}</span>
                        </div>
                        <div class="trip-item-meta">
                            <span class="trip-meta-time"><i class="ri-time-line"></i> ${s.start_fmt} &ndash; ${s.end_fmt}</span>
                            <span class="trip-meta-time" style="font-family:'JetBrains Mono', monospace; font-weight:500;"><i class="ri-map-pin-line"></i> ${s.location}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function switchSidebarTab(tab) {
            document.getElementById('tab-btn-trips').classList.toggle('active', tab === 'trips');
            document.getElementById('tab-btn-stops').classList.toggle('active', tab === 'stops');
            document.getElementById('trip-list-wrap').style.display = tab === 'trips' ? 'flex' : 'none';
            document.getElementById('stop-list-wrap').style.display = tab === 'stops' ? 'flex' : 'none';
        }

        function selectTrip(tripIndex) {
            if (!trackData || !trackData.trips || !trackData.trips[tripIndex]) return;

            document.querySelectorAll('.trip-item-card').forEach((el, i) => el.classList.toggle('active', i === tripIndex));

            const trip = trackData.trips[tripIndex];
            const tripPts = trackData.points.slice(trip.start_idx, trip.end_idx + 1);

            if (tripPts.length > 0) {
                const bounds = new maplibregl.LngLatBounds();
                tripPts.forEach(p => bounds.extend([p.lng, p.lat]));
                map.fitBounds(bounds, { padding: 80, maxZoom: 16, duration: 600 });

                // Stop any running playback and move BOTH the visual playhead
                // and the underlying playback pointer to this trip's start.
                // Previously only the visuals were updated here, so pressing
                // Play afterwards resumed from wherever playback had last
                // stopped instead of from the clicked trip.
                pausePlayback();
                currentPointIndex = trip.start_idx;
                updatePlaybackState(trip.start_idx);
            }
        }

        function zoomToCoordinate(lng, lat) {
            map.flyTo({ center: [lng, lat], zoom: 15, duration: 600 });
        }

        /* ============================================================
           6. CLIENT-SIDE PLAYBACK ENGINE
           ============================================================ */
        function togglePlayback() {
            if (!trackData || !trackData.points || !trackData.points.length) {
                return;
            }

            if (isPlaying) {
                pausePlayback();
            } else {
                startPlayback();
            }
        }

        function startPlayback() {
            if (!trackData || !trackData.points || !trackData.points.length) return;

            // If at end of track, loop back to start
            if (currentPointIndex >= trackData.points.length - 1) {
                currentPointIndex = 0;
            }

            isPlaying = true;
            document.getElementById('play-pause-icon').className = 'ri-pause-fill';
            document.getElementById('btn-play-pause').classList.add('bg-danger');
            setMapStatus('online', `Playing (${playbackSpeed}x)`);

            lastFrameTimestamp = performance.now();
            animationFrameId = requestAnimationFrame(playbackLoop);
        }

        function pausePlayback() {
            isPlaying = false;
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
            document.getElementById('play-pause-icon').className = 'ri-play-fill';
            document.getElementById('btn-play-pause').classList.remove('bg-danger');
            setMapStatus('online', 'Paused');
        }

        function toggleTelemetryHUD() {
            const toggleBtn = document.getElementById('telemetry-toggle-btn');
            toggleTelemetryHud();
            const isOpen = window.matchMedia('(max-width: 1024px)').matches
                ? document.getElementById('telemetry-hud').classList.contains('show-mobile')
                : getComputedStyle(document.getElementById('telemetry-hud')).display !== 'none';
            if (toggleBtn) toggleBtn.querySelector('i').className = isOpen ? 'ri-close-line' : 'ri-radar-line';
        }

        function setPlaybackSpeed(speed) {
            playbackSpeed = speed;
            document.querySelectorAll('.speed-btn').forEach(btn => {
                btn.classList.toggle('active', parseFloat(btn.dataset.speed) === speed);
            });
            if (isPlaying) {
                setMapStatus('online', `Playing (${playbackSpeed}x)`);
            }
        }

        function playbackLoop(now) {
            if (!isPlaying || !trackData || !trackData.points) return;

            const deltaMs = now - lastFrameTimestamp;
            lastFrameTimestamp = now;

            // Calculate progress increment: base 1 GPS point advance per 200ms at 1x speed
            const step = (deltaMs / 200) * playbackSpeed;
            currentPointIndex += step;

            if (currentPointIndex >= trackData.points.length - 1) {
                currentPointIndex = trackData.points.length - 1;
                updatePlaybackState(Math.floor(currentPointIndex));
                pausePlayback();
                return;
            }

            updatePlaybackState(Math.floor(currentPointIndex));
            animationFrameId = requestAnimationFrame(playbackLoop);
        }

        function seekTrack(action) {
            if (!trackData || !trackData.points || !trackData.points.length) return;

            const total = trackData.points.length;
            if (action === 'first') {
                currentPointIndex = 0;
            } else if (action === 'last') {
                currentPointIndex = total - 1;
            } else if (action === 'prev') {
                currentPointIndex = Math.max(0, currentPointIndex - 10);
            } else if (action === 'next') {
                currentPointIndex = Math.min(total - 1, currentPointIndex + 10);
            }

            updatePlaybackState(Math.floor(currentPointIndex));
        }

        /* ============================================================
           7. REAL-TIME HUD & VEHICLE MARKER SYNCHRONIZATION
           ============================================================ */
        function updatePlaybackState(idx) {
            if (!trackData || !trackData.points || !trackData.points[idx]) return;

            const pt = trackData.points[idx];
            const total = trackData.points.length;
            const progressPercent = total > 1 ? (idx / (total - 1)) * 100 : 0;

            // 1. Move & Rotate Vehicle Marker
            if (vehicleMarker && mapReady) {
                vehicleMarker.setLngLat([pt.lng, pt.lat]);
                if (!vehicleMarker._map) {
                    vehicleMarker.addTo(map);
                }

                // Rotate heading arrow
                const arrow = document.getElementById('marker-arrow');
                if (arrow) {
                    arrow.style.transform = `translateX(-50%) rotate(${pt.course || 0}deg)`;
                }
            }

            // 2. Update Timeline Handle & Progress Bar
            document.getElementById('timeline-progress-bar').style.width = progressPercent + '%';
            document.getElementById('timeline-handle').style.left = progressPercent + '%';

            // 3. Update Digital Clock
            document.getElementById('digital-clock-time').textContent = pt.time;
            document.getElementById('digital-clock-date').textContent = pt.date_fmt ? pt.date_fmt.split(',')[0] : '';

            // 4. Update Telemetry HUD Readouts
            document.getElementById('hud-speed').innerHTML = `${pt.speed} <span class="fs-6 font-normal">km/h</span>`;
            document.getElementById('hud-course').textContent = `${pt.course}°`;
            document.getElementById('hud-alt').innerHTML = `${pt.alt} <span class="fs-6 font-normal">m</span>`;
            document.getElementById('hud-sats').textContent = pt.sats;
            document.getElementById('hud-coordinates').textContent = `${Number(pt.lat).toFixed(5)}, ${Number(pt.lng).toFixed(5)}`;
            document.getElementById('hud-gps-time').textContent = pt.date_fmt || pt.datetime || pt.time || '—';
            document.getElementById('hud-status-badge').textContent = pt.speed > 0 ? `MOVING (${pt.speed} km/h)` : 'STOPPED';
            document.getElementById('hud-status-badge').className = pt.speed > 0 ? 'badge bg-success' : 'badge bg-secondary';

            // 5. Update Sensors dynamically
            const sensors = pt.sensors || {};
            const sensorsContainer = document.getElementById('hud-sensors-container');
            if (sensorsContainer) {
                let sHtml = '';
                for (const [sKey, sVal] of Object.entries(sensors)) {
                    // Skip sensors with no data (showing --)
                    if (sVal === '--' || sVal === '' || sVal === null) {
                        continue;
                    }
                    
                    const isIgnition = sKey.toLowerCase().includes('ignition');
                    const colorStyle = isIgnition ? (sVal === 'On' ? 'color: #22c55e;' : 'color: #94a3b8;') : '';
                    sHtml += `
                        <div class="hud-sensor-row">
                            <span class="hud-sensor-name">${escapeHtml(sKey)}</span>
                            <span class="hud-sensor-val" style="${colorStyle}">${escapeHtml(sVal)}</span>
                        </div>
                    `;
                }
                sensorsContainer.innerHTML = sHtml || '<div class="text-muted text-center py-1">No sensors configured</div>';
            }

            // Sync active trip highlighting in sidebar
            if (pt.trip_idx !== undefined && trackData.trips && trackData.trips[pt.trip_idx]) {
                document.querySelectorAll('.trip-item-card').forEach((c, i) => c.classList.toggle('active', i === pt.trip_idx));
            }
        }

        /* ============================================================
           8. TIMELINE SCRUBBING (DRAG & CLICK SEEK)
           ============================================================ */
        function initTimelineScrubbing() {
            const trackWrap = document.getElementById('timeline-track-wrap');
            let isDragging = false;

            function seekFromEvent(e) {
                if (!trackData || !trackData.points || !trackData.points.length) return;

                const rect = trackWrap.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
                const targetIdx = Math.round(ratio * (trackData.points.length - 1));

                currentPointIndex = targetIdx;
                updatePlaybackState(targetIdx);
            }

            trackWrap.addEventListener('mousedown', function (e) {
                isDragging = true;
                pausePlayback();
                seekFromEvent(e);
            });

            window.addEventListener('mousemove', function (e) {
                if (isDragging) {
                    seekFromEvent(e);
                }
            });

            window.addEventListener('mouseup', function () {
                if (isDragging) {
                    isDragging = false;
                }
            });

            // Touch events for mobile/tablets
            trackWrap.addEventListener('touchstart', function (e) {
                isDragging = true;
                pausePlayback();
                seekFromEvent(e);
            });

            window.addEventListener('touchmove', function (e) {
                if (isDragging) {
                    seekFromEvent(e);
                }
            });

            window.addEventListener('touchend', function () {
                if (isDragging) {
                    isDragging = false;
                }
            });
        }

        /* ============================================================
           9. FULLSCREEN / FULL VIEW TOGGLE ENGINE
           ============================================================ */
        function isFullscreenActive() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
        }

        function toggleFullscreen() {
            const wrapper = document.querySelector('.track-player-wrapper');
            if (!wrapper) return;

            if (!isFullscreenActive()) {
                if (wrapper.requestFullscreen) {
                    wrapper.requestFullscreen();
                } else if (wrapper.webkitRequestFullscreen) {
                    wrapper.webkitRequestFullscreen();
                } else if (wrapper.mozRequestFullScreen) {
                    wrapper.mozRequestFullScreen();
                } else if (wrapper.msRequestFullscreen) {
                    wrapper.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        }

        function handleFullscreenChange() {
            const isFs = isFullscreenActive();

            // Update Top button
            const iconTop = document.getElementById('fs-main-icon');
            const textTop = document.getElementById('fs-main-text');
            if (iconTop) iconTop.className = isFs ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line';
            if (textTop) textTop.textContent = isFs ? '{{ __("Exit Full") }}' : '{{ __("Full View") }}';

            // Update Map Floating button
            const iconMap = document.getElementById('fs-map-icon');
            const textMap = document.getElementById('fs-map-text');
            if (iconMap) iconMap.className = isFs ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line';
            if (textMap) textMap.textContent = isFs ? '{{ __("Exit Full") }}' : '{{ __("Full View") }}';

            // Recalibrate MapLibre GL canvas bounds smoothly
            if (map && mapReady) {
                map.resize();
                setTimeout(() => map.resize(), 100);
                setTimeout(() => map.resize(), 250);
                setTimeout(() => map.resize(), 500);
            }
        }

        window.addEventListener('resize', function () {
            if (map && mapReady) {
                map.resize();
            }
        });

        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);

        function setMapStatus(type, message) {
            const pill = document.getElementById('map-status-pill');
            const dot = document.getElementById('status-dot');
            const text = document.getElementById('map-status-text');

            text.textContent = message;
            if (type === 'loading') {
                dot.style.background = '#f59e0b';
                dot.style.boxShadow = '0 0 0 3px rgba(245, 158, 11, 0.35)';
            } else if (type === 'warning') {
                dot.style.background = '#ef4444';
                dot.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.35)';
            } else {
                dot.style.background = '#22c55e';
                dot.style.boxShadow = '0 0 0 3px rgba(34, 197, 94, 0.35)';
            }
        }

        function toggleTelemetryHud() {
            const hud = document.getElementById('telemetry-hud');
            const button = document.getElementById('telemetry-hud-toggle');
            const mobile = window.matchMedia('(max-width: 1024px)').matches;
            const isOpen = mobile ? hud.classList.contains('show-mobile') : getComputedStyle(hud).display !== 'none';
            if (mobile) {
                hud.classList.toggle('show-mobile', !isOpen);
            } else {
                hud.style.display = isOpen ? 'none' : 'block';
            }
            button?.setAttribute('aria-pressed', String(!isOpen));
            button?.classList.toggle('active', !isOpen);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        /* ============================================================
           EXPORT & DOWNLOAD HANDLERS
           ============================================================ */
        function toggleExportMenu(e) {
            if (e) e.stopPropagation();
            
            // Handle header export menu
            const menuHeader = document.getElementById('export-menu-list-header');
            if (menuHeader) {
                const isVisible = menuHeader.style.display === 'block';
                menuHeader.style.display = isVisible ? 'none' : 'block';
            }
            
            // Handle old export menu (if still exists)
            const menu = document.getElementById('export-menu-list');
            if (menu) {
                menu.classList.toggle('show');
            }
        }

        document.addEventListener('click', function(e) {
            // Handle header dropdown
            const dropdownHeader = document.getElementById('export-history-dropdown-header');
            const menuHeader = document.getElementById('export-menu-list-header');
            if (dropdownHeader && !dropdownHeader.contains(e.target) && menuHeader) {
                menuHeader.style.display = 'none';
            }
            
            // Handle old dropdown (if still exists)
            const dropdown = document.getElementById('export-history-dropdown');
            const menu = document.getElementById('export-menu-list');
            if (dropdown && !dropdown.contains(e.target) && menu) {
                menu.classList.remove('show');
            }
        });

        function exportVehicleHistory(format) {
            // Hide both menus
            const menuHeader = document.getElementById('export-menu-list-header');
            if (menuHeader) menuHeader.style.display = 'none';
            
            const menu = document.getElementById('export-menu-list');
            if (menu) menu.classList.remove('show');

            const vehicleId = document.getElementById('select-vehicle').value;
            const from = document.getElementById('input-from').value;
            const to = document.getElementById('input-to').value;
            const tripDetector = document.getElementById('check-trip-detector').checked ? 1 : 0;

            if (!vehicleId) {
                alert('Please select a vehicle first.');
                return;
            }

            if (!from || !to) {
                alert('Please select both From and To dates.');
                return;
            }

            const exportUrl = `{{ route('vehicle_history.export') }}?vehicle_id=${encodeURIComponent(vehicleId)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&trip_detector=${tripDetector}&format=${encodeURIComponent(format)}`;

            // Trigger browser file download
            window.location.href = exportUrl;
        }
    </script>
</x-layouts.auth>
