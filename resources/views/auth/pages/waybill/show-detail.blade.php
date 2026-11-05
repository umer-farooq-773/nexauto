{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<div class="row g-4 print-content">
    {{-- ===================== LEFT COLUMN ===================== --}}
    <div class="col-xl-8">

        {{-- ============ WAYBILL INFORMATION CARD ============ --}}
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile blue">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('WayBill Information') }}</h6>
                            <small class="text-muted">{{ __('Way Bill Details') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Read Only') }}</span>
                </div>

                <div class="row g-3">

                    {{-- ============ IDENTIFICATION ============ --}}
                    <div class="col-12">
                        <div class="subsection-title">
                            <i class="ri-file-list-3-line"></i> {{ __('Waybill Identification') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-hashtag"></i> {{ __('Waybill Number') }}</label>
                            <div class="info-value">
                                <span class="chip chip-blue">{{ $data->waybill_number }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-truck-line"></i> {{ __('Asset') }}</label>
                            <div class="info-value">
                                <i class="ri-truck-line text-primary me-1"></i>
                                {{ $data->vehicle?->fleet_no ?? '-' }} ({{ $data->vehicle?->truck_no ?? '-' }})
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-user-line"></i> {{ __('Driver') }}</label>
                            <div class="info-value">
                                <i class="ri-user-line text-success me-1"></i>{{ $data->driver?->full_name ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-route-line"></i> {{ __('Route / Trip') }}</label>
                            <div class="info-value">
                                @if($data->origin && $data->destination)
                                    <i class="ri-map-pin-2-fill text-primary me-1"></i>
                                    {{ $data->origin }}
                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                    {{ $data->destination }}
                                @else
                                    <i class="ri-subtract-line text-muted me-1"></i> -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Dispatch Date') }}</label>
                            <div class="info-value text-primary fw-bold">
                                <i class="ri-calendar-2-line me-1"></i>
                                {{ $data->dispatch_date?->format('d M Y') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    {{-- ============ CARGO ============ --}}
                    <div class="col-12 mt-3">
                        <div class="subsection-title cyan">
                            <i class="ri-box-3-line"></i> {{ __('Cargo Information') }}
                        </div>
                    </div>

                    @if($data->cargos && $data->cargos->count() > 0)
                        <div class="col-12">
                            <div class="table-responsive border rounded-3 overflow-hidden shadow-sm">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2 px-3">#</th>
                                            <th class="py-2 px-3">{{ __('Cargo Description') }}</th>
                                            <th class="py-2 px-3">{{ __('Quantity') }}</th>
                                            <th class="py-2 px-3">{{ __('Cargo Weight') }}</th>
                                            <th class="py-2 px-3">{{ __('Volume Unit') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data->cargos as $index => $cargo)
                                            <tr>
                                                <td class="py-2 px-3 fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="py-2 px-3 fw-semibold text-dark">{{ $cargo->cargo_description ?? '-' }}</td>
                                                <td class="py-2 px-3">
                                                    <span class="chip chip-cyan">{{ $cargo->quantity ?? '-' }} {{ $cargo->quantity_unit ?? '' }}</span>
                                                </td>
                                                <td class="py-2 px-3">
                                                    <span class="chip chip-slate">{{ $cargo->cargo_weight ?? '-' }} {{ $cargo->weight_unit ?? '' }}</span>
                                                </td>
                                                <td class="py-2 px-3">
                                                    <span class="chip chip-blue">{{ $cargo->volume_unit ?? '-' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-box-3-line"></i> {{ __('Cargo Description') }}</label>
                                <div class="info-value">{{ $data->cargo_description ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-scales-3-line"></i> {{ __('Quantity') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-cyan">{{ $data->quantity ?? '-' }} {{ $data->quantity_unit ?? '' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-scales-3-line"></i> {{ __('Cargo Weight') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-cyan">{{ $data->cargo_weight_kg ?? '-' }} {{ $data->weight_unit ?? '' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-drop-line"></i> {{ __('Volume Unit') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-cyan">{{ $data->volume_unit ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-information-line"></i> {{ __('Status') }}</label>
                            <div class="info-value">
                                <span class="chip chip-cyan">{{ $data->status?->badge_label ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- ============ NOTES & DOCUMENTS ============ --}}
                    <div class="col-12 mt-3">
                        <div class="subsection-title amber">
                            <i class="ri-sticky-note-line"></i> {{ __('Notes & Documents') }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-align-left"></i> {{ __('Notes') }}</label>
                            <div class="info-value">{{ $data->notes ?? '-' }}</div>
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

                    {{-- ============ AUDIT TRAIL ============ --}}
                    <div class="col-12 mt-3">
                        <div class="subsection-title slate">
                            <i class="ri-file-list-3-line"></i> {{ __('Audit Trail') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-user-add-line"></i> {{ __('Created By') }}</label>
                            <div class="info-value">{{ $data->creator?->full_name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="ri-time-line"></i> {{ __('Created At') }}</label>
                            <div class="info-value">
                                <i class="ri-calendar-2-line text-muted me-1"></i>
                                {{ $data->created_at ? $data->created_at->format('d M Y, h:i A') : '-' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ===================== RIGHT COLUMN ===================== --}}
    <div class="col-xl-4">

        {{-- ============ ASSET INFORMATION ============ --}}
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-tile green">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Asset Information') }}</h6>
                            <small class="text-muted">{{ __('Trip & asset details') }}</small>
                        </div>
                    </div>
                </div>

                <div class="side-list">
                    <div class="side-item">
                        <span class="side-label"><i class="ri-scales-3-line"></i> {{ __('Cargo Weight') }}</span>
                        <span class="side-value">{{ $data->cargo_weight_kg !== null ? number_format($data->cargo_weight_kg, 1) : '-' }}&nbsp;{{ $data->weight_unit ?? '' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-flag-line"></i> {{ __('Destination') }}</span>
                        <span class="side-value">{{ $data->destination ?? '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-road-map-line"></i> {{ __('Distance') }}</span>
                        <span class="side-value">{{ $data->distance ?? '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-time-line"></i> {{ __('Estimated Duration') }}</span>
                        <span class="side-value">{{ $data->estimated_duration ?? '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-calendar-check-line"></i> {{ __('Dispatch Date') }}</span>
                        <span class="side-value">{{ $data->dispatch_date ? \Carbon\Carbon::parse($data->dispatch_date)->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-timer-line"></i> {{ __('Estimated Dispatch Time') }}</span>
                        <span class="side-value">{{ $data->estimated_dispatch_time ?? '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label"><i class="ri-route-line"></i> {{ __('Route') }}</span>
                        <span class="side-value">
                            @if($data->origin && $data->destination)
                                <i class="ri-map-pin-2-fill text-primary me-1"></i>
                                {{ $data->origin }}
                                <i class="ri-arrow-right-line text-muted mx-1"></i>
                                <i class="ri-map-pin-2-fill text-danger me-1"></i>
                                {{ $data->destination }}
                            @else
                                <i class="ri-subtract-line text-muted me-1"></i> -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    .required-badge {
        background-color: #e0e7ff; color: #4f46e5;
        font-size: 0.75rem; font-weight: 600;
        padding: 0.35rem 0.85rem; border-radius: 50px; white-space: nowrap;
    }

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
    .chip-red    { background: #fee2e2; color: #b91c1c; }
    .chip-cyan   { background: #cffafe; color: #0e7490; }
    .chip-slate  { background: #e2e8f0; color: #334155; }

    .section-card {
        background: #ffffff; border: 1px solid #e2e8f0 !important;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important; }

    @media (max-width: 768px) {
        .section-header { flex-direction: column; align-items: flex-start; }
        .info-group { padding: 12px 14px; }
        .icon-tile { width: 40px; height: 40px; font-size: 1.15rem; }
    }
</style>
