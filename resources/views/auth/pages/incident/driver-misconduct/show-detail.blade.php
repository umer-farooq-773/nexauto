<div class="row g-4 print-content">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile amber">
                            <i class="ri-user-unfollow-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Incident Information') }}</h6>
                            <small class="text-muted">{{ __('Breathaliser Test Details') }}</small>
                        </div>
                    </div>

                    <div>
                        @if($data->status == '0')
                            <span class="chip chip-amber">
                                <i class="ri-time-line me-1"></i> {{ __('Open') }}
                            </span>
                        @elseif($data->status == '1')
                            <span class="chip chip-blue">
                                <i class="ri-search-line me-1"></i> {{ __('Under Review') }}
                            </span>
                        @elseif($data->status == '2')
                            <span class="chip chip-green">
                                <i class="ri-checkbox-circle-fill me-1"></i> {{ __('Closed') }}
                            </span>
                        @else
                            <span class="chip chip-slate">-</span>
                        @endif
                    </div>
                </div>

                <div class="row g-3">

                    {{-- ============ MISCONDUCT IDENTIFICATION ============ --}}
                    <div class="col-12">
                        <div class="subsection-title amber">
                            <i class="ri-user-unfollow-line"></i> {{ __('Misconduct Identification') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-truck-line"></i> {{ __('Driver') }}</label>
                            <div class="info-value">
                                <span class="chip chip-blue">{{ $data->vehicle?->fleet_no ?? '-' }} - {{ $data->vehicle?->truck_no ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-file-invoice-line"></i> {{ __('Waybill') }}</label>
                            <div class="info-value">
                                <span class="chip chip-slate">{{ $data->waybill?->waybill_number ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Misconduct Date') }}</label>
                            <div class="info-value">
                                <i class="ri-calendar-2-line text-muted me-1"></i>{{ $data->misconduct_date }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-map-pin-line"></i> {{ __('Location') }}</label>
                            <div class="info-value">
                                <i class="ri-map-pin-2-fill text-danger me-1"></i>{{ $data->misconduct_location }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-scales-3-line"></i> {{ __('Misconduct Type') }}</label>
                            <div class="info-value text-primary fw-bold">{{ $data->misconduct_type }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-error-warning-line"></i> {{ __('Severity') }}</label>
                            <div class="info-value">{!! $data->severity_badge !!}</div>
                        </div>
                    </div>

                    {{-- <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="fas fa-check-circle me-1"></i> {{ __('Status') }}</label>
                            <div>{!! $data->status_badge !!}</div>
                        </div>
                    </div> --}}

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-checkbox-circle-line"></i> {{ __('Action Taken') }}</label>
                            <div class="info-value">{!! $data->action_taken !!}</div>
                        </div>
                    </div>

                    {{-- ============ REMARKS & DOCUMENTS ============ --}}
                    <div class="col-12 mt-3">
                        <div class="subsection-title slate">
                            <i class="ri-file-text-line"></i> {{ __('Remarks & Documents') }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-align-left"></i> {{ __('Remarks') }}</label>
                            <div class="info-value">{{ $data->remarks ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-folder-line"></i> {{ __('Document') }}</label>
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

    {{-- Right Column --}}
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
                        <span class="side-label"><i class="ri-user-unfollow-line"></i> {{ __('Incident Status') }}</span>
                        @php
                            $statusConfig = $statusConfig ?? [
                                '0' => ['label' => 'Open',     'icon' => 'time-line',            'class' => 'chip-amber'],
                                '1' => ['label' => 'Reviewed', 'icon' => 'search-line',          'class' => 'chip-blue'],
                                '2' => ['label' => 'Resolved', 'icon' => 'checkbox-circle-fill', 'class' => 'chip-green'],
                            ];
                            $status = $statusConfig[$data->status] ?? ['label' => 'N/A', 'icon' => 'subtract-line', 'class' => 'chip-slate'];
                        @endphp
                        <span class="chip {{ $status['class'] }}">
                            <i class="ri-{{ $status['icon'] }} me-1"></i>
                            <span>{{ __($status['label']) }}</span>
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
                </div>
            </div>
        </div>

        {{-- ============ ASSET INFORMATION ============ --}}
        @if($data->vehicle)
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile blue">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('VeAssethicle Information') }}</h6>
                            <small class="text-muted">{{ __('Cargo & route details') }}</small>
                        </div>
                    </div>
                </div>

                <div class="side-list">
                    <div class="side-item">
                        <span class="side-label"><i class="ri-box-3-line"></i> {{ __('Cargo') }}</span>
                        <span class="side-value">{{ $data->cargo_description ?? ($data->waybill?->cargo_description ?? '-') }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-route-line"></i> {{ __('Current Waybill Route') }}</span>
                        <span class="side-value">{{ $data->waybill?->origin ?? '-' }}{{ $data->waybill?->destination ? ' -> ' . $data->waybill->destination : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

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
