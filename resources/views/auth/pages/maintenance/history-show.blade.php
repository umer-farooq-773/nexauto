<x-layouts.auth page-title="{{ __('Maintenance History Details') }}" sub-title="{{ __('Component Servicing, Serial Audit Trail & Asset History') }}">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">

    @php
        $maint = $item->maintenance;
        $veh = $maint?->vehicle;
        $driverNames = [];
        if ($veh && $veh->drivers) {
            $driverNames = $veh->drivers->pluck('full_name')->filter()->toArray();
        }
        $isLocked = (string)($maint?->payment_status ?? '') === '2';
    @endphp

    @if($isLocked)
    <div class="alert alert-success d-flex align-items-center gap-3 rounded-4 shadow-sm border-0 mb-4 p-3 bg-success-subtle text-success-emphasis no-print">
        <i class="ri-lock-2-fill fs-3 text-success"></i>
        <div>
            <h6 class="fw-bold mb-0">{{ __('Record Locked (Payment Processed)') }}</h6>
            <small>{{ __('Payment has been processed for this maintenance record (#:ref). All modifications, item additions, and deletions are locked.', ['ref' => $maint?->reference_no ?: ($maint?->id ? substr($maint->id, 0, 8) : '')]) }}</small>
        </div>
    </div>
    @endif

    {{-- Top Action Toolbar --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <a href="{{ route('maintenance.history') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                    <i class="ri-arrow-left-line"></i>
                    <span>{{ __('Back to Maintenance History') }}</span>
                </a>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if(!$isLocked)
                        <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="openEditHistoryItemModal({{ json_encode($item) }})">
                            <i class="ri-edit-line"></i>
                            <span>{{ __('Edit Item') }}</span>
                        </button>

                        @if($maint)
                            <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addHistoryItemModal">
                                <i class="ri-add-line"></i>
                                <span>{{ __('Add Another Item') }}</span>
                            </button>
                        @endif

                        @if($maint)
                            <form action="{{ route('maintenance.items.destroy', [$maint->id, $item->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this maintenance history item?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="history">
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                                    <i class="ri-delete-bin-line"></i>
                                    <span>{{ __('Delete Item') }}</span>
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-monospace d-inline-flex align-items-center gap-1">
                            <i class="ri-lock-2-fill me-1"></i> {{ __('Locked (Payment Completed)') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Metric Summary Row --}}
    <div class="row g-3 mb-4 print-content">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 kpi-summary-card">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="kpi-icon-tile bg-primary-subtle text-primary">
                        <i class="ri-tools-line"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">{{ __('Component') }}</small>
                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 160px;">{{ $item->item_name ?: ucfirst($item->category) }}</h6>
                        <span class="badge bg-light text-secondary border font-monospace mt-1" style="font-size: 0.7rem;">{{ ucfirst($item->category) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 kpi-summary-card">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="kpi-icon-tile bg-info-subtle text-info">
                        <i class="ri-truck-line"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">{{ __('Asset / Vehicle') }}</small>
                        <h6 class="fw-bold text-dark mb-0">{{ $veh?->truck_no ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $veh?->fleet_no ? "Fleet: {$veh->fleet_no}" : '–' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 kpi-summary-card">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="kpi-icon-tile bg-warning-subtle text-warning">
                        <i class="ri-restart-line"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">{{ __('Action Performed') }}</small>
                        <div class="mt-1">{!! $item->action_badge !!}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 kpi-summary-card">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="kpi-icon-tile bg-success-subtle text-success">
                        <i class="ri-money-dollar-circle-line"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">{{ __('Item Cost') }}</small>
                        <h5 class="fw-bold text-success font-monospace mb-0">
                            {{ $item->cost ? number_format((float)$item->cost, 2) : '–' }}
                        </h5>
                        @if($item->unit_cost)
                            <small class="text-muted">({{ $item->quantity ?: 1 }} &times; {{ number_format((float)$item->unit_cost, 2) }})</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="row g-4 print-content">
        {{-- ================= LEFT COLUMN ================= --}}
        <div class="col-lg-8">

            {{-- Card 1: Component Specification & Parameters --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon-tile text-primary bg-primary-subtle">
                                <i class="ri-function-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ __('Component Specification & Details') }}</h6>
                                <small class="text-muted">{{ __('Technical specifications, parameters, and part measurements') }}</small>
                            </div>
                        </div>
                        <div>{!! $item->category_badge !!}</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-price-tag-3-line me-1"></i> {{ __('Component Name / Part') }}</label>
                                <div class="fw-bold text-dark fs-6">{{ $item->item_name ?: ucfirst($item->category) }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-building-line me-1"></i> {{ __('Manufacturer / Brand') }}</label>
                                <div class="fw-semibold text-dark">{{ $item->brand ?: '–' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-shapes-line me-1"></i> {{ __('Model / Pattern') }}</label>
                                <div class="fw-semibold text-dark">{{ $item->model ?: '–' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-ruler-2-line me-1"></i> {{ __('Size / Rating / Voltage') }}</label>
                                <div class="fw-semibold text-dark">{{ $item->size ?: '–' }}</div>
                            </div>
                        </div>

                        {{-- Tyre Specific Fields --}}
                        @if($item->category === 'tyre' || $item->axle || $item->position || $item->tread_depth_mm)
                            <div class="col-md-4">
                                <div class="info-group p-3 rounded-3 bg-light border">
                                    <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-compass-3-line me-1"></i> {{ __('Axle Location') }}</label>
                                    <div class="fw-semibold text-dark">{{ $item->axle ?: '–' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group p-3 rounded-3 bg-light border">
                                    <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-crosshair-2-line me-1"></i> {{ __('Position') }}</label>
                                    <div class="fw-semibold text-dark">{{ $item->position ?: '–' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group p-3 rounded-3 bg-light border">
                                    <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-ruler-line me-1"></i> {{ __('Tread Depth') }}</label>
                                    <div class="fw-bold font-monospace text-primary">
                                        {{ $item->tread_depth_mm ? "{$item->tread_depth_mm} mm" : '–' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-map-pin-2-line me-1"></i> {{ __('Location on Vehicle') }}</label>
                                <div class="fw-semibold text-dark">{{ $item->location ?: '–' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-stack-line me-1"></i> {{ __('Quantity') }}</label>
                                <div class="fw-bold text-dark fs-6">{{ $item->quantity ?: 1 }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-price-tag-2-line me-1"></i> {{ __('Unit Cost') }}</label>
                                <div class="fw-semibold font-monospace text-dark">
                                    {{ $item->unit_cost ? number_format((float)$item->unit_cost, 2) : '–' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-money-dollar-circle-line me-1"></i> {{ __('Total Cost') }}</label>
                                <div class="fw-bold font-monospace text-success fs-6">
                                    {{ $item->cost ? number_format((float)$item->cost, 2) : '–' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Serial Number Audit Trail & Security Control --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="section-icon-tile text-success bg-success-subtle">
                            <i class="ri-barcode-box-line"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">{{ __('Serial Number Audit Trail & Theft Control') }}</h6>
                            <small class="text-muted">{{ __('Physical identification markers tracked against vehicle asset registry') }}</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-success-subtle border-success-subtle">
                                <label class="text-success small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1">
                                    <i class="ri-checkbox-circle-line"></i> {{ __('Newly Installed Component Serial') }}
                                </label>
                                <div class="font-monospace fs-5 fw-bold text-dark">
                                    {{ $item->new_serial_no ?: '–' }}
                                </div>
                                <small class="text-muted">{{ __('Currently active and tracked in vehicle register') }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border bg-danger-subtle border-danger-subtle">
                                <label class="text-danger small fw-bold text-uppercase d-flex align-items-center gap-1 mb-1">
                                    <i class="ri-indeterminate-circle-line"></i> {{ __('Removed / Old Component Serial') }}
                                </label>
                                <div class="font-monospace fs-5 fw-bold text-dark">
                                    {{ $item->old_serial_no ?: '–' }}
                                </div>
                                <small class="text-muted">{{ __('Decommissioned / archived in component history') }}</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-group p-3 rounded-3 bg-light border">
                                <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-question-line me-1"></i> {{ __('Reason for Replacement / Servicing') }}</label>
                                <div class="fw-semibold text-dark">{{ $item->reason ?: 'Scheduled maintenance / replacement' }}</div>
                            </div>
                        </div>

                        @if($item->notes)
                            <div class="col-12">
                                <div class="info-group p-3 rounded-3 bg-light border">
                                    <label class="info-label text-muted small fw-semibold text-uppercase"><i class="ri-file-text-line me-1"></i> {{ __('Technician Notes & Comments') }}</label>
                                    <div class="text-dark">{{ $item->notes }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card 3: Live Vehicle Component Register (if vehicle present) --}}
            @if($veh)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon-tile text-cyan bg-cyan-subtle">
                                    <i class="ri-shield-keyhole-line"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $veh->fleet_no }} – {{ __('Current Active Vehicle Components') }}</h6>
                                    <small class="text-muted">{{ __('Components currently installed and operational on this vehicle asset') }}</small>
                                </div>
                            </div>
                        </div>

                        @if($veh->activeComponents && $veh->activeComponents->count())
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light text-dark fw-bold">
                                        <tr>
                                            <th>{{ __('Component') }}</th>
                                            <th>{{ __('Location') }}</th>
                                            <th>{{ __('Serial Number') }}</th>
                                            <th>{{ __('Brand / Spec') }}</th>
                                            <th>{{ __('Installed Date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($veh->activeComponents as $comp)
                                            <tr class="{{ $comp->serial_no === $item->new_serial_no ? 'table-success' : '' }}">
                                                <td>{!! $comp->category_badge !!}</td>
                                                <td class="fw-semibold text-dark">{{ $comp->location }}</td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                                                        <i class="ri-barcode-line me-1"></i>{{ $comp->serial_no }}
                                                    </span>
                                                    @if($comp->serial_no === $item->new_serial_no)
                                                        <span class="badge bg-success ms-1"><i class="ri-check-double-line"></i> This Item</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted">{{ $comp->brand }} {{ $comp->size ?: $comp->model }}</td>
                                                <td>{{ $comp->installed_date ? $comp->installed_date->format('d/m/Y') : '–' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3 bg-light rounded-3 text-muted small">
                                <i class="ri-information-line me-1"></i> {{ __('No active components registered yet for this vehicle.') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- ================= RIGHT COLUMN ================= --}}
        <div class="col-lg-4">

            {{-- Card 1: Verification File / Document / Photo --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="section-icon-tile text-danger bg-danger-subtle">
                            <i class="ri-attachment-2"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">{{ __('Item Verification File') }}</h6>
                            <small class="text-muted">{{ __('Physical proof of part installation, invoice or photo') }}</small>
                        </div>
                    </div>

                    @if($item->photo_path)
                        @php
                            $ext = strtolower(pathinfo($item->photo_path, PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                            $isPdf = $ext === 'pdf';
                            $fileUrl = $item->photo_url;
                            $fileName = basename($item->photo_path);
                        @endphp

                        @if($isImg)
                            <div class="photo-preview-box rounded-3 overflow-hidden border position-relative text-center bg-black">
                                <img src="{{ $fileUrl }}" alt="{{ $item->item_name ?: 'Verification Photo' }}" class="img-fluid" style="max-height: 240px; width: 100%; object-fit: contain; cursor: pointer;" onclick="previewHistoryShowPhoto('{{ $fileUrl }}', '{{ addslashes($item->item_name ?: ucfirst($item->category)) }}')">
                                <div class="photo-hover-overlay" onclick="previewHistoryShowPhoto('{{ $fileUrl }}', '{{ addslashes($item->item_name ?: ucfirst($item->category)) }}')">
                                    <i class="ri-zoom-in-line fs-3 text-white"></i>
                                    <span class="text-white small fw-semibold">{{ __('Click to enlarge') }}</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="previewHistoryShowPhoto('{{ $fileUrl }}', '{{ addslashes($item->item_name ?: ucfirst($item->category)) }}')">
                                    <i class="ri-eye-line me-1"></i> {{ __('View Fullscreen') }}
                                </button>
                                <a href="{{ $fileUrl }}" download target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                                    <i class="ri-download-2-line me-1"></i> {{ __('Download') }}
                                </a>
                            </div>
                        @elseif($isPdf)
                            <div class="p-4 rounded-3 bg-light border text-center">
                                <i class="ri-file-pdf-2-line text-danger" style="font-size: 3.2rem;"></i>
                                <div class="fw-bold text-dark text-truncate mt-2" title="{{ $fileName }}">{{ $fileName }}</div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace mt-1">PDF Document</span>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3">
                                        <i class="ri-external-link-line me-1"></i> {{ __('Open PDF') }}
                                    </a>
                                    <a href="{{ $fileUrl }}" download target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                                        <i class="ri-download-2-line me-1"></i> {{ __('Download') }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-3 bg-light border text-center">
                                <i class="ri-file-text-line text-primary" style="font-size: 3.2rem;"></i>
                                <div class="fw-bold text-dark text-truncate mt-2" title="{{ $fileName }}">{{ $fileName }}</div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace mt-1">{{ strtoupper($ext) }} File</span>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <a href="{{ $fileUrl }}" download target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="ri-download-2-line me-1"></i> {{ __('Download File') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 px-3 bg-light rounded-3 border border-dashed">
                            <i class="ri-attachment-line text-muted" style="font-size: 2.2rem;"></i>
                            <div class="text-muted small mt-1">{{ __('No verification file or photo attached for this item.') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card 2: Vehicle & Operator Profile --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="section-icon-tile text-primary bg-primary-subtle">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">{{ __('Vehicle Asset') }}</h6>
                            <small class="text-muted">{{ __('Vehicle details & driver profile') }}</small>
                        </div>
                    </div>

                    @if($veh)
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Truck No') }}</span>
                                <span class="fw-bold text-dark">{{ $veh->truck_no }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Fleet No') }}</span>
                                <span class="badge bg-primary-subtle text-primary font-monospace">{{ $veh->fleet_no ?: '–' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Make & Model') }}</span>
                                <span class="fw-semibold text-dark">{{ $veh->vehicle_make_model?->name ?? '–' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Assigned Driver') }}</span>
                                <span class="fw-semibold text-dark">{{ !empty($driverNames) ? implode(', ', $driverNames) : '–' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Service Date') }}</span>
                                <span class="fw-semibold text-dark">
                                    {{ $maint?->maintenance_date ? \Carbon\Carbon::parse($maint->maintenance_date)->format('d M Y') : $item->created_at->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <span class="text-muted small">–</span>
                    @endif
                </div>
            </div>

            {{-- Card 3: Associated Maintenance Record --}}
            @if($maint)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="section-icon-tile text-secondary bg-secondary-subtle">
                                <i class="ri-file-list-3-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ __('Maintenance Record Reference') }}</h6>
                                <small class="text-muted">{{ __('Parent maintenance event log') }}</small>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Log Reference') }}</span>
                                <span class="font-monospace fw-bold text-dark">{{ $maint->reference_no ?: ('#MT-' . substr($maint->id, 0, 8)) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                <span class="text-muted small fw-semibold">{{ __('Service Provider') }}</span>
                                <span class="fw-semibold text-dark">{{ $maint->service_provider ?: '–' }}</span>
                            </div>
                            @if($maint->next_maintenance_date)
                                <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light">
                                    <span class="text-muted small fw-semibold">{{ __('Next Due Date') }}</span>
                                    <span class="fw-semibold text-dark">{{ $maint->next_maintenance_date }}</span>
                                </div>
                            @endif
                            <a href="{{ route('maintenance.show', $maint->id) }}" class="btn btn-outline-primary rounded-pill btn-sm w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="ri-external-link-line"></i>
                                <span>{{ __('Open Maintenance Record') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Card 4: Sibling Items Serviced on Same Date --}}
            @if($maint && $maint->items && $maint->items->count() > 1)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="section-icon-tile text-info bg-info-subtle">
                                <i class="ri-git-merge-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ __('Other Items in Same Service') }}</h6>
                                <small class="text-muted">{{ $maint->items->count() - 1 }} {{ __('other components recorded') }}</small>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            @foreach($maint->items as $sibling)
                                @if($sibling->id !== $item->id)
                                    <a href="{{ route('maintenance.history.show', $sibling->id) }}" class="text-decoration-none p-2 rounded-3 border bg-light d-flex justify-content-between align-items-center sibling-item-link">
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $sibling->item_name ?: ucfirst($sibling->category) }}</div>
                                            <small class="text-muted">{{ $sibling->location ?: ucfirst($sibling->action) }}</small>
                                        </div>
                                        <div class="text-end">
                                            {!! $sibling->category_badge !!}
                                            <i class="ri-arrow-right-s-line text-muted ms-1"></i>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Card 5: Maintenance Event Attachments & Invoices --}}
            @if($maint && $maint->maintenanceFiles && $maint->maintenanceFiles->count())
                @php
                    $maintDocs = $maint->maintenanceFiles->filter(function($f) {
                        return !str_contains($f->file_type ?? '', 'image');
                    });
                    $maintImages = $maint->maintenanceFiles->filter(function($f) {
                        return str_contains($f->file_type ?? '', 'image');
                    });
                @endphp
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="section-icon-tile text-primary bg-primary-subtle">
                                <i class="ri-folder-open-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ __('Service Event Files') }}</h6>
                                <small class="text-muted">{{ __('Invoices, inspection reports & job cards') }} ({{ $maint->maintenanceFiles->count() }})</small>
                            </div>
                        </div>

                        @if($maintDocs->count())
                            <div class="mb-3">
                                <small class="fw-bold text-dark d-block mb-2"><i class="ri-file-text-line me-1"></i>{{ __('Documents') }} ({{ $maintDocs->count() }})</small>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($maintDocs as $mFile)
                                        @php
                                            $isMDocPdf = str_contains($mFile->file_type ?? '', 'pdf') || str_ends_with(strtolower($mFile->path), '.pdf');
                                        @endphp
                                        <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <i class="{{ $isMDocPdf ? 'ri-file-pdf-2-line text-danger' : 'ri-file-text-line text-primary' }} fs-5"></i>
                                                <span class="text-truncate small fw-semibold text-dark" style="max-width: 170px;" title="{{ $mFile->name }}">{{ $mFile->name }}</span>
                                            </div>
                                            <a href="{{ Storage::url($mFile->path) }}" target="_blank" download class="btn btn-sm btn-light border rounded-pill py-0 px-2" title="{{ __('Download') }}">
                                                <i class="ri-download-2-line"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($maintImages->count())
                            <div>
                                <small class="fw-bold text-dark d-block mb-2"><i class="ri-image-line me-1"></i>{{ __('Service Photos') }} ({{ $maintImages->count() }})</small>
                                <div class="row g-2">
                                    @foreach($maintImages as $mFile)
                                        <div class="col-6">
                                            <div class="rounded-3 overflow-hidden border bg-light text-center p-1">
                                                <img src="{{ Storage::url($mFile->path) }}" alt="{{ $mFile->name }}" class="img-fluid rounded" style="height: 70px; width: 100%; object-fit: cover; cursor: pointer;" onclick="previewHistoryShowPhoto('{{ Storage::url($mFile->path) }}', '{{ addslashes($mFile->name) }}')">
                                                <small class="text-muted text-truncate d-block mt-1" style="font-size: 0.68rem;" title="{{ $mFile->name }}">{{ $mFile->name }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ============================================================
         MODAL: EDIT THIS MAINTENANCE HISTORY ITEM
         ============================================================ --}}
    @if(!$isLocked)
    <div class="modal fade" id="editHistoryItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon-tile text-primary bg-primary-subtle" style="width: 38px; height: 38px;">
                            <i class="ri-edit-line"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">{{ __('Edit Maintenance History Item') }}</h5>
                            <small class="text-muted">{{ __('Update component parameters, serial numbers, or inspection notes') }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                @if($maint)
                    <form id="editHistoryItemForm" action="{{ route('maintenance.items.update', ['maintenance' => $maint->id, 'item' => $item->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="history_show">

                        <div class="modal-body p-4">
                            {{-- Category Selection --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="ri-function-line text-primary me-1"></i> {{ __('Component Category') }} <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    @foreach(['tyre' => ['ri-disc-line', 'text-primary', 'Tyre'], 'battery' => ['ri-battery-2-charge-line', 'text-warning', 'Battery'], 'telematics' => ['ri-radar-line', 'text-info', 'Telematics'], 'brake' => ['ri-stop-circle-line', 'text-danger', 'Brakes'], 'engine' => ['ri-settings-4-line', 'text-secondary', 'Engine'], 'general' => ['ri-tools-line', 'text-dark', 'General']] as $catKey => $catMeta)
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                                <input type="radio" name="category" id="modal-cat-{{ $catKey }}" value="{{ $catKey }}" {{ $item->category === $catKey ? 'checked' : '' }} onchange="switchHistoryEditCategory('{{ $catKey }}')">
                                                <div class="category-icon {{ $catMeta[1] }}"><i class="{{ $catMeta[0] }}"></i></div>
                                                <div class="category-name">{{ $catMeta[2] }}</div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <hr class="opacity-10 my-3">

                            {{-- Dynamic Fields Container --}}
                            <div id="history-edit-category-fields">
                                {{-- TYRE FIELDS --}}
                                <div class="category-fields-group {{ $item->category === 'tyre' ? '' : 'd-none' }}" id="hedit-fields-tyre">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action" id="hedit-tyre-action" class="form-select">
                                                <option value="replace" {{ $item->action === 'replace' ? 'selected' : '' }}>Replace</option>
                                                <option value="rotate" {{ $item->action === 'rotate' ? 'selected' : '' }}>Rotate</option>
                                                <option value="repair" {{ $item->action === 'repair' ? 'selected' : '' }}>Repair</option>
                                                <option value="inspect" {{ $item->action === 'inspect' ? 'selected' : '' }}>Inspect / Pressure Check</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Axle') }} <span class="text-danger">*</span></label>
                                            <select name="axle" id="hedit-tyre-axle" class="form-select">
                                                <option value="Front Axle" {{ $item->axle === 'Front Axle' ? 'selected' : '' }}>Front Axle</option>
                                                <option value="Rear Axle 1" {{ $item->axle === 'Rear Axle 1' ? 'selected' : '' }}>Rear Axle 1</option>
                                                <option value="Rear Axle 2" {{ $item->axle === 'Rear Axle 2' ? 'selected' : '' }}>Rear Axle 2</option>
                                                <option value="Rear Axle 3" {{ $item->axle === 'Rear Axle 3' ? 'selected' : '' }}>Rear Axle 3</option>
                                                <option value="Trailer Axle 1" {{ $item->axle === 'Trailer Axle 1' ? 'selected' : '' }}>Trailer Axle 1</option>
                                                <option value="Trailer Axle 2" {{ $item->axle === 'Trailer Axle 2' ? 'selected' : '' }}>Trailer Axle 2</option>
                                                <option value="Spare Tyre" {{ $item->axle === 'Spare Tyre' ? 'selected' : '' }}>Spare Tyre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Position') }} <span class="text-danger">*</span></label>
                                            <select name="position" id="hedit-tyre-position" class="form-select">
                                                <option value="Left Outer" {{ $item->position === 'Left Outer' ? 'selected' : '' }}>Left Outer</option>
                                                <option value="Left Inner" {{ $item->position === 'Left Inner' ? 'selected' : '' }}>Left Inner</option>
                                                <option value="Right Outer" {{ $item->position === 'Right Outer' ? 'selected' : '' }}>Right Outer</option>
                                                <option value="Right Inner" {{ $item->position === 'Right Inner' ? 'selected' : '' }}>Right Inner</option>
                                                <option value="Front Left" {{ $item->position === 'Front Left' ? 'selected' : '' }}>Front Left</option>
                                                <option value="Front Right" {{ $item->position === 'Front Right' ? 'selected' : '' }}>Front Right</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Tyre Size') }}</label>
                                            <input type="text" name="size" id="hedit-tyre-size" class="form-control" value="{{ $item->size }}" placeholder="e.g. 24R18.22 or 315/80 R22.5">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Brand') }}</label>
                                            <select name="brand" id="hedit-tyre-brand" class="form-select">
                                                @foreach(['Michelin', 'Bridgestone', 'Goodyear', 'Continental', 'Pirelli', 'Hankook', 'Dunlop', 'Triangle', 'Other'] as $b)
                                                    <option value="{{ $b }}" {{ $item->brand === $b ? 'selected' : '' }}>{{ $b }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Pattern / Model') }}</label>
                                            <input type="text" name="model" id="hedit-tyre-model" class="form-control" value="{{ $item->model }}" placeholder="e.g. X Multi D / R150">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Tyre ID / Serial') }}</label>
                                            <input type="text" name="old_serial_no" id="hedit-tyre-old-serial" class="form-control font-monospace" value="{{ $item->old_serial_no }}" placeholder="e.g. TYR-000457">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Tyre ID / Serial') }}</label>
                                            <input type="text" name="new_serial_no" id="hedit-tyre-new-serial" class="form-control font-monospace" value="{{ $item->new_serial_no }}" placeholder="e.g. TYR-000892">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Tread Depth (mm)') }}</label>
                                            <input type="number" step="0.1" name="tread_depth_mm" id="hedit-tyre-tread" class="form-control font-monospace" value="{{ $item->tread_depth_mm }}" placeholder="e.g. 8-12 mm (max 1000)" max="1000">
                                            <small class="text-muted">{{ __('Typical new tyre: 8-12mm. Max allowed: 1000mm') }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Reason for Replacement') }}</label>
                                            <select name="reason" id="hedit-tyre-reason" class="form-select">
                                                @foreach(['Worn', 'Puncture / Damage', 'Sidewall Bulge', 'Blowout', 'Uneven Wear', 'Scheduled Replacement'] as $r)
                                                    <option value="{{ $r }}" {{ $item->reason === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- BATTERY FIELDS --}}
                                <div class="category-fields-group {{ $item->category === 'battery' ? '' : 'd-none' }}" id="hedit-fields-battery">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Battery Position') }} <span class="text-danger">*</span></label>
                                            <select name="location_battery" id="hedit-battery-location" class="form-select">
                                                <option value="Main Battery 1" {{ $item->location === 'Main Battery 1' ? 'selected' : '' }}>Main Battery 1</option>
                                                <option value="Main Battery 2" {{ $item->location === 'Main Battery 2' ? 'selected' : '' }}>Main Battery 2</option>
                                                <option value="Auxiliary Battery" {{ $item->location === 'Auxiliary Battery' ? 'selected' : '' }}>Auxiliary Battery</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action_battery" id="hedit-battery-action" class="form-select">
                                                <option value="replace" {{ $item->action === 'replace' ? 'selected' : '' }}>Replace</option>
                                                <option value="recharge" {{ $item->action === 'recharge' ? 'selected' : '' }}>Recharge</option>
                                                <option value="inspect" {{ $item->action === 'inspect' ? 'selected' : '' }}>Test / Inspect</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Battery Serial Number') }}</label>
                                            <input type="text" name="old_serial_no_battery" id="hedit-battery-old-serial" class="form-control font-monospace" value="{{ $item->old_serial_no }}" placeholder="e.g. BAT-00124">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Battery Serial Number') }}</label>
                                            <input type="text" name="new_serial_no_battery" id="hedit-battery-new-serial" class="form-control font-monospace" value="{{ $item->new_serial_no }}" placeholder="e.g. BAT-00876">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Manufacturer / Brand') }}</label>
                                            <input type="text" name="brand_battery" id="hedit-battery-brand" class="form-control" value="{{ $item->brand }}" placeholder="e.g. Exide, Bosch, Varta">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Battery Rating / Voltage') }}</label>
                                            <input type="text" name="size_battery" id="hedit-battery-size" class="form-control" value="{{ $item->size }}" placeholder="e.g. 12V 180Ah / 24V">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <select name="reason_battery" id="hedit-battery-reason" class="form-select">
                                                @foreach(['Failed', 'Weak / Low Voltage', 'Dead Cell', 'Scheduled Replacement'] as $r)
                                                    <option value="{{ $r }}" {{ $item->reason === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- TELEMATICS FIELDS --}}
                                <div class="category-fields-group {{ $item->category === 'telematics' ? '' : 'd-none' }}" id="hedit-fields-telematics">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Equipment Type') }} <span class="text-danger">*</span></label>
                                            <select name="item_name_telematics" id="hedit-telematics-item-name" class="form-select">
                                                @foreach(['GPS Tracker', 'Fuel Level Sensor (FLS)', 'Dashcam / Camera', 'Temperature Sensor', 'Panic Button'] as $eq)
                                                    <option value="{{ $eq }}" {{ $item->item_name === $eq ? 'selected' : '' }}>{{ $eq }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action_telematics" id="hedit-telematics-action" class="form-select">
                                                <option value="replace" {{ $item->action === 'replace' ? 'selected' : '' }}>Replace</option>
                                                <option value="install" {{ $item->action === 'install' ? 'selected' : '' }}>Install New</option>
                                                <option value="calibrate" {{ $item->action === 'calibrate' ? 'selected' : '' }}>Calibrate</option>
                                                <option value="repair" {{ $item->action === 'repair' ? 'selected' : '' }}>Repair / Check</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Location on Vehicle') }}</label>
                                            <select name="location_telematics" id="hedit-telematics-location" class="form-select">
                                                @foreach(['Cab / Dashboard', 'Fuel Tank Left', 'Fuel Tank Right', 'Windshield', 'Cargo Area'] as $loc)
                                                    <option value="{{ $loc }}" {{ $item->location === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Serial / IMEI Number') }}</label>
                                            <input type="text" name="old_serial_no_telematics" id="hedit-telematics-old-serial" class="form-control font-monospace" value="{{ $item->old_serial_no }}" placeholder="e.g. 863XXXXXXXXXXXX">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Serial / IMEI Number') }}</label>
                                            <input type="text" name="new_serial_no_telematics" id="hedit-telematics-new-serial" class="form-control font-monospace" value="{{ $item->new_serial_no }}" placeholder="e.g. 863YYYYYYYYYYYY">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Manufacturer') }}</label>
                                            <input type="text" name="brand_telematics" id="hedit-telematics-brand" class="form-control" value="{{ $item->brand }}" placeholder="e.g. Teltonika, Ruptela">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Model') }}</label>
                                            <input type="text" name="model_telematics" id="hedit-telematics-model" class="form-control" value="{{ $item->model }}" placeholder="e.g. FMC920, FMB125">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <select name="reason_telematics" id="hedit-telematics-reason" class="form-select">
                                                @foreach(['Faulty', 'Hardware Upgrade', 'Missing / Tampered', 'Periodic Calibration'] as $r)
                                                    <option value="{{ $r }}" {{ $item->reason === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- GENERAL / BRAKES / ENGINE FIELDS --}}
                                <div class="category-fields-group {{ in_array($item->category, ['general', 'brake', 'engine', 'other']) ? '' : 'd-none' }}" id="hedit-fields-general">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Component Name / Part') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="item_name_general" id="hedit-general-item-name" class="form-control" value="{{ $item->item_name }}" placeholder="e.g. Brake Pads, Oil Filter, Alternator">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action_general" id="hedit-general-action" class="form-select">
                                                <option value="replace" {{ $item->action === 'replace' ? 'selected' : '' }}>Replace</option>
                                                <option value="service" {{ $item->action === 'service' ? 'selected' : '' }}>Service / Flush</option>
                                                <option value="repair" {{ $item->action === 'repair' ? 'selected' : '' }}>Repair</option>
                                                <option value="inspect" {{ $item->action === 'inspect' ? 'selected' : '' }}>Inspect</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Location / Position') }}</label>
                                            <input type="text" name="location_general" id="hedit-general-location" class="form-control" value="{{ $item->location }}" placeholder="e.g. Front Axle, Engine Bay">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Brand / Manufacturer') }}</label>
                                            <input type="text" name="brand_general" id="hedit-general-brand" class="form-control" value="{{ $item->brand }}" placeholder="e.g. Bosch, Knorr-Bremse">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Part / Serial Number') }}</label>
                                            <input type="text" name="old_serial_no_general" id="hedit-general-old-serial" class="form-control font-monospace" value="{{ $item->old_serial_no }}" placeholder="e.g. PN-10293">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Part / Serial Number') }}</label>
                                            <input type="text" name="new_serial_no_general" id="hedit-general-new-serial" class="form-control font-monospace" value="{{ $item->new_serial_no }}" placeholder="e.g. PN-994820">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <input type="text" name="reason_general" id="hedit-general-reason" class="form-control" value="{{ $item->reason }}" placeholder="e.g. Worn down, Leaking">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="opacity-10 my-3">

                            {{-- Common Fields: Photo, Quantity, Unit Cost & Total Cost --}}
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold"><i class="ri-attachment-line text-primary me-1"></i> {{ __('Update Photo / Verification Document') }}</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*,.pdf,.doc,.docx">
                                    <small class="text-muted">{{ __('Leave empty to keep existing file. Supports Images (JPG, PNG, WEBP) or Documents (PDF, DOC, DOCX) — max 10MB.') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-stack-line text-secondary me-1"></i> {{ __('Quantity') }}</label>
                                    <input type="number" step="1" min="1" name="quantity" id="hedit-item-quantity" class="form-control font-monospace" value="{{ $item->quantity ?: 1 }}" placeholder="1" oninput="calculateEditCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-price-tag-2-line text-primary me-1"></i> {{ __('Unit Cost') }}</label>
                                    <input type="number" step="0.01" min="0" name="unit_cost" id="hedit-item-unit-cost" class="form-control font-monospace" value="{{ $item->unit_cost }}" placeholder="0.00" oninput="calculateEditCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-money-dollar-circle-line text-success me-1"></i> {{ __('Total Cost') }}</label>
                                    <input type="number" step="0.01" min="0" name="cost" id="hedit-item-cost" class="form-control font-monospace" value="{{ $item->cost }}" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="ri-save-line me-1"></i> {{ __('Update History Item') }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================
         MODAL: ADD NEW ITEM UNDER THIS MAINTENANCE EVENT
         ============================================================ --}}
    @if($maint && !$isLocked)
        <div class="modal fade" id="addHistoryItemModal" tabindex="-1" aria-labelledby="addHistoryItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-tile green" style="width: 38px; height: 38px; font-size: 1.1rem;">
                                <i class="ri-tools-line"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="addHistoryItemModalLabel">
                                    {{ __('Add Maintenance Item / Component') }}
                                </h5>
                                <small class="text-muted">{{ __('Capture parts, serial changes, tyre axles, and mechanical work') }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('maintenance.items.store', $maint->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="redirect_to" value="history_show">

                        <div class="modal-body p-4">
                            {{-- Step 1: Category Picker --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="ri-function-line text-primary me-1"></i> {{ __('Select Component Category') }} <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                            <input type="radio" name="category" value="tyre" checked onchange="switchHistoryAddCategory('tyre')">
                                            <div class="category-icon text-primary"><i class="ri-disc-line"></i></div>
                                            <div class="category-name">Tyre</div>
                                        </label>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                            <input type="radio" name="category" value="battery" onchange="switchHistoryAddCategory('battery')">
                                            <div class="category-icon text-warning"><i class="ri-battery-2-charge-line"></i></div>
                                            <div class="category-name">Battery</div>
                                        </label>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                            <input type="radio" name="category" value="telematics" onchange="switchHistoryAddCategory('telematics')">
                                            <div class="category-icon text-info"><i class="ri-radar-line"></i></div>
                                            <div class="category-name">Telematics</div>
                                        </label>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                            <input type="radio" name="category" value="brake" onchange="switchHistoryAddCategory('brake')">
                                            <div class="category-icon text-danger"><i class="ri-stop-circle-line"></i></div>
                                            <div class="category-name">Brakes</div>
                                        </label>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                            <input type="radio" name="category" value="engine" onchange="switchHistoryAddCategory('engine')">
                                            <div class="category-icon text-secondary"><i class="ri-settings-4-line"></i></div>
                                            <div class="category-name">Engine</div>
                                        </label>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                            <input type="radio" name="category" value="general" onchange="switchHistoryAddCategory('general')">
                                            <div class="category-icon text-dark"><i class="ri-tools-line"></i></div>
                                            <div class="category-name">General</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="opacity-10 my-3">

                            {{-- Dynamic Add Category Container --}}
                            <div id="history-add-category-fields">
                                {{-- ============ TYRE FORM FIELDS ============ --}}
                                <div class="category-fields-group" id="hadd-fields-tyre">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action" class="form-select action-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="rotate">Rotate</option>
                                                <option value="repair">Repair</option>
                                                <option value="inspect">Inspect / Pressure Check</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Axle') }} <span class="text-danger">*</span></label>
                                            <select name="axle" class="form-select">
                                                <option value="Front Axle">Front Axle</option>
                                                <option value="Rear Axle 1" selected>Rear Axle 1</option>
                                                <option value="Rear Axle 2">Rear Axle 2</option>
                                                <option value="Rear Axle 3">Rear Axle 3</option>
                                                <option value="Trailer Axle 1">Trailer Axle 1</option>
                                                <option value="Trailer Axle 2">Trailer Axle 2</option>
                                                <option value="Spare Tyre">Spare Tyre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Position') }} <span class="text-danger">*</span></label>
                                            <select name="position" class="form-select">
                                                <option value="Left Outer" selected>Left Outer</option>
                                                <option value="Left Inner">Left Inner</option>
                                                <option value="Right Outer">Right Outer</option>
                                                <option value="Right Inner">Right Inner</option>
                                                <option value="Front Left">Front Left</option>
                                                <option value="Front Right">Front Right</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Tyre Size') }}</label>
                                            <input type="text" name="size" class="form-control" placeholder="e.g. 24R18.22 or 315/80 R22.5">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Brand') }}</label>
                                            <select name="brand" class="form-select">
                                                <option value="Michelin" selected>Michelin</option>
                                                <option value="Bridgestone">Bridgestone</option>
                                                <option value="Goodyear">Goodyear</option>
                                                <option value="Continental">Continental</option>
                                                <option value="Pirelli">Pirelli</option>
                                                <option value="Hankook">Hankook</option>
                                                <option value="Dunlop">Dunlop</option>
                                                <option value="Triangle">Triangle</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Pattern / Model') }}</label>
                                            <input type="text" name="model" class="form-control" placeholder="e.g. X Multi D / R150">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Tyre ID / Serial') }}</label>
                                            <input type="text" name="old_serial_no" class="form-control font-monospace" placeholder="e.g. TYR-000457">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Tyre ID / Serial') }}</label>
                                            <input type="text" name="new_serial_no" class="form-control font-monospace" placeholder="e.g. TYR-000892">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Tread Depth (mm)') }}</label>
                                            <input type="number" step="0.1" name="tread_depth_mm" class="form-control font-monospace" placeholder="e.g. 8-12 mm (max 1000)" max="1000">
                                            <small class="text-muted">{{ __('Typical new tyre: 8-12mm. Max allowed: 1000mm') }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Reason for Replacement') }}</label>
                                            <select name="reason" class="form-select">
                                                <option value="Worn" selected>Worn</option>
                                                <option value="Puncture / Damage">Puncture / Damage</option>
                                                <option value="Sidewall Bulge">Sidewall Bulge</option>
                                                <option value="Blowout">Blowout</option>
                                                <option value="Uneven Wear">Uneven Wear</option>
                                                <option value="Scheduled Replacement">Scheduled Replacement</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- ============ BATTERY FORM FIELDS ============ --}}
                                <div class="category-fields-group d-none" id="hadd-fields-battery">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Battery Position') }} <span class="text-danger">*</span></label>
                                            <select name="location_battery" class="form-select">
                                                <option value="Main Battery 1" selected>Main Battery 1</option>
                                                <option value="Main Battery 2">Main Battery 2</option>
                                                <option value="Auxiliary Battery">Auxiliary Battery</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action_battery" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="recharge">Recharge</option>
                                                <option value="inspect">Test / Inspect</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Battery Serial Number') }}</label>
                                            <input type="text" name="old_serial_no_battery" class="form-control font-monospace" placeholder="e.g. BAT-00124">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Battery Serial Number') }}</label>
                                            <input type="text" name="new_serial_no_battery" class="form-control font-monospace" placeholder="e.g. BAT-00876">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Manufacturer / Brand') }}</label>
                                            <input type="text" name="brand_battery" class="form-control" placeholder="e.g. Exide, Bosch, Varta">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Battery Rating / Voltage') }}</label>
                                            <input type="text" name="size_battery" class="form-control" placeholder="e.g. 12V 180Ah / 24V">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <select name="reason_battery" class="form-select">
                                                <option value="Failed" selected>Failed</option>
                                                <option value="Weak / Low Voltage">Weak / Low Voltage</option>
                                                <option value="Dead Cell">Dead Cell</option>
                                                <option value="Scheduled Replacement">Scheduled Replacement</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- ============ TELEMATICS FORM FIELDS ============ --}}
                                <div class="category-fields-group d-none" id="hadd-fields-telematics">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Equipment Type') }} <span class="text-danger">*</span></label>
                                            <select name="item_name_telematics" class="form-select">
                                                <option value="GPS Tracker" selected>GPS Tracker</option>
                                                <option value="Fuel Level Sensor (FLS)">Fuel Level Sensor (FLS)</option>
                                                <option value="Dashcam / Camera">Dashcam / Camera</option>
                                                <option value="Temperature Sensor">Temperature Sensor</option>
                                                <option value="Panic Button">Panic Button</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action_telematics" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="install">Install New</option>
                                                <option value="calibrate">Calibrate</option>
                                                <option value="repair">Repair / Check</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Location on Vehicle') }}</label>
                                            <select name="location_telematics" class="form-select">
                                                <option value="Cab / Dashboard" selected>Cab / Dashboard</option>
                                                <option value="Fuel Tank Left">Fuel Tank Left</option>
                                                <option value="Fuel Tank Right">Fuel Tank Right</option>
                                                <option value="Windshield">Windshield</option>
                                                <option value="Cargo Area">Cargo Area</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Serial / IMEI Number') }}</label>
                                            <input type="text" name="old_serial_no_telematics" class="form-control font-monospace" placeholder="e.g. 863XXXXXXXXXXXX">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Serial / IMEI Number') }}</label>
                                            <input type="text" name="new_serial_no_telematics" class="form-control font-monospace" placeholder="e.g. 863YYYYYYYYYYYY">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Manufacturer') }}</label>
                                            <input type="text" name="brand_telematics" class="form-control" placeholder="e.g. Teltonika, Ruptela">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Model') }}</label>
                                            <input type="text" name="model_telematics" class="form-control" placeholder="e.g. FMC920, FMB125">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <select name="reason_telematics" class="form-select">
                                                <option value="Faulty" selected>Faulty</option>
                                                <option value="Hardware Upgrade">Hardware Upgrade</option>
                                                <option value="Missing / Tampered">Missing / Tampered</option>
                                                <option value="Periodic Calibration">Periodic Calibration</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- ============ BRAKE / ENGINE / GENERAL FORM FIELDS ============ --}}
                                <div class="category-fields-group d-none" id="hadd-fields-general">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Component Name / Part') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="item_name_general" class="form-control" placeholder="e.g. Brake Pads, Oil Filter, Alternator">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select name="action_general" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="service">Service / Flush</option>
                                                <option value="repair">Repair</option>
                                                <option value="inspect">Inspect</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Location / Position') }}</label>
                                            <input type="text" name="location_general" class="form-control" placeholder="e.g. Front Axle, Engine Bay">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Brand / Manufacturer') }}</label>
                                            <input type="text" name="brand_general" class="form-control" placeholder="e.g. Bosch, Knorr-Bremse">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Part / Serial Number') }}</label>
                                            <input type="text" name="old_serial_no_general" class="form-control font-monospace" placeholder="e.g. PN-10293">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Part / Serial Number') }}</label>
                                            <input type="text" name="new_serial_no_general" class="form-control font-monospace" placeholder="e.g. PN-994820">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <input type="text" name="reason_general" class="form-control" placeholder="e.g. Worn down, Leaking">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="opacity-10 my-3">

                            {{-- Common Fields: Photo, Quantity, Unit Cost & Total Cost --}}
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold"><i class="ri-attachment-line text-primary me-1"></i> {{ __('Photo / Verification Document') }}</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*,.pdf,.doc,.docx">
                                    <small class="text-muted">{{ __('Optional attachment. Supports Images (JPG, PNG, WEBP) or Documents (PDF, DOC, DOCX) — max 10MB.') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-stack-line text-secondary me-1"></i> {{ __('Quantity') }}</label>
                                    <input type="number" step="1" min="1" name="quantity" id="hadd-item-quantity" class="form-control font-monospace" value="1" placeholder="1" oninput="calculateAddCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-price-tag-2-line text-primary me-1"></i> {{ __('Unit Cost') }}</label>
                                    <input type="number" step="0.01" min="0" name="unit_cost" id="hadd-item-unit-cost" class="form-control font-monospace" placeholder="0.00" oninput="calculateAddCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-money-dollar-circle-line text-success me-1"></i> {{ __('Total Cost') }}</label>
                                    <input type="number" step="0.01" min="0" name="cost" id="hadd-item-cost" class="form-control font-monospace" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="ri-check-line me-1"></i> {{ __('Save Maintenance Item') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MODAL: PHOTO PREVIEW FULLSCREEN
         ============================================================ --}}
    <div class="modal fade" id="historyShowPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold mb-0" id="historyShowPhotoModalTitle">
                        <i class="ri-image-line me-1"></i> {{ __('Component Photo Verification') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center bg-black">
                    <img id="historyShowPhotoModalImage" src="" alt="Verification Photo" class="img-fluid" style="max-height: 75vh; width: auto; object-fit: contain;">
                </div>
                <div class="modal-footer border-0 bg-dark p-2 justify-content-between">
                    <a id="historyShowPhotoModalDownload" href="" target="_blank" download class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="ri-download-2-line me-1"></i> {{ __('Open Original') }}
                    </a>
                    <button type="button" class="btn-sm btn btn-light rounded-pill px-3" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('auth_scripts')
    <script>
        function calculateEditCost() {
            const qty = parseFloat(document.getElementById('hedit-item-quantity')?.value) || 1;
            const unitCost = parseFloat(document.getElementById('hedit-item-unit-cost')?.value);
            const costInput = document.getElementById('hedit-item-cost');
            if (!isNaN(unitCost) && costInput) {
                costInput.value = (qty * unitCost).toFixed(2);
            }
        }

        function calculateAddCost() {
            const qty = parseFloat(document.getElementById('hadd-item-quantity')?.value) || 1;
            const unitCost = parseFloat(document.getElementById('hadd-item-unit-cost')?.value);
            const costInput = document.getElementById('hadd-item-cost');
            if (!isNaN(unitCost) && costInput) {
                costInput.value = (qty * unitCost).toFixed(2);
            }
        }

        function previewHistoryShowPhoto(url, title) {
            document.getElementById('historyShowPhotoModalImage').src = url;
            document.getElementById('historyShowPhotoModalDownload').href = url;
            document.getElementById('historyShowPhotoModalTitle').innerHTML = '<i class="ri-image-line me-1"></i> ' + (title || 'Photo Verification');
            const modal = new bootstrap.Modal(document.getElementById('historyShowPhotoModal'));
            modal.show();
        }

        function openEditHistoryItemModal(item) {
            const modalEl = document.getElementById('editHistoryItemModal');
            if (!modalEl) return;

            const cat = item.category || 'general';
            const radioBtn = document.getElementById('modal-cat-' + cat) || document.getElementById('modal-cat-general');
            if (radioBtn) {
                radioBtn.checked = true;
            }
            switchHistoryEditCategory(cat);

            // Populate common cost and quantity fields
            const qtyInput = document.getElementById('hedit-item-quantity');
            if (qtyInput) qtyInput.value = item.quantity || 1;

            const unitCostInput = document.getElementById('hedit-item-unit-cost');
            if (unitCostInput) unitCostInput.value = (item.unit_cost !== null && item.unit_cost !== undefined) ? item.unit_cost : '';

            const costInput = document.getElementById('hedit-item-cost');
            if (costInput) costInput.value = (item.cost !== null && item.cost !== undefined) ? item.cost : '';

            // Populate category specific fields
            if (cat === 'tyre') {
                if (item.action && document.getElementById('hedit-tyre-action')) document.getElementById('hedit-tyre-action').value = item.action;
                if (item.axle && document.getElementById('hedit-tyre-axle')) document.getElementById('hedit-tyre-axle').value = item.axle;
                if (item.position && document.getElementById('hedit-tyre-position')) document.getElementById('hedit-tyre-position').value = item.position;
                if (document.getElementById('hedit-tyre-size')) document.getElementById('hedit-tyre-size').value = item.size || '';
                if (item.brand && document.getElementById('hedit-tyre-brand')) document.getElementById('hedit-tyre-brand').value = item.brand;
                if (document.getElementById('hedit-tyre-model')) document.getElementById('hedit-tyre-model').value = item.model || '';
                if (document.getElementById('hedit-tyre-old-serial')) document.getElementById('hedit-tyre-old-serial').value = item.old_serial_no || '';
                if (document.getElementById('hedit-tyre-new-serial')) document.getElementById('hedit-tyre-new-serial').value = item.new_serial_no || '';
                if (document.getElementById('hedit-tyre-tread')) document.getElementById('hedit-tyre-tread').value = item.tread_depth_mm || '';
                if (item.reason && document.getElementById('hedit-tyre-reason')) document.getElementById('hedit-tyre-reason').value = item.reason;
            } else if (cat === 'battery') {
                if (item.location && document.getElementById('hedit-battery-location')) document.getElementById('hedit-battery-location').value = item.location;
                if (item.action && document.getElementById('hedit-battery-action')) document.getElementById('hedit-battery-action').value = item.action;
                if (document.getElementById('hedit-battery-old-serial')) document.getElementById('hedit-battery-old-serial').value = item.old_serial_no || '';
                if (document.getElementById('hedit-battery-new-serial')) document.getElementById('hedit-battery-new-serial').value = item.new_serial_no || '';
                if (document.getElementById('hedit-battery-brand')) document.getElementById('hedit-battery-brand').value = item.brand || '';
                if (document.getElementById('hedit-battery-size')) document.getElementById('hedit-battery-size').value = item.size || '';
                if (item.reason && document.getElementById('hedit-battery-reason')) document.getElementById('hedit-battery-reason').value = item.reason;
            } else if (cat === 'telematics') {
                if (item.item_name && document.getElementById('hedit-telematics-item-name')) document.getElementById('hedit-telematics-item-name').value = item.item_name;
                if (item.action && document.getElementById('hedit-telematics-action')) document.getElementById('hedit-telematics-action').value = item.action;
                if (item.location && document.getElementById('hedit-telematics-location')) document.getElementById('hedit-telematics-location').value = item.location;
                if (document.getElementById('hedit-telematics-old-serial')) document.getElementById('hedit-telematics-old-serial').value = item.old_serial_no || '';
                if (document.getElementById('hedit-telematics-new-serial')) document.getElementById('hedit-telematics-new-serial').value = item.new_serial_no || '';
                if (document.getElementById('hedit-telematics-brand')) document.getElementById('hedit-telematics-brand').value = item.brand || '';
                if (document.getElementById('hedit-telematics-model')) document.getElementById('hedit-telematics-model').value = item.model || '';
                if (item.reason && document.getElementById('hedit-telematics-reason')) document.getElementById('hedit-telematics-reason').value = item.reason;
            } else {
                if (document.getElementById('hedit-general-item-name')) document.getElementById('hedit-general-item-name').value = item.item_name || '';
                if (item.action && document.getElementById('hedit-general-action')) document.getElementById('hedit-general-action').value = item.action;
                if (document.getElementById('hedit-general-location')) document.getElementById('hedit-general-location').value = item.location || '';
                if (document.getElementById('hedit-general-old-serial')) document.getElementById('hedit-general-old-serial').value = item.old_serial_no || '';
                if (document.getElementById('hedit-general-new-serial')) document.getElementById('hedit-general-new-serial').value = item.new_serial_no || '';
                if (document.getElementById('hedit-general-brand')) document.getElementById('hedit-general-brand').value = item.brand || '';
                if (document.getElementById('hedit-general-reason')) document.getElementById('hedit-general-reason').value = item.reason || '';
            }

            const modal = new bootstrap.Modal(document.getElementById('editHistoryItemModal'));
            modal.show();
        }

        function switchHistoryEditCategory(cat) {
            const sections = ['hedit-fields-tyre', 'hedit-fields-battery', 'hedit-fields-telematics', 'hedit-fields-general'];
            sections.forEach(function(sId) {
                const s = document.getElementById(sId);
                if (s) {
                    s.classList.add('d-none');
                    s.querySelectorAll('input, select, textarea').forEach(f => f.disabled = true);
                }
            });

            let activeId = 'hedit-fields-general';
            if (cat === 'tyre') activeId = 'hedit-fields-tyre';
            else if (cat === 'battery') activeId = 'hedit-fields-battery';
            else if (cat === 'telematics') activeId = 'hedit-fields-telematics';

            const active = document.getElementById(activeId);
            if (active) {
                active.classList.remove('d-none');
                active.querySelectorAll('input, select, textarea').forEach(f => f.disabled = false);
            }
        }

        function switchHistoryAddCategory(cat) {
            const sections = ['hadd-fields-tyre', 'hadd-fields-battery', 'hadd-fields-telematics', 'hadd-fields-general'];
            sections.forEach(function(sId) {
                const s = document.getElementById(sId);
                if (s) {
                    s.classList.add('d-none');
                    s.querySelectorAll('input, select, textarea').forEach(f => f.disabled = true);
                }
            });

            let activeId = 'hadd-fields-general';
            if (cat === 'tyre') activeId = 'hadd-fields-tyre';
            else if (cat === 'battery') activeId = 'hadd-fields-battery';
            else if (cat === 'telematics') activeId = 'hadd-fields-telematics';

            const active = document.getElementById(activeId);
            if (active) {
                active.classList.remove('d-none');
                active.querySelectorAll('input, select, textarea').forEach(f => f.disabled = false);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize add modal category fields (tyre is selected by default)
            switchHistoryAddCategory('tyre');

            // Reset add modal on hide
            const addHistoryModal = document.getElementById('addHistoryItemModal');
            if (addHistoryModal) {
                addHistoryModal.addEventListener('hidden.bs.modal', function() {
                    const tyreRadio = addHistoryModal.querySelector('input[name="category"][value="tyre"]');
                    if (tyreRadio) {
                        tyreRadio.checked = true;
                        switchHistoryAddCategory('tyre');
                    }
                    const form = addHistoryModal.querySelector('form');
                    if (form) {
                        form.reset();
                    }
                });
            }
        });
    </script>
    @endpush

    <style>
        .kpi-summary-card {
            transition: all 0.25s ease;
        }
        .kpi-summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06) !important;
        }
        .kpi-icon-tile {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        .section-icon-tile {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .icon-tile {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .icon-tile.blue   { background: #e0e7ff; color: #4f46e5; }
        .icon-tile.green  { background: #dcfce7; color: #16a34a; }
        .bg-cyan-subtle {
            background-color: #cffafe !important;
        }
        .text-cyan {
            color: #0891b2 !important;
        }
        .category-radio-card {
            cursor: pointer;
            background: #f8fafc;
            border-color: #e2e8f0;
            transition: all 0.2s ease;
            user-select: none;
        }
        .category-radio-card input {
            display: none;
        }
        .category-radio-card .category-icon {
            font-size: 1.3rem;
            margin-bottom: 2px;
        }
        .category-radio-card .category-name {
            font-size: 0.72rem;
            font-weight: 700;
            color: #334155;
        }
        .category-radio-card:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }
        .category-radio-card:has(input:checked) {
            background: #eff6ff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
        }
        .category-radio-card:has(input:checked) .category-name {
            color: #1d4ed8;
        }
        .photo-hover-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.45);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            cursor: pointer;
        }
        .photo-preview-box:hover .photo-hover-overlay {
            opacity: 1;
        }
        .sibling-item-link {
            transition: all 0.2s ease;
        }
        .sibling-item-link:hover {
            background: #ffffff !important;
            border-color: #3b82f6 !important;
            transform: translateX(3px);
        }
        @media print {
            header, .navbar, .sidebar, .no-print, .btn, .modal { display: none !important; }
            body { background: white !important; font-size: 11pt; color: black !important; }
            .print-content, .print-content * { visibility: visible; }
            .print-content { position: absolute; left: 0; top: 0; width: 100% !important; margin: 0 !important; }
            .card { border: 1px solid #ccc !important; box-shadow: none !important; margin-bottom: 15px !important; }
            .table { width: 100% !important; border-collapse: collapse !important; }
            .table th, .table td { border: 1px solid #ddd !important; padding: 6px !important; }
        }
    </style>

</x-layouts.auth>
