<x-layouts.auth page-title="{{ __('Maintenance History') }}" sub-title="{{ __('Comprehensive Audit Trail of Vehicle Maintenance Items, Components & Servicing') }}">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">

    <x-slot:actionButton>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <x-export-csv
                table="vehicle_maintenance_items"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />
        </div>
    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small
            icon="fa-clipboard-list"
            title="{{ number_format($stats['total_items']) }}"
            description="{{ __('Total History Items') }}"
            route="{{ route('maintenance.history') }}"
            iconbg="bg-primary"
            icontext="text-white"
        />

        <x-auth.quick-link-card-small
            icon="fa-wrench"
            title="{{ number_format($stats['total_maintenance_records']) }}"
            description="{{ __('Maintenance Logs') }}"
            route="{{ route('maintenance.index') }}"
            iconbg="bg-secondary"
            icontext="text-white"
        />

        <x-auth.quick-link-card-small
            icon="fa-circle-notch"
            title="{{ number_format($stats['tyre_count']) }}"
            description="{{ __('Tyres') }}"
            route="{{ route('maintenance.history', ['category' => 'tyre']) }}"
            iconbg="bg-info"
            icontext="text-white"
        />

        <x-auth.quick-link-card-small
            icon="fa-car-battery"
            title="{{ number_format($stats['battery_count']) }}"
            description="{{ __('Batteries') }}"
            route="{{ route('maintenance.history', ['category' => 'battery']) }}"
            iconbg="bg-warning"
            icontext="text-dark"
        />
    </x-slot:quickLinks>

    {{-- ═══════════════════════════════════════════════════════════════
         SECONDARY METRIC ROW
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3 col-xl-2">
            <a href="{{ route('maintenance.history', ['category' => 'telematics']) }}" class="text-decoration-none">
                <div class="mini-kpi-card card h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="mini-kpi-icon mini-icon-info">
                            <i class="ri-radar-line"></i>
                        </div>
                        <div>
                            <h5 class="mini-kpi-value">{{ number_format($stats['telematics_count']) }}</h5>
                            <small class="mini-kpi-label">{{ __('Telematics') }}</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3 col-xl-2">
            <a href="{{ route('maintenance.history', ['category' => 'brake']) }}" class="text-decoration-none">
                <div class="mini-kpi-card card h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="mini-kpi-icon mini-icon-danger">
                            <i class="ri-stop-circle-line"></i>
                        </div>
                        <div>
                            <h5 class="mini-kpi-value">{{ number_format($stats['brake_count']) }}</h5>
                            <small class="mini-kpi-label">{{ __('Brakes') }}</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3 col-xl-2">
            <a href="{{ route('maintenance.history', ['category' => 'engine']) }}" class="text-decoration-none">
                <div class="mini-kpi-card card h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="mini-kpi-icon mini-icon-slate">
                            <i class="ri-settings-4-line"></i>
                        </div>
                        <div>
                            <h5 class="mini-kpi-value">{{ number_format($stats['engine_count']) }}</h5>
                            <small class="mini-kpi-label">{{ __('Engine') }}</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3 col-xl-2">
            <a href="{{ route('maintenance.history', ['category' => 'general']) }}" class="text-decoration-none">
                <div class="mini-kpi-card card h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="mini-kpi-icon mini-icon-neutral">
                            <i class="ri-tools-fill"></i>
                        </div>
                        <div>
                            <h5 class="mini-kpi-value">{{ number_format($stats['general_count']) }}</h5>
                            <small class="mini-kpi-label">{{ __('General') }}</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card spend-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="spend-icon-circle">
                            <i class="ri-cash-line"></i>
                        </div>
                        <div>
                            <small class="spend-label">{{ __('Total Maintenance Spend') }}</small>
                            <h4 class="spend-value">{{ number_format($stats['total_cost'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="spend-badge">
                        <i class="ri-shield-check-line"></i>
                        <span>{{ __('Audit Ready') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         FILTER PANEL
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-4">

            <div class="filter-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="filter-icon-badge">
                        <i class="ri-filter-3-line"></i>
                    </div>
                    <div>
                        <h6 class="filter-title">{{ __('Filter Maintenance History') }}</h6>
                        <small class="filter-subtitle">{{ __('Filter by vehicle asset, component category, maintenance action, or date range') }}</small>
                    </div>
                </div>
                @if(request()->hasAny(['vehicle_id', 'maintenance_type_id', 'category', 'action', 'date_from', 'date_to', 'search']))
                    <a href="{{ route('maintenance.history') }}" class="btn-clear-filter">
                        <i class="ri-refresh-line"></i>
                        <span>{{ __('Clear Filters') }}</span>
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('maintenance.history') }}" id="maintenanceHistoryFilterForm">
                <div class="row g-3 align-items-end">

                    {{-- Vehicle Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label class="filter-label">
                            <i class="ri-truck-line"></i>
                            <span>{{ __('Asset / Vehicle') }}</span>
                        </label>
                        <select name="vehicle_id" class="form-select filter-select searchable-vehicle">
                            <option value="all">{{ __('All Vehicles') }}</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->truck_no }} {{ $vehicle->fleet_no ? "({$vehicle->fleet_no})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Maintenance Type Filter --}}
                    <div class="col-md-6 col-lg-2">
                        <label class="filter-label">
                            <i class="ri-shield-wrench-line"></i>
                            <span>{{ __('Maintenance Type') }}</span>
                        </label>
                        <select name="maintenance_type_id" class="form-select filter-select">
                            <option value="all">{{ __('All Types') }}</option>
                            @foreach($maintenanceTypes as $mType)
                                <option value="{{ $mType->id }}" {{ request('maintenance_type_id') == $mType->id ? 'selected' : '' }}>
                                    {{ $mType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div class="col-md-6 col-lg-2">
                        <label class="filter-label">
                            <i class="ri-function-line"></i>
                            <span>{{ __('Category') }}</span>
                        </label>
                        <select name="category" class="form-select filter-select">
                            <option value="all">{{ __('All Categories') }}</option>
                            <option value="tyre" {{ request('category') === 'tyre' ? 'selected' : '' }}>Tyre</option>
                            <option value="battery" {{ request('category') === 'battery' ? 'selected' : '' }}>Battery</option>
                            <option value="telematics" {{ request('category') === 'telematics' ? 'selected' : '' }}>Telematics</option>
                            <option value="brake" {{ request('category') === 'brake' ? 'selected' : '' }}>Brakes</option>
                            <option value="engine" {{ request('category') === 'engine' ? 'selected' : '' }}>Engine</option>
                            <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>General</option>
                        </select>
                    </div>

                    {{-- Action Filter --}}
                    <div class="col-md-6 col-lg-2">
                        <label class="filter-label">
                            <i class="ri-tools-line"></i>
                            <span>{{ __('Action') }}</span>
                        </label>
                        <select name="action" class="form-select filter-select">
                            <option value="all">{{ __('All Actions') }}</option>
                            <option value="replace" {{ request('action') === 'replace' ? 'selected' : '' }}>Replace</option>
                            <option value="rotate" {{ request('action') === 'rotate' ? 'selected' : '' }}>Rotate</option>
                            <option value="install" {{ request('action') === 'install' ? 'selected' : '' }}>Install</option>
                            <option value="repair" {{ request('action') === 'repair' ? 'selected' : '' }}>Repair</option>
                            <option value="inspect" {{ request('action') === 'inspect' ? 'selected' : '' }}>Inspect</option>
                            <option value="service" {{ request('action') === 'service' ? 'selected' : '' }}>Service</option>
                            <option value="recharge" {{ request('action') === 'recharge' ? 'selected' : '' }}>Recharge</option>
                            <option value="calibrate" {{ request('action') === 'calibrate' ? 'selected' : '' }}>Calibrate</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div class="col-md-6 col-lg-2">
                        <label class="filter-label">
                            <i class="ri-calendar-line"></i>
                            <span>{{ __('From') }}</span>
                        </label>
                        <input type="date" name="date_from" class="form-control filter-input" value="{{ request('date_from') }}" placeholder="dd/mm/yyyy">
                    </div>

                    {{-- Date To --}}
                    <div class="col-md-6 col-lg-2">
                        <label class="filter-label">
                            <i class="ri-calendar-event-line"></i>
                            <span>{{ __('To') }}</span>
                        </label>
                        <input type="date" name="date_to" class="form-control filter-input" value="{{ request('date_to') }}" placeholder="dd/mm/yyyy">
                    </div>

                    {{-- Submit --}}
                    <div class="col-md-6 col-lg-1">
                        <button type="submit" class="btn-filter-submit" title="{{ __('Apply Filters') }}">
                            <i class="ri-search-line"></i>
                        </button>
                    </div>
                </div>

                {{-- Keyword Search --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="keyword-search-wrapper">
                            <i class="ri-search-2-line keyword-search-icon"></i>
                            <input type="text"
                                   name="search"
                                   class="keyword-search-input"
                                   placeholder="{{ __('Quick search by Serial Number, Brand, Model, Part Name, Axle/Location, or Service Provider...') }}"
                                   value="{{ request('search') }}">
                            @if(request('search'))
                                <a href="{{ route('maintenance.history', request()->except('search')) }}" class="keyword-search-clear" title="{{ __('Clear') }}">
                                    <i class="ri-close-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         MAIN TABLE
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white w-100 rounded-4 shadow-sm border overflow-hidden">
            <x-auth.datatable :search="false" :init="false" id="maintenanceHistoryTable">
                <thead>
                    <tr class="text-dark">
                        <th scope="col" style="width: 18%;">{{ __('Vehicle') }}</th>
                        <th scope="col" style="width: 12%;">{{ __('Date') }}</th>
                        <th scope="col" style="width: 20%;">{{ __('Component') }}</th>
                        <th scope="col" style="width: 12%;">{{ __('Action') }}</th>
                        <th scope="col" style="width: 15%;">{{ __('Location') }}</th>
                        <th scope="col" style="width: 10%;" class="text-center">{{ __('Cost') }}</th>
                        <th scope="col" style="width: 13%;" class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $maint = $item->maintenance;
                            $veh = $maint?->vehicle;
                        @endphp
                        <tr>
                            {{-- Vehicle --}}
                            <td>
                                @if($veh)
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark text-truncate" style="max-width: 160px;" title="{{ $veh->truck_no }}">
                                            <i class="ri-truck-line text-primary me-1"></i>{{ $veh->truck_no }}
                                        </span>
                                        @if($veh->fleet_no)
                                            <span class="text-muted small">{{ $veh->fleet_no }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                @php
                                    $recordDate = $maint?->maintenance_date ?? $item->created_at;
                                @endphp
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark">{{ $recordDate ? \Carbon\Carbon::parse($recordDate)->format('d M Y') : '–' }}</span>
                                    <span class="text-muted small">{{ $item->created_at ? $item->created_at->format('h:i A') : '' }}</span>
                                </div>
                            </td>

                            {{-- Component --}}
                            {{-- Component --}}
                            <td>
                                <div class="component-cell">
                                    <span class="component-name text-truncate"
                                        title="{{ $item->item_name ?: ucfirst($item->category) }}">
                                        {{ $item->item_name ?: ucfirst($item->category) }}
                                    </span>

                                    <div class="component-meta">
                                        {!! $item->category_badge !!}
                                        @if($item->brand)
                                            <span class="component-brand text-truncate"
                                                title="{{ $item->brand }}{{ $item->model ? ' ' . $item->model : '' }}">
                                                <i class="ri-price-tag-3-line"></i>
                                                {{ $item->brand }}{{ $item->model ? ' ' . $item->model : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Action --}}
                            <td>
                                {!! $item->action_badge !!}
                            </td>

                            {{-- Location --}}
                            {{-- Location --}}
                            <td>
                                <div class="location-cell">
                                    @if($item->location)
                                        <span class="location-name text-truncate" title="{{ $item->location }}">
                                            <i class="ri-map-pin-2-fill"></i>
                                            <span class="text-truncate">{{ $item->location }}</span>
                                        </span>
                                    @endif

                                    @if($item->new_serial_no)
                                        <span class="serial-chip text-truncate" title="{{ $item->new_serial_no }}">
                                            <i class="ri-barcode-line"></i>
                                            {{ Str::limit($item->new_serial_no, 14) }}
                                        </span>
                                    @endif

                                    @if(!$item->location && !$item->new_serial_no)
                                        <span class="text-muted">–</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Cost --}}
                            <td class="text-center">
                                @if($item->cost)
                                    <span class="fw-bold text-primary font-monospace">{{ number_format($item->cost, 2) }}</span>
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td class="text-center">
                                <a href="{{ route('maintenance.history.show', $item->id) }}" 
                                   class="btn btn-sm btn-primary rounded-pill shadow-sm d-inline-flex align-items-center gap-1 action-view-btn px-3 py-1" 
                                   title="{{ __('View details') }}">
                                    <i class="ri-eye-line"></i>
                                    <span class="d-none d-xl-inline">{{ __('View') }}</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         PHOTO PREVIEW MODAL
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="historyPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold mb-0" id="historyPhotoModalTitle">
                        <i class="ri-image-line me-1"></i> {{ __('Component Photo Verification') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center bg-black">
                    <img id="historyPhotoModalImage" src="" alt="Verification Photo" class="img-fluid" style="max-height: 75vh; width: auto; object-fit: contain;">
                </div>
                <div class="modal-footer border-0 bg-dark p-2 justify-content-between">
                    <a id="historyPhotoModalDownload" href="" target="_blank" download class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="ri-download-2-line me-1"></i> {{ __('Open Original') }}
                    </a>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SCRIPTS
    ═══════════════════════════════════════════════════════════════ --}}
    @push('auth_scripts')
    <script>
        $(document).ready(function() {
            const tableId = '#maintenanceHistoryTable';

            // Searchable vehicle dropdown
            if ($.fn.select2) {
                $('.searchable-vehicle').select2({
                    placeholder: "{{ __('All Vehicles') }}",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            }

            // Destroy existing DataTable instance if any
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }

            // Reinitialize with correct full-width settings
            $(tableId).DataTable({
                responsive: false,
                autoWidth: false,
                scrollX: false,
                ordering: true,
                order: [[1, 'desc']],
                columnDefs: [
                    { targets: [6], orderable: false }
                ],
                select: false,
                paging: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
                info: true,
                searching: false,
                language: {
                    lengthMenu: "Show _MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ records",
                    infoEmpty: "No records available",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No maintenance history records found"
                },
                dom: '<"dt-top-bar"l>t<"dt-bottom-bar"ip>',
                initComplete: function() {
                    $(tableId).css('width', '100%');
                    $('.dataTables_wrapper').css('width', '100%');
                },
                drawCallback: function() {
                    $(tableId).css('width', '100%');
                    $('.dataTables_wrapper').css('width', '100%');
                }
            });
        });

        function previewHistoryPhoto(url, title) {
            document.getElementById('historyPhotoModalImage').src = url;
            document.getElementById('historyPhotoModalDownload').href = url;
            document.getElementById('historyPhotoModalTitle').innerHTML = '<i class="ri-image-line me-1"></i> ' + (title || 'Photo Verification');
            const modal = new bootstrap.Modal(document.getElementById('historyPhotoModal'));
            modal.show();
        }
    </script>
    @endpush

    {{-- ═══════════════════════════════════════════════════════════════
         STYLES
    ═══════════════════════════════════════════════════════════════ --}}
    <style>
        /* ═══════════════════════════════════════════════════════════
           DATATABLES 100% CONTAINER WIDTH & NO OVERFLOW FIX
        ═══════════════════════════════════════════════════════════ */
        .dataTables_wrapper,
        #maintenanceHistoryTable_wrapper,
        .dt-premium-shell,
        .dt-premium-body,
        #maintenanceHistoryTable {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }

        #maintenanceHistoryTable {
            table-layout: fixed !important;
            border-collapse: separate;
        }

        #maintenanceHistoryTable td,
        #maintenanceHistoryTable th {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-wrap: break-word;
            box-sizing: border-box;
            vertical-align: middle;
        }

        /* Prevent multi-line wrapping in specific cells */
        #maintenanceHistoryTable td > div.component-cell {
            max-height: none;          /* stop the 60px clipping */
            overflow: visible;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .component-name {
            display: block;
            max-width: 100%;
            font-weight: 700;
            font-size: 0.88rem;
            color: #0f172a;
            line-height: 1.25;
        }
        .component-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            max-height: none;
            overflow: visible;
        }
        .component-brand {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            max-width: 150px;
            padding: 2px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .component-brand i {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        #maintenanceHistoryTable td > div.location-cell {
            max-height: none;          /* stop the 60px clipping */
            overflow: visible;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        .location-name {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
            font-weight: 700;
            font-size: 0.85rem;
            color: #0f172a;
            line-height: 1.25;
        }
        .location-name i {
            flex-shrink: 0;
            color: #ef4444;            /* red map pin */
            font-size: 0.95rem;
        }
        .location-name > span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .serial-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            max-width: 100%;
            padding: 2px 9px;
            border-radius: 6px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .serial-chip i {
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        /* Ensure toolbars fit 100% */
        .dt-top-bar,
        .dt-bottom-bar,
        .dataTables_info,
        .dataTables_paginate {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Prevent horizontal scroll on desktop, enable only on mobile */
        @media (min-width: 992px) {
            .dt-premium-body,
            .table-responsive,
            .dt-premium-shell,
            .dataTables_wrapper,
            #maintenanceHistoryTable_wrapper,
            .all-list-body,
            .card-body {
                overflow-x: hidden !important;
            }
            #maintenanceHistoryTable {
                table-layout: fixed !important;
                width: 100% !important;
            }
        }

        @media (max-width: 991.98px) {
            .dt-premium-body,
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            #maintenanceHistoryTable {
                table-layout: auto !important;
                min-width: 900px;
            }
        }
        .mini-icon-neutral { background: #f1f5f9; color: #64748b; }

        .mini-kpi-value {
            font-weight: 800;
            font-size: 1.05rem;
            margin: 0;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
            line-height: 1.1;
        }
        .mini-kpi-label {
            display: block;
            color: #64748b;
            font-weight: 600;
            font-size: 0.72rem;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        /* ═══════════════════════════════════════════════════════════
           SPEND CARD
        ═══════════════════════════════════════════════════════════ */
        .spend-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px !important;
            position: relative;
            overflow: hidden;
        }
        .spend-card::before {
            content: '';
            position: absolute;
            top: -40%; right: -20%;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .spend-icon-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            backdrop-filter: blur(6px);
            flex-shrink: 0;
        }
        .spend-label {
            color: rgba(255, 255, 255, 0.65);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.08em;
            font-size: 0.68rem;
            display: block;
        }
        .spend-value {
            font-weight: 800;
            margin: 2px 0 0;
            color: #ffffff;
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 1.35rem;
            letter-spacing: -0.01em;
        }
        .spend-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
        }

        /* ═══════════════════════════════════════════════════════════
           FILTER CARD
        ═══════════════════════════════════════════════════════════ */
        .filter-card {
            background: linear-gradient(180deg, #ffffff 0%, #fafcff 100%);
            border: 1px solid #e2e8f0 !important;
            border-radius: 16px !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            position: relative;
            overflow: hidden;
        }
        .filter-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
        }

        .filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eef2f7;
        }
        .filter-icon-badge {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #4f46e5;
            flex-shrink: 0;
        }
        .filter-title {
            font-weight: 700;
            font-size: 1rem;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .filter-subtitle {
            color: #64748b;
            font-size: 0.78rem;
        }
        .btn-clear-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-clear-filter:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
            transform: translateY(-1px);
        }

        .filter-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
        }
        .filter-label i {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .filter-select,
        .filter-input {
            height: 44px;
            border-radius: 10px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #f8fafc !important;
            font-size: 0.87rem;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s ease;
        }
        .filter-input[type="date"] {
            padding: 0 12px;
            line-height: 42px;
        }
        .filter-input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        .filter-input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }
        .filter-select:hover,
        .filter-input:hover {
            border-color: #cbd5e1 !important;
            background: #ffffff !important;
        }
        .filter-select:focus,
        .filter-input:focus {
            border-color: #6366f1 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
            outline: none !important;
        }

        .btn-filter-submit {
            width: 100%;
            height: 44px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow:
                0 2px 4px rgba(59, 130, 246, 0.25),
                0 6px 16px -4px rgba(59, 130, 246, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        .btn-filter-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, 0.35) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        .btn-filter-submit:hover::after {
            transform: translateX(100%);
        }
        .btn-filter-submit:hover {
            transform: translateY(-2px);
            box-shadow:
                0 4px 8px rgba(59, 130, 246, 0.3),
                0 12px 28px -6px rgba(59, 130, 246, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }
        .btn-filter-submit:active {
            transform: translateY(0);
        }

        /* ── Keyword Search ── */
        .keyword-search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .keyword-search-icon {
            position: absolute;
            left: 16px;
            font-size: 1.1rem;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 2;
        }
        .keyword-search-input {
            width: 100%;
            height: 46px;
            padding: 0 48px 0 46px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 0.88rem;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s ease;
            outline: none;
        }
        .keyword-search-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .keyword-search-input:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        .keyword-search-input:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }
        .keyword-search-wrapper:focus-within .keyword-search-icon {
            color: #6366f1;
        }
        .keyword-search-clear {
            position: absolute;
            right: 12px;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .keyword-search-clear:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .filter-card {
            position: relative;
            z-index: 1;
        }

        /* ═══════════════════════════════════════════════════════════
           SELECT2 OVERRIDE
        ═══════════════════════════════════════════════════════════ */
        .select2-container--default .select2-selection--single {
            height: 44px !important;
            padding: 8px 12px !important;
            border-radius: 10px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #f8fafc !important;
            transition: all 0.2s ease;
        }
        .select2-container--default .select2-selection--single:hover {
            border-color: #cbd5e1 !important;
            background: #ffffff !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            line-height: 26px !important;
            padding-left: 4px !important;
            font-weight: 500;
            font-size: 0.87rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #94a3b8 transparent transparent !important;
            border-width: 5px 4px 0 !important;
        }
        .select2-dropdown {
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1) !important;
            overflow: hidden;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 10px !important;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            height: 38px;
            border-radius: 8px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #ffffff !important;
            padding: 6px 12px !important;
            font-size: 0.85rem !important;
            outline: none !important;
            transition: all 0.2s ease;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }
        .select2-container--default .select2-results__option {
            padding: 10px 14px !important;
            font-size: 0.87rem !important;
            transition: background 0.15s ease;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background: #eef2ff !important;
            color: #4f46e5 !important;
            font-weight: 600;
        }
        .select2-results__message {
            padding: 12px !important;
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* ═══════════════════════════════════════════════════════════
           DATATABLE PREMIUM POLISH
        ═══════════════════════════════════════════════════════════ */
        .dt-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #f1f5f9;
        }
        .dt-top-bar .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }
        .dt-top-bar .dataTables_length select {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px 30px 6px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            background-color: #f8fafc;
            background-position: right 8px center;
            min-width: 80px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .dt-top-bar .dataTables_length select:hover {
            border-color: #cbd5e1;
            background-color: #ffffff;
        }
        .dt-top-bar .dataTables_length select:focus {
            border-color: #6366f1;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
            outline: none;
        }

        /* DataTable premium table */
        #maintenanceHistoryTable {
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
            font-size: 0.87rem;
            color: #1e293b;
        }
        #maintenanceHistoryTable thead th {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: #475569;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: 800;
            padding: 14px 16px;
            border: none;
            border-bottom: 2px solid #eef2f7;
            white-space: nowrap;
            position: relative;
            vertical-align: middle;
        }
        #maintenanceHistoryTable thead th.sorting,
        #maintenanceHistoryTable thead th.sorting_asc,
        #maintenanceHistoryTable thead th.sorting_desc {
            cursor: pointer;
            user-select: none;
            transition: color 0.2s ease;
        }
        #maintenanceHistoryTable thead th.sorting:hover,
        #maintenanceHistoryTable thead th.sorting_asc,
        #maintenanceHistoryTable thead th.sorting_desc {
            color: #4f46e5;
        }
        #maintenanceHistoryTable thead th.sorting::after,
        #maintenanceHistoryTable thead th.sorting_asc::after,
        #maintenanceHistoryTable thead th.sorting_desc::after {
            display: inline-block;
            margin-left: 6px;
            font-family: 'remixicon' !important;
            font-size: 12px;
            vertical-align: middle;
            transition: all 0.2s ease;
        }
        #maintenanceHistoryTable thead th.sorting::after       { content: '\ea4c'; opacity: .35; }
        #maintenanceHistoryTable thead th.sorting_asc::after   { content: '\ea4e'; color: #4f46e5; opacity: 1; }
        #maintenanceHistoryTable thead th.sorting_desc::after  { content: '\ea4d'; color: #4f46e5; opacity: 1; }

        #maintenanceHistoryTable tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #1e293b;
            font-weight: 500;
            font-size: 0.87rem;
            transition: background 0.15s ease;
        }
        #maintenanceHistoryTable tbody tr {
            transition: all 0.18s ease;
            position: relative;
        }
        #maintenanceHistoryTable tbody tr:hover {
            background: linear-gradient(90deg, #f8fafc 0%, #fbfdff 100%);
        }
        #maintenanceHistoryTable tbody tr:hover td {
            color: #0f172a;
        }
        #maintenanceHistoryTable tbody tr:last-child td {
            border-bottom: none;
        }

        #maintenanceHistoryTable tbody tr:nth-child(even) {
            background: #fcfdfe;
        }
        #maintenanceHistoryTable tbody tr:nth-child(even):hover {
            background: linear-gradient(90deg, #f8fafc 0%, #fbfdff 100%);
        }

        #maintenanceHistoryTable tbody td.text-end,
        #maintenanceHistoryTable tbody td.font-monospace {
            font-variant-numeric: tabular-nums;
        }

        /* ═══════════════════════════════════════════════════════════
           DATATABLE BOTTOM BAR + PAGINATION
        ═══════════════════════════════════════════════════════════ */
        .dt-bottom-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 18px;
            border-top: 1px solid #f1f5f9;
            background: linear-gradient(0deg, #ffffff 0%, #f8fafc 100%);
        }
        .dt-bottom-bar .dataTables_info {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 600;
            padding: 0;
        }
        .dt-bottom-bar .dataTables_paginate {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .dt-bottom-bar .dataTables_paginate .paginate_button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 12px !important;
            margin: 0 !important;
            border-radius: 10px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #64748b !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            text-decoration: none !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .dt-bottom-bar .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
            background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%) !important;
            border-color: #a5b4fc !important;
            color: #4338ca !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(99, 102, 241, 0.18);
        }
        .dt-bottom-bar .dataTables_paginate .paginate_button.current,
        .dt-bottom-bar .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%) !important;
            border-color: #4f46e5 !important;
            color: #ffffff !important;
            box-shadow:
                0 6px 16px rgba(99, 102, 241, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            transform: translateY(-1px);
        }
        .dt-bottom-bar .dataTables_paginate .paginate_button.disabled,
        .dt-bottom-bar .dataTables_paginate .paginate_button.disabled:hover {
            background: #f8fafc !important;
            border-color: #f1f5f9 !important;
            color: #cbd5e1 !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
            opacity: 1;
        }
        .dt-bottom-bar .dataTables_paginate .paginate_button.previous,
        .dt-bottom-bar .dataTables_paginate .paginate_button.next {
            font-weight: 600 !important;
        }
        .dt-bottom-bar .dataTables_paginate .ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            color: #94a3b8;
            font-weight: 700;
        }

        /* ═══════════════════════════════════════════════════════════
           ACTION BUTTON
        ═══════════════════════════════════════════════════════════ */
        .action-view-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
            white-space: nowrap;
            min-width: 70px;
            justify-content: center;
        }
        .action-view-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, 0.35) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        .action-view-btn:hover::after { transform: translateX(100%); }
        .action-view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4) !important;
            color: #ffffff;
        }
        .action-view-btn:active { transform: translateY(0); }

        @media (max-width: 1199.98px) {
            .action-view-btn span {
                display: none !important;
            }
            .action-view-btn {
                min-width: 38px;
                padding: 0.375rem !important;
            }
        }

        .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }

        /* ═══════════════════════════════════════════════════════════
           RESPONSIVE
        ═══════════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .filter-header { flex-direction: column; align-items: flex-start; }
            .dt-top-bar,
            .dt-bottom-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .dt-bottom-bar .dataTables_paginate {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
            }
            .dt-bottom-bar .dataTables_paginate .paginate_button {
                min-width: 32px;
                height: 32px;
                padding: 0 8px !important;
                font-size: 0.75rem !important;
            }
            .keyword-search-input {
                font-size: 0.82rem;
                padding-left: 40px;
                padding-right: 40px;
            }
            .spend-value { font-size: 1.1rem; }
            
            /* Table columns responsive adjustments */
            #maintenanceHistoryTable thead th {
                font-size: 0.65rem;
                padding: 10px 8px;
            }
            #maintenanceHistoryTable tbody td {
                font-size: 0.8rem;
                padding: 10px 8px;
            }
        }

        @media (max-width: 576px) {
            .mini-kpi-card .card-body {
                padding: 0.75rem !important;
            }
            .filter-card .card-body {
                padding: 1rem !important;
            }
            .btn-filter-submit {
                height: 40px;
                font-size: 0.9rem;
            }
        }

        /* ═══════════════════════════════════════════════════════════
           PRINT
        ═══════════════════════════════════════════════════════════ */
        @media print {
            .filter-card,
            .mini-kpi-card,
            .spend-card,
            .btn-clear-filter,
            .dt-top-bar,
            .dt-bottom-bar {
                display: none !important;
            }
            .action-view-btn { box-shadow: none; background: #f1f5f9; color: #334155; }
        }
    </style>
</x-layouts.auth>
