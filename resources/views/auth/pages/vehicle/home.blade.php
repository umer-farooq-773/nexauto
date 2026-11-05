<x-layouts.auth page-title="{{__('language.vehicle_management')}}"
    sub-title="{{__('language.vehicle_management_sub_title')}}">

    {{-- Remix Icon CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    {{-- ═══════════════════════════════════════════════════════════════
         QUICK LINK STATS ROW
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="row g-4">
        @can('vehicle_management')
            <x-auth.quick-link-card icon="fa-info-circle" :title="__('Total No Of Assets')"
            description="{{$data['all_vehicle_count']}}" route="vehicle.list" iconbg="bg-blue-light"
            icontext="" />
        @endcan

        @can('driver_management')
            <x-auth.quick-link-card icon="fa-user-tie" :title="__('Total No Of   Drivers')"
            description="{{$data['driver_detail_count']}}" route="driver.index" iconbg="bg-green-light"
            icontext="" />
        @endcan

        <x-auth.quick-link-card icon="fa-users" :title="__('Total No Of Driver Mates')"
        description="{{$data['driver_mate_count']}}" route="driver_mate.index" iconbg="bg-green-light"
        icontext="" />

        <x-auth.quick-link-card icon="fa-route" :title="__('Active Trips')"
            description="{{$data['route_trip_count']}}" route="route_trip.index" iconbg="bg-yellow-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-file-invoice-dollar" :title="__('Total Waybills')"
            description="{{$data['total_waybill_count']}}" route="waybill.index"
            iconbg="bg-orange-light" icontext="" />

        <x-auth.quick-link-card icon="fa-hourglass-half" :title="__('Awaiting Waybills')"
            description="{{$data['awaiting_waybill_count']}}" route="waybill.awaiting" iconbg="bg-blue-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-truck-loading" :title="__('Trips Due in 24 Hours')"
            description="{{$data['allocated_waybill_count']}}" route="waybill.index" iconbg="bg-blue-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-clock-rotate-left" :title="__('Maintenance')"
            description="{{ $data['maintenance_count'] }}" route="maintenance.index" iconbg="bg-blue-light"
            icontext="" />
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         CHARTS ROW
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mt-3">

        {{-- ── Operation Performance (Line + Bar) ── --}}
        <div class="col-sm-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 dashboard-card h-100">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                        <div>
                            <h5 class="card-title fw-bold mb-1 text-dark">{{ __('Operation Performance') }}</h5>
                            <p class="text-muted small mb-0">{{ __('Assets Added & Maintenance Records') }}</p>
                        </div>
                        <div class="d-flex gap-2 chart-period-buttons">
                            <button class="btn btn-sm rounded-pill px-3 vh-chart-btn active" data-period="weekly">{{ __('Weekly') }}</button>
                            <button class="btn btn-sm rounded-pill px-3 vh-chart-btn" data-period="monthly">{{ __('Monthly') }}</button>
                            <button class="btn btn-sm rounded-pill px-3 vh-chart-btn" data-period="yearly">{{ __('Yearly') }}</button>
                        </div>
                    </div>

                    <div class="chart-canvas-container">
                        <canvas id="performanceLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Utilization Status (Donut) ── --}}
        <div class="col-sm-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 dashboard-card h-100">
                <div class="card-body p-4">

                    <div class="mb-3">
                        <h6 class="mb-1 fw-bold text-dark">{{ __('Utilization Status') }}</h6>
                        <span class="text-muted small">{{ __('Live Asset availability across regions') }}</span>
                    </div>

                    <div class="donut-chart-wrapper">
                        <canvas id="statusDonutChart"></canvas>
                        <div class="donut-center-overlay">
                            <span class="donut-center-label">{{ __('Fleet') }}</span>
                            <h2 class="donut-center-value">{{ $data['all_vehicle_count'] }}</h2>
                            <span class="chip chip-blue">{{ __('Units') }}</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-4 text-center status-mini-col">
                                <div class="status-mini-label">{{ __('Active') }}</div>
                                <div class="status-mini-value text-success">70%</div>
                            </div>
                            <div class="col-4 text-center status-mini-col">
                                <div class="status-mini-label">{{ __('Service') }}</div>
                                <div class="status-mini-value text-danger">15%</div>
                            </div>
                            <div class="col-4 text-center status-mini-col">
                                <div class="status-mini-label">{{ __('Idle') }}</div>
                                <div class="status-mini-value text-warning">15%</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         BOTTOM ROW — RECENT ACTIVITY + ACTIVE FLEET
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mt-3">

        {{-- ── Recent Activity ── --}}
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 dashboard-card h-100">
                <div class="card-body p-4">

                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-history-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Recent Activity') }}</h6>
                                <small class="text-muted">{{ __('Latest fleet events') }}</small>
                            </div>
                        </div>
                        <a class="view-all-link" href="">{{ __('View All') }} <i class="ri-arrow-right-line"></i></a>
                    </div>

                    <div class="timeline-wrapper">

                        <div class="timeline-item">
                            <div class="activity-line"></div>
                            <div class="icon-sm bg-primary-subtle text-primary rounded-circle me-3 z-index-1">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">{{ __('New Asset Onboarded') }}</p>
                                <p class="text-muted extra-small mb-0">{{ __('VMS-8820 (Hino Truck) added to Fleet A • 5 mins ago') }}</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="icon-sm bg-info-subtle text-info rounded-circle me-3 z-index-1">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">{{ __('Driver Assigned') }}</p>
                                <p class="text-muted extra-small mb-0">{{ __('Zahid Khan assigned to VMS-1102 • Route: Karachi-LHR • 45 mins ago') }}</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="icon-sm bg-success-subtle text-success rounded-circle me-3 z-index-1">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">{{ __('Inspection Completed') }}</p>
                                <p class="text-muted extra-small mb-0">{{ __('VMS-9941 passed monthly fitness test • Verified by Inspector Ali • 2 hours ago') }}</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="icon-sm bg-warning-subtle text-warning rounded-circle me-3 z-index-1">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">{{ __('Document Expiry Alert') }}</p>
                                <p class="text-muted extra-small mb-0">{{ __('VMS-2024 Insurance expiring in 3 days • Action Required • 5 hours ago') }}</p>
                            </div>
                        </div>

                        <div class="timeline-item last">
                            <div class="icon-sm bg-secondary-subtle text-secondary rounded-circle me-3 z-index-1">
                                <i class="fas fa-toggle-on"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-bold small text-dark">{{ __('Asset Status Updated') }}</p>
                                <p class="text-muted extra-small mb-0">{{ __("VMS-7822 changed from 'Idle' to 'In-Transit' • 8 hours ago") }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── Active Fleet Status ── --}}
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 dashboard-card h-100">
                <div class="card-body p-4">

                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile green">
                                <i class="ri-truck-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Active Fleet Status') }}</h6>
                                <small class="text-muted">{{ __('Live vehicle tracking') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="fleet-status-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Asset No') }}</th>
                                    <th>{{ __('Driver Name') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Location') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="fleet-id">VMS-7822</span></td>
                                    <td>Ahmed Ali</td>
                                    <td><span class="chip chip-green">{{ __('OnTrip') }}</span></td>
                                    <td>Lahore</td>
                                </tr>
                                <tr>
                                    <td><span class="fleet-id">VMS-7822</span></td>
                                    <td>Ahmed Ali</td>
                                    <td><span class="chip chip-red">{{ __('Stuck') }}</span></td>
                                    <td>Lahore</td>
                                </tr>
                                <tr>
                                    <td><span class="fleet-id">VMS-7822</span></td>
                                    <td>Ahmed Ali</td>
                                    <td><span class="chip chip-amber">{{ __('Loading') }}</span></td>
                                    <td>Lahore</td>
                                </tr>
                                <tr>
                                    <td><span class="fleet-id">VMS-7822</span></td>
                                    <td>Ahmed Ali</td>
                                    <td><span class="chip chip-green">{{ __('OnTrip') }}</span></td>
                                    <td>Lahore</td>
                                </tr>
                                <tr>
                                    <td><span class="fleet-id">VMS-7822</span></td>
                                    <td>Ahmed Ali</td>
                                    <td><span class="chip chip-green">{{ __('OnTrip') }}</span></td>
                                    <td>Lahore</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SHARED DASHBOARD STYLES
    ═══════════════════════════════════════════════════════════════ --}}
    <style>
        /* ── Card shell ── */
        .dashboard-card {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        .dashboard-card:hover {
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.06) !important;
        }

        /* ── Icon tiles ── */
        .icon-tile {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; flex-shrink: 0;
        }
        .icon-tile.blue   { background: #e0e7ff; color: #4f46e5; }
        .icon-tile.green  { background: #dcfce7; color: #16a34a; }

        /* ── Section header ── */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap;
        }
        .section-header-left { display: flex; align-items: center; gap: 0.85rem; }
        .section-title { font-weight: 700; font-size: 1.05rem; color: #1e293b; }

        /* ── View All link ── */
        .view-all-link {
            font-size: 0.82rem;
            font-weight: 600;
            color: #4f46e5;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
        }
        .view-all-link:hover {
            color: #4338ca;
            gap: 8px;
        }

        /* ── Chart period buttons ── */
        .chart-period-buttons .vh-chart-btn {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-weight: 600;
            font-size: 0.78rem;
            transition: all 0.2s ease;
        }
        .chart-period-buttons .vh-chart-btn:hover:not(.active) {
            border-color: #c7d2fe;
            background: #f8faff;
            color: #4f46e5;
        }
        .chart-period-buttons .vh-chart-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* ── Chart canvas container (replaces inline 320px) ── */
        .chart-canvas-container {
            height: 320px;
            position: relative;
            width: 100%;
        }

        /* ── Donut chart wrapper ── */
        .donut-chart-wrapper {
            max-width: 250px;
            position: relative;
            width: 100%;
            margin: auto;
        }

        /* ── Center overlay on donut ── */
        .donut-center-overlay {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }
        .donut-center-label {
            display: block;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .donut-center-value {
            font-weight: 800;
            margin: 2px 0 4px;
            color: #0f172a;
            font-size: 1.75rem;
            line-height: 1;
        }

        /* ── Chips (for Units badge) ── */
        .chip {
            display: inline-flex; align-items: center;
            padding: 5px 12px; border-radius: 50px;
            font-size: 0.75rem; font-weight: 700;
            letter-spacing: 0.3px;
        }
        .chip-blue   { background: #e0e7ff; color: #4f46e5; }
        .chip-green  { background: #dcfce7; color: #15803d; }
        .chip-red    { background: #fee2e2; color: #b91c1c; }
        .chip-amber  { background: #fef3c7; color: #b45309; }

        /* ── Utilization status mini columns ── */
        .status-mini-col {
            padding: 8px 4px;
            border-right: 1px solid #f1f5f9;
        }
        .status-mini-col:last-child { border-right: none; }
        .status-mini-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .status-mini-value {
            font-size: 1.05rem;
            font-weight: 800;
        }

        /* ── Timeline ── */
        .timeline-wrapper {
            position: relative;
        }
        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            position: relative;
        }
        .timeline-item.last { margin-bottom: 0; }
        .timeline-item .icon-sm {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .timeline-item .activity-line {
            position: absolute;
            left: 17px;
            top: 34px;
            bottom: -1.25rem;
            width: 1px;
            background: linear-gradient(180deg, #e2e8f0 0%, #f1f5f9 100%);
        }
        .timeline-item:last-child .activity-line {
            display: none;
        }
        .timeline-item p {
            line-height: 1.4;
        }
        .timeline-item .extra-small {
            font-size: 0.78rem;
            line-height: 1.5;
        }

        /* ── Fleet status table ── */
        .fleet-status-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .fleet-status-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            padding: 12px 14px;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .fleet-status-table thead th:first-child { border-top-left-radius: 10px; }
        .fleet-status-table thead th:last-child { border-top-right-radius: 10px; }
        .fleet-status-table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.87rem;
            color: #1e293b;
            vertical-align: middle;
        }
        .fleet-status-table tbody tr {
            transition: background 0.15s ease;
        }
        .fleet-status-table tbody tr:hover {
            background: #f8fafc;
        }
        .fleet-status-table tbody tr:last-child td {
            border-bottom: none;
        }
        .fleet-id {
            font-weight: 700;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
        }

        /* ── Chart canvas responsiveness ── */
        @media (max-width: 768px) {
            .chart-canvas-container { height: 260px; }
            .donut-center-value { font-size: 1.4rem; }
        }

        /* ── Print ── */
        @media print {
            .dashboard-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            .chart-period-buttons { display: none; }
        }
    </style>

    {{-- ═══════════════════════════════════════════════════════════════
         SCRIPT — UNCHANGED
    ═══════════════════════════════════════════════════════════════ --}}
    @push('auth_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    @php
        $vhChartPayload = $chartData ?? ['labels' => [], 'assets' => [], 'maintenance' => []];
    @endphp
    const vhChartData  = @json($vhChartPayload);
    const vhChartUrl   = '{{ route("vehicle.chart_data") }}';

    document.addEventListener('DOMContentLoaded', function () {

        // ── Operation Performance Bar+Line Chart ─────────────────────────────
        const ctxLine = document.getElementById('performanceLineChart').getContext('2d');

        const vhMainChart = new Chart(ctxLine, {
            type: 'bar',
            data: {
                labels: [], // Start empty, will be populated by initial fetch
                datasets: [{
                    label: 'Assets Added',
                    data: [],
                    backgroundColor: 'rgba(67, 97, 238, 0.8)',
                    borderColor: '#4361ee',
                    borderWidth: 2,
                    borderRadius: 6,
                    order: 2,
                    yAxisID: 'y',
                }, {
                    label: 'Maintenance Records',
                    data: [],
                    type: 'line',
                    borderColor: '#e71d36',
                    backgroundColor: 'rgba(231, 29, 54, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#e71d36',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    order: 1,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 12, weight: '500' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        titleColor: '#1a1a2e',
                        bodyColor: '#4a4a6a',
                        borderColor: 'rgba(0,0,0,0.05)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: true,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(ctx) {
                                return (ctx.dataset.label || '') + ': ' + (ctx.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#6c757d' }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, color: '#6c757d', stepSize: 1 },
                        title: {
                            display: true,
                            text: 'Number of Assets',
                            color: '#4361ee',
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#6c757d', stepSize: 1 },
                        title: {
                            display: true,
                            text: 'Maintenance Records',
                            color: '#e71d36',
                            font: { size: 11, weight: 'bold' }
                        }
                    }
                }
            }
        });

        // ── Period filter buttons ─────────────────────────────────────────────
        document.querySelectorAll('.vh-chart-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.vh-chart-btn').forEach(function (b) {
                    b.classList.remove('active', 'btn-outline-primary');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-outline-primary');

                // Clear while loading
                vhMainChart.data.labels = [];
                vhMainChart.data.datasets[0].data = [];
                vhMainChart.data.datasets[1].data = [];
                vhMainChart.update('none');

                fetch(vhChartUrl + '?period=' + this.dataset.period, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    vhMainChart.data.labels           = json.labels;
                    vhMainChart.data.datasets[0].data = json.assets;
                    vhMainChart.data.datasets[1].data = json.maintenance;
                    vhMainChart.update();
                })
                .catch(function (err) { console.error('Chart filter error:', err); });
            });
        });

        // ── Load initial weekly data on page load ─────────────────────────────
        const weeklyBtn = document.querySelector('.vh-chart-btn[data-period="weekly"]');
        if (weeklyBtn) {
            weeklyBtn.click();
        }

        // ── Donut Chart ───────────────────────────────────────────────────────
        const ctxDonut = document.getElementById('statusDonutChart').getContext('2d');

        const activeVehicleCount = {{ $data['active_vehicle_count'] }};
        const inMaintenanceCount = {{ $data['in_maintenance_count'] }};
        const unserviceableCount = {{ $data['unserviceable_count'] }};

        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Serviceable', 'In Maintenance', 'Unserviceable'],
                datasets: [{
                    data: [activeVehicleCount, inMaintenanceCount, unserviceableCount],
                    backgroundColor: ['#2ec4b6', '#e71d36', '#ff9f1c'],
                    hoverBackgroundColor: ['#26a69a', '#d32f2f', '#ffa000'],
                    borderWidth: 0,
                    weight: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '40%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        displayColors: true
                    }
                },
                animation: { animateScale: true, animateRotate: true }
            }
        });
    });
    </script>

    <style>
    .btn-outline-primary.active {
        background: #4361ee;
        color: white;
        border-color: #4361ee;
    }
    .btn-outline-primary:hover:not(.active) {
        background: rgba(67, 97, 238, 0.08);
        border-color: #4361ee;
    }
    .hover-shadow { transition: all 0.3s ease-in-out; }
    .hover-shadow:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08) !important; }
    </style>
    @endpush
</x-layouts.auth>