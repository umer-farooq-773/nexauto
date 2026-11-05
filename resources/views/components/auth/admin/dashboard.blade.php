<div class="row g-3 g-xl-4 mb-4">
    <x-auth.quick-link-card icon="fa-truck-fast" :title="__('Total Assets')"
        description="{{$result['total_vehicle']}}" route="vehicle.index" iconbg="bg-primary-subtle"
        icontext="text-primary" />

    <x-auth.quick-link-card icon="fa-users" :title="__('Total Drivers')"
        description="{{$result['total_driver_mate']}}" route="driver_mate.index" iconbg="bg-secondary-subtle"
        icontext="text-secondary" />

    <x-auth.quick-link-card icon="fa-shield-heart" :title="__('Total Incidents')"
        description="{{$result['total_incident']}}" route="incident_home" iconbg="bg-success-subtle" icontext="text-success" />

    <x-auth.quick-link-card icon="fa-map-location-dot" :title="__('Ongoing Trips')"
        description="{{$result['total_trips']}}" route="" iconbg="bg-warning-subtle" icontext="text-warning" />

    {{-- <x-auth.quick-link-card icon="fa-clipboard-list" :title="__('Inspections')"
        description="{{$result['total_inspection']}}" route="" iconbg="bg-info-subtle" icontext="text-info" />

    <x-auth.quick-link-card icon="fa-circle-check" :title="__('Completed Trips')"
        description="{{$result['total_gps']}}" route="" iconbg="bg-success-subtle" icontext="text-success" /> --}}
</div>

<!-- Map Card - Optimized Height -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                <span class="badge-icon bg-primary">
                    <i class="fas fa-map-location-dot text-white"></i>
                </span>
                Live Vehicle Map
            </h5>
            <p class="text-muted small mb-0">
                <span id="map-status-text">Connecting to Wialon...</span>
                <span class="badge bg-primary-subtle text-primary ms-2 px-2 py-1" id="vehicle-count-badge" style="font-size: 0.65rem;">
                    <i class="fas fa-truck me-1"></i>
                    <span id="dashboard-vehicle-count">0</span> vehicles
                </span>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-success bg-opacity-10 rounded-pill px-3 py-1">
                <span class="pulse-dot me-1"></span>
                <span class="fw-semibold" style="font-size: 0.7rem;">Live</span>
            </span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openFullscreenMap()" style="font-size: 0.75rem; padding: 0.25rem 0.75rem;">
                <i class="fas fa-expand me-1"></i> Full Screen
            </button>
        </div>
    </div>
    <div class="card-body p-0" style="position: relative;">
        <div id="dashboard-map-container" style="width: 100%; height: 380px; background: #e8edf3;">
            <div id="dashboard-map" style="width: 100%; height: 100%;"></div>
            <div id="dashboard-map-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(232, 237, 243, 0.95); display: flex; align-items: center; justify-content: center; z-index: 1000;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Loading map...</span>
                    </div>
                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.9rem;">Initializing live map...</p>
                    <p class="text-muted small mt-1" style="font-size: 0.75rem;">Connecting to Wialon tracking system</p>
                </div>
            </div>
        </div>
        <!-- Mini Stats Bar -->
        <div class="d-flex justify-content-around align-items-center bg-light bg-opacity-90 px-3 py-2 border-top" style="font-size: 0.7rem;">
            <div class="text-center">
                <span class="text-muted d-block" style="font-size: 0.6rem;">Online</span>
                <span class="fw-bold text-success" id="online-count">0</span>
            </div>
            <div class="text-center">
                <span class="text-muted d-block" style="font-size: 0.6rem;">Idle</span>
                <span class="fw-bold text-warning" id="idle-count">0</span>
            </div>
            <div class="text-center">
                <span class="text-muted d-block" style="font-size: 0.6rem;">Moving</span>
                <span class="fw-bold text-primary" id="moving-count">0</span>
            </div>
            <div class="text-center">
                <span class="text-muted d-block" style="font-size: 0.6rem;">Offline</span>
                <span class="fw-bold text-secondary" id="offline-count">0</span>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Map Modal -->
<div class="modal fade" id="fullscreenMapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="background: #e8edf3; border: none; border-radius: 0;">
            <div class="modal-header" style="background: #fff; border-bottom: 1px solid #e9ecef; padding: 0.75rem 1.5rem;">
                <div>
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <i class="fas fa-map-location-dot text-primary"></i>
                        Live Vehicle Map
                        <span class="badge bg-primary-subtle text-primary ms-2" id="fullscreen-vehicle-count">0 vehicles</span>
                    </h5>
                    <span class="text-muted small" id="fullscreen-map-status">Live tracking active</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="position: relative;">
                <div id="fullscreen-map-container" style="width: 100%; height: calc(100vh - 70px); background: #e8edf3;">
                    <div id="fullscreen-map" style="width: 100%; height: 100%;"></div>
                    <div id="fullscreen-map-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(232, 237, 243, 0.95); display: flex; align-items: center; justify-content: center; z-index: 1000;">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                                <span class="visually-hidden">Loading map...</span>
                            </div>
                            <p class="text-muted mb-0 fw-semibold">Loading fullscreen map...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $wialonHost = rtrim(config('services.wialon.host'), '/');
    $wialonToken = config('services.wialon.token');
@endphp

<!-- Wialon Web SDK -->
<script src="{{ $wialonHost }}/wsdk/script/wialon.js"></script>

<!-- MapLibre GL JS with Wialon-like style -->
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.0/dist/maplibre-gl.css" />
<script src="https://unpkg.com/maplibre-gl@4.7.0/dist/maplibre-gl.js"></script>

<div class="row g-3 g-xl-4">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm hover-shadow transition-all rounded-3 overflow-hidden h-100">
            <div class="card-body p-3 p-xl-4 d-flex flex-column">
                <div class="d-flex flex-wrap gap-2 gap-md-3 justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <span class="badge-icon bg-primary">
                                <i class="fas fa-chart-line text-white"></i>
                            </span>
                            Completed Trips & Incidents
                        </h5>
                        <p class="text-muted small mb-0" style="font-size: 0.7rem;">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Completed Trips vs incident records
                        </p>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" style="flex-wrap: wrap; gap: 0.25rem;">
                        <button class="btn btn-primary rounded-pill px-3 chart-period-btn active" data-period="weekly" style="font-size: 0.7rem; padding: 0.25rem 0.75rem;">
                            <i class="fas fa-calendar-week me-1"></i> Weekly
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-3 chart-period-btn" data-period="monthly" style="font-size: 0.7rem; padding: 0.25rem 0.75rem;">
                            <i class="fas fa-calendar-alt me-1"></i> Monthly
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-3 chart-period-btn" data-period="yearly" style="font-size: 0.7rem; padding: 0.25rem 0.75rem;">
                            <i class="fas fa-calendar-year me-1"></i> Yearly
                        </button>
                    </div>
                </div>
                <div style="height: 380px; position: relative; width: 100%; flex-shrink: 0;">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm hover-shadow transition-all rounded-3 overflow-hidden h-100">
            <div class="card-body p-3 p-xl-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <span class="badge-icon bg-success">
                                <i class="fas fa-receipt text-white"></i>
                            </span>
                            Way Bill
                        </h5>
                        <p class="text-muted small mb-0" style="font-size: 0.7rem;">Status distribution overview</p>
                    </div>
                    <span class="badge bg-success bg-opacity-10 rounded-pill px-3 py-1">
                        <span class="pulse-dot me-1"></span>
                        <span class="fw-semibold" style="font-size: 0.65rem;">Live</span>
                    </span>
                </div>
                <div style="height: 260px; position: relative; width: 100%; flex-shrink: 0;">
                    <div style="width: 100%; max-width: 250px; margin: 0 auto; height: 100%; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-center flex-wrap gap-1" id="statusLegend"></div>
            </div>
        </div>
    </div>
</div>

@push('auth_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

@php
    $waybillStatusDataPayload = $statusCounts ?? [
        'labels' => [],
        'data'   => [],
        'colors' => [],
        'total'  => 0,
    ];

    $chartDataPayload = $chartData ?? [
        'labels'    => [],
        'assets'    => [],
        'incidents' => [],
    ];
@endphp

<script>
// ── Dashboard Map with MapLibre & Wialon Integration ──────────────────────────
const WIALON_HOST = @json($wialonHost);
const WIALON_TOKEN = @json($wialonToken);

let dashboardMap = null;
let fullscreenMap = null;
let wialonSession = null;
let markers = {};
let fullscreenMarkers = {};
let updateInterval = null;
let isFullscreenOpen = false;

// ── Map Style (Wialon-like) ──────────────────────────────────────────────────
const MAP_STYLE = {
    version: 8,
    sources: {
        'osm': {
            type: 'raster',
            tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
            tileSize: 256,
            attribution: '© OpenStreetMap contributors'
        }
    },
    layers: [{
        id: 'osm',
        type: 'raster',
        source: 'osm',
        minzoom: 0,
        maxzoom: 19
    }]
};

// ── Create Map Instance ──────────────────────────────────────────────────────
function createMapInstance(container, center, zoom) {
    const map = new maplibregl.Map({
        container: container,
        style: MAP_STYLE,
        center: center || [69.3451, 30.3753],
        zoom: zoom || 5,
        attributionControl: false,
        logoPosition: 'bottom-right',
    });

    // Add attribution control
    map.addControl(new maplibregl.AttributionControl({
        compact: true
    }), 'bottom-right');

    // Navigation controls with Wialon-like style
    map.addControl(new maplibregl.NavigationControl({
        visualizePitch: true,
        showZoom: true,
        showCompass: false
    }), 'top-right');

    // Scale control
    map.addControl(new maplibregl.ScaleControl({
        maxWidth: 120,
        unit: 'metric'
    }), 'bottom-left');

    // Add a subtle grid for Wialon-like feel
    map.on('load', function() {
        // Hide loading overlay
        const loadingOverlay = document.getElementById(container + '-loading');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }

        // Add a subtle background grid
        map.addSource('grid', {
            type: 'geojson',
            data: {
                type: 'FeatureCollection',
                features: []
            }
        });
    });

    return map;
}

// ── Initialize Dashboard Map ──────────────────────────────────────────────────
function initDashboardMap() {
    console.log('Initializing dashboard map...');
    dashboardMap = createMapInstance('dashboard-map');
    setTimeout(() => dashboardMap.resize(), 500);
}

// ── Initialize Fullscreen Map ──────────────────────────────────────────────────
function initFullscreenMap() {
    if (fullscreenMap) {
        fullscreenMap.remove();
        fullscreenMap = null;
    }
    fullscreenMap = createMapInstance('fullscreen-map');
    setTimeout(() => fullscreenMap.resize(), 500);
    return fullscreenMap;
}

// ── Open Fullscreen Modal ──────────────────────────────────────────────────────
function openFullscreenMap() {
    const modal = new bootstrap.Modal(document.getElementById('fullscreenMapModal'));
    modal.show();

    // Initialize fullscreen map when modal is shown
    document.getElementById('fullscreenMapModal').addEventListener('shown.bs.modal', function() {
        isFullscreenOpen = true;
        const map = initFullscreenMap();

        // Copy markers to fullscreen
        setTimeout(() => {
            copyMarkersToFullscreen(map);
            // Update vehicle count
            const count = Object.keys(markers).length;
            document.getElementById('fullscreen-vehicle-count').textContent = count + ' vehicles';
            document.getElementById('fullscreen-map-status').textContent = 'Live tracking active';

            // Fit bounds
            const positions = Object.values(markers).map(m => m.pos);
            if (positions.length > 0) {
                const bounds = positions.reduce((b, pos) => {
                    return b.extend([pos.x, pos.y]);
                }, new maplibregl.LngLatBounds([positions[0].x, positions[0].y], [positions[0].x, positions[0].y]));
                map.fitBounds(bounds, { padding: 50, maxZoom: 14, duration: 1000 });
            }
        }, 300);
    });

    document.getElementById('fullscreenMapModal').addEventListener('hidden.bs.modal', function() {
        isFullscreenOpen = false;
        if (fullscreenMap) {
            fullscreenMap.remove();
            fullscreenMap = null;
        }
        fullscreenMarkers = {};
    });
}

// ── Copy Markers to Fullscreen ────────────────────────────────────────────────
function copyMarkersToFullscreen(map) {
    // Clear existing fullscreen markers
    Object.values(fullscreenMarkers).forEach(m => m.marker.remove());
    fullscreenMarkers = {};

    Object.entries(markers).forEach(([id, data]) => {
        const { pos, unit, color, status, speed } = data;
        const el = createMarkerElement(color, status);
        const popup = createPopupContent(unit, pos, speed, status);

        const marker = new maplibregl.Marker({
            element: el,
            anchor: 'center',
        })
        .setLngLat([pos.x, pos.y])
        .setPopup(popup)
        .addTo(map);

        fullscreenMarkers[id] = { marker, element: el, pos, unit, status };
    });
}

// ── Create Marker Element ──────────────────────────────────────────────────────
function createMarkerElement(color, status) {
    const el = document.createElement('div');
    el.className = 'vehicle-marker';
    el.innerHTML = `
        <div style="
            background: ${color};
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            position: relative;
        ">
            <i class="fas fa-truck"></i>
            ${status === 'moving' ? '<div style="position:absolute;width:12px;height:12px;background:rgba(34,197,94,0.4);border-radius:50%;animation:ping 1.5s ease-in-out infinite;top:-2px;right:-2px;"></div>' : ''}
        </div>
    `;
    el.style.cursor = 'pointer';
    return el;
}

// ── Create Popup Content ──────────────────────────────────────────────────────
function createPopupContent(unit, pos, speed, status) {
    const statusBadge = {
        'online': '<span class="badge bg-success" style="font-size:0.6rem;">Online</span>',
        'idle': '<span class="badge bg-warning" style="font-size:0.6rem;">Idle</span>',
        'moving': '<span class="badge bg-primary" style="font-size:0.6rem;">Moving</span>',
        'offline': '<span class="badge bg-secondary" style="font-size:0.6rem;">Offline</span>'
    };

    return new maplibregl.Popup({ offset: 28, className: 'wialon-popup' }).setHTML(`
        <div style="min-width: 190px; font-family: 'Inter', 'Segoe UI', sans-serif; padding: 2px 0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <h6 style="margin:0;font-weight:700;font-size:0.85rem;color:#1a1a2e;">${unit.getName()}</h6>
                ${statusBadge[status] || ''}
            </div>
            <div style="font-size:0.72rem;color:#4a4a6a;display:grid;grid-template-columns:auto 1fr;gap:2px 10px;">
                <span style="color:#6c757d;"><i class="fas fa-tachometer-alt" style="width:14px;"></i> Speed</span>
                <span><strong>${Math.round(speed)} km/h</strong></span>
                <span style="color:#6c757d;"><i class="fas fa-clock" style="width:14px;"></i> Last</span>
                <span>${formatTime(pos.t)}</span>
                <span style="color:#6c757d;"><i class="fas fa-map-marker-alt" style="width:14px;"></i> Location</span>
                <span style="font-size:0.65rem;">${pos.y.toFixed(5)}, ${pos.x.toFixed(5)}</span>
            </div>
        </div>
    `);
}

// ── Initialize Wialon Session ──────────────────────────────────────────────────
function initWialonSession() {
    console.log('Connecting to Wialon...');
    updateMapStatus('Connecting to Wialon...', 'warning');

    wialonSession = wialon.core.Session.getInstance();
    wialonSession.initSession(WIALON_HOST);

    wialonSession.loginToken(WIALON_TOKEN, '', function(code) {
        if (code) {
            console.error('✗ Wialon login failed, code:', code);
            updateMapStatus('Connection failed', 'danger');
            showMapError('Failed to connect to Wialon. Error code: ' + code);
            return;
        }

        console.log('✓ Wialon login successful');
        updateMapStatus('Connected - Loading vehicles...', 'success');
        loadVehicles();
    });
}

// ── Load Vehicles ──────────────────────────────────────────────────────────────
function loadVehicles() {
    const flags = wialon.item.Item.dataFlag.base | wialon.item.Unit.dataFlag.lastMessage;

    wialonSession.loadLibrary('unitLocation');
    wialonSession.updateDataFlags(
        [{ type: 'type', data: 'avl_unit', flags: flags, mode: 0 }],
        function(code) {
            if (code) {
                console.error('✗ Failed to load units, code:', code);
                updateMapStatus('Failed to load vehicles', 'danger');
                return;
            }

            const units = wialonSession.getItems('avl_unit');
            console.log('✓ Loaded', units.length, 'units');

            updateVehicleCount(units.length);
            displayVehiclesOnMap(units);
            updateMapStatus('Live tracking active', 'success');

            // Hide loading overlay
            const loadingOverlay = document.getElementById('dashboard-map-loading');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }

            startRealtimeUpdates();
        }
    );
}

// ── Display Vehicles on Map ──────────────────────────────────────────────────
function displayVehiclesOnMap(units) {
    let online = 0, idle = 0, moving = 0, offline = 0;

    units.forEach(function(unit) {
        const pos = unit.getPosition();
        if (!pos || !pos.y || !pos.x) return;

        const speed = pos.s || 0;
        const now = Math.floor(Date.now() / 1000);
        const age = now - (pos.t || 0);
        const isOffline = age > 1800;

        let color, status;
        if (isOffline) {
            color = '#94a3b8';
            status = 'offline';
            offline++;
        } else if (speed < 1) {
            color = '#f59e0b';
            status = 'idle';
            idle++;
        } else if (speed < 5) {
            color = '#f59e0b';
            status = 'idle';
            idle++;
        } else {
            color = '#22c55e';
            status = 'moving';
            moving++;
        }
        online++;

        const el = createMarkerElement(color, status);
        const popup = createPopupContent(unit, pos, speed, status);

        const marker = new maplibregl.Marker({
            element: el,
            anchor: 'center',
        })
        .setLngLat([pos.x, pos.y])
        .setPopup(popup)
        .addTo(dashboardMap);

        markers[unit.getId()] = {
            marker: marker,
            element: el,
            pos: pos,
            unit: unit,
            color: color,
            status: status,
            speed: speed
        };
    });

    // Update stats
    document.getElementById('online-count').textContent = online;
    document.getElementById('idle-count').textContent = idle;
    document.getElementById('moving-count').textContent = moving;
    document.getElementById('offline-count').textContent = offline;

    // Fit bounds
    const markerPositions = units
        .map(u => u.getPosition())
        .filter(p => p && p.y && p.x)
        .map(p => [p.x, p.y]);

    if (markerPositions.length > 0) {
        const bounds = markerPositions.reduce((b, pos) => {
            return b.extend(pos);
        }, new maplibregl.LngLatBounds(markerPositions[0], markerPositions[0]));

        dashboardMap.fitBounds(bounds, {
            padding: 50,
            maxZoom: 14,
            duration: 1000
        });
    }
}

// ── Format Time ──────────────────────────────────────────────────────────────
function formatTime(timestamp) {
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return date.toLocaleString();
}

// ── Update Map Status ──────────────────────────────────────────────────────────
function updateMapStatus(text, type) {
    const statusEl = document.getElementById('map-status-text');
    if (statusEl) {
        statusEl.textContent = text;
        statusEl.className = '';
        if (type === 'success') statusEl.classList.add('text-success');
        else if (type === 'warning') statusEl.classList.add('text-warning');
        else if (type === 'danger') statusEl.classList.add('text-danger');
        else statusEl.classList.add('text-muted');
    }
}

// ── Update Vehicle Count ──────────────────────────────────────────────────────
function updateVehicleCount(count) {
    const countEl = document.getElementById('dashboard-vehicle-count');
    if (countEl) {
        countEl.textContent = count;
    }
}

// ── Show Map Error ────────────────────────────────────────────────────────────
function showMapError(message) {
    const loadingOverlay = document.getElementById('dashboard-map-loading');
    if (loadingOverlay) {
        loadingOverlay.innerHTML = `
            <div class="text-center text-danger">
                <i class="fas fa-exclamation-triangle fs-1 mb-2"></i>
                <h6 class="mb-1">Map Connection Error</h6>
                <p class="text-muted small mb-2">${message}</p>
                <button class="btn btn-sm btn-primary" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i> Retry
                </button>
            </div>
        `;
    }
}

// ── Start Real-time Updates ──────────────────────────────────────────────────
function startRealtimeUpdates() {
    updateInterval = setInterval(function() {
        const units = wialonSession.getItems('avl_unit');
        let online = 0, idle = 0, moving = 0, offline = 0;

        units.forEach(function(unit) {
            const pos = unit.getPosition();
            const markerData = markers[unit.getId()];

            if (markerData && pos && pos.y && pos.x) {
                const speed = pos.s || 0;
                const now = Math.floor(Date.now() / 1000);
                const age = now - (pos.t || 0);
                const isOffline = age > 1800;

                let color, status;
                if (isOffline) {
                    color = '#94a3b8';
                    status = 'offline';
                    offline++;
                } else if (speed < 1) {
                    color = '#f59e0b';
                    status = 'idle';
                    idle++;
                } else if (speed < 5) {
                    color = '#f59e0b';
                    status = 'idle';
                    idle++;
                } else {
                    color = '#22c55e';
                    status = 'moving';
                    moving++;
                }
                online++;

                // Update marker position
                markerData.marker.setLngLat([pos.x, pos.y]);
                markerData.pos = pos;
                markerData.status = status;
                markerData.speed = speed;

                // Update marker color
                const el = markerData.element;
                el.innerHTML = `
                    <div style="
                        background: ${color};
                        width: 32px;
                        height: 32px;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 12px rgba(0,0,0,0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 13px;
                        cursor: pointer;
                        transition: transform 0.2s;
                        position: relative;
                    ">
                        <i class="fas fa-truck"></i>
                        ${status === 'moving' ? '<div style="position:absolute;width:12px;height:12px;background:rgba(34,197,94,0.4);border-radius:50%;animation:ping 1.5s ease-in-out infinite;top:-2px;right:-2px;"></div>' : ''}
                    </div>
                `;

                // Update fullscreen marker if open
                if (isFullscreenOpen && fullscreenMarkers[unit.getId()]) {
                    const fsMarker = fullscreenMarkers[unit.getId()];
                    fsMarker.marker.setLngLat([pos.x, pos.y]);
                    const fsEl = fsMarker.element;
                    fsEl.innerHTML = `
                        <div style="
                            background: ${color};
                            width: 32px;
                            height: 32px;
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-size: 13px;
                            cursor: pointer;
                            transition: transform 0.2s;
                            position: relative;
                        ">
                            <i class="fas fa-truck"></i>
                            ${status === 'moving' ? '<div style="position:absolute;width:12px;height:12px;background:rgba(34,197,94,0.4);border-radius:50%;animation:ping 1.5s ease-in-out infinite;top:-2px;right:-2px;"></div>' : ''}
                        </div>
                    `;
                }
            }
        });

        document.getElementById('online-count').textContent = online;
        document.getElementById('idle-count').textContent = idle;
        document.getElementById('moving-count').textContent = moving;
        document.getElementById('offline-count').textContent = offline;

    }, 30000);
}

// ── Initialize on Page Load ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    if (typeof maplibregl !== 'undefined') {
        initDashboardMap();
        setTimeout(initWialonSession, 800);
    } else {
        console.error('MapLibre not loaded');
        showMapError('MapLibre failed to load. Please check your internet connection.');
    }
});

// ── Handle Window Resize ──────────────────────────────────────────────────────
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        if (dashboardMap) {
            dashboardMap.resize();
        }
        if (fullscreenMap && isFullscreenOpen) {
            fullscreenMap.resize();
        }
    }, 250);
});

// ── Cleanup ──────────────────────────────────────────────────────────────────
window.addEventListener('beforeunload', function() {
    if (updateInterval) clearInterval(updateInterval);
    if (wialonSession) {
        try { wialonSession.logout(); } catch (e) {}
    }
    if (dashboardMap) {
        try { dashboardMap.remove(); } catch (e) {}
    }
    if (fullscreenMap) {
        try { fullscreenMap.remove(); } catch (e) {}
    }
});

// ── Expose functions globally ──────────────────────────────────────────────────
window.openFullscreenMap = openFullscreenMap;

// ── Chart Data ──────────────────────────────────────────────────────────────────
const waybillStatusData = @json($waybillStatusDataPayload);
const mainChartData = @json($chartDataPayload);
const chartDataUrl = '{{ route("dashboard.chart_data") }}';

console.log('Dashboard initialized');

document.addEventListener("DOMContentLoaded", function() {
    // ── Main Bar/Line Chart ───────────────────────────────────────────────
    const ctxMain = document.getElementById('mainChart');
    if (!ctxMain) {
        console.error('mainChart canvas not found');
        return;
    }

    const ctx = ctxMain.getContext('2d');

    const gradient1 = ctx.createLinearGradient(0, 0, 0, 350);
    gradient1.addColorStop(0, 'rgba(67, 97, 238, 0.9)');
    gradient1.addColorStop(0.4, 'rgba(67, 97, 238, 0.6)');
    gradient1.addColorStop(0.7, 'rgba(67, 97, 238, 0.3)');
    gradient1.addColorStop(1, 'rgba(67, 97, 238, 0.05)');

    const gradient2 = ctx.createLinearGradient(0, 0, 0, 350);
    gradient2.addColorStop(0, 'rgba(255, 107, 107, 0.9)');
    gradient2.addColorStop(0.4, 'rgba(255, 107, 107, 0.5)');
    gradient2.addColorStop(0.7, 'rgba(255, 107, 107, 0.2)');
    gradient2.addColorStop(1, 'rgba(255, 107, 107, 0.02)');

    const chartLabels = mainChartData.labels && mainChartData.labels.length > 0 ? mainChartData.labels : ['No Data'];
    const chartAssets = mainChartData.assets && mainChartData.assets.length > 0 ? mainChartData.assets : [0];
    const chartIncidents = mainChartData.incidents && mainChartData.incidents.length > 0 ? mainChartData.incidents : [0];

    const mainChartConfig = {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Completed Trips',
                data: chartAssets,
                backgroundColor: gradient1,
                borderColor: '#4361ee',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                order: 2,
                yAxisID: 'y',
                hoverBackgroundColor: 'rgba(67, 97, 238, 1)',
                hoverBorderColor: '#ffffff',
                hoverBorderWidth: 3,
            }, {
                label: 'Incident Records',
                data: chartIncidents,
                type: 'line',
                borderColor: '#ff6b6b',
                backgroundColor: gradient2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ff6b6b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 10,
                pointHoverBackgroundColor: '#ff6b6b',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 4,
                order: 1,
                yAxisID: 'y1',
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
                axis: 'x'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: {
                            size: 11,
                            weight: '600',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        color: '#2d3436'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.98)',
                    titleColor: '#1a1a2e',
                    bodyColor: '#4a4a6a',
                    borderColor: 'rgba(67, 97, 238, 0.3)',
                    borderWidth: 2,
                    padding: 14,
                    cornerRadius: 14,
                    displayColors: true,
                    boxPadding: 8,
                    usePointStyle: true,
                    titleFont: {
                        size: 13,
                        weight: '700',
                        family: "'Inter', 'Segoe UI', sans-serif"
                    },
                    bodyFont: {
                        size: 12,
                        weight: '500',
                        family: "'Inter', 'Segoe UI', sans-serif"
                    },
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y || 0;
                            const label = context.dataset.label || '';
                            return label + ': ' + value.toLocaleString();
                        },
                        afterBody: function(tooltipItems) {
                            const total = tooltipItems.reduce((sum, item) => {
                                return sum + (item.parsed.y || 0);
                            }, 0);
                            return 'Total: ' + total.toLocaleString();
                        }
                    }
                },
                datalabels: {
                    display: function(context) {
                        return context.datasetIndex === 0 && context.dataIndex % 2 === 0;
                    },
                    color: '#4361ee',
                    anchor: 'end',
                    align: 'top',
                    font: {
                        weight: '700',
                        size: 10
                    },
                    formatter: function(value) {
                        return value > 0 ? value : '';
                    },
                    offset: 2
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: true,
                        borderColor: 'rgba(0,0,0,0.06)'
                    },
                    ticks: {
                        font: {
                            size: 10,
                            weight: '600',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        color: '#636e72',
                        padding: 8,
                        maxRotation: 45,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(0,0,0,0.04)',
                        drawBorder: false,
                        drawTicks: false,
                    },
                    ticks: {
                        font: {
                            size: 10,
                            weight: '500',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        color: '#636e72',
                        stepSize: 1,
                        padding: 8,
                        maxTicksLimit: 8,
                    },
                    title: {
                        display: true,
                        text: 'Completed Trips',
                        color: '#4361ee',
                        font: {
                            size: 10,
                            weight: '700',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        padding: { bottom: 8 }
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        display: false,
                        drawTicks: false,
                    },
                    ticks: {
                        font: {
                            size: 10,
                            weight: '500',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        color: '#636e72',
                        stepSize: 1,
                        padding: 8,
                        maxTicksLimit: 8,
                    },
                    title: {
                        display: true,
                        text: 'Incident Records',
                        color: '#ff6b6b',
                        font: {
                            size: 10,
                            weight: '700',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        padding: { bottom: 8 }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        },
        plugins: []
    };

    Chart.register(ChartDataLabels);

    const mainChart = new Chart(ctx, mainChartConfig);

    // ── Period filter buttons ─────────────────────────────────────────────
    let currentPeriod = 'weekly';
    let isUpdating = false;

    function updateChartData(period) {
        if (isUpdating) return;
        isUpdating = true;

        mainChart.data.labels = ['Loading...'];
        mainChart.data.datasets[0].data = [0];
        mainChart.data.datasets[1].data = [0];
        mainChart.update('none');

        fetch(chartDataUrl + '?period=' + period, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(function(data) {
            mainChart.data.labels = data.labels || ['No Data'];
            mainChart.data.datasets[0].data = data.assets || [0];
            mainChart.data.datasets[1].data = data.incidents || [0];
            mainChart.update();
            currentPeriod = period;
        })
        .catch(function(err) {
            console.error('Chart filter error:', err);
            fetch(chartDataUrl + '?period=' + currentPeriod, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                mainChart.data.labels = data.labels || ['No Data'];
                mainChart.data.datasets[0].data = data.assets || [0];
                mainChart.data.datasets[1].data = data.incidents || [0];
                mainChart.update();
            })
            .catch(() => {});
        })
        .finally(function() {
            isUpdating = false;
        });
    }

    document.querySelectorAll('.chart-period-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            if (period === currentPeriod) return;

            document.querySelectorAll('.chart-period-btn').forEach(function(b) {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-primary');

            updateChartData(period);
        });
    });

    const weeklyBtn = document.querySelector('.chart-period-btn[data-period="weekly"]');
    if (weeklyBtn) {
        weeklyBtn.classList.add('active', 'btn-primary');
        weeklyBtn.classList.remove('btn-outline-secondary');
    }

    // ── Waybill Status Chart ──────────────────────────────────────────────
    const ctxStatus = document.getElementById('statusChart');
    if (!ctxStatus) {
        console.error('statusChart canvas not found');
        return;
    }

    const ctxStatusContext = ctxStatus.getContext('2d');

    const hasData = waybillStatusData.data && waybillStatusData.data.some(v => v > 0);
    const donutLabels = hasData ? waybillStatusData.labels : ['No Data'];
    const donutData = hasData ? waybillStatusData.data : [1];
    const donutColors = hasData ? waybillStatusData.colors : ['#e9ecef'];

    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: function(chart) {
            const { width, height, ctx } = chart;
            ctx.save();

            const centerX = width / 2;
            const centerY = height / 2;

            const radius = Math.min(width, height) * 0.3;

            ctx.shadowColor = 'rgba(0,0,0,0.06)';
            ctx.shadowBlur = 30;
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,0.92)';
            ctx.fill();
            ctx.shadowBlur = 0;

            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(67, 97, 238, 0.1)';
            ctx.lineWidth = 2;
            ctx.stroke();

            const text = hasData ? waybillStatusData.total : '0';
            const subText = hasData ? 'Total Waybills' : 'No Data';

            ctx.font = '700 32px Inter, Segoe UI, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            if (hasData) {
                const gradient = ctx.createLinearGradient(centerX - 50, centerY - 20, centerX + 50, centerY + 20);
                gradient.addColorStop(0, '#4361ee');
                gradient.addColorStop(0.5, '#6c5ce7');
                gradient.addColorStop(1, '#a29bfe');
                ctx.fillStyle = gradient;
            } else {
                ctx.fillStyle = '#adb5bd';
            }
            ctx.fillText(text, width / 2, height / 2 - 8);

            ctx.font = '500 10px Inter, Segoe UI, sans-serif';
            ctx.fillStyle = '#6c757d';
            ctx.fillText(subText, width / 2, height / 2 + 28);

            ctx.restore();
        }
    };

    const statusChart = new Chart(ctxStatusContext, {
        type: 'doughnut',
        data: {
            labels: donutLabels,
            datasets: [{
                data: donutData,
                backgroundColor: donutColors,
                borderColor: '#ffffff',
                borderWidth: 4,
                hoverOffset: hasData ? 15 : 0,
                hoverBorderWidth: 6,
                hoverBorderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: hasData,
                    backgroundColor: 'rgba(255,255,255,0.98)',
                    titleColor: '#1a1a2e',
                    bodyColor: '#4a4a6a',
                    borderColor: 'rgba(67, 97, 238, 0.2)',
                    borderWidth: 2,
                    padding: 14,
                    cornerRadius: 14,
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 11, weight: '500' },
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        },
        plugins: [centerTextPlugin]
    });

    const legendContainer = document.getElementById('statusLegend');
    if (legendContainer) {
        if (!hasData) {
            legendContainer.innerHTML = '<small class="text-muted">No waybill data available</small>';
        } else {
            const total = waybillStatusData.total || 0;
            waybillStatusData.labels.forEach(function(label, index) {
                const color = waybillStatusData.colors[index] || '#adb5bd';
                const count = waybillStatusData.data[index] ?? 0;
                const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';

                const item = document.createElement('div');
                item.className = 'legend-item-enhanced';
                item.innerHTML = `
                    <div class="legend-dot-enhanced" style="background: ${color};"></div>
                    <div class="legend-content">
                        <span class="legend-label-enhanced">${label}</span>
                        <span class="legend-stats-enhanced">${count} (${percentage}%)</span>
                    </div>
                    <div class="legend-bar-enhanced">
                        <div class="legend-bar-fill" style="width: ${percentage}%; background: ${color};"></div>
                    </div>
                `;
                legendContainer.appendChild(item);
            });
        }
    }
});
</script>

<style>
/* ── Import Google Font ────────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

/* ── Enhanced Card Styles ────────────────────────────────────────────────── */
.hover-shadow {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-shadow:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.1) !important;
}

.transition-all {
    transition: all 0.3s ease;
}

.card {
    background: #ffffff !important;
}

/* ── Badge Icon ────────────────────────────────────────────────────────────── */
.badge-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}

.badge-icon.bg-primary {
    background: linear-gradient(135deg, #4361ee 0%, #6c5ce7 100%);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
}

.badge-icon.bg-success {
    background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.25);
}

/* ── Button Styles ────────────────────────────────────────────────────────── */
.btn-group .btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    font-size: 0.7rem;
    padding: 0.3rem 1rem;
    position: relative;
    overflow: hidden;
}

.btn-group .btn-primary {
    background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    border-color: #4361ee;
    color: white;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
}

.btn-group .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
}

.btn-group .btn-outline-secondary {
    border-color: #e9ecef;
    color: #6c757d;
}

.btn-group .btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
    transform: translateY(-2px);
}

.btn-group .btn-outline-secondary.active {
    background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    color: white;
    border-color: #4361ee;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
}

/* ── Pulse Animation ──────────────────────────────────────────────────────── */
.pulse-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(0.8);
    }
}

/* ── Ping Animation for Moving Vehicles ──────────────────────────────────── */
@keyframes ping {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(2.5);
        opacity: 0;
    }
}

/* ── Legend Styles ────────────────────────────────────────────────────────── */
.legend-item-enhanced {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.6rem;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
    flex: 1 1 calc(50% - 0.5rem);
    min-width: 100px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
}

.legend-item-enhanced:hover {
    transform: translateX(3px) scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border-color: rgba(67, 97, 238, 0.08);
}

.legend-dot-enhanced {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.9);
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}

.legend-item-enhanced:hover .legend-dot-enhanced {
    transform: scale(1.15);
}

.legend-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.05rem;
    min-width: 0;
}

.legend-label-enhanced {
    font-size: 0.65rem;
    font-weight: 600;
    color: #2d3436;
    line-height: 1.2;
}

.legend-stats-enhanced {
    font-size: 0.55rem;
    font-weight: 500;
    color: #b2bec3;
    line-height: 1.2;
}

.legend-bar-enhanced {
    width: 32px;
    height: 3px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    flex-shrink: 0;
}

.legend-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── Map Container ────────────────────────────────────────────────────────── */
#dashboard-map-container {
    position: relative;
    overflow: hidden;
}

#dashboard-map {
    width: 100%;
    height: 100%;
}

/* ── Wialon-like Popup Styles ────────────────────────────────────────────── */
.wialon-popup .maplibregl-popup-content {
    border-radius: 12px !important;
    padding: 12px 16px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15) !important;
    border: 1px solid rgba(0,0,0,0.06) !important;
    background: #ffffff !important;
}

.wialon-popup .maplibregl-popup-tip {
    border-top-color: #ffffff !important;
}

/* ── Map Controls ──────────────────────────────────────────────────────────── */
.maplibregl-ctrl-group {
    border-radius: 10px !important;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
    border: 1px solid rgba(0,0,0,0.06) !important;
    background: #ffffff !important;
}

.maplibregl-ctrl button {
    width: 34px !important;
    height: 34px !important;
    background: #ffffff !important;
    border-radius: 0 !important;
}

.maplibregl-ctrl button:first-child {
    border-radius: 10px 10px 0 0 !important;
}

.maplibregl-ctrl button:last-child {
    border-radius: 0 0 10px 10px !important;
}

.maplibregl-ctrl button .maplibregl-ctrl-icon {
    background-size: 16px !important;
}

.maplibregl-ctrl button:hover {
    background: #f0f2f5 !important;
}

/* ── Scale Control ────────────────────────────────────────────────────────── */
.maplibregl-ctrl-scale {
    background: rgba(255,255,255,0.9) !important;
    border: 1px solid rgba(0,0,0,0.08) !important;
    border-radius: 4px !important;
    padding: 2px 8px !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    color: #1a1a2e !important;
    backdrop-filter: blur(4px);
}

/* ── Vehicle Marker Animation ────────────────────────────────────────────── */
.vehicle-marker {
    animation: markerPop 0.3s ease-out;
}

@keyframes markerPop {
    0% {
        transform: scale(0.5);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* ── Fullscreen Modal ──────────────────────────────────────────────────────── */
.modal-fullscreen .modal-content {
    background: #e8edf3 !important;
    border: none !important;
    border-radius: 0 !important;
}

.modal-fullscreen .modal-header {
    background: #ffffff !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 1.5rem !important;
    flex-shrink: 0;
}

.modal-fullscreen .modal-body {
    padding: 0 !important;
    flex: 1;
}

/* ── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    #dashboard-map-container {
        height: 320px !important;
    }

    .btn-group .btn {
        padding: 0.2rem 0.6rem;
        font-size: 0.6rem;
    }

    .btn-group .btn .me-1 {
        margin-right: 0.15rem !important;
    }

    .legend-item-enhanced {
        flex: 1 1 100%;
        padding: 0.3rem 0.5rem;
        min-width: 0;
    }

    .badge-icon {
        width: 28px;
        height: 28px;
        font-size: 0.75rem;
    }

    .legend-bar-enhanced {
        width: 24px;
    }

    .maplibregl-ctrl-group {
        transform: scale(0.85);
        transform-origin: top right;
    }

    .modal-fullscreen .modal-header {
        padding: 0.5rem 1rem !important;
    }
}

@media (max-width: 1200px) and (min-width: 769px) {
    .legend-item-enhanced {
        flex: 1 1 100%;
    }
}

@media (min-width: 769px) and (max-width: 991px) {
    #dashboard-map-container {
        height: 350px !important;
    }
}
</style>
@endpush
