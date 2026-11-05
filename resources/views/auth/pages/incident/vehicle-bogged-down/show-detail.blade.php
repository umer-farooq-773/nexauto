<div class="row g-4 print-content">
    {{-- ===================== LEFT COLUMN ===================== --}}
    <div class="col-xl-8">

        {{-- ============ INCIDENT INFORMATION CARD ============ --}}
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile slate">
                            <i class="ri-roadster-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Incident Information') }}</h6>
                            <small class="text-muted">{{ __('Asset Bogged Down Detail') }}</small>
                        </div>
                    </div>

                    <div>
                        @if ($data->payment_status == '0')
                            <span class="chip chip-amber">
                                <i class="ri-time-line me-1"></i> {{ __('Pending') }}
                            </span>
                        @elseif($data->payment_status == '1')
                            <span class="chip chip-green">
                                <i class="ri-checkbox-circle-fill me-1"></i> {{ __('Approved') }}
                            </span>
                        @elseif($data->payment_status == '2')
                            <span class="chip chip-blue">
                                <i class="ri-handshake-line me-1"></i> {{ __('Processed') }}
                            </span>
                        @else
                            <span class="chip chip-slate">-</span>
                        @endif
                    </div>
                </div>

                <div class="row g-3">

                    {{-- ============ BOGGED DOWN OVERVIEW ============ --}}
                    <div class="col-12">
                        <div class="subsection-title slate">
                            <i class="ri-roadster-line"></i> {{ __('Bogged Down Overview') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-truck-line"></i> {{ __('Asset') }}</label>
                            <div class="info-value">
                                <span class="chip chip-blue">{{ $data->vehicle?->fleet_no }} - {{ $data->vehicle?->truck_no }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Bogged Date') }}</label>
                            <div class="info-value">
                                <i class="ri-calendar-2-line text-muted me-1"></i>{{ $data->bogged_date }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-map-pin-line"></i> {{ __('Stuck Location') }}</label>
                            <div class="info-value">
                                <i class="ri-map-pin-2-fill text-danger me-1"></i>{{ $data->stuck_location }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-roadster-line"></i> {{ __('Ground Condition') }}</label>
                            <div class="info-value">{{ $data->ground_condition ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- ============ RECOVERY ============ --}}
                    <div class="col-12 mt-3">
                        <div class="subsection-title amber">
                            <i class="ri-tools-line"></i> {{ __('Recovery Details') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-money-dollar-circle-line"></i> {{ __('Recovery Cost') }}</label>
                            <div class="info-value text-primary fw-bold">
                                {{ $data->recovery_cost ? number_format($data->recovery_cost, 2) : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-calendar-close-line"></i> {{ __('Recovered Date') }}</label>
                            <div class="info-value">
                                <i class="ri-calendar-2-line text-muted me-1"></i>
                                {{ $data->recovered_date ? \Carbon\Carbon::parse($data->recovered_date)->format('d M Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-checkbox-circle-line"></i> {{ __('Recovery Notes') }}</label>
                            <div class="info-value">{{ $data->description ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- ============ DOCUMENT ============ --}}
                    <div class="col-12 mt-3">
                        <div class="subsection-title cyan">
                            <i class="ri-attachment-line"></i> {{ __('Document') }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-folder-line"></i> {{ __('Attached Document') }}</label>
                            <div class="info-value">
                                @if ($data->document_url)
                                    <a href="{{ $data->document_url }}" target="_blank" class="text-decoration-none">
                                        <span class="chip chip-red">
                                            <i class="ri-file-pdf-2-line me-1"></i>
                                            {{ $data->document_name ?? __('View Document') }}
                                        </span>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ===================== RIGHT COLUMN ===================== --}}
    <div class="col-xl-4">

        {{-- ============ ACTIVITY & APPROVAL LOG ============ --}}
        <x-auth.incident-audit-card :data="$data" />

        {{-- ============ STATUS OVERVIEW ============ --}}
        <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile green">
                            <i class="ri-pie-chart-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Status Overview') }}</h6>
                            <small class="text-muted">{{ __('Approval & progress state') }}</small>
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
                            @if ($data->payment_status == '0') chip-amber
                            @elseif($data->payment_status == '1') chip-green
                            @elseif($data->payment_status == '2') chip-blue
                            @else chip-slate @endif">
                            <i class="ri-{{ $data->payment_status == '0' ? 'time-line' : ($data->payment_status == '1' ? 'checkbox-circle-fill' : ($data->payment_status == '2' ? 'handshake-line' : 'subtract-line')) }} me-1"></i>
                            @if ($data->payment_status == '0') {{ __('Pending') }}
                            @elseif($data->payment_status == '1') {{ __('Approved') }}
                            @elseif($data->payment_status == '2') {{ __('Processed') }}
                            @else {{ __('N/A') }} @endif
                        </span>
                    </div>

                    <div class="side-item">
                        <span class="side-label"><i class="ri-roadster-line"></i> {{ __('Incident Status') }}</span>
                        <span class="chip
                            @if ($data->status == '0') chip-amber
                            @elseif($data->status == '1') chip-green
                            @elseif($data->status == '2') chip-blue
                            @else chip-slate @endif">
                            <i class="ri-{{ $data->status == '0' ? 'time-line' : ($data->status == '1' ? 'checkbox-circle-fill' : ($data->status == '2' ? 'loader-4-line' : 'subtract-line')) }} me-1"></i>
                            @if ($data->status == '0') {{ __('Pending') }}
                            @elseif($data->status == '1') {{ __('Resolved') }}
                            @elseif($data->status == '2') {{ __('In Progress') }}
                            @else {{ __('N/A') }} @endif
                        </span>
                    </div>

                    @if ($approved_by)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-user-follow-line"></i> {{ __('Approved By') }}</span>
                        <span class="side-value text-success">
                            <i class="ri-user-check-line me-1"></i>
                            {{ $approved_by->first_name.' '.$approved_by->last_name }}
                        </span>
                    </div>
                    @endif

                    @if ($payment_paid_by)
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
        @if ($data->client_approved && in_array($data->payment_status, ['0', '1']))
        <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
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
                    <button type="button" class="btn btn-gradient-blue" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
                        <i class="ri-bank-card-line me-2"></i> {{ __('Process Payment') }}
                    </button>
                    @if ($data->status != '1')
                    <button type="button" class="btn btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#statusChangeModal">
                        <i class="ri-refresh-line me-2"></i> {{ __('Update Status') }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ============ ASSET INFORMATION ============ --}}
        @if ($data->vehicle)
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile blue">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Asset Information') }}</h6>
                            <small class="text-muted">{{ __('Linked vehicle details') }}</small>
                        </div>
                    </div>
                </div>

                <div class="side-list">
                    <div class="side-item">
                        <span class="side-label"><i class="ri-hashtag"></i> {{ __('Fleet No') }}</span>
                        <span class="side-value">{{ $data->vehicle->fleet_no ?? '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-truck-line"></i> {{ __('Truck No') }}</span>
                        <span class="side-value">{{ $data->vehicle->truck_no ?? '-' }}</span>
                    </div>
                    @if ($data->vehicle->drivers && $data->vehicle->drivers->count() > 0)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-user-line"></i> {{ __('Driver') }}</span>
                        <span class="side-value">{{ $data->vehicle->drivers->pluck('full_name')->join(', ') }}</span>
                    </div>
                    @endif
                    @if ($data->vehicle->make)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-building-2-line"></i> {{ __('Make') }}</span>
                        <span class="side-value">{{ $data->vehicle->make ?? '-' }}</span>
                    </div>
                    @endif
                    @if ($data->vehicle->model)
                    <div class="side-item">
                        <span class="side-label"><i class="ri-roadster-line"></i> {{ __('Model') }}</span>
                        <span class="side-value">{{ $data->vehicle->model ?? '-' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@include('auth.pages.maintenance._expense_history')

{{-- Chat Section --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-0">
                <x-auth.incident-chat :incident-type="'vehicle_breakdown'" :incident-id="$data->id" />
            </div>
        </div>
    </div>
</div>

{{-- Status Change Modal --}}
@php
    $user = auth()->user();
    $canChangeStatus =
        $user &&
        $user->hasRole([
            \App\Constants\Constants::ADMIN,
            \App\Constants\Constants::SUPERADMIN,
            \App\Constants\Constants::CHAMPS_MANAGEMENT,
        ]);
    $clientApproved = (bool) ($data->client_approved ?? false);
    $paymentApproved = (string) ($data->payment_status ?? '') === '2';
@endphp

@if ($canChangeStatus && $clientApproved && $paymentApproved)
    <div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="statusChangeModalLabel">
                        <i class="ri-refresh-line text-warning me-2"></i>
                        {{ __('Update Status') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm" method="POST" action="{{ route('vehicle_breakdown.status_update', $data->id) }}">
                    @method('PATCH')
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                                <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
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
                            <i class="ri-refresh-line me-1"></i> {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Styles --}}
<style>
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

    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
    }
    .section-header-left { display: flex; align-items: center; gap: 0.85rem; }
    .section-title { font-weight: 700; font-size: 1.05rem; color: #1e293b; }

    .subsection-title {
        font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: #4f46e5; padding: 0.5rem 0;
        border-bottom: 2px solid #e0e7ff; margin-top: 0.5rem;
    }
    .subsection-title i { margin-right: 0.35rem; }
    .subsection-title.amber  { color: #d97706; border-bottom-color: #fef3c7; }
    .subsection-title.cyan   { color: #0891b2; border-bottom-color: #cffafe; }
    .subsection-title.slate  { color: #475569; border-bottom-color: #e2e8f0; }

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

    .chip {
        display: inline-flex; align-items: center;
        padding: 5px 12px; border-radius: 50px;
        font-size: 0.82rem; font-weight: 600;
    }
    .chip-blue   { background: #e0e7ff; color: #4f46e5; }
    .chip-green  { background: #dcfce7; color: #15803d; }
    .chip-red    { background: #fee2e2; color: #b91c1c; }
    .chip-amber  { background: #fef3c7; color: #b45309; }
    .chip-cyan   { background: #cffafe; color: #0e7490; }
    .chip-slate  { background: #e2e8f0; color: #334155; }

    .section-card {
        background: #ffffff; border: 1px solid #e2e8f0 !important;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important; }

    .btn-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none; color: #ffffff; font-weight: 600;
        border-radius: 50px; padding: 0.55rem 1.25rem;
        transition: all 0.25s ease;
    }
    .btn-gradient-blue:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: #ffffff; transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
    }

    @media (max-width: 768px) {
        .section-header { flex-direction: column; align-items: flex-start; }
        .info-group { padding: 12px 14px; }
        .icon-tile { width: 40px; height: 40px; font-size: 1.15rem; }
    }

    @media print {
        body * { visibility: hidden; }
        .print-content, .print-content * { visibility: visible; }
        .print-content { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
        .no-print { display: none !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        @page { margin: 0.5in; }
    }
</style>
