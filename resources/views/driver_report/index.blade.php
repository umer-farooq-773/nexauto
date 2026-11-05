<x-layouts.auth page-title="{{ __('Driver Incidents Report') }}" sub-title="{{ __('Incidents Report alongs to Driver') }}">

@push('auth_styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    :root{
        --dir-primary:#4f46e5; --dir-primary-soft:#eef2ff; --dir-border:#e9ecf2;
        --dir-text-soft:#6b7280;
    }
    .dir-page-head{
        background:linear-gradient(135deg,#4f46e5 0%,#6366f1 55%,#818cf8 100%);
        border-radius:1rem; padding:1.25rem 1.5rem; color:#fff; margin-bottom:1.25rem;
        box-shadow:0 8px 24px -8px rgba(79,70,229,.45);
    }
    .dir-page-head h4{font-weight:700;margin:0;}
    .dir-page-head p{margin:0;opacity:.85;font-size:.875rem;}
    .dir-card{border:1px solid var(--dir-border);border-radius:1rem;box-shadow:0 1px 3px rgba(16,24,40,.04);}
    .dir-card .card-body{padding:1.25rem;}
    .dir-section-title{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--dir-text-soft);font-weight:700;margin:1rem 0 .5rem;}
    .dir-sidebar{position:sticky; top:1rem;}

    /* ---- custom searchable multi-select ---- */
    .dir-ms{position:relative;}
    .dir-ms-toggle{
        width:100%;text-align:left;background:#fff;border:1px solid #d7dbe3;border-radius:.6rem;
        padding:.55rem .75rem;display:flex;justify-content:space-between;align-items:center;font-size:.9rem;
    }
    .dir-ms-toggle:hover{border-color:var(--dir-primary);}
    .dir-ms-menu{
        display:none;position:absolute;z-index:1050;top:calc(100% + 6px);left:0;width:100%;
        background:#fff;border:1px solid var(--dir-border);border-radius:.75rem;
        box-shadow:0 12px 28px -8px rgba(16,24,40,.18); padding:.6rem;max-height:340px;overflow:hidden;
        flex-direction:column;
    }
    .dir-ms.open .dir-ms-menu{display:flex;}
    .dir-ms-search{border:1px solid #e2e5ec;border-radius:.5rem;padding:.4rem .6rem;font-size:.85rem;margin-bottom:.5rem;}
    .dir-ms-list{overflow-y:auto;flex:1;}
    .dir-ms-group-label{font-size:.68rem;text-transform:uppercase;color:var(--dir-text-soft);font-weight:700;margin:.4rem 0 .15rem;}
    .dir-ms-item{display:flex;align-items:center;gap:.5rem;padding:.3rem .35rem;border-radius:.4rem;font-size:.85rem;cursor:pointer;}
    .dir-ms-item:hover{background:var(--dir-primary-soft);}
    .dir-ms-footer{display:flex;justify-content:space-between;border-top:1px solid var(--dir-border);padding-top:.5rem;margin-top:.4rem;}
    .dir-ms-footer button{font-size:.78rem;}

    .dir-quick-btn{border-radius:.5rem !important;}
    .dir-quick-btn.active{background:var(--dir-primary);color:#fff;border-color:var(--dir-primary);}

    .dir-kpi{border:1px solid var(--dir-border);border-radius:.85rem;padding:.85rem 1rem;background:#fff;}
    .dir-kpi .val{font-size:1.4rem;font-weight:700;}
    .dir-kpi .lbl{font-size:.75rem;color:var(--dir-text-soft);}

    #dirResultsTable tbody tr{border-left:3px solid transparent;}
    #dirResultsTable tbody tr:hover{background:#f8f9fc;}
    .dir-type-badge{font-size:.7rem;font-weight:600;padding:.3rem .55rem;border-radius:.4rem;color:#fff;}
    .dir-loading-overlay{display:none;position:absolute;inset:0;background:rgba(255,255,255,.7);z-index:5;align-items:center;justify-content:center;}
    .dir-result-wrap{position:relative;min-height:120px;}
    #dirEmptyState{display:none;}
    .dir-pagination-bar{display:flex;justify-content:space-between;align-items:center;margin-top:.75rem;flex-wrap:wrap;gap:.5rem;}
    th[data-sort]{cursor:pointer;white-space:nowrap;}
    th[data-sort] i{font-size:.7rem;opacity:.5;}
    th[data-sort].active i{opacity:1;}
</style>
@endpush

<div class="dir-page-head">
    <h4><i class="bi bi-shield-exclamation me-1"></i> {{ __('Driver Incidents Report') }}</h4>
    <p>{{ __('Filter, review and export incident & behavior records across your fleet.') }}</p>
</div>

<div class="row g-3">

    <div class="col-lg-3">
        <div class="card dir-card dir-sidebar">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-1 text-primary"></i> {{ __('Report Filters') }}</h6>

                <label class="form-label fw-semibold small">{{ __('Driver') }}</label>
                <div class="dir-ms mb-2" id="dirDriverMs">
                    <button type="button" class="dir-ms-toggle">
                        <span class="dir-ms-toggle-label">{{ __('All Drivers') }}</span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <div class="dir-ms-menu">
                        <input type="text" class="dir-ms-search" placeholder="{{ __('Search driver...') }}">
                        <div class="dir-ms-list">
                            @foreach($drivers as $driver)
                                <label class="dir-ms-item" data-label="{{ strtolower($driver->full_name ?: 'unnamed driver') }}">
                                    <input type="checkbox" class="form-check-input dir-ms-checkbox" value="{{ $driver->id }}">
                                    <span>{{ $driver->full_name ?: 'Unnamed Driver' }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="dir-ms-footer">
                            <button type="button" class="btn btn-link p-0 dir-ms-selectall">{{ __('Select all') }}</button>
                            <button type="button" class="btn btn-link p-0 text-danger dir-ms-clear">{{ __('Clear') }}</button>
                        </div>
                    </div>
                </div>

                <label class="form-label fw-semibold small mt-2">{{ __('Incident / Behavior Type') }}</label>
                <div class="dir-ms mb-2" id="dirTypeMs">
                    <button type="button" class="dir-ms-toggle">
                        <span class="dir-ms-toggle-label">{{ __('All Types') }}</span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <div class="dir-ms-menu">
                        <input type="text" class="dir-ms-search" placeholder="{{ __('Search type...') }}">
                        <div class="dir-ms-list">
                            @foreach(['incident' => 'Incident Types', 'behavior' => 'Behavior', 'inspection' => 'Inspection'] as $groupKey => $groupLabel)
                                <div class="dir-ms-group-label" data-group="{{ $groupKey }}">{{ __($groupLabel) }}</div>
                                @foreach($types as $key => $meta)
                                    @if($meta['group'] === $groupKey)
                                        <label class="dir-ms-item" data-label="{{ strtolower($meta['label']) }}" data-group="{{ $groupKey }}">
                                            <input type="checkbox" class="form-check-input dir-ms-checkbox dir-type" value="{{ $key }}" checked>
                                            <span>{{ $meta['label'] }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                        <div class="dir-ms-footer">
                            <button type="button" class="btn btn-link p-0 dir-ms-selectall">{{ __('Select all') }}</button>
                            <button type="button" class="btn btn-link p-0 text-danger dir-ms-clear">{{ __('Clear') }}</button>
                        </div>
                    </div>
                </div>

                <div class="dir-section-title">{{ __('Quick Range') }}</div>
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm dir-quick-btn" data-range="today">{{ __('Today') }}</button>
                    <button type="button" class="btn btn-outline-primary btn-sm dir-quick-btn" data-range="yesterday">{{ __('Yesterday') }}</button>
                    <button type="button" class="btn btn-outline-primary btn-sm dir-quick-btn" data-range="week">{{ __('Week') }}</button>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2 dir-quick-btn" data-range="month">{{ __('Month') }}</button>

                <div class="dir-section-title mb-1">{{ __('Or select custom dates') }}</div>
                <div class="mb-2">
                    <label class="form-label small mb-1 text-muted">{{ __('From') }}</label>
                    <input type="date" id="dirDateFrom" class="form-control form-control-sm">
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1 text-muted">{{ __('To') }}</label>
                    <input type="date" id="dirDateTo" class="form-control form-control-sm">
                </div>

                <button type="button" id="dirResetBtn" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset filters') }}
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-9">

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="dir-kpi"><div class="val" id="dirKpiTotal">0</div><div class="lbl">{{ __('Total Records') }}</div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dir-kpi"><div class="val" id="dirKpiTopType">-</div><div class="lbl">{{ __('Most Frequent Type') }}</div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dir-kpi"><div class="val" id="dirKpiDrivers">0</div><div class="lbl">{{ __('Drivers Involved') }}</div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dir-kpi"><div class="val" id="dirKpiCost">0</div><div class="lbl">{{ __('Total Cost Recorded') }}</div></div>
            </div>
        </div>

        <div class="card dir-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-1 text-primary"></i> {{ __('Incidents by Type') }}</h6>
                </div>
                <canvas id="dirChart" height="80"></canvas>
            </div>
        </div>

        <div class="card dir-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-table me-1 text-primary"></i> {{ __('Report Result') }}</h6>
                    <a id="dirExportBtn" href="#" class="btn btn-primary btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> {{ __('Export Excel') }}
                    </a>
                </div>

                <div class="dir-result-wrap">
                    <div class="dir-loading-overlay" id="dirLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                    <div id="dirEmptyState" class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-text" style="font-size:2rem;"></i>
                        <p class="mt-2 mb-0">{{ __('No records match the selected filters.') }}</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle" id="dirResultsTable">
                            <thead class="table-light">
                                <tr>
                                    <th data-sort="incident_type">{{ __('Type') }} <i class="bi bi-arrow-down-up"></i></th>
                                    <th data-sort="driver_name">{{ __('Driver') }} <i class="bi bi-arrow-down-up"></i></th>
                                    <th>{{ __('Vehicle') }}</th>
                                    <th data-sort="incident_date">{{ __('Date') }} <i class="bi bi-arrow-down-up"></i></th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Cost') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="dir-pagination-bar">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">{{ __('Rows per page') }}</span>
                            <select id="dirPageSize" class="form-select form-select-sm" style="width:auto;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">{{ __('All') }}</option>
                            </select>
                            <span class="text-muted small" id="dirRangeLabel"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="dirPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
(function () {
    const DATA_URL   = "{{ route('incident_report.data') }}";
    const EXPORT_URL = "{{ route('incident_report.export') }}";

    const typeColors = {
        'Accident': '#dc3545', 'Impoundment': '#fd7e14', 'Vehicle Breakdown': '#6f42c1',
        'Illegal Detention': '#d63384', 'Bogged Down': '#795548', 'Route Diversion': '#0dcaf0',
        'Illegal Haulage': '#ffc107', 'Breathaliser Test': '#20c997', 'Driver Misconduct': '#0d6efd',
        'Goods Inspection': '#6c757d'
    };

    let chart = null;
    let currentSort = 'incident_date';
    let currentDirection = 'desc';
    let debounceTimer = null;
    let allRecords = [];
    let currentPage = 1;
    let pageSize = 25;

    const el = (id) => document.getElementById(id);

    function debouncedFetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchData, 250);
    }

    function initMultiSelect(containerId) {
        const container = el(containerId);
        const toggle = container.querySelector('.dir-ms-toggle');
        const search = container.querySelector('.dir-ms-search');
        const items = Array.from(container.querySelectorAll('.dir-ms-item'));
        const label = container.querySelector('.dir-ms-toggle-label');
        const defaultLabel = label.textContent;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.dir-ms.open').forEach(o => { if (o !== container) o.classList.remove('open'); });
            container.classList.toggle('open');
            if (container.classList.contains('open')) search.focus();
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) container.classList.remove('open');
        });

        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            items.forEach(it => { it.style.display = it.dataset.label.includes(q) ? 'flex' : 'none'; });
            container.querySelectorAll('.dir-ms-group-label').forEach(g => {
                const anyVisible = items.some(it => it.dataset.group === g.dataset.group && it.style.display !== 'none');
                g.style.display = anyVisible ? 'block' : 'none';
            });
        });

        function updateLabel() {
            const checked = items.filter(it => it.querySelector('input').checked);
            if (checked.length === 0) label.textContent = 'None selected';
            else if (checked.length === items.length) label.textContent = defaultLabel;
            else label.textContent = checked.length + ' selected';
        }

        items.forEach(it => {
            it.querySelector('input').addEventListener('change', () => { updateLabel(); debouncedFetch(); });
        });

        container.querySelector('.dir-ms-selectall').addEventListener('click', () => {
            items.forEach(it => { if (it.style.display !== 'none') it.querySelector('input').checked = true; });
            updateLabel(); debouncedFetch();
        });
        container.querySelector('.dir-ms-clear').addEventListener('click', () => {
            items.forEach(it => it.querySelector('input').checked = false);
            updateLabel(); debouncedFetch();
        });

        updateLabel();

        return {
            getChecked: () => items.filter(it => it.querySelector('input').checked).map(it => it.querySelector('input').value),
            checkAll: () => { items.forEach(it => it.querySelector('input').checked = true); updateLabel(); },
            uncheckAll: () => { items.forEach(it => it.querySelector('input').checked = false); updateLabel(); },
        };
    }

    const driverMs = initMultiSelect('dirDriverMs');
    const typeMs   = initMultiSelect('dirTypeMs');

    function currentFilters() {
        return {
            driver_id: driverMs.getChecked(),
            types: typeMs.getChecked(),
            date_from: el('dirDateFrom').value || '',
            date_to: el('dirDateTo').value || '',
            sort: currentSort,
            direction: currentDirection,
        };
    }

    function buildQuery(params) {
        const usp = new URLSearchParams();
        Object.entries(params).forEach(([k, v]) => {
            if (Array.isArray(v)) {
                v.forEach(item => usp.append(k + '[]', item));
            } else if (v !== '' && v !== null && v !== undefined) {
                usp.append(k, v);
            }
        });
        return usp.toString();
    }

    function updateExportLink() {
        el('dirExportBtn').href = EXPORT_URL + '?' + buildQuery(currentFilters());
    }

    function fmtMoney(n) {
        return Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    function renderKpis(json) {
        el('dirKpiTotal').textContent = json.total;
        const labels = json.chart.labels, values = json.chart.values;
        let topType = '-';
        if (labels.length) {
            const maxIdx = values.indexOf(Math.max(...values));
            topType = labels[maxIdx];
        }
        el('dirKpiTopType').textContent = topType;

        const uniqueDrivers = new Set(json.records.filter(r => r.driver_name && r.driver_name !== 'Unassigned').map(r => r.driver_name));
        el('dirKpiDrivers').textContent = uniqueDrivers.size;

        const totalCost = json.records.reduce((sum, r) => sum + (r.cost ? Number(r.cost) : 0), 0);
        el('dirKpiCost').textContent = fmtMoney(totalCost);
    }

    function renderChart(labels, values) {
        const ctx = document.getElementById('dirChart').getContext('2d');
        const colors = labels.map(l => typeColors[l] || '#6c757d');

        if (chart) chart.destroy();
        chart = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Incidents', data: values, backgroundColor: colors, borderRadius: 6 }] },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    function renderPage() {
        const tbody = document.querySelector('#dirResultsTable tbody');
        tbody.innerHTML = '';

        const total = allRecords.length;
        const size = pageSize === 'all' ? total : pageSize;
        const totalPages = size > 0 ? Math.ceil(total / size) : 1;
        currentPage = Math.min(Math.max(currentPage, 1), Math.max(totalPages, 1));

        const start = size > 0 ? (currentPage - 1) * size : 0;
        const end = size > 0 ? start + size : total;
        const pageRows = allRecords.slice(start, end);

        pageRows.forEach(r => {
            const tr = document.createElement('tr');
            tr.style.borderLeftColor = typeColors[r.incident_type] || '#adb5bd';
            tr.innerHTML = `
                <td><span class="dir-type-badge" style="background:${typeColors[r.incident_type] || '#6c757d'}">${r.incident_type}</span></td>
                <td>${r.driver_name ?? 'Unassigned'}</td>
                <td>${r.fleet_no ?? '-'} ${r.truck_no ? '(' + r.truck_no + ')' : ''}</td>
                <td>${r.incident_date ?? '-'}</td>
                <td>${r.location ?? '-'}</td>
                <td>${r.reference ?? '-'}</td>
                <td>${r.cost !== null && r.cost !== undefined ? fmtMoney(r.cost) : '-'}</td>
                <td>${r.status ?? '-'}</td>
            `;
            tbody.appendChild(tr);
        });

        el('dirRangeLabel').textContent = total === 0 ? '' : `${start + 1}-${Math.min(end, total)} of ${total}`;
        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        const ul = el('dirPagination');
        ul.innerHTML = '';
        if (totalPages <= 1) return;

        const makeItem = (label, page, disabled, active) => {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            const a = document.createElement('a');
            a.className = 'page-link'; a.href = '#'; a.textContent = label;
            a.addEventListener('click', (e) => { e.preventDefault(); if (!disabled) { currentPage = page; renderPage(); } });
            li.appendChild(a);
            return li;
        };

        ul.appendChild(makeItem('«', currentPage - 1, currentPage === 1, false));

        const windowSize = 2;
        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= windowSize) {
                ul.appendChild(makeItem(p, p, false, p === currentPage));
            } else if (Math.abs(p - currentPage) === windowSize + 1) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<span class="page-link">…</span>';
                ul.appendChild(li);
            }
        }

        ul.appendChild(makeItem('»', currentPage + 1, currentPage === totalPages, false));
    }

    el('dirPageSize').addEventListener('change', (e) => {
        pageSize = e.target.value === 'all' ? 'all' : parseInt(e.target.value, 10);
        currentPage = 1;
        renderPage();
    });

    function fetchData() {
        el('dirLoading').style.display = 'flex';
        el('dirEmptyState').style.display = 'none';

        fetch(DATA_URL + '?' + buildQuery(currentFilters()))
            .then(res => res.json())
            .then(json => {
                el('dirLoading').style.display = 'none';
                allRecords = json.records;
                currentPage = 1;

                renderKpis(json);
                renderChart(json.chart.labels, json.chart.values);

                if (json.total === 0) {
                    el('dirEmptyState').style.display = 'block';
                    document.querySelector('#dirResultsTable tbody').innerHTML = '';
                    el('dirRangeLabel').textContent = '';
                    el('dirPagination').innerHTML = '';
                } else {
                    renderPage();
                }

                updateExportLink();
            })
            .catch(() => { el('dirLoading').style.display = 'none'; });
    }

    function setQuickRange(range) {
        const today = new Date();
        let from = new Date(today), to = new Date(today);

        if (range === 'yesterday') { from.setDate(today.getDate() - 1); to.setDate(today.getDate() - 1); }
        else if (range === 'week') { from.setDate(today.getDate() - 7); }
        else if (range === 'month') { from.setDate(today.getDate() - 30); }

        const fmt = (d) => d.toISOString().split('T')[0];
        el('dirDateFrom').value = fmt(from);
        el('dirDateTo').value = fmt(to);

        document.querySelectorAll('.dir-quick-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.dir-quick-btn[data-range="${range}"]`).classList.add('active');
        debouncedFetch();
    }

    document.querySelectorAll('.dir-quick-btn').forEach(btn => btn.addEventListener('click', () => setQuickRange(btn.dataset.range)));
    el('dirDateFrom').addEventListener('change', () => { document.querySelectorAll('.dir-quick-btn').forEach(b => b.classList.remove('active')); debouncedFetch(); });
    el('dirDateTo').addEventListener('change', () => { document.querySelectorAll('.dir-quick-btn').forEach(b => b.classList.remove('active')); debouncedFetch(); });

    el('dirResetBtn').addEventListener('click', () => {
        driverMs.uncheckAll();
        typeMs.checkAll();
        el('dirDateFrom').value = '';
        el('dirDateTo').value = '';
        document.querySelectorAll('.dir-quick-btn').forEach(b => b.classList.remove('active'));
        pageSize = 10;
        el('dirPageSize').value = '10';
        fetchData();
    });

    document.querySelectorAll('#dirResultsTable thead th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const sortKey = th.dataset.sort;
            document.querySelectorAll('#dirResultsTable thead th[data-sort]').forEach(t => t.classList.remove('active'));
            th.classList.add('active');
            if (currentSort === sortKey) {
                currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = sortKey; currentDirection = 'asc';
            }
            fetchData();
        });
    });

    fetchData();
})();
</script>
@endpush

</x-layouts.auth>
