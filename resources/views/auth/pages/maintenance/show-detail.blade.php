{{-- Remix Icon CDN & Styles --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<style>
    .maintenance-image-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .maintenance-image-card img:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
</style>

@if((string)($data->payment_status ?? '') === '2')
<div class="alert alert-success d-flex align-items-center gap-3 rounded-4 shadow-sm border-0 mb-4 p-3 bg-success-subtle text-success-emphasis no-print">
    <i class="ri-lock-2-fill fs-3 text-success"></i>
    <div>
        <h6 class="fw-bold mb-0">{{ __('Record Locked (Payment Processed)') }}</h6>
        <small>{{ __('Payment has been processed for this maintenance record. All modifications and item updates are locked.') }}</small>
    </div>
</div>
@endif

<div class="row g-4 print-content">
    {{-- ===================== LEFT COLUMN ===================== --}}
    <div class="col-xl-8">

        {{-- ============ MAINTENANCE INFORMATION CARD ============ --}}
        <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile blue">
                            <i class="ri-tools-line"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="section-title mb-0">
                                    {{ __('Maintenance Record') }}
                                </h6>
                                <span class="badge bg-primary-subtle text-primary fw-bold font-monospace px-2 py-1" style="font-size: 0.85rem;">
                                    #{{ $data->reference_no ?: ('MT-' . str_pad($data->id, 6, '0', STR_PAD_LEFT)) }}
                                </span>
                            </div>
                            <small class="text-muted">
                                {{ __('Asset Maintenance & Component Overhaul Details') }}
                            </small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if($data->priority)
                            @php
                                $priorityClass = match(strtolower($data->priority)) {
                                    'urgent', 'emergency' => 'chip-red',
                                    'medium', 'scheduled' => 'chip-amber',
                                    default => 'chip-blue',
                                };
                            @endphp
                            <span class="chip {{ $priorityClass }}">
                                <i class="ri-flag-line me-1"></i> {{ $data->priority }}
                            </span>
                        @endif

                        @if($data->status == '1')
                            <span class="chip chip-green">
                                <i class="ri-checkbox-circle-fill me-1"></i> {{ __('Completed') }}
                            </span>
                        @elseif($data->status == '2')
                            <span class="chip chip-blue">
                                <i class="ri-loader-4-line me-1"></i> {{ __('Open / In Progress') }}
                            </span>
                        @else
                            <span class="chip chip-amber">
                                <i class="ri-time-line me-1"></i> {{ __('Pending') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="subsection-title">
                            <i class="ri-truck-line"></i> {{ __('Vehicle & Service Summary') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-truck-line"></i> {{ __('Vehicle / Asset') }}</label>
                            <div class="info-value">
                                <span class="chip chip-blue">
                                    {{ $data->vehicle?->fleet_no }} – {{ $data->vehicle?->truck_no }}
                                    @if($data->vehicle?->vehicle_make_model)
                                        ({{ $data->vehicle->vehicle_make_model->name }})
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-dashboard-3-line"></i> {{ __('Current Odometer') }}</label>
                            <div class="info-value fw-bold text-dark font-monospace">
                                <i class="ri-speed-up-line text-primary me-1"></i>
                                {{ $data->odometer ?: '–' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Maintenance Date') }}</label>
                            <div class="info-value">
                                <i class="ri-calendar-2-line text-muted me-1"></i>
                                {{ $data->maintenance_date ? ($data->maintenance_date instanceof \DateTime ? $data->maintenance_date->format('d/m/Y') : $data->maintenance_date) : '–' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-calendar-event-line"></i> {{ __('Expected Completion Date') }}</label>
                            <div class="info-value">
                                <i class="ri-calendar-2-line text-muted me-1"></i>
                                {{ $data->expected_completion_date ? ($data->expected_completion_date instanceof \DateTime ? $data->expected_completion_date->format('d/m/Y') : $data->expected_completion_date) : '–' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-building-line"></i> {{ __('Service Provider / Workshop') }}</label>
                            <div class="info-value fw-semibold">{{ $data->service_provider ?: '–' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-money-dollar-circle-line"></i> {{ __('Estimated / Total Cost') }}</label>
                            <div class="info-value text-primary fw-bold font-monospace">
                                {{ $data->cost ? number_format($data->cost, 2) : '–' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-hammer-line"></i> {{ __('Maintenance Types') }}</label>
                            <div class="info-value">
                                @if($data->maintenanceTypes && $data->maintenanceTypes->count())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($data->maintenanceTypes as $type)
                                            <span class="chip chip-cyan"><i class="ri-wrench-line me-1"></i>{{ $type->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    –
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($data->description)
                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-file-text-line"></i> {{ __('Description & Scope of Work') }}</label>
                            <div class="info-value text-secondary" style="white-space: pre-line;">{{ $data->description }}</div>
                        </div>
                    </div>
                    @endif

                    {{-- Initial Documents & Photos - Display in Grid --}}
                    @if($data->maintenanceFiles && $data->maintenanceFiles->count())
                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-attachment-line"></i> {{ __('Initial Documents & Photos') }}</label>
                            
                            @php
                                $images = $data->maintenanceFiles->filter(function($file) {
                                    $mime = $file->file_type ?? '';
                                    return str_contains($mime, 'image');
                                });
                                
                                $documents = $data->maintenanceFiles->filter(function($file) {
                                    $mime = $file->file_type ?? '';
                                    return !str_contains($mime, 'image');
                                });
                            @endphp

                            {{-- Display Documents First (if any) --}}
                            @if($documents->count())
                            <div class="mb-3">
                                <small class="fw-bold text-dark d-block mb-2"><i class="ri-file-text-line me-1"></i>{{ __('Documents') }}</small>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($documents as $file)
                                        @php
                                            $icon = str_contains($file->file_type, 'pdf') 
                                                ? 'ri-file-pdf-2-line text-danger' 
                                                : 'ri-file-text-line text-primary';
                                        @endphp
                                        <a href="{{ Storage::url($file->path) }}" target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1 py-1 px-2">
                                            <i class="{{ $icon }}"></i>
                                            <span class="text-truncate" style="max-width: 150px;">{{ $file->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Display Images in Grid --}}
                            @if($images->count())
                            <div>
                                <small class="fw-bold text-dark d-block mb-2"><i class="ri-image-line me-1"></i>{{ __('Images') }} ({{ $images->count() }})</small>
                                <div class="row g-2">
                                    @foreach($images as $file)
                                    <div class="col-lg-3 col-md-4 col-6">
                                        <div class="maintenance-image-card">
                                            <a href="{{ Storage::url($file->path) }}" target="_blank">
                                                <img src="{{ Storage::url($file->path) }}" alt="{{ $file->name }}" class="img-fluid rounded border">
                                            </a>
                                            <div class="mt-1">
                                                <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;">{{ $file->name }}</small>
                                            </div>
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
        </div>
    </div>

    {{-- ===================== RIGHT COLUMN (STATUS & ASSET SUMMARY) ===================== --}}
    <div class="col-xl-4">

        {{-- ============ STATUS OVERVIEW ============ --}}
        <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile green">
                            <i class="ri-pie-chart-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Status & Approvals') }}</h6>
                            <small class="text-muted">{{ __('Lifecycle & payment state') }}</small>
                        </div>
                    </div>
                </div>

                <div class="side-list">
                    <div class="side-item">
                        <span class="side-label"><i class="ri-shield-check-line"></i> {{ __('Client Approval') }}</span>
                        <span class="chip {{ $data->client_approved ? 'chip-green' : 'chip-amber' }}">
                            <i class="ri-{{ $data->client_approved ? 'checkbox-circle-fill' : 'time-line' }} me-1"></i>
                            {{ $data->client_approved ? __('Approved') : __('Pending') }}
                        </span>
                    </div>

                    <div class="side-item">
                        <span class="side-label"><i class="ri-money-dollar-circle-line"></i> {{ __('Payment Status') }}</span>
                        <span class="chip
                            @if($data->payment_status == '0') chip-amber
                            @elseif($data->payment_status == '1') chip-green
                            @elseif($data->payment_status == '2') chip-blue
                            @else chip-slate @endif">
                            <i class="ri-{{ $data->payment_status == '0' ? 'time-line' : ($data->payment_status == '1' ? 'checkbox-circle-fill' : ($data->payment_status == '2' ? 'handshake-line' : 'subtract-line')) }} me-1"></i>
                            @if($data->payment_status == '0') {{ __('Pending') }}
                            @elseif($data->payment_status == '1') {{ __('Approved') }}
                            @elseif($data->payment_status == '2') {{ __('Processed') }}
                            @else {{ __('N/A') }} @endif
                        </span>
                    </div>

                    <div class="side-item">
                        <span class="side-label"><i class="ri-tools-line"></i> {{ __('Maintenance Status') }}</span>
                        <span class="chip
                            @if($data->status == '0') chip-amber
                            @elseif($data->status == '1') chip-green
                            @elseif($data->status == '2') chip-blue
                            @else chip-slate @endif">
                            <i class="ri-{{ $data->status == '0' ? 'time-line' : ($data->status == '1' ? 'checkbox-circle-fill' : ($data->status == '2' ? 'loader-4-line' : 'subtract-line')) }} me-1"></i>
                            @if($data->status == '0') {{ __('Pending') }}
                            @elseif($data->status == '1') {{ __('Completed') }}
                            @elseif($data->status == '2') {{ __('In Progress') }}
                            @else {{ __('N/A') }} @endif
                        </span>
                    </div>

                    @if($approved_by)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-user-follow-line"></i> {{ __('Approved By') }}</span>
                        <span class="side-value text-success">
                            <i class="ri-user-check-line me-1"></i>
                            {{ $approved_by->first_name.' '.$approved_by->last_name }}
                        </span>
                    </div>
                    @endif

                    @if($payment_paid_by)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-user-line"></i> {{ __('Payment By') }}</span>
                        <span class="side-value text-primary">
                            <i class="ri-user-star-line me-1"></i>
                            {{ $payment_paid_by->first_name.' '.$payment_paid_by->last_name }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ QUICK ACTIONS ============ --}}
        @if($data->client_approved && in_array($data->payment_status, ['0', '1']))
        <div class="card border-0 shadow-sm rounded-4 section-card mb-4 no-print">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile amber">
                            <i class="ri-flashlight-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Quick Actions') }}</h6>
                            <small class="text-muted">{{ __('Available operations') }}</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-gradient-blue" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="ri-bank-card-line me-2"></i> {{ __('Process Payment') }}
                    </button>
                    @if($data->status != '1')
                    <button type="button" class="btn btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#statusChangeModal">
                        <i class="ri-refresh-line me-2"></i> {{ __('Update Status') }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ============ ASSET DETAILS ============ --}}
        @if($data->vehicle)
        <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile blue">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Asset Details') }}</h6>
                            <small class="text-muted">{{ __('Vehicle identification') }}</small>
                        </div>
                    </div>
                </div>

                <div class="side-list">
                    <div class="side-item">
                        <span class="side-label"><i class="ri-hashtag"></i> {{ __('Fleet No') }}</span>
                        <span class="side-value fw-bold text-primary">{{ $data->vehicle->fleet_no ?? '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-truck-line"></i> {{ __('Registration / Truck No') }}</span>
                        <span class="side-value">{{ $data->vehicle->truck_no ?? '-' }}</span>
                    </div>
                    @if($data->vehicle->drivers && $data->vehicle->drivers->count() > 0)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-user-line"></i> {{ __('Assigned Driver') }}</span>
                        <span class="side-value">{{ $data->vehicle->drivers->pluck('full_name')->join(', ') }}</span>
                    </div>
                    @endif
                    @if($data->vehicle->make || $data->vehicle->vehicle_make_model)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-building-2-line"></i> {{ __('Make & Model') }}</span>
                        <span class="side-value">
                            {{ $data->vehicle->vehicle_make_model ? $data->vehicle->vehicle_make_model->name : ($data->vehicle->make . ' ' . $data->vehicle->model) }}
                        </span>
                    </div>
                    @endif
                    @if($data->vehicle->gps_unit_id)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-radar-line"></i> {{ __('GPS Unit ID') }}</span>
                        <span class="side-value font-monospace">{{ $data->vehicle->gps_unit_id }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ============================================================
     WORK & COMPONENTS SECTION (PDF CORE STEP 2 - 6) - FULL WIDTH COL-12
     ============================================================ --}}
<div class="row mb-4 print-content">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 section-card" id="work-components-section">
            <div class="card-body p-4">

                <div class="section-header mb-3">
                    <div class="section-header-left">
                        <div class="icon-tile green">
                            <i class="ri-cpu-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Work & Components') }}</h6>
                            <small class="text-muted">{{ __('Detailed parts, serial changes, and work items captured as they happen') }}</small>
                        </div>
                    </div>

                    <div>
                        @if((string)($data->payment_status ?? '') !== '2')
                        <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addMaintenanceItemModal">
                            <i class="ri-add-circle-line"></i>
                            <span>{{ __('+ Add Maintenance Item') }}</span>
                        </button>
                        @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-monospace">
                            <i class="ri-lock-2-line me-1"></i> {{ __('Locked (Payment Processed)') }}
                        </span>
                        @endif
                    </div>
                </div>

                @if($data->items && $data->items->count())
                    <div class="table-responsive rounded-3 border mt-3">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="bg-light text-dark fw-bold">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Location / Position') }}</th>
                                    <th>{{ __('Item Specification') }}</th>
                                    <th>{{ __('Old ID / Serial') }}</th>
                                    <th>{{ __('New ID / Serial') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Cost') }}</th>
                                    <th class="text-center">{{ __('Photo') }}</th>
                                    <th class="text-center no-print" style="width: 60px;">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data->items as $index => $item)
                                <tr>
                                    <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        {!! $item->category_badge !!}
                                    </td>
                                    <td>
                                        {!! $item->action_badge !!}
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        {{ $item->location ?: '–' }}
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-bold text-dark">{{ $item->item_description }}</span>
                                            @if($item->tread_depth_mm)
                                                <span class="badge bg-secondary-subtle text-secondary ms-1 font-monospace" title="Tread Depth">
                                                    {{ $item->tread_depth_mm }} mm
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->old_serial_no)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1">
                                                {{ $item->old_serial_no }}
                                            </span>
                                        @else
                                            <span class="text-muted">–</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->new_serial_no)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                                                {{ $item->new_serial_no }}
                                            </span>
                                        @else
                                            <span class="text-muted">–</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->reason)
                                            <span class="text-dark">{{ $item->reason }}</span>
                                        @else
                                            <span class="text-muted">–</span>
                                        @endif
                                    </td>
                                    <td class="font-monospace text-primary fw-semibold">
                                        {{ $item->cost ? number_format($item->cost, 2) : '–' }}
                                        @if($item->unit_cost)
                                            <div class="text-muted small" style="font-size: 0.75rem; font-family: sans-serif;">({{ $item->quantity ?: 1 }} &times; {{ number_format((float)$item->unit_cost, 2) }})</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->photo_url)
                                            <a href="{{ $item->photo_url }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2" title="View Verification Photo">
                                                <i class="ri-image-line text-success"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">–</span>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        @if((string)($data->payment_status ?? '') !== '2')
                                        <div class="dropdown">
                                            <button class="btn btn-sm border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-2-fill"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <button type="button" class="dropdown-item text-primary" onclick="openEditItemModal({{ json_encode($item) }})">
                                                        <i class="ri-edit-line me-2"></i> {{ __('Edit Item') }}
                                                    </button>
                                                </li>
                                                <li>
                                                    <form action="{{ route('maintenance.items.destroy', [$data->id, $item->id]) }}" method="POST" onsubmit="return confirm('Remove this maintenance item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="ri-delete-bin-line me-2"></i> {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                        @else
                                        <span class="text-muted" title="{{ __('Locked after payment') }}"><i class="ri-lock-2-line text-secondary"></i></span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 px-3 bg-light rounded-3 border border-dashed">
                        <div class="text-muted mb-2"><i class="ri-tools-line" style="font-size: 2.5rem;"></i></div>
                        <h6 class="fw-bold text-dark mb-1">{{ __('No maintenance items recorded yet') }}</h6>
                        <p class="text-muted small mb-3">{{ __('Add individual tyres, battery, telematics (GPS/FLS), or mechanical parts replaced during this service.') }}</p>
                        @if((string)($data->payment_status ?? '') !== '2')
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addMaintenanceItemModal">
                            <i class="ri-add-line me-1"></i> {{ __('+ Add First Item') }}
                        </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     CURRENT VEHICLE COMPONENT REGISTER & THEFT CONTROL TRAIL (PDF STEP 7) - FULL WIDTH COL-12
     ============================================================ --}}
@if($data->vehicle)
<div class="row mb-4 print-content">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-3">
                    <div class="section-header-left">
                        <div class="icon-tile cyan">
                            <i class="ri-shield-keyhole-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">
                                {{ $data->vehicle->fleet_no }} – {{ __('Current Component Register') }}
                            </h6>
                            <small class="text-muted">{{ __('Live configuration & component theft/control lifecycle trail (Tracks what is currently installed vs historical replacements)') }}</small>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-sm" id="componentTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3" id="active-comp-tab" data-bs-toggle="pill" data-bs-target="#active-comp-content" type="button" role="tab" style="font-size:0.75rem;">
                                <i class="ri-checkbox-circle-line me-1"></i> Active Components
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3" id="history-comp-tab" data-bs-toggle="pill" data-bs-target="#history-comp-content" type="button" role="tab" style="font-size:0.75rem;">
                                <i class="ri-history-line me-1"></i> Replaced / Audit Trail
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content mt-3" id="componentTabContent">
                    {{-- Tab 1: Active Components --}}
                    <div class="tab-pane fade show active" id="active-comp-content" role="tabpanel">
                        @if($data->vehicle->activeComponents && $data->vehicle->activeComponents->count())
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light text-dark fw-bold">
                                        <tr>
                                            <th>{{ __('Component') }}</th>
                                            <th>{{ __('Location / Position') }}</th>
                                            <th>{{ __('Current ID / Serial Installed') }}</th>
                                            <th>{{ __('Specification') }}</th>
                                            <th>{{ __('Installed Date') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data->vehicle->activeComponents as $comp)
                                        <tr>
                                            <td>{!! $comp->category_badge !!}</td>
                                            <td class="fw-semibold text-dark">{{ $comp->location }}</td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                                                    <i class="ri-barcode-line me-1"></i>{{ $comp->serial_no }}
                                                </span>
                                            </td>
                                            <td class="text-muted">
                                                {{ $comp->brand }} {{ $comp->size ?: $comp->model }}
                                            </td>
                                            <td>
                                                <i class="ri-calendar-line text-muted me-1"></i>
                                                {{ $comp->installed_date ? $comp->installed_date->format('d/m/Y') : '–' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><i class="ri-check-line me-1"></i>Active</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted text-center py-3 bg-light rounded-3 small">
                                <i class="ri-information-line me-1"></i> No active components registered yet. Once maintenance items with serial numbers are saved, they automatically populate here.
                            </div>
                        @endif
                    </div>

                    {{-- Tab 2: Replaced / Audit Trail --}}
                    <div class="tab-pane fade" id="history-comp-content" role="tabpanel">
                        @if($data->vehicle->componentHistory && $data->vehicle->componentHistory->count())
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light text-dark fw-bold">
                                        <tr>
                                            <th>{{ __('Component') }}</th>
                                            <th>{{ __('Location') }}</th>
                                            <th>{{ __('Old Serial Removed') }}</th>
                                            <th>{{ __('Removed Date') }}</th>
                                            <th>{{ __('Removal Reason / Audit Note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data->vehicle->componentHistory as $hist)
                                        <tr>
                                            <td>{!! $hist->category_badge !!}</td>
                                            <td class="fw-semibold text-dark">{{ $hist->location }}</td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1">
                                                    {{ $hist->serial_no }}
                                                </span>
                                            </td>
                                            <td>
                                                <i class="ri-calendar-close-line text-danger me-1"></i>
                                                {{ $hist->removed_date ? $hist->removed_date->format('d/m/Y') : '–' }}
                                            </td>
                                            <td class="text-secondary small">
                                                {{ $hist->removed_reason ?: 'Replaced during service' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted text-center py-3 bg-light rounded-3 small">
                                <i class="ri-history-line me-1"></i> No component replacement history recorded for this asset yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@include('auth.pages.maintenance._expense_history', ['payments' => $payments])

{{-- Chat Section --}}
<div class="row mt-4 no-print">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-0">
                <x-auth.incident-chat :incident-type="'vehicle_maintenance'" :incident-id="$data->id" />
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL: ADD MAINTENANCE ITEM (PDF CORE FUNCTIONALITY)
     ============================================================ --}}
@if((string)($data->payment_status ?? '') !== '2')
<div class="modal fade" id="addMaintenanceItemModal" tabindex="-1" aria-labelledby="addMaintenanceItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-tile green" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="ri-tools-line"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="addMaintenanceItemModalLabel">
                            {{ __('Add Maintenance Item / Component') }}
                        </h5>
                        <small class="text-muted">{{ __('Capture parts, serial changes, tyre axles, and mechanical work') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('maintenance.items.store', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">

                    {{-- Step 1: Category Picker --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i class="ri-function-line text-primary me-1"></i> {{ __('Select Component Category') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" value="tyre" checked onchange="switchCategoryForm('tyre')">
                                    <div class="category-icon text-primary"><i class="ri-disc-line"></i></div>
                                    <div class="category-name">Tyre</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" value="battery" onchange="switchCategoryForm('battery')">
                                    <div class="category-icon text-warning"><i class="ri-battery-2-charge-line"></i></div>
                                    <div class="category-name">Battery</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" value="telematics" onchange="switchCategoryForm('telematics')">
                                    <div class="category-icon text-info"><i class="ri-radar-line"></i></div>
                                    <div class="category-name">Telematics</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" value="brake" onchange="switchCategoryForm('brake')">
                                    <div class="category-icon text-danger"><i class="ri-stop-circle-line"></i></div>
                                    <div class="category-name">Brakes</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" value="engine" onchange="switchCategoryForm('engine')">
                                    <div class="category-icon text-secondary"><i class="ri-settings-4-line"></i></div>
                                    <div class="category-name">Engine</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" value="general" onchange="switchCategoryForm('general')">
                                    <div class="category-icon text-dark"><i class="ri-tools-line"></i></div>
                                    <div class="category-name">General</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="opacity-10 my-3">

                    {{-- Dynamic Container --}}
                    <div id="dynamic-category-fields">
                        {{-- ============ TYRE FORM FIELDS ============ --}}
                        <div class="category-fields-group" id="fields-tyre">
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
                                    <input type="number" step="0.1" name="tread_depth_mm" class="form-control font-monospace @error('tread_depth_mm') is-invalid @enderror" placeholder="e.g. 8-12 mm (max 1000)" max="1000">
                                    @error('tread_depth_mm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Typical new tyre: 8-12mm. Max allowed: 1000mm</small>
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
                        <div class="category-fields-group d-none" id="fields-battery">
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

                        {{-- ============ TELEMATICS (GPS / FLS / CAMERA) FORM FIELDS ============ --}}
                        <div class="category-fields-group d-none" id="fields-telematics">
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
                        <div class="category-fields-group d-none" id="fields-general">
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
                            <input type="number" step="1" min="1" name="quantity" id="add-item-quantity" class="form-control font-monospace" value="1" placeholder="1" oninput="calculateAddModalCost()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="ri-price-tag-2-line text-primary me-1"></i> {{ __('Unit Cost') }}</label>
                            <input type="number" step="0.01" min="0" name="unit_cost" id="add-item-unit-cost" class="form-control font-monospace" placeholder="0.00" oninput="calculateAddModalCost()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="ri-money-dollar-circle-line text-success me-1"></i> {{ __('Total Cost') }}</label>
                            <input type="number" step="0.01" min="0" name="cost" id="add-item-cost" class="form-control font-monospace" placeholder="0.00">
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

{{-- Status Change Modal --}}
@php
    $user = auth()->user();
    $canChangeStatus = $user && $user->hasRole([\App\Constants\Constants::ADMIN, \App\Constants\Constants::SUPERADMIN, \App\Constants\Constants::CHAMPS_MANAGEMENT]);
    $clientApproved = (bool) ($data->client_approved ?? false);
    $paymentApproved = (string) ($data->payment_status ?? '') === '2';
@endphp

@if($canChangeStatus && $clientApproved && $paymentApproved)
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="statusChangeModalLabel">
                    <i class="ri-refresh-line text-warning me-2"></i>
                    {{ __('Update Maintenance Status') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST" action="{{ route('maintenance.status_update', $data->id) }}">
                @method('PATCH')
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="">{{ __('Select Status') }}</option>
                            <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>{{ __('Completed / Resolved') }}</option>
                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="{{ __('Optional remarks...') }}">{{ $data->description ?? '' }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning rounded-pill">
                        <i class="ri-refresh-line me-1"></i> {{ __('Update Status') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ============================================================
     MODAL: EDIT MAINTENANCE ITEM
     ============================================================ --}}
<div class="modal fade" id="editMaintenanceItemModal" tabindex="-1" aria-labelledby="editMaintenanceItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-tile blue" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="ri-edit-line"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="editMaintenanceItemModalLabel">
                            {{ __('Edit Maintenance Item / Component') }}
                        </h5>
                        <small class="text-muted">{{ __('Update item parameters, serial codes, or inspection notes') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editMaintenanceItemForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">

                    {{-- Step 1: Category Picker --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i class="ri-function-line text-primary me-1"></i> {{ __('Component Category') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" id="edit-cat-tyre" value="tyre" onchange="switchEditCategoryForm('tyre')">
                                    <div class="category-icon text-primary"><i class="ri-disc-line"></i></div>
                                    <div class="category-name">Tyre</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" id="edit-cat-battery" value="battery" onchange="switchEditCategoryForm('battery')">
                                    <div class="category-icon text-warning"><i class="ri-battery-2-charge-line"></i></div>
                                    <div class="category-name">Battery</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" id="edit-cat-telematics" value="telematics" onchange="switchEditCategoryForm('telematics')">
                                    <div class="category-icon text-info"><i class="ri-radar-line"></i></div>
                                    <div class="category-name">Telematics</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" id="edit-cat-brake" value="brake" onchange="switchEditCategoryForm('brake')">
                                    <div class="category-icon text-danger"><i class="ri-stop-circle-line"></i></div>
                                    <div class="category-name">Brakes</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" id="edit-cat-engine" value="engine" onchange="switchEditCategoryForm('engine')">
                                    <div class="category-icon text-secondary"><i class="ri-settings-4-line"></i></div>
                                    <div class="category-name">Engine</div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="category-radio-card w-100 text-center p-2 rounded-3 border">
                                    <input type="radio" name="category" id="edit-cat-general" value="general" onchange="switchEditCategoryForm('general')">
                                    <div class="category-icon text-dark"><i class="ri-tools-line"></i></div>
                                    <div class="category-name">General</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="opacity-10 my-3">

                    {{-- Dynamic Edit Container --}}
                    <div id="dynamic-edit-category-fields">
                        {{-- TYRE FIELDS --}}
                        <div class="category-fields-group" id="edit-fields-tyre">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                    <select name="action" id="edit-tyre-action" class="form-select">
                                        <option value="replace">Replace</option>
                                        <option value="rotate">Rotate</option>
                                        <option value="repair">Repair</option>
                                        <option value="inspect">Inspect / Pressure Check</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Axle') }} <span class="text-danger">*</span></label>
                                    <select name="axle" id="edit-tyre-axle" class="form-select">
                                        <option value="Front Axle">Front Axle</option>
                                        <option value="Rear Axle 1">Rear Axle 1</option>
                                        <option value="Rear Axle 2">Rear Axle 2</option>
                                        <option value="Rear Axle 3">Rear Axle 3</option>
                                        <option value="Trailer Axle 1">Trailer Axle 1</option>
                                        <option value="Trailer Axle 2">Trailer Axle 2</option>
                                        <option value="Spare Tyre">Spare Tyre</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Position') }} <span class="text-danger">*</span></label>
                                    <select name="position" id="edit-tyre-position" class="form-select">
                                        <option value="Left Outer">Left Outer</option>
                                        <option value="Left Inner">Left Inner</option>
                                        <option value="Right Outer">Right Outer</option>
                                        <option value="Right Inner">Right Inner</option>
                                        <option value="Front Left">Front Left</option>
                                        <option value="Front Right">Front Right</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Tyre Size') }}</label>
                                    <input type="text" name="size" id="edit-tyre-size" class="form-control" placeholder="e.g. 24R18.22">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Brand') }}</label>
                                    <select name="brand" id="edit-tyre-brand" class="form-select">
                                        <option value="Michelin">Michelin</option>
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
                                    <input type="text" name="model" id="edit-tyre-model" class="form-control" placeholder="e.g. X Multi D">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Old Tyre ID / Serial') }}</label>
                                    <input type="text" name="old_serial_no" id="edit-tyre-old-serial" class="form-control font-monospace" placeholder="e.g. TYR-000457">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('New Tyre ID / Serial') }}</label>
                                    <input type="text" name="new_serial_no" id="edit-tyre-new-serial" class="form-control font-monospace" placeholder="e.g. TYR-000892">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Tread Depth (mm)') }}</label>
                                    <input type="number" step="0.1" name="tread_depth_mm" id="edit-tyre-tread" class="form-control font-monospace" placeholder="e.g. 32 mm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Reason for Replacement') }}</label>
                                    <select name="reason" id="edit-tyre-reason" class="form-select">
                                        <option value="Worn">Worn</option>
                                        <option value="Puncture / Damage">Puncture / Damage</option>
                                        <option value="Sidewall Bulge">Sidewall Bulge</option>
                                        <option value="Blowout">Blowout</option>
                                        <option value="Uneven Wear">Uneven Wear</option>
                                        <option value="Scheduled Replacement">Scheduled Replacement</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- BATTERY FIELDS --}}
                        <div class="category-fields-group d-none" id="edit-fields-battery">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Battery Position') }} <span class="text-danger">*</span></label>
                                    <select name="location_battery" id="edit-battery-location" class="form-select">
                                        <option value="Main Battery 1">Main Battery 1</option>
                                        <option value="Main Battery 2">Main Battery 2</option>
                                        <option value="Auxiliary Battery">Auxiliary Battery</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                    <select name="action_battery" id="edit-battery-action" class="form-select">
                                        <option value="replace">Replace</option>
                                        <option value="recharge">Recharge</option>
                                        <option value="inspect">Test / Inspect</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Old Battery Serial Number') }}</label>
                                    <input type="text" name="old_serial_no_battery" id="edit-battery-old-serial" class="form-control font-monospace" placeholder="e.g. BAT-00124">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('New Battery Serial Number') }}</label>
                                    <input type="text" name="new_serial_no_battery" id="edit-battery-new-serial" class="form-control font-monospace" placeholder="e.g. BAT-00876">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Manufacturer / Brand') }}</label>
                                    <input type="text" name="brand_battery" id="edit-battery-brand" class="form-control" placeholder="e.g. Exide, Bosch, Varta">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Battery Rating / Voltage') }}</label>
                                    <input type="text" name="size_battery" id="edit-battery-size" class="form-control" placeholder="e.g. 12V 180Ah / 24V">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                    <select name="reason_battery" id="edit-battery-reason" class="form-select">
                                        <option value="Failed">Failed</option>
                                        <option value="Weak / Low Voltage">Weak / Low Voltage</option>
                                        <option value="Dead Cell">Dead Cell</option>
                                        <option value="Scheduled Replacement">Scheduled Replacement</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- TELEMATICS FIELDS --}}
                        <div class="category-fields-group d-none" id="edit-fields-telematics">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Equipment Type') }} <span class="text-danger">*</span></label>
                                    <select name="item_name_telematics" id="edit-telematics-item-name" class="form-select">
                                        <option value="GPS Tracker">GPS Tracker</option>
                                        <option value="Fuel Level Sensor (FLS)">Fuel Level Sensor (FLS)</option>
                                        <option value="Dashcam / Camera">Dashcam / Camera</option>
                                        <option value="Temperature Sensor">Temperature Sensor</option>
                                        <option value="Panic Button">Panic Button</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                    <select name="action_telematics" id="edit-telematics-action" class="form-select">
                                        <option value="replace">Replace</option>
                                        <option value="install">Install New</option>
                                        <option value="calibrate">Calibrate</option>
                                        <option value="repair">Repair / Check</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Location on Vehicle') }}</label>
                                    <select name="location_telematics" id="edit-telematics-location" class="form-select">
                                        <option value="Cab / Dashboard">Cab / Dashboard</option>
                                        <option value="Fuel Tank Left">Fuel Tank Left</option>
                                        <option value="Fuel Tank Right">Fuel Tank Right</option>
                                        <option value="Windshield">Windshield</option>
                                        <option value="Cargo Area">Cargo Area</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Old Serial / IMEI Number') }}</label>
                                    <input type="text" name="old_serial_no_telematics" id="edit-telematics-old-serial" class="form-control font-monospace" placeholder="e.g. 863XXXXXXXXXXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('New Serial / IMEI Number') }}</label>
                                    <input type="text" name="new_serial_no_telematics" id="edit-telematics-new-serial" class="form-control font-monospace" placeholder="e.g. 863YYYYYYYYYYYY">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Manufacturer') }}</label>
                                    <input type="text" name="brand_telematics" id="edit-telematics-brand" class="form-control" placeholder="e.g. Teltonika, Ruptela">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Model') }}</label>
                                    <input type="text" name="model_telematics" id="edit-telematics-model" class="form-control" placeholder="e.g. FMC920, FMB125">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                    <select name="reason_telematics" id="edit-telematics-reason" class="form-select">
                                        <option value="Faulty">Faulty</option>
                                        <option value="Hardware Upgrade">Hardware Upgrade</option>
                                        <option value="Missing / Tampered">Missing / Tampered</option>
                                        <option value="Periodic Calibration">Periodic Calibration</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- GENERAL / BRAKES / ENGINE FIELDS --}}
                        <div class="category-fields-group d-none" id="edit-fields-general">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Component Name / Part') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="item_name_general" id="edit-general-item-name" class="form-control" placeholder="e.g. Brake Pads, Oil Filter">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                    <select name="action_general" id="edit-general-action" class="form-select">
                                        <option value="replace">Replace</option>
                                        <option value="service">Service / Flush</option>
                                        <option value="repair">Repair</option>
                                        <option value="inspect">Inspect</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Location / Position') }}</label>
                                    <input type="text" name="location_general" id="edit-general-location" class="form-control" placeholder="e.g. Front Axle, Engine Bay">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Brand / Manufacturer') }}</label>
                                    <input type="text" name="brand_general" id="edit-general-brand" class="form-control" placeholder="e.g. Bosch, Knorr-Bremse">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Old Part / Serial Number') }}</label>
                                    <input type="text" name="old_serial_no_general" id="edit-general-old-serial" class="form-control font-monospace" placeholder="e.g. PN-10293">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('New Part / Serial Number') }}</label>
                                    <input type="text" name="new_serial_no_general" id="edit-general-new-serial" class="form-control font-monospace" placeholder="e.g. PN-994820">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                    <input type="text" name="reason_general" id="edit-general-reason" class="form-control" placeholder="e.g. Worn down, Leaking">
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
                            <input type="number" step="1" min="1" name="quantity" id="edit-item-quantity" class="form-control font-monospace" value="1" placeholder="1" oninput="calculateEditModalCost()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="ri-price-tag-2-line text-primary me-1"></i> {{ __('Unit Cost') }}</label>
                            <input type="number" step="0.01" min="0" name="unit_cost" id="edit-item-unit-cost" class="form-control font-monospace" placeholder="0.00" oninput="calculateEditModalCost()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><i class="ri-money-dollar-circle-line text-success me-1"></i> {{ __('Total Cost') }}</label>
                            <input type="number" step="0.01" min="0" name="cost" id="edit-item-cost" class="form-control font-monospace" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="ri-save-line me-1"></i> {{ __('Update Maintenance Item') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function calculateAddModalCost() {
    const qty = parseFloat(document.getElementById('add-item-quantity')?.value) || 1;
    const unitCost = parseFloat(document.getElementById('add-item-unit-cost')?.value);
    const costInput = document.getElementById('add-item-cost');
    if (!isNaN(unitCost) && costInput) {
        costInput.value = (qty * unitCost).toFixed(2);
    }
}

function calculateEditModalCost() {
    const qty = parseFloat(document.getElementById('edit-item-quantity')?.value) || 1;
    const unitCost = parseFloat(document.getElementById('edit-item-unit-cost')?.value);
    const costInput = document.getElementById('edit-item-cost');
    if (!isNaN(unitCost) && costInput) {
        costInput.value = (qty * unitCost).toFixed(2);
    }
}

function switchCategoryForm(cat) {
    console.log('switchCategoryForm called with category:', cat);
    
    // Hide all category sections and disable their inputs
    const sections = ['fields-tyre', 'fields-battery', 'fields-telematics', 'fields-general'];
    sections.forEach(function(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.add('d-none');
            // Disable all inputs, selects, and textareas in hidden sections
            section.querySelectorAll('input, select, textarea').forEach(function(field) {
                field.disabled = true;
            });
        }
    });

    // Show the selected category section and enable its inputs
    let activeSection;
    if (cat === 'tyre') {
        activeSection = document.getElementById('fields-tyre');
    } else if (cat === 'battery') {
        activeSection = document.getElementById('fields-battery');
    } else if (cat === 'telematics') {
        activeSection = document.getElementById('fields-telematics');
    } else {
        activeSection = document.getElementById('fields-general');
    }
    
    if (activeSection) {
        activeSection.classList.remove('d-none');
        // Enable all inputs, selects, and textareas in the active section
        activeSection.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.disabled = false;
        });
    }
}

function switchEditCategoryForm(cat) {
    // Hide all category sections and disable their inputs
    const sections = ['edit-fields-tyre', 'edit-fields-battery', 'edit-fields-telematics', 'edit-fields-general'];
    sections.forEach(function(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.add('d-none');
            // Disable all inputs, selects, and textareas in hidden sections
            section.querySelectorAll('input, select, textarea').forEach(function(field) {
                field.disabled = true;
            });
        }
    });

    // Show the selected category section and enable its inputs
    let activeSection;
    if (cat === 'tyre') {
        activeSection = document.getElementById('edit-fields-tyre');
    } else if (cat === 'battery') {
        activeSection = document.getElementById('edit-fields-battery');
    } else if (cat === 'telematics') {
        activeSection = document.getElementById('edit-fields-telematics');
    } else {
        activeSection = document.getElementById('edit-fields-general');
    }
    
    if (activeSection) {
        activeSection.classList.remove('d-none');
        // Enable all inputs, selects, and textareas in the active section
        activeSection.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.disabled = false;
        });
    }
}

// Initialize form on page load - ensure tyre fields are enabled by default
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the add form (tyre is checked by default)
    switchCategoryForm('tyre');
    
    // Reset form when modal is hidden
    const addModal = document.getElementById('addMaintenanceItemModal');
    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function() {
            const tyreRadio = document.querySelector('input[name="category"][value="tyre"]');
            if (tyreRadio) {
                tyreRadio.checked = true;
                switchCategoryForm('tyre');
            }
            const form = addModal.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    }
});

function openEditItemModal(item) {
    const modalEl = document.getElementById('editMaintenanceItemModal');
    if (!modalEl) return;

    const form = document.getElementById('editMaintenanceItemForm');
    const updateUrlTemplate = "{{ route('maintenance.items.update', ['maintenance' => $data->id, 'item' => ':itemId']) }}";
    if (form) {
        form.action = updateUrlTemplate.replace(':itemId', item.id);
    }

    const cat = item.category || 'general';

    // Check radio button
    const radioBtn = document.getElementById('edit-cat-' + cat) || document.getElementById('edit-cat-general');
    if (radioBtn) {
        radioBtn.checked = true;
    }
    switchEditCategoryForm(cat);

    // Populate common fields
    const qtyInput = document.getElementById('edit-item-quantity');
    if (qtyInput) qtyInput.value = item.quantity || 1;

    const unitCostInput = document.getElementById('edit-item-unit-cost');
    if (unitCostInput) unitCostInput.value = (item.unit_cost !== null && item.unit_cost !== undefined) ? item.unit_cost : '';

    const costInput = document.getElementById('edit-item-cost');
    if (costInput) costInput.value = (item.cost !== null && item.cost !== undefined) ? item.cost : '';

    if (cat === 'tyre') {
        if (item.action && document.getElementById('edit-tyre-action')) document.getElementById('edit-tyre-action').value = item.action;
        if (item.axle && document.getElementById('edit-tyre-axle')) document.getElementById('edit-tyre-axle').value = item.axle;
        if (item.position && document.getElementById('edit-tyre-position')) document.getElementById('edit-tyre-position').value = item.position;
        if (document.getElementById('edit-tyre-size')) document.getElementById('edit-tyre-size').value = item.size || '';
        if (item.brand && document.getElementById('edit-tyre-brand')) document.getElementById('edit-tyre-brand').value = item.brand;
        if (document.getElementById('edit-tyre-model')) document.getElementById('edit-tyre-model').value = item.model || '';
        if (document.getElementById('edit-tyre-old-serial')) document.getElementById('edit-tyre-old-serial').value = item.old_serial_no || '';
        if (document.getElementById('edit-tyre-new-serial')) document.getElementById('edit-tyre-new-serial').value = item.new_serial_no || '';
        if (document.getElementById('edit-tyre-tread')) document.getElementById('edit-tyre-tread').value = item.tread_depth_mm || '';
        if (item.reason && document.getElementById('edit-tyre-reason')) document.getElementById('edit-tyre-reason').value = item.reason;
    } else if (cat === 'battery') {
        if (item.location && document.getElementById('edit-battery-location')) document.getElementById('edit-battery-location').value = item.location;
        if (item.action && document.getElementById('edit-battery-action')) document.getElementById('edit-battery-action').value = item.action;
        if (document.getElementById('edit-battery-old-serial')) document.getElementById('edit-battery-old-serial').value = item.old_serial_no || '';
        if (document.getElementById('edit-battery-new-serial')) document.getElementById('edit-battery-new-serial').value = item.new_serial_no || '';
        if (document.getElementById('edit-battery-brand')) document.getElementById('edit-battery-brand').value = item.brand || '';
        if (document.getElementById('edit-battery-size')) document.getElementById('edit-battery-size').value = item.size || '';
        if (item.reason && document.getElementById('edit-battery-reason')) document.getElementById('edit-battery-reason').value = item.reason;
    } else if (cat === 'telematics') {
        if (item.item_name && document.getElementById('edit-telematics-item-name')) document.getElementById('edit-telematics-item-name').value = item.item_name;
        if (item.action && document.getElementById('edit-telematics-action')) document.getElementById('edit-telematics-action').value = item.action;
        if (item.location && document.getElementById('edit-telematics-location')) document.getElementById('edit-telematics-location').value = item.location;
        if (document.getElementById('edit-telematics-old-serial')) document.getElementById('edit-telematics-old-serial').value = item.old_serial_no || '';
        if (document.getElementById('edit-telematics-new-serial')) document.getElementById('edit-telematics-new-serial').value = item.new_serial_no || '';
        if (document.getElementById('edit-telematics-brand')) document.getElementById('edit-telematics-brand').value = item.brand || '';
        if (document.getElementById('edit-telematics-model')) document.getElementById('edit-telematics-model').value = item.model || '';
        if (item.reason && document.getElementById('edit-telematics-reason')) document.getElementById('edit-telematics-reason').value = item.reason;
    } else {
        if (document.getElementById('edit-general-item-name')) document.getElementById('edit-general-item-name').value = item.item_name || '';
        if (item.action && document.getElementById('edit-general-action')) document.getElementById('edit-general-action').value = item.action;
        if (document.getElementById('edit-general-location')) document.getElementById('edit-general-location').value = item.location || '';
        if (document.getElementById('edit-general-old-serial')) document.getElementById('edit-general-old-serial').value = item.old_serial_no || '';
        if (document.getElementById('edit-general-new-serial')) document.getElementById('edit-general-new-serial').value = item.new_serial_no || '';
        if (document.getElementById('edit-general-brand')) document.getElementById('edit-general-brand').value = item.brand || '';
        if (document.getElementById('edit-general-reason')) document.getElementById('edit-general-reason').value = item.reason || '';
    }

    const editModal = new bootstrap.Modal(modalEl);
    editModal.show();
}

// Print details helper
function printDetails() {
    window.print();
}
</script>

{{-- Styles --}}
<style>
    /* ===== CATEGORY RADIO CARDS ===== */
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
        font-size: 1.4rem;
        margin-bottom: 2px;
    }
    .category-radio-card .category-name {
        font-size: 0.75rem;
        font-weight: 700;
        color: #334155;
    }
    .category-radio-card:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    .category-radio-card:has(input:checked) {
        background: #eff6ff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }
    .category-radio-card:has(input:checked) .category-name {
        color: #1d4ed8;
    }

    /* ===== ICON TILES ===== */
    .icon-tile {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; flex-shrink: 0;
    }
    .icon-tile.blue   { background: #e0e7ff; color: #4f46e5; }
    .icon-tile.amber  { background: #fef3c7; color: #d97706; }
    .icon-tile.red    { background: #fee2e2; color: #dc2626; }
    .icon-tile.cyan   { background: #cffafe; color: #0891b2; }
    .icon-tile.green  { background: #dcfce7; color: #16a34a; }
    .icon-tile.slate  { background: #e2e8f0; color: #475569; }

    /* ===== SECTION HEADERS ===== */
    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
    }
    .section-header-left { display: flex; align-items: center; gap: 0.85rem; }
    .section-title { font-weight: 700; font-size: 1.05rem; color: #1e293b; letter-spacing: -0.01em; }

    /* ===== SUBSECTION TITLES ===== */
    .subsection-title {
        font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: #4f46e5; padding: 0.5rem 0;
        border-bottom: 2px solid #e0e7ff; margin-top: 0.5rem;
    }
    .subsection-title i { margin-right: 0.35rem; }

    /* ===== INFO GROUPS ===== */
    .info-group {
        padding: 14px 16px; background: #f8fafc;
        border: 1px solid #e2e8f0; border-radius: 10px;
        transition: all 0.2s ease; height: 100%;
    }
    .info-group:hover {
        background: #ffffff; border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .info-label {
        display: block; font-size: 0.7rem; text-transform: uppercase;
        letter-spacing: 0.5px; color: #64748b; font-weight: 600; margin-bottom: 6px;
    }
    .info-label i { font-size: 0.85rem; }
    .info-value { font-size: 0.95rem; color: #1e293b; font-weight: 500; line-height: 1.5; }

    /* ===== SIDE LISTS ===== */
    .side-list { display: flex; flex-direction: column; gap: 0.5rem; }
    .side-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 14px; background: #f8fafc;
        border: 1px solid #e2e8f0; border-radius: 10px;
        transition: all 0.2s ease;
    }
    .side-item:hover { background: #ffffff; border-color: #cbd5e1; }
    .side-label {
        color: #64748b; font-size: 0.82rem; font-weight: 600;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .side-label i { font-size: 1rem; color: #94a3b8; }
    .side-value { font-weight: 600; color: #1e293b; font-size: 0.9rem; }

    /* ===== CHIPS ===== */
    .chip {
        display: inline-flex; align-items: center;
        padding: 5px 12px; border-radius: 50px;
        font-size: 0.82rem; font-weight: 600; letter-spacing: 0.2px;
    }
    .chip-blue   { background: #e0e7ff; color: #4f46e5; }
    .chip-green  { background: #dcfce7; color: #15803d; }
    .chip-red    { background: #fee2e2; color: #b91c1c; }
    .chip-amber  { background: #fef3c7; color: #b45309; }
    .chip-cyan   { background: #cffafe; color: #0e7490; }
    .chip-slate  { background: #e2e8f0; color: #334155; }

    /* ===== CARDS ===== */
    .section-card {
        background: #ffffff; border: 1px solid #e2e8f0 !important;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important; }

    /* ===== GRADIENT BUTTON ===== */
    .btn-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none; color: #ffffff; font-weight: 600;
        border-radius: 50px; padding: 0.55rem 1.25rem;
        transition: all 0.25s ease; letter-spacing: 0.3px;
    }
    .btn-gradient-blue:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: #ffffff; transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
    }

    /* ===== PRINT STYLES ===== */
    @media print {
        header, .navbar, .sidebar, .no-print, .btn, .dropdown, #statusChangeModal { display: none !important; }
        body { background: white !important; font-size: 11pt; color: black !important; }
        .print-content, .print-content * { visibility: visible; }
        .print-content { position: absolute; left: 0; top: 0; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .card { border: 1px solid #ccc !important; box-shadow: none !important; page-break-inside: avoid; margin-bottom: 15px !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td { border: 1px solid #ddd !important; padding: 6px !important; }
        @page { margin: 1.5cm; }
    }
</style>
