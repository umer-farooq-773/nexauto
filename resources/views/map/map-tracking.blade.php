@if(!($embedded ?? false))
    <x-layouts.auth page-title="Fleet Tracking" sub-title="Live positions &amp; status">
@endif

    @php $wialonHost = rtrim($wialonHost ?? config('services.wialon.host'), '/'); @endphp
    <!-- Wialon Web SDK — must be loaded from the SAME host the session
         connects to. .com vs .eu mismatch breaks the real-time push channel. -->
    <script src="{{ $wialonHost }}/wsdk/script/wialon.js"></script>

    <!-- Leaflet.js — raster tiles from OpenStreetMap, no API key -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:        #4f46e5;
            --primary-dark:   #3730a3;
            --primary-soft:   #eef2ff;
            --surface:        #ffffff;
            --bg:             #f4f5f9;
            --border:         #e6e8f0;
            --text:           #1a1b2e;
            --text-muted:     #6b7086;
            --text-faint:     #9599ad;
            --radius-lg:      16px;
            --radius-md:      10px;
            --shadow-sm:      0 1px 2px rgba(17, 17, 40, 0.04), 0 1px 1px rgba(17,17,40,0.03);
            --shadow-md:      0 4px 14px rgba(17, 17, 40, 0.07);

            /* Red is reserved for OFFLINE (a real problem: no signal).
               Stopped is a normal, connected state — it gets a neutral
               slate, never red, so it can never look like an alarm. */
            --c-offline: #94a3b8;
            --c-stopped: #64748b;
            --c-slow:    #f59e0b;
            --c-medium:  #eab308;
            --c-moving:  #22c55e;
            --c-fast:    #2563eb;
        }

        * { box-sizing: border-box; }



        .container { max-width: 1360px; margin: auto; }

        /* ── Header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .page-title .icon-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow-md);
        }
        .page-title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .page-title p {
            margin: 2px 0 0;
            font-size: 13px;
            color: var(--text-muted);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }
        .status-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
        .status-pill.loading { background: #fffbeb; color: #92700c; }
        .status-pill.loading .dot { background: #f59e0b; }
        .status-pill.online  { background: #ecfdf3; color: #067647; }
        .status-pill.online  .dot { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
        .status-pill.warning { background: #fffbeb; color: #92700c; }
        .status-pill.warning .dot { background: #f59e0b; }
        .status-pill.offline { background: #fef3f2; color: #b42318; }
        .status-pill.offline .dot { background: #ef4444; }

        /* ── Toolbar ── */
        .toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 12px 14px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-sm);
        }
        .toolbar label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }
        .toolbar select {
            min-width: 260px;
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            font-family: inherit;
            font-size: 13px;
            color: var(--text);
        }
        .toolbar select:focus { outline: 2px solid var(--primary-soft); border-color: var(--primary); }
        .toolbar button {
            padding: 9px 16px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            background: var(--primary);
            color: white;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.15s;
        }
        .toolbar button:hover { background: var(--primary-dark); }
        #vehicle-count {
            margin-left: auto;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--bg);
            padding: 6px 12px;
            border-radius: 999px;
        }

        /* ── Searchable vehicle combobox (replaces the plain <select>) ── */
        .vehicle-combobox {
            position: relative;
            min-width: 260px;
        }
        .vehicle-combobox input {
            width: 100%;
            padding: 9px 34px 9px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            font-family: inherit;
            font-size: 13px;
            color: var(--text);
        }
        .vehicle-combobox input:focus { outline: 2px solid var(--primary-soft); border-color: var(--primary); }
        .vehicle-combobox input:disabled { cursor: not-allowed; opacity: .7; }
        .vehicle-combobox .vc-clear {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 0;
            background: transparent;
            color: var(--text-faint);
            font-size: 15px;
            line-height: 1;
            cursor: pointer;
            display: none;
            border-radius: 50%;
        }
        .vehicle-combobox .vc-clear:hover { background: var(--border); color: var(--text); }
        .vehicle-combobox .vc-clear.show { display: block; }

        .vehicle-search-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 260px;
            overflow-y: auto;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            z-index: 1000;
        }
        .vehicle-search-dropdown.open { display: block; }
        .vehicle-search-dropdown .vs-item {
            padding: 10px 12px;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(230, 232, 240, 0.5);
        }
        .vehicle-search-dropdown .vs-item:last-child {
            border-bottom: none;
        }
        .vehicle-search-dropdown .vs-item:hover,
        .vehicle-search-dropdown .vs-item.active {
            background: var(--primary-soft);
        }
        .vehicle-search-dropdown .vs-item mark {
            background: #fde68a;
            border-radius: 2px;
            padding: 0 2px;
            font-weight: 600;
        }
        .vehicle-search-dropdown .vs-empty {
            padding: 10px 12px;
            font-size: 12.5px;
            color: var(--text-faint);
        }
        .vehicle-search-dropdown .vs-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }
        .vehicle-search-dropdown .vs-dot.offline { background: var(--c-offline); }
        .vehicle-search-dropdown .vs-dot.stopped { background: var(--c-stopped); }
        .vehicle-search-dropdown .vs-dot.slow { background: var(--c-slow); }
        .vehicle-search-dropdown .vs-dot.medium { background: var(--c-medium); }
        .vehicle-search-dropdown .vs-dot.moving { background: var(--c-moving); }
        .vehicle-search-dropdown .vs-dot.fast { background: var(--c-fast); }
        .vehicle-search-dropdown .vs-name {
            flex: 1;
            min-width: 0;
            font-weight: 500;
            color: var(--text);
        }
        .vehicle-search-dropdown .vs-status {
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 3px 8px;
            border-radius: 10px;
            white-space: nowrap;
            margin-left: auto;
        }
        .vehicle-search-dropdown .vs-status.offline {
            background: #f1f5f9;
            color: #64748b;
        }
        .vehicle-search-dropdown .vs-status.stopped {
            background: #f1f5f9;
            color: #475569;
        }
        .vehicle-search-dropdown .vs-status.slow {
            background: #fef3c7;
            color: #92400e;
        }
        .vehicle-search-dropdown .vs-status.medium {
            background: #fef9c3;
            color: #854d0e;
        }
        .vehicle-search-dropdown .vs-status.moving {
            background: #d1fae5;
            color: #065f46;
        }
        .vehicle-search-dropdown .vs-status.fast {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ── Map ── */
        #map {
            height: 560px;
            width: 100%;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
        }

        /* ── Info panel ── */
        .info-panel {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            margin-top: 16px;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            box-shadow: 0 2px 8px rgba(17, 17, 40, 0.06), 0 1px 3px rgba(17,17,40,0.04);
        }
        .info-box {
            padding: 14px 16px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid rgba(230, 232, 240, 0.6);
            transition: all 0.2s ease;
        }
        .info-box:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
            transform: translateY(-1px);
        }
        .info-label {
            color: var(--text-faint);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .info-label::before {
            content: '';
            width: 3px;
            height: 10px;
            background: var(--primary);
            border-radius: 2px;
        }
        .info-value {
            color: var(--text);
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }
        .info-value.empty {
            color: var(--text-faint);
            font-weight: 500;
        }

        /* ── Section shell (fleet status panel) ── */
        .panel {
            margin-top: 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px;
            box-shadow: var(--shadow-sm);
        }
        .panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .panel-title h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }
        .panel-title .hint {
            font-size: 11.5px;
            color: var(--text-faint);
            font-weight: 500;
        }

        /* ── Shared status dot (used on the fleet status cards) ── */
        .status-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .status-dot.online { background: var(--c-moving); box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
        .status-dot.offline { background: var(--c-offline); }

        /* ── Fleet status filters ── */
        .fleet-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding-bottom: 14px;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }
        .fleet-filter-search input {
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            font-family: inherit;
            font-size: 13px;
            color: var(--text);
        }
        .fleet-filter-search input:focus { outline: 2px solid var(--primary-soft); border-color: var(--primary); }
        .fleet-filter-bar select {
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            font-family: inherit;
            font-size: 13px;
            color: var(--text);
        }
        .fleet-filter-bar select:focus { outline: 2px solid var(--primary-soft); border-color: var(--primary); }
        .fleet-filter-bar button {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .fleet-filter-bar button:hover { border-color: var(--primary); color: var(--primary); }
        .fleet-filter-bar button.active {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary-dark);
        }
        .fleet-filter-count {
            margin-left: auto;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* ── Live status cards (was the confusing progress-bar panel) ── */
        .legend-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 11.5px;
            color: var(--text-muted);
            align-items: center;
            padding-bottom: 14px;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }
        .legend-row .swatch {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .legend-row .dot { width: 8px; height: 8px; border-radius: 50%; }

        /* max-height + scroll keeps the DOM bounded when a fleet has
           hundreds of vehicles — see the pagination logic in JS below,
           which caps how many cards are ever mounted at once. */
        #route-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 10px;
            max-height: 640px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .route-list-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }
        .route-list-footer button {
            padding: 8px 16px;
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .route-list-footer button:hover { background: var(--primary-dark); }
        .route-list-footer span { font-size: 12px; color: var(--text-faint); }

        .route-card {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 12px 14px;
            cursor: pointer;
            transition: border-color 0.15s, transform 0.1s;
        }
        .route-card:hover { border-color: var(--primary); transform: translateY(-1px); }

        .route-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .route-name { font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 7px; }
        .status-badge {
            font-size: 10.5px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .route-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 10px;
        }
        .route-meta-item { font-size: 11.5px; color: var(--text-muted); }
        .route-meta-item b { color: var(--text); font-weight: 600; }

        /* ── Debug console (local env only) ── */
        .dev-panel { margin-top: 16px; }
        .dev-panel summary {
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            padding: 10px 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dev-panel summary .env-tag {
            font-size: 10px;
            background: #1a1b2e;
            color: #b8d7ff;
            padding: 2px 8px;
            border-radius: 999px;
            letter-spacing: 0.03em;
        }
        #debug-info {
            margin-top: 6px;
            background: #14161f;
            color: #b8d7ff;
            padding: 14px 16px;
            border-radius: var(--radius-md);
            font-family: 'SF Mono', Consolas, monospace;
            font-size: 11.5px;
            line-height: 1.6;
            max-height: 280px;
            overflow-y: auto;
        }

        /* Leaflet popup styling (was .maplibregl-popup-content) */
        .leaflet-popup-content-wrapper,
        .leaflet-popup-tip { border-radius: 10px; }
        .leaflet-popup-content {
            line-height: 1.6;
            font-family: 'Inter', sans-serif;
            font-size: 12.5px;
            margin: 10px 12px;
        }
        .truck-icon {
            font-size: 22px;
            line-height: 1;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,.35));
            transform-origin: center center;
            display: inline-block;
            transition: transform 0.35s ease;
        }
        .start-icon { font-size: 17px; line-height: 1; }
        /* Custom divIcons should not inherit Leaflet's default icon box/border */
        .vehicle-marker-icon, .start-marker-icon, .route-checkpoint-icon { background: transparent; border: none; }
        #show-all-btn{
            background-color:#2899b6;
        }
    </style>
</head>
<div class="map-page">
<div class="container">

    <div class="page-header">
        <div class="page-title">
            <div class="icon-badge">🚛</div>
            <div>
                <h1>Fleet Tracking</h1>
                <p>Live positions &amp; status</p>
            </div>
        </div>
        <div id="status" class="status-pill loading"><span class="dot"></span><span id="status-text">Connecting…</span></div>
    </div>

    <div class="toolbar">
        <label for="vehicle-search">Vehicle</label>

        <div class="vehicle-combobox" id="vehicle-combobox">
            <input
                type="text"
                id="vehicle-search"
                placeholder="Loading vehicles…"
                disabled
                autocomplete="off"
                role="combobox"
                aria-expanded="false"
                aria-autocomplete="list"
            />
            <button type="button" class="vc-clear" id="vehicle-search-clear" title="Clear" tabindex="-1">✕</button>
            <div id="vehicle-search-dropdown" class="vehicle-search-dropdown" role="listbox"></div>
        </div>

        <!-- Kept in the DOM (hidden) purely so any other code/back-end integration
             that expects a <select id="vehicle-select"> still finds one. -->
        <select id="vehicle-select" style="display:none" aria-hidden="true" tabindex="-1"></select>

        <button type="button" id="go-btn">Go</button>&nbsp;&nbsp;
        <button type="button" id="show-all-btn">Show all vehicles</button>
        <button type="button" id="toggle-traffic-btn" style="background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;"></span> Road Traffic
        </button>
        <button type="button" id="toggle-weather-btn" style="background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#0284c7;"></span> Weather
        </button>
        <span id="vehicle-count">0 vehicles</span>
    </div>

    <div id="map"></div>

    <div class="info-panel" id="info-panel" style="display: none;">
        <div class="info-box"><div class="info-label">Vehicle</div><div class="info-value" id="selected-name">–</div></div>
        <div class="info-box"><div class="info-label">Latitude</div><div class="info-value" id="selected-lat">–</div></div>
        <div class="info-box"><div class="info-label">Longitude</div><div class="info-value" id="selected-lng">–</div></div>
        <div class="info-box"><div class="info-label">Speed</div><div class="info-value" id="selected-speed">–</div></div>
        <div class="info-box"><div class="info-label">GPS time</div><div class="info-value" id="selected-time">–</div></div>
        <div class="info-box"><div class="info-label">Distance travelled</div><div class="info-value" id="selected-distance">–</div></div>
        <div class="info-box"><div class="info-label">Status</div><div class="info-value" id="selected-progress">–</div></div>
    </div>

    <div class="panel">
        <div class="panel-title">
            <h3>Live status</h3>
            <span class="hint">Dot colour = current speed band, same as the trail on the map · click a card to jump to it on the map</span>
        </div>

        <div class="fleet-filter-bar">
            <div class="fleet-filter-search">
                <input type="text" id="fleet-filter-name" placeholder="Filter by fleet name…" autocomplete="off" />
            </div>

            <select id="fleet-filter-status" aria-label="Filter by status">
                <option value="all">All statuses</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>

            <select id="fleet-filter-speed" aria-label="Filter by speed">
                <option value="all">Any speed</option>
                <option value="stopped">Stopped (0 km/h)</option>
                <option value="slow">Slow (1–20)</option>
                <option value="medium">Medium (21–50)</option>
                <option value="moving">Moving (51–80)</option>
                <option value="fast">Fast (&gt;80)</option>
            </select>

            <button type="button" id="fleet-filter-location" title="Only show vehicles currently visible on the map">📍 In map view</button>
            <button type="button" id="fleet-filter-reset">Reset filters</button>

            <span id="fleet-filter-count" class="fleet-filter-count">0 vehicles</span>
        </div>

        <div class="legend-row">
            <span class="swatch"><span class="dot" style="background:var(--c-offline)"></span>Offline — no recent signal</span>
            <span class="swatch"><span class="dot" style="background:var(--c-stopped)"></span>Stopped — connected, 0 km/h</span>
            <span class="swatch"><span class="dot" style="background:var(--c-slow)"></span>Slow (1–20)</span>
            <span class="swatch"><span class="dot" style="background:var(--c-medium)"></span>Medium (21–50)</span>
            <span class="swatch"><span class="dot" style="background:var(--c-moving)"></span>Moving (51–80)</span>
            <span class="swatch"><span class="dot" style="background:var(--c-fast)"></span>Fast (&gt;80)</span>
        </div>

        <div id="route-list"></div>

        <div id="route-list-footer" class="route-list-footer" style="display:none;">
            <button type="button" id="route-list-more">Load more vehicles</button>
            <span id="route-list-remaining"></span>
        </div>
    </div>
    @if (app()->environment('local'))
        <details class="dev-panel">
            <summary>
                <span class="env-tag">LOCAL</span>
                Developer log
            </summary>

            <div id="debug-info">🔍 Waiting for page load…</div>
        </details>
    @endif

</div>

<script>

/* ============================================================
   CONFIGURATION
   ============================================================ */

const WIALON_HOST = @json($wialonHost ?? config('services.wialon.host'));
const WIALON_TOKEN = @json($wialonToken ?? '');
const APP_ENV = @json(config('app.env'));
const IS_LOCAL = APP_ENV === 'local';
const OFFLINE_THRESHOLD_SECONDS = 15 * 60;
const OSM_TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const OSM_ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
const POLL_INTERVAL_MS = 8000;
const TRAIL_MIN_METRES = 8;
const MARKER_ANIM_MS = 600;

/* Fleet status panel is capped at FLEET_PAGE_SIZE mounted cards at a
   time — with 100+ vehicles this keeps the DOM small and re-renders
   fast; "Load more" raises the cap without ever building the whole list. */
const FLEET_PAGE_SIZE = 30;

let session = null;
let map = null;
let units = [];
let markers = {};
let startMarkers = {};
let vehicleData = {};
let unitMeta = {};      // unitId -> { imei }
let listeners = {};
let selectedVehicleId = null;
let followSelectedVehicle = true;
let trails = {};
let trailLayers = {};   // unitId -> L.LayerGroup holding that vehicle's coloured trail segments
let mapReady = false;
let pendingInitialFit = false;
let pollTimer = null;
let liveDataFlags = 0;
let routes = [];                 // avl_route items loaded from Wialon
let plannedRouteLayers = {};     // unitId -> { polyline, checkpoints: [markers] }

/* Fleet status filter/pagination state */
let fleetFilters = { name: '', status: 'all', speed: 'all', inMapView: false };
let fleetPageLimit = FLEET_PAGE_SIZE;
let fleetFilterNameTimer = null;
let fleetFooterRefreshTimer = null;


/* Debug panel only exists in the DOM when APP_ENV=local (Blade strips it
   server-side otherwise), so guard every call. */
function debug(message) {
    if (!IS_LOCAL) return;
    const element = document.getElementById('debug-info');
    if (!element) return;
    const time = new Date().toLocaleTimeString();
    element.innerHTML += '<br>[' + time + '] ' + escapeHtml(message);
    element.scrollTop = element.scrollHeight;
}

function logUnit(action, unitId) {
    const imei = unitMeta[unitId] ? unitMeta[unitId].imei : 'unknown';
    console.log('[Fleet] ' + action, { unit_id: unitId, imei: imei });
}

function setStatus(type, message) {
    const pill = document.getElementById('status');
    const text = document.getElementById('status-text');
    pill.className = 'status-pill ' + type;
    text.textContent = message;
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}



function initMap() {
    debug('Initializing map…');

    map = L.map('map', {
        center: [33.6844, 73.0479],
        zoom: 6,
        zoomControl: false
    });

    L.tileLayer(OSM_TILE_URL, {
        maxZoom: 19,
        attribution: OSM_ATTRIBUTION
    }).addTo(map);

    L.control.zoom({ position: 'topright' }).addTo(map);
    L.control.scale({ position: 'bottomleft', metric: true, imperial: false }).addTo(map);

    map.on('tileerror', function () {
        debug('Tile load error.');
    });

    // Re-apply the "in map view" location filter whenever the user pans or
    // zooms, so the fleet status list always reflects what's on screen.
    map.on('moveend', function () {
        if (fleetFilters.inMapView) renderFleetStatusPanel(true);
    });

    // Leaflet's map is interactive immediately after construction — no
    // async "style load" step like a vector-tile library, so mark ready now.
    mapReady = true;
    debug('Map initialized.');
    if (pendingInitialFit) { fitAllVehicles(); pendingInitialFit = false; }
}



function initWialonSession() {
    debug('Creating Wialon session…');
    session = wialon.core.Session.getInstance();
    session.initSession(String(WIALON_HOST).replace(/\/+$/, ''));
}

function loginToWialon() {

    if (!WIALON_TOKEN) {
        debug('Wialon token is empty.');
        setStatus('offline', 'Token missing');
        return;
    }

    session.loginToken(WIALON_TOKEN, '', function (code) {

        if (code) {
            debug('Login failed: ' + getWialonError(code));
            setStatus('offline', 'Login failed');
            return;
        }

        debug('Login successful. Host: ' + WIALON_HOST);
        setStatus('online', 'Connected');

        try { session.setEvtPollInterval(2); } catch (e) { debug('setEvtPollInterval unavailable: ' + e); }

        loadVehicles();
    });
}



function loadVehicles() {

    debug('Loading vehicles…');

    liveDataFlags =
        wialon.item.Item.dataFlag.base |
        wialon.item.Unit.dataFlag.messages |
        wialon.item.Unit.dataFlag.lastMessage;

    if (wialon.item.Unit.dataFlag.position) {
        liveDataFlags |= wialon.item.Unit.dataFlag.position;
    }

    session.updateDataFlags(
        [{ type: 'type', data: 'avl_unit', flags: liveDataFlags, mode: 0 }],
        function (code) {

            if (code) {
                debug('updateDataFlags failed: ' + getWialonError(code));
                setStatus('offline', 'Could not load vehicles');
                return;
            }

            units = session.getItems('avl_unit');

            if (!units || !units.length) {
                debug('No avl_unit items found.');
                setStatus('warning', 'No vehicles found');
                return;
            }

            units.forEach(function (unit) {
                unitMeta[unit.getId()] = {
                    imei: typeof unit.getUniqueId === 'function' ? unit.getUniqueId() : 'n/a'
                };
            });

            debug('Found ' + units.length + ' vehicle(s).');

            document.getElementById('vehicle-count').textContent = units.length + (units.length === 1 ? ' vehicle' : ' vehicles');

            populateVehicleDropdown();
            loadRoutes();
            subscribeUnitsForLiveUpdates(function () {
                attachRealtimeListeners();

                if (mapReady) loadInitialPositions();
                else map.on('load', loadInitialPositions);

                startPositionPolling();
                setStatus('online', 'Tracking ' + units.length + ' vehicle' + (units.length === 1 ? '' : 's'));
            });
        }
    );
}

function subscribeUnitsForLiveUpdates(callback) {

    const unitIds = units.map(function (unit) { return unit.getId(); });
    if (!unitIds.length) { if (callback) callback(); return; }

    debug('Subscribing ' + unitIds.length + ' unit(s) for live GPS…');

    session.updateDataFlags(
        [{ type: 'col', data: unitIds, flags: liveDataFlags, mode: 1 }],
        function (code) {
            if (code) {
                debug('Live subscription failed: ' + getWialonError(code));
            } else {
                debug('Live subscription active.');
            }
            if (callback) callback();
        }
    );
}

function startPositionPolling() {

    if (pollTimer) clearInterval(pollTimer);

    pollTimer = setInterval(function () {
        if (!session || !units.length) return;

        const unitIds = units.map(function (unit) { return unit.getId(); });

        // Record previous position timestamps before polling to detect changed data
        const prevTimestamps = {};
        units.forEach(function (unit) {
            const id = unit.getId();
            const pos = unit.getPosition();
            prevTimestamps[id] = pos ? pos.t : 0;
        });

        // Use mode: 0 (replace/reset flags) to force Wialon SDK to re-fetch live unit position data from server
        session.updateDataFlags(
            [{ type: 'col', data: unitIds, flags: liveDataFlags, mode: 0 }],
            function (code) {
                if (code) {
                    debug('[Poll] updateDataFlags failed: ' + getWialonError(code));
                    return;
                }

                refreshAllUnitPositions('poll');

                let updatedCount = 0;
                units.forEach(function (unit) {
                    const id = unit.getId();
                    const pos = unit.getPosition();
                    const newTime = pos ? pos.t : 0;
                    if (newTime !== prevTimestamps[id]) {
                        updatedCount++;
                    }
                });

                if (updatedCount > 0) {
                    debug('[Poll] Server refresh complete: ' + updatedCount + ' of ' + units.length + ' unit(s) received fresh position data.');
                } else {
                    debug('[Poll] Server refresh complete: No new position data across ' + units.length + ' unit(s).');
                }
            }
        );
    }, POLL_INTERVAL_MS);
}


/* ============================================================
   PLANNED ROUTES (Wialon "Routes" feature — separate from the
   live GPS trail. A route is its own item type, "avl_route",
   and is never loaded automatically — it has to be requested.)
   ============================================================ */

function loadRoutes(callback) {

    debug('Loading routes…');

    // 0x00000001 base properties + 0x00000100 configuration (rcfg.units)
    // + 0x00000200 check points (rpts). Hex values per Wialon's documented
    // route data-flag table — kept as literals so they don't depend on any
    // particular JS-wrapper property name.
    const ROUTE_FLAGS = 0x00000001 | 0x00000100 | 0x00000200;

    session.updateDataFlags(
        [{ type: 'type', data: 'avl_route', flags: ROUTE_FLAGS, mode: 0 }],
        function (code) {

            if (code) {
                debug('Loading routes failed: ' + getWialonError(code));
                if (callback) callback();
                return;
            }

            routes = session.getItems('avl_route') || [];
            debug('Found ' + routes.length + ' route(s) on the server.');
            if (callback) callback();
        }
    );
}

function getRouteName(route) {
    if (typeof route.getName === 'function') return route.getName();
    return route.nm || ('Route ' + route.getId());
}

// The JS SDK generally exposes one getter per loaded data flag, but the
// exact method name for "avl_route" items isn't something we could verify
// against a live server, so each getter tries the documented name first
// and falls back to the raw loaded field before giving up.
function getRouteConfig(route) {
    if (typeof route.getConfiguration === 'function') return route.getConfiguration();
    if (typeof route.getRouteConfiguration === 'function') return route.getRouteConfiguration();
    return route.rcfg || null;
}

function getRouteCheckpoints(route) {
    if (typeof route.getCheckPoints === 'function') return route.getCheckPoints();
    if (typeof route.getRoutePoints === 'function') return route.getRoutePoints();
    return route.rpts || null;
}

function getUnitNameById(unitId) {
    const unit = units.find(function (u) { return u.getId() === unitId; });
    return unit ? unit.getName() : null;
}

function findRoutesForUnit(unitId) {

    const unitName = getUnitNameById(unitId);

    return routes.filter(function (route) {

        const cfg = getRouteConfig(route);
        if (cfg && Array.isArray(cfg.units) && cfg.units.indexOf(unitId) !== -1) return true;

        // Fallback for routes that were built without explicitly assigning
        // the unit in the route config (e.g. assigned only via a round/schedule):
        // match on the route name containing the vehicle's name.
        if (unitName) {
            const routeName = getRouteName(route).toLowerCase();
            if (routeName.indexOf(unitName.toLowerCase()) !== -1) return true;
        }

        return false;
    });
}

function clearPlannedRoute(unitId) {
    const existing = plannedRouteLayers[unitId];
    if (!existing) return;
    if (existing.polyline) map.removeLayer(existing.polyline);
    (existing.checkpoints || []).forEach(function (m) { map.removeLayer(m); });
    delete plannedRouteLayers[unitId];
}

function clearAllPlannedRoutes() {
    Object.keys(plannedRouteLayers).forEach(function (id) { clearPlannedRoute(Number(id)); });
}

function renderPlannedRoute(unitId) {

    if (!mapReady) return;

    const matches = findRoutesForUnit(unitId);

    if (!matches.length) {
        debug('No Wialon route is linked to this vehicle (checked route "units" list and route name match).');
        return;
    }

    if (matches.length > 1) {
        debug(matches.length + ' routes matched this vehicle — showing "' + getRouteName(matches[0]) + '".');
    }

    const route = matches[0];
    const checkpoints = getRouteCheckpoints(route);

    if (!checkpoints || !checkpoints.length) {
        debug('Route "' + getRouteName(route) + '" has no check points configured.');
        return;
    }

    // IMPORTANT: for route check points, Wialon uses "x" for latitude and
    // "y" for longitude — the OPPOSITE of unit position messages (where "y"
    // is latitude, "x" is longitude). Getting this backwards silently plots
    // the route on the wrong side of the point.
    const latlngs = checkpoints
        .map(function (cp) { return [Number(cp.x), Number(cp.y)]; })
        .filter(function (ll) { return isFinite(ll[0]) && isFinite(ll[1]); });

    if (!latlngs.length) {
        debug('Route "' + getRouteName(route) + '" check points had no valid coordinates.');
        return;
    }

    const polyline = L.polyline(latlngs, {
        color: '#4f46e5',
        weight: 4,
        opacity: 0.9,
        dashArray: '8 6'
    }).addTo(map);

    const checkpointMarkers = checkpoints.map(function (cp, index) {
        const lat = Number(cp.x), lng = Number(cp.y);
        if (!isFinite(lat) || !isFinite(lng)) return null;

        const isFirst = index === 0;
        const isLast = index === checkpoints.length - 1;
        const label = isFirst ? '🟢' : (isLast ? '🏁' : '📍');

        const icon = L.divIcon({
            className: 'route-checkpoint-icon',
            html: '<div class="start-icon">' + label + '</div>',
            iconSize: [18, 18],
            iconAnchor: [9, 18]
        });

        return L.marker([lat, lng], { icon: icon })
            .bindPopup('<strong>' + escapeHtml(cp.n || ('Checkpoint ' + (index + 1))) + '</strong>')
            .addTo(map);
    }).filter(Boolean);

    plannedRouteLayers[unitId] = { polyline: polyline, checkpoints: checkpointMarkers };

    debug('Planned route "' + getRouteName(route) + '" drawn with ' + latlngs.length + ' check point(s).');
}


/* ============================================================
   INITIAL POSITIONS
   ============================================================ */

function loadInitialPositions() {

    units.forEach(function (unit) {
        applyUnitPosition(unit, 'initial');
    });

    fitAllVehicles();
    flushAllTrailLayers();
    debug('Initial positions displayed.');

    // Auto-select vehicle from URL parameter if present
    autoSelectVehicleFromURL();
}

function autoSelectVehicleFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleParam = urlParams.get('vehicle');

    if (!vehicleParam) return;

    debug('Auto-selecting vehicle from URL: ' + vehicleParam);

    // Find the vehicle by fleet_no
    const matchedUnit = units.find(function(unit) {
        const name = unit.getName ? unit.getName() : '';
        return name.toLowerCase() === vehicleParam.toLowerCase() ||
               name.toLowerCase().includes(vehicleParam.toLowerCase());
    });

    if (matchedUnit) {
        const unitId = matchedUnit.getId();
        selectedVehicleId = unitId;

        // Update search input
        const input = document.getElementById('vehicle-search');
        const vehicleName = matchedUnit.getName();
        if (input) input.value = vehicleName;

        // Update hidden select
        const select = document.getElementById('vehicle-select');
        if (select) select.value = unitId;

        // Update clear button visibility
        toggleClearButton();

        // Wait for vehicle data to be loaded before focusing
        // Try multiple times with increasing delays to ensure data is available
        let attempts = 0;
        const maxAttempts = 10;
        const checkInterval = 300;

        const tryFocus = function() {
            attempts++;

            // Check if vehicle data is available
            if (vehicleData[unitId]) {
                focusVehicle(unitId);
                debug('Auto-selected and focused vehicle: ' + vehicleName + ' (attempt ' + attempts + ')');
            } else if (attempts < maxAttempts) {
                // Data not ready yet, try again
                debug('Vehicle data not ready, retrying... (attempt ' + attempts + ')');
                setTimeout(tryFocus, checkInterval);
            } else {
                // Give up after max attempts
                debug('Failed to load vehicle data after ' + maxAttempts + ' attempts for: ' + vehicleName);
            }
        };

        // Start the first attempt after a short delay
        setTimeout(tryFocus, 500);
    } else {
        debug('Vehicle not found: ' + vehicleParam);
    }
}

function fitAllVehicles() {

    if (!mapReady) { pendingInitialFit = true; return; }

    const coords = Object.values(vehicleData).map(d => [d.lat, d.lng]);
    if (!coords.length) return;

    if (coords.length === 1) { map.flyTo(coords[0], 12); return; }

    const bounds = L.latLngBounds(coords);
    map.fitBounds(bounds, { padding: [60, 60], maxZoom: 14 });
}


/* ============================================================
   REAL-TIME LISTENERS
   ============================================================ */

function attachRealtimeListeners() {

    debug('Registering real-time listeners…');

    try {
        if (!listeners._session) {
            listeners._session = {};
            listeners._session.lastMsg = session.addListener('lastMessageChanged', function (event) {
                const unit = event && event.getTarget ? event.getTarget() : null;
                if (unit && typeof unit.getId === 'function') applyUnitPosition(unit, 'lastMessageChanged');
            });
            listeners._session.pos = session.addListener('positionChanged', function (event) {
                const unit = event && event.getTarget ? event.getTarget() : null;
                if (unit && typeof unit.getId === 'function') applyUnitPosition(unit, 'positionChanged');
            });
            listeners._session.item = session.addListener('itemChanged', function (event) {
                const item = event && event.getTarget ? event.getTarget() : null;
                debug('Session itemChanged: unit ' + (item && item.getId ? item.getId() : '?'));
            });
        }
    } catch (e) {
        debug('Could not attach session listener: ' + e);
    }

    units.forEach(function (unit) {

        const id = unit.getId();

        if (listeners[id]) {
            try {
                if (listeners[id].msg) unit.removeListenerById(listeners[id].msg);
                if (listeners[id].pos) unit.removeListenerById(listeners[id].pos);
                if (listeners[id].changePos) unit.removeListenerById(listeners[id].changePos);
            } catch (error) { /* no-op */ }
        }

        listeners[id] = {};
        listeners[id].msg = unit.addListener('messageRegistered', function (event) { handleMessageRegistered(unit, event); });
        listeners[id].pos = unit.addListener('positionChanged', function () { applyUnitPosition(unit, 'unit.positionChanged'); });
        listeners[id].changePos = unit.addListener('changePosition', function () { applyUnitPosition(unit, 'changePosition'); });
    });

    debug('Listeners registered. Waiting for GPS messages…');

    let firstEventReceived = false;
    window._wialonFirstEventReceived = function () { firstEventReceived = true; };

    setTimeout(function () {
        if (!firstEventReceived) {
            debug('No realtime event in 2 minutes — polling fallback remains active every ' + (POLL_INTERVAL_MS / 1000) + 's.');
        }
    }, 120000);
}

function positionFromUnit(unit, msg) {

    let latitude = null, longitude = null, speed = 0, course = 0, timestamp = 0;
    const sdkPosition = unit.getPosition();

    if (sdkPosition) {
        latitude = Number(sdkPosition.y);
        longitude = Number(sdkPosition.x);
        speed = Number(sdkPosition.s || 0);
        course = Number(sdkPosition.c || 0);
        timestamp = Number(sdkPosition.t || 0);
    }

    if ((latitude === null || !isFinite(latitude)) && msg && msg.pos) {
        const p = msg.pos;
        latitude = Number(p.y);
        longitude = Number(p.x);
        speed = Number(p.s || 0);
        course = Number(p.c || 0);
        timestamp = Number(msg.t || p.t || 0);
    }

    if (latitude === null || !isFinite(latitude) || !isFinite(longitude)) return null;

    return {
        id: unit.getId(),
        name: unit.getName(),
        lat: latitude,
        lng: longitude,
        speed: speed,
        course: course,
        time: timestamp,
        online: true
    };
}

function applyUnitPosition(unit, source, msg) {

    const data = positionFromUnit(unit, msg || null);
    if (!data) {
        return;
    }

    const now = Math.floor(Date.now() / 1000);
    data.online = data.time > 0 && (now - data.time) <= OFFLINE_THRESHOLD_SECONDS;

    const unitId = data.id;
    const existing = vehicleData[unitId];

    const sameFix = existing &&
        existing.lat === data.lat &&
        existing.lng === data.lng &&
        existing.time === data.time;

    if (sameFix) {
        if (source !== 'poll') {
            console.log('[Fleet] Duplicate fix (' + source + ') ignored for unit ' + unitId + ' (timestamp: ' + data.time + ')');
            debug('Duplicate fix (' + source + '): ' + data.name + ' (' + unitId + ') timestamp ' + data.time + ' unchanged');
        }
        if (existing.course !== data.course || existing.speed !== data.speed || existing.online !== data.online) {
            existing.speed = data.speed;
            existing.course = data.course;
            existing.online = data.online;
            updateMarkerVisual(existing);
            if (selectedVehicleId === unitId) updateInfoPanel(existing);
        }
        return;
    }

    if (window._wialonFirstEventReceived) window._wialonFirstEventReceived();

    logUnit('fix received', unitId);
    console.log('[Fleet] Genuine new position fix (' + source + ') for unit ' + unitId + ' (timestamp: ' + data.time + ')');
    debug('Genuine new fix (' + source + '): ' + data.name + ' (' + unitId + ') → ' +
        data.lat.toFixed(5) + ', ' + data.lng.toFixed(5) + ' @ ' + data.speed + ' km/h, course ' + data.course + '°');

    vehicleData[unitId] = data;

    createOrMoveMarker(data, source !== 'initial');
    addTrailPoint(unitId, data.lat, data.lng, data.speed, data.time);
    if (selectedVehicleId === unitId) {
        updateInfoPanel(data);
        if (followSelectedVehicle && mapReady) {
            map.panTo([data.lat, data.lng], { animate: true, duration: MARKER_ANIM_MS / 1000 });
        }
    }
}

function refreshAllUnitPositions(source) {
    units.forEach(function (unit) { applyUnitPosition(unit, source || 'refresh'); });
}

function handleMessageRegistered(unit, event) {

    let msg = null;
    if (event && typeof event.getData === 'function') msg = event.getData();
    else if (event && event.data !== undefined) msg = event.data;

    applyUnitPosition(unit, 'messageRegistered', msg);
}

function handlePositionChanged(unit, event) {
    applyUnitPosition(unit, 'handlePositionChanged');
}


/* ============================================================
   VEHICLE DATA OBJECT
   ============================================================ */

function createVehicleData(unit, position) {
    const now = Math.floor(Date.now() / 1000);
    const timestamp = Number(position.t || now);
    const age = now - timestamp;

    return {
        id: unit.getId(),
        name: unit.getName(),
        lat: Number(position.y),
        lng: Number(position.x),
        speed: Number(position.s || 0),
        course: Number(position.c || 0),
        time: timestamp,
        online: age <= OFFLINE_THRESHOLD_SECONDS
    };
}


/* ============================================================
   MARKERS
   ============================================================ */

function courseToRotation(course) {
    if (!isFinite(course)) return 0;
    /* Truck emoji faces east at 0° — Wialon course is degrees clockwise from north. */
    return course - 90;
}

function updateMarkerVisual(data) {

    const marker = markers[data.id];
    if (!marker) return;

    const el = marker.getElement();
    const icon = el ? el.querySelector('.truck-icon') : null;
    if (icon) icon.style.transform = 'rotate(' + courseToRotation(data.course) + 'deg)';

    const popup = marker.getPopup();
    if (popup) popup.setContent(getPopupContent(data));
}

function animateMarkerTo(marker, lat, lng) {

    const start = marker.getLatLng();
    const startTime = performance.now();

    function frame(now) {
        const t = Math.min(1, (now - startTime) / MARKER_ANIM_MS);
        const eased = t * (2 - t);
        marker.setLatLng([
            start.lat + (lat - start.lat) * eased,
            start.lng + (lng - start.lng) * eased
        ]);
        if (t < 1) requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
}

function createOrMoveMarker(data, animate) {

    if (!mapReady || !isFinite(data.lat) || !isFinite(data.lng)) return;

    const shouldAnimate = animate !== false;

    if (markers[data.id]) {
        if (shouldAnimate) animateMarkerTo(markers[data.id], data.lat, data.lng);
        else markers[data.id].setLatLng([data.lat, data.lng]);
        updateMarkerVisual(data);
        return;
    }

    const icon = L.divIcon({
        className: 'vehicle-marker-icon',
        html: '<div class="truck-icon" style="transform:rotate(' + courseToRotation(data.course) + 'deg)">🚛</div>',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    const marker = L.marker([data.lat, data.lng], { icon: icon })
        .bindPopup(getPopupContent(data), { offset: [0, -10] })
        .addTo(map);

    markers[data.id] = marker;
}

function createStartMarker(unitId, lat, lng, timestamp) {

    if (!mapReady || startMarkers[unitId]) return;

    const icon = L.divIcon({
        className: 'start-marker-icon',
        html: '<div class="start-icon">🏁</div>',
        iconSize: [20, 20],
        iconAnchor: [10, 20]
    });

    const label = vehicleData[unitId] ? vehicleData[unitId].name : unitId;
    const timeText = timestamp ? new Date(timestamp * 1000).toLocaleString() : 'unknown';

    startMarkers[unitId] = L.marker([lat, lng], { icon: icon })
        .bindPopup('<strong>' + escapeHtml(label) + ' — start</strong><br>' + escapeHtml(timeText), { offset: [0, -14] })
        .addTo(map);
}

function getPopupContent(data) {
    const gpsTime = data.time ? new Date(data.time * 1000).toLocaleString() : 'Unknown';
    const status = data.online ? 'Online' : 'Offline';

    return (
        '<strong>' + escapeHtml(data.name) + '</strong>' +
        '<br>Lat: ' + data.lat.toFixed(6) +
        '<br>Lng: ' + data.lng.toFixed(6) +
        '<br>Speed: ' + data.speed + ' km/h' +
        '<br>Course: ' + data.course + '°' +
        '<br>GPS time: ' + gpsTime +
        '<br>' + status
    );
}


/* ============================================================
   TRAIL
   ============================================================ */

const SPEED_COLOURS = [
    { max: 0,        colour: '#64748b', label: 'Stopped' },
    { max: 20,       colour: '#f59e0b', label: 'Slow'    },
    { max: 50,       colour: '#eab308', label: 'Medium'  },
    { max: 80,       colour: '#22c55e', label: 'Moving'  },
    { max: Infinity, colour: '#2563eb', label: 'Fast'    }
];
const OFFLINE_COLOUR = '#94a3b8';

function speedToColour(kmh) { for (const b of SPEED_COLOURS) if (kmh <= b.max) return b.colour; return '#2563eb'; }
function speedLabel(kmh) { for (const b of SPEED_COLOURS) if (kmh <= b.max) return b.label; return 'Fast'; }

function getVehicleStatus(data) {
    if (!data || data.online === false) {
        return { key: 'offline', label: 'Offline', colour: OFFLINE_COLOUR };
    }
    return { key: 'online', label: speedLabel(data.speed), colour: speedToColour(data.speed) };
}

function formatAgo(unixSeconds) {
    if (!unixSeconds) return '–';
    const diff = Math.max(0, Math.floor(Date.now() / 1000) - unixSeconds);
    if (diff < 60) return diff + 's ago';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

function haversineMetres(a, b) {
    const R = 6371000;
    const φ1 = a.lat * Math.PI / 180, φ2 = b.lat * Math.PI / 180;
    const Δφ = (b.lat - a.lat) * Math.PI / 180, Δλ = (b.lng - a.lng) * Math.PI / 180;
    const s = Math.sin(Δφ / 2) ** 2 + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
}

function formatDistance(m) { return m >= 1000 ? (m / 1000).toFixed(1) + ' km' : Math.round(m) + ' m'; }

function trailLayerGroup(unitId) {
    if (!trailLayers[unitId]) {
        trailLayers[unitId] = L.layerGroup().addTo(map);
    }
    return trailLayers[unitId];
}

function addTrailPoint(unitId, lat, lng, speed, timestamp) {

    speed = Number(speed) || 0;
    const newPt = { lat, lng, speed, t: timestamp };

    if (!trails[unitId]) {
        trails[unitId] = {
            points: [], features: [], totalDistance: 0,
            startPoint: { lat, lng },
            startTime: timestamp || Math.floor(Date.now() / 1000)
        };
    }

    const trail = trails[unitId];
    const prev = trail.points[trail.points.length - 1];

    // Only skip an exact duplicate GPS message — never skip based on speed
    if (prev && prev.t === newPt.t) return;

    trail.points.push(newPt);

    if (!prev) {
        if (mapReady) createStartMarker(unitId, lat, lng, trail.startTime);
        else map.once('load', () => createStartMarker(unitId, lat, lng, trail.startTime));
        updateRoutePanel(unitId);
        return;
    }

    const segmentMetres = haversineMetres(prev, newPt);
    if (segmentMetres >= TRAIL_MIN_METRES) {
        trail.totalDistance += segmentMetres;

        const segment = {
            colour: speedToColour(speed),
            latlngs: [[prev.lat, prev.lng], [lat, lng]]
        };
        trail.features.push(segment);

        if (mapReady) {
            L.polyline(segment.latlngs, {
                color: segment.colour,
                weight: 4,
                opacity: 0.85
            }).addTo(trailLayerGroup(unitId));
        }
    }

    // Always refresh the card/panel, even if the vehicle barely moved
    updateRoutePanel(unitId);
}

function flushAllTrailLayers() {
    Object.keys(trails).forEach(function (unitId) { syncTrailLayer(Number(unitId)); });
}

function syncTrailLayer(unitId) {

    if (!mapReady) return;

    const trail = trails[unitId];
    if (!trail || !trail.features.length) return;

    const group = trailLayerGroup(unitId);
    group.clearLayers();

    trail.features.forEach(function (segment) {
        L.polyline(segment.latlngs, {
            color: segment.colour,
            weight: 4,
            opacity: 0.85
        }).addTo(group);
    });
}


/* ============================================================
   FLEET STATUS FILTERS + PAGINATION

   With a small fleet, none of this matters — every vehicle just
   shows up. Once a fleet grows past a hundred or so vehicles,
   two things start to hurt: (1) scanning a huge unsorted list to
   find one truck, and (2) mounting hundreds of live-updating DOM
   cards at once. The filters solve (1); fleetPageLimit solves (2)
   by capping how many cards are ever built, with "Load more" to
   raise the cap on demand.
   ============================================================ */

function unitPassesFleetFilters(unitId) {

    const data = vehicleData[unitId];
    const unit = units.find(function (u) { return u.getId() === unitId; });
    const name = unit ? unit.getName() : String(unitId);

    if (fleetFilters.name && name.toLowerCase().indexOf(fleetFilters.name.toLowerCase()) === -1) {
        return false;
    }

    const status = getVehicleStatus(data);

    if (fleetFilters.status === 'online' && status.key !== 'online') return false;
    if (fleetFilters.status === 'offline' && status.key !== 'offline') return false;

    if (fleetFilters.speed !== 'all') {
        if (status.key === 'offline') return false; // speed bands only make sense for online vehicles
        if (speedLabel(data.speed).toLowerCase() !== fleetFilters.speed) return false;
    }

    if (fleetFilters.inMapView) {
        if (!data || !mapReady) return false;
        if (!map.getBounds().contains([data.lat, data.lng])) return false;
    }

    return true;
}

function isUnitVisibleInFleetPanel(unitId) {

    if (!unitPassesFleetFilters(unitId)) return false;

    if (document.getElementById('route-item-' + unitId)) return true;

    const mountedCount = document.querySelectorAll('#route-list .route-card').length;
    return mountedCount < fleetPageLimit;
}

function scheduleFleetFooterRefresh() {
    if (fleetFooterRefreshTimer) return;
    fleetFooterRefreshTimer = setTimeout(function () {
        fleetFooterRefreshTimer = null;
        refreshFleetFilterFooter();
    }, 250);
}

function refreshFleetFilterFooter() {

    const totalMatches = units.filter(function (unit) {
        const id = unit.getId();
        return trails[id] && unitPassesFleetFilters(id);
    }).length;

    const shown = document.querySelectorAll('#route-list .route-card').length;

    document.getElementById('fleet-filter-count').textContent =
        shown + ' of ' + totalMatches + ' vehicle' + (totalMatches === 1 ? '' : 's');

    const footer = document.getElementById('route-list-footer');
    const remaining = totalMatches - shown;

    if (remaining > 0) {
        footer.style.display = 'flex';
        document.getElementById('route-list-remaining').textContent = remaining + ' more not shown';
    } else {
        footer.style.display = 'none';
    }
}

// Full rebuild — used whenever a filter changes or the page limit grows.
// resetPage=true also collapses the page size back to FLEET_PAGE_SIZE
// (e.g. after typing a new search term), so filtering never leaves a
// stale, oversized page mounted.
function renderFleetStatusPanel(resetPage) {

    if (resetPage) fleetPageLimit = FLEET_PAGE_SIZE;

    document.getElementById('route-list').innerHTML = '';

    units.forEach(function (unit) {
        updateRoutePanel(unit.getId());
    });

    refreshFleetFilterFooter();
}

function setFleetFilterActiveStates() {
    document.getElementById('fleet-filter-location').classList.toggle('active', fleetFilters.inMapView);
}

(function initFleetFilters() {

    const nameInput = document.getElementById('fleet-filter-name');
    const statusSelect = document.getElementById('fleet-filter-status');
    const speedSelect = document.getElementById('fleet-filter-speed');
    const locationBtn = document.getElementById('fleet-filter-location');
    const resetBtn = document.getElementById('fleet-filter-reset');
    const loadMoreBtn = document.getElementById('route-list-more');

    nameInput.addEventListener('input', function () {
        const value = nameInput.value;
        if (fleetFilterNameTimer) clearTimeout(fleetFilterNameTimer);
        fleetFilterNameTimer = setTimeout(function () {
            fleetFilters.name = value.trim();
            renderFleetStatusPanel(true);
        }, 250);
    });

    statusSelect.addEventListener('change', function () {
        fleetFilters.status = statusSelect.value;
        renderFleetStatusPanel(true);
    });

    speedSelect.addEventListener('change', function () {
        fleetFilters.speed = speedSelect.value;
        renderFleetStatusPanel(true);
    });

    locationBtn.addEventListener('click', function () {
        fleetFilters.inMapView = !fleetFilters.inMapView;
        setFleetFilterActiveStates();
        renderFleetStatusPanel(true);
    });

    resetBtn.addEventListener('click', function () {
        fleetFilters = { name: '', status: 'all', speed: 'all', inMapView: false };
        nameInput.value = '';
        statusSelect.value = 'all';
        speedSelect.value = 'all';
        setFleetFilterActiveStates();
        renderFleetStatusPanel(true);
    });

    loadMoreBtn.addEventListener('click', function () {
        fleetPageLimit += FLEET_PAGE_SIZE;
        renderFleetStatusPanel(false);
    });
})();


/* ============================================================
   LIVE STATUS CARDS
   ============================================================ */

function updateRoutePanel(unitId) {

    const trail = trails[unitId];
    if (!trail) return;

    if (!isUnitVisibleInFleetPanel(unitId)) {
        const existingCard = document.getElementById('route-item-' + unitId);
        if (existingCard) {
            existingCard.remove();
            scheduleFleetFooterRefresh();
        }
        return;
    }

    const data = vehicleData[unitId];
    const name = data ? data.name : String(unitId);
    const dist = trail.totalDistance;
    const status = getVehicleStatus(data);      // one function, one truth — feeds both the dot and the badge
    const isOffline = status.key === 'offline';

    const cardId = 'route-item-' + unitId;
    let card = document.getElementById(cardId);
    let isNewCard = false;

    if (!card) {
        isNewCard = true;
        card = document.createElement('div');
        card.id = cardId;
        card.className = 'route-card';
        card.title = 'Click to locate this vehicle on the map';
        card.addEventListener('click', function () {
            selectVehicle(unitId);
            focusVehicle(unitId);
        });
        document.getElementById('route-list').appendChild(card);
    }

    const startTimeText = trail.startTime ? new Date(trail.startTime * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '–';

    const speedLine = isOffline
        ? 'Last speed <b>' + (data ? data.speed : 0) + ' km/h</b>'
        : 'Speed <b>' + data.speed + ' km/h</b>';

    const freshnessLine = isOffline
        ? 'Silent <b>' + formatAgo(data ? data.time : null) + '</b>'
        : 'Updated <b>' + formatAgo(data ? data.time : null) + '</b>';

    card.innerHTML =
        '<div class="route-card-top">' +
            '<span class="route-name">' +
                '<span class="status-dot ' + (isOffline ? 'offline' : 'online') + '"></span>' +
                '🚛 ' + escapeHtml(name) +
            '</span>' +
            '<span class="status-badge" style="background:' + status.colour + '22;color:' + status.colour + '">' + status.label + '</span>' +
        '</div>' +
        '<div class="route-meta-grid">' +
            '<div class="route-meta-item">' + speedLine + '</div>' +
            '<div class="route-meta-item">' + freshnessLine + '</div>' +
            '<div class="route-meta-item">Distance <b>' + formatDistance(dist) + '</b></div>' +
            '<div class="route-meta-item">First seen <b>' + startTimeText + '</b></div>' +
        '</div>';

    if (isNewCard) scheduleFleetFooterRefresh();

    if (selectedVehicleId === unitId && data) {
        document.getElementById('selected-distance').textContent = formatDistance(dist);
        document.getElementById('selected-progress').textContent = status.label + (isOffline ? '' : ' · ' + data.speed + ' km/h');
    }
}


/* ============================================================
   DROPDOWN / INFO PANEL
   ============================================================ */

function populateVehicleDropdown() {
    // Hidden <select> is kept in sync (value + options) so any external
    // code that reads/sets #vehicle-select keeps working unmodified.
    const select = document.getElementById('vehicle-select');
    select.innerHTML = '<option value="">Select vehicle…</option>';
    units.forEach(function (unit) {
        const option = document.createElement('option');
        option.value = unit.getId();
        option.textContent = unit.getName();
        select.appendChild(option);
    });

    const input = document.getElementById('vehicle-search');
    input.disabled = false;
    input.placeholder = 'Search vehicle…';
}

/* ---- Single source of truth for "a vehicle got picked" ---- */
function selectVehicle(id) {
    id = Number(id);
    const unit = units.find(function (u) { return u.getId() === id; });
    if (!unit) return;

    selectedVehicleId = id;

    const select = document.getElementById('vehicle-select');
    select.value = id;

    const input = document.getElementById('vehicle-search');
    input.value = unit.getName();
    toggleClearButton();

    closeVehicleDropdown();
}

/* ---- Case-insensitive filtering + rendering of the dropdown list ---- */
function highlightMatch(name, query) {
    if (!query) return escapeHtml(name);
    const idx = name.toLowerCase().indexOf(query.toLowerCase());
    if (idx === -1) return escapeHtml(name);
    return escapeHtml(name.slice(0, idx)) +
        '<mark>' + escapeHtml(name.slice(idx, idx + query.length)) + '</mark>' +
        escapeHtml(name.slice(idx + query.length));
}

function renderVehicleDropdown(query) {
    const dropdown = document.getElementById('vehicle-search-dropdown');
    const input = document.getElementById('vehicle-search');
    const q = (query || '').trim().toLowerCase();

    // Helper function to get vehicle status text
    function getVehicleStatus(data) {
        if (!data || !data.online) return 'Offline';
        const speed = data.speed || 0;
        if (speed === 0) return 'Stopped';
        if (speed <= 20) return 'Slow';
        if (speed <= 50) return 'Medium';
        if (speed <= 80) return 'Moving';
        return 'Fast';
    }

    // Helper function to get status CSS class
    function getStatusClass(data) {
        if (!data || !data.online) return 'offline';
        const speed = data.speed || 0;
        if (speed === 0) return 'stopped';
        if (speed <= 20) return 'slow';
        if (speed <= 50) return 'medium';
        if (speed <= 80) return 'moving';
        return 'fast';
    }

    // .toLowerCase() on both sides of the comparison = case-insensitive,
    // matches anywhere in the name (not just the start).
    const matches = units.filter(function (unit) {
        return unit.getName().toLowerCase().indexOf(q) !== -1;
    });

    dropdown.innerHTML = '';

    if (!matches.length) {
        dropdown.innerHTML = '<div class="vs-empty">No vehicles match "' + escapeHtml(query || '') + '"</div>';
        dropdown.classList.add('open');
        input.setAttribute('aria-expanded', 'true');
        return;
    }

    matches.forEach(function (unit) {
        const id = unit.getId();
        const data = vehicleData[id];
        const status = getVehicleStatus(data);
        const statusClass = getStatusClass(data);

        const item = document.createElement('div');
        item.className = 'vs-item';
        item.setAttribute('role', 'option');
        item.innerHTML =
            '<span class="vs-dot ' + statusClass + '"></span>' +
            '<span class="vs-name">' + highlightMatch(unit.getName(), query) + '</span>' +
            '<span class="vs-status ' + statusClass + '">' + escapeHtml(status) + '</span>';

        // mousedown (not click) fires before the input's blur hides the list
        item.addEventListener('mousedown', function (e) {
            e.preventDefault();
            selectVehicle(id);
        });

        dropdown.appendChild(item);
    });

    dropdown.classList.add('open');
    input.setAttribute('aria-expanded', 'true');
}

function closeVehicleDropdown() {
    document.getElementById('vehicle-search-dropdown').classList.remove('open');
    document.getElementById('vehicle-search').setAttribute('aria-expanded', 'false');
}

function toggleClearButton() {
    const input = document.getElementById('vehicle-search');
    const clearBtn = document.getElementById('vehicle-search-clear');
    clearBtn.classList.toggle('show', input.value.length > 0);
}

(function initVehicleSearch() {
    const input = document.getElementById('vehicle-search');
    const clearBtn = document.getElementById('vehicle-search-clear');
    const combobox = document.getElementById('vehicle-combobox');

    input.addEventListener('focus', function () {
        renderVehicleDropdown(input.value);
    });

    input.addEventListener('input', function () {
        // Typing invalidates the previously selected vehicle until a new
        // one is picked from the list.
        selectedVehicleId = null;
        document.getElementById('vehicle-select').value = '';
        toggleClearButton();
        renderVehicleDropdown(input.value);
    });

    input.addEventListener('blur', function () {
        // Delay so a mousedown on a dropdown item / clear button registers first.
        setTimeout(closeVehicleDropdown, 150);
    });

    input.addEventListener('keydown', function (e) {
        const dropdown = document.getElementById('vehicle-search-dropdown');

        if (e.key === 'Escape') {
            closeVehicleDropdown();
            input.blur();
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            const active = dropdown.querySelector('.vs-item.active') || dropdown.querySelector('.vs-item');
            if (active) active.dispatchEvent(new Event('mousedown'));
            return;
        }

        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const items = Array.from(dropdown.querySelectorAll('.vs-item'));
            if (!items.length) return;

            let idx = items.findIndex(function (el) { return el.classList.contains('active'); });
            items.forEach(function (el) { el.classList.remove('active'); });

            if (e.key === 'ArrowDown') idx = (idx + 1) % items.length;
            else idx = idx <= 0 ? items.length - 1 : idx - 1;

            items[idx].classList.add('active');
            items[idx].scrollIntoView({ block: 'nearest' });
        }
    });

    clearBtn.addEventListener('mousedown', function (e) {
        e.preventDefault();
        input.value = '';
        selectedVehicleId = null;
        document.getElementById('vehicle-select').value = '';
        toggleClearButton();
        input.focus();
        renderVehicleDropdown('');
    });

    document.addEventListener('click', function (e) {
        if (!combobox.contains(e.target)) closeVehicleDropdown();
    });
})();

function focusVehicle(id) {
    const data = vehicleData[id];
    if (!data) { debug('No position data for unit ' + id); return; }
    followSelectedVehicle = true;
    updateInfoPanel(data);

    // Fly to vehicle location without scrolling the page
    map.flyTo([data.lat, data.lng], 15, { duration: 0.8 });

    // Open popup and prevent page scroll
    if (markers[id]) {
        // Ensure map container stays in view without scrolling page
        const mapContainer = document.getElementById('map');
        if (mapContainer) {
            // Use scrollIntoView with block: 'nearest' to avoid scrolling if already visible
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        }

        // Small delay to let map animation start before opening popup
        setTimeout(function() {
            markers[id].openPopup();
        }, 300);
    }

    clearAllPlannedRoutes();
    renderPlannedRoute(id);
}

// Go button - Show selected vehicle on map
document.getElementById('go-btn').addEventListener('click', function () {
    if (selectedVehicleId) {
        focusVehicle(selectedVehicleId);
    } else {
        // No vehicle selected, focus search input
        const input = document.getElementById('vehicle-search');
        input.focus();
        renderVehicleDropdown(input.value);
    }
});

document.getElementById('show-all-btn').addEventListener('click', function () {
    // Clear selection
    selectedVehicleId = null;
    followSelectedVehicle = false;

    // Clear search input
    const input = document.getElementById('vehicle-search');
    input.value = '';

    // Clear hidden select
    const select = document.getElementById('vehicle-select');
    select.value = '';

    // Update clear button visibility
    toggleClearButton();

    // Close dropdown if open
    closeVehicleDropdown();

    // Close all open popups
    map.closePopup();

    // Close all vehicle marker popups
    Object.keys(markers).forEach(function(markerId) {
        if (markers[markerId] && markers[markerId].isPopupOpen()) {
            markers[markerId].closePopup();
        }
    });

    // Reset info panel to defaults and hide it
    document.getElementById('info-panel').style.display = 'none';
    document.getElementById('selected-name').textContent = '–';
    document.getElementById('selected-lat').textContent = '–';
    document.getElementById('selected-lng').textContent = '–';
    document.getElementById('selected-speed').textContent = '–';
    document.getElementById('selected-time').textContent = '–';
    document.getElementById('selected-distance').textContent = '–';
    document.getElementById('selected-progress').textContent = '–';

    // Clear all planned routes
    clearAllPlannedRoutes();

    // Show all vehicles on map
    fitAllVehicles();

    debug('Showing all vehicles - popups closed, view reset');
});

function updateInfoPanel(data) {
    if (!data) return;

    // Show the info panel when a vehicle is selected
    document.getElementById('info-panel').style.display = 'grid';

    document.getElementById('selected-name').textContent = data.name;
    document.getElementById('selected-lat').textContent = data.lat.toFixed(6);
    document.getElementById('selected-lng').textContent = data.lng.toFixed(6);
    document.getElementById('selected-speed').textContent = data.speed + ' km/h';
    document.getElementById('selected-time').textContent = data.time ? new Date(data.time * 1000).toLocaleString() : '–';

    const trail = trails[data.id];
    if (trail) {
        document.getElementById('selected-distance').textContent = formatDistance(trail.totalDistance);
        document.getElementById('selected-progress').textContent =
            (data.online === false ? 'Offline' : speedLabel(data.speed)) + ' · ' + data.speed + ' km/h';
    } else {
        document.getElementById('selected-distance').textContent = '–';
        document.getElementById('selected-progress').textContent = '–';
    }
}


/* ============================================================
   PERIODIC ONLINE/OFFLINE CHECK
   ============================================================ */

setInterval(function () {
    const now = Math.floor(Date.now() / 1000);
    Object.keys(vehicleData).forEach(function (id) {
        const data = vehicleData[id];
        if (!data) return;
        data.online = data.time > 0 && (now - data.time) <= OFFLINE_THRESHOLD_SECONDS;
        updateRoutePanel(Number(id));
    });
}, 30000);


/* ============================================================
   WIALON ERROR TEXT
   ============================================================ */

function getWialonError(code) {
    try {
        if (wialon && wialon.core && wialon.core.Errors) {
            return wialon.core.Errors.getErrorText(code) + ' (code ' + code + ')';
        }
    } catch (error) { /* no-op */ }
    return 'Wialon error code ' + code;
}


// Map Tracking Traffic & Weather Overlays
let trackingTrafficLayer = null;
let trackingWeatherLayer = null;

document.getElementById('toggle-traffic-btn')?.addEventListener('click', function () {
    const btn = this;
    if (trackingTrafficLayer) {
        map.removeLayer(trackingTrafficLayer);
        trackingTrafficLayer = null;
        btn.style.background = '#ffffff';
        btn.style.color = '#0f172a';
        btn.style.borderColor = '#cbd5e1';
    } else {
        if (!map.getPane('trafficPane')) {
            map.createPane('trafficPane');
            map.getPane('trafficPane').style.zIndex = 250;
        }
        trackingTrafficLayer = L.tileLayer('{{ url('my-account/journey/tile/traffic') }}/{z}/{x}/{y}.png', {
            pane: 'trafficPane',
            maxZoom: 19,
            opacity: 0.85,
            attribution: '&copy; TomTom Traffic'
        }).addTo(map);
        btn.style.background = '#ef4444';
        btn.style.color = '#ffffff';
        btn.style.borderColor = '#dc2626';
    }
});

document.getElementById('toggle-weather-btn')?.addEventListener('click', function () {
    const btn = this;
    if (trackingWeatherLayer) {
        map.removeLayer(trackingWeatherLayer);
        trackingWeatherLayer = null;
        btn.style.background = '#ffffff';
        btn.style.color = '#0f172a';
        btn.style.borderColor = '#cbd5e1';
    } else {
        if (!map.getPane('weatherPane')) {
            map.createPane('weatherPane');
            map.getPane('weatherPane').style.zIndex = 260;
        }
        trackingWeatherLayer = L.tileLayer('{{ url('my-account/journey/tile/weather/precipitation_new') }}/{z}/{x}/{y}.png', {
            pane: 'weatherPane',
            maxZoom: 19,
            opacity: 0.65,
            attribution: '&copy; OpenWeather'
        }).addTo(map);
        btn.style.background = '#0284c7';
        btn.style.color = '#ffffff';
        btn.style.borderColor = '#0369a1';
    }
});

/* ============================================================
   PAGE LOAD
   ============================================================ */

window.addEventListener('load', function () {

    debug('Page loaded. Env: ' + APP_ENV);

    if (typeof wialon === 'undefined') {
        debug('Wialon SDK not available.');
        setStatus('offline', 'SDK failed to load');
        return;
    }

    initMap();
    initWialonSession();
    loginToWialon();
});

</script>
</div>
@if(!($embedded ?? false))
    </x-layouts.auth>
@endif
