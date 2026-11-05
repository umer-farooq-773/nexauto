{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<x-layouts.auth page-title="{{ __('Goods Inspection Details') }}" sub-title="">
    {{-- ===================== HEADER ACTION BAR ===================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 topbar-card">
                <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-tile blue">
                            <i class="ri-shield-check-line"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ __('Goods Inspection Record') }}</h5>
                            <small class="text-muted">
                                {{ __('Ref') }}: <span class="fw-semibold text-primary">#{{ $data->id ?? '—' }}</span>
                                &nbsp;•&nbsp;
                                {{ __('Fleet') }}: <span class="fw-semibold">{{ $data->fleet_no ?? '—' }}</span>
                            </small>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        {{-- <button type="button" onclick="window.print()" class="btn btn-gradient-blue shadow-sm">
                            <i class="fas fa-file-pdf me-1"></i> {{ __('PDF Download') }}
                        </button> --}}
                        <x-auth.view-map-button :incident="$data" incidentType="goods_inspection" :incidentId="$data->id" />
                        <x-auth.incident-action-buttons
                            :data="$data"
                            approve-route="goods_inspection.client_approve"
                            table="goods_inspections"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 print-content">
        {{-- ===================== LEFT COLUMN ===================== --}}
        <div class="col-xl-8">

            {{-- ============ MAIN DETAILS CARD ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">

                    {{-- Section Header --}}
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-clipboard-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Goods Inspection Details') }}</h6>
                                <small class="text-muted">{{ __('Inspection Information & Module Findings') }}</small>
                            </div>
                        </div>
                        <span class="required-badge">{{ __('Read Only') }}</span>
                    </div>

                    <div class="row g-3">

                        {{-- ============ ASSET & DISPATCH ============ --}}
                        <div class="col-12">
                            <div class="subsection-title">
                                <i class="ri-truck-line"></i> {{ __('Asset & Dispatch Information') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-truck-line"></i> {{ __('Fleet No') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-blue">{{ $data->fleet_no ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Inspection Date') }}</label>
                                <div class="info-value">
                                    <i class="ri-calendar-2-line text-muted me-1"></i>
                                    {{ $data->inspection_date ? $data->inspection_date->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-file-invoice-line"></i> {{ __('Waybill Number') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-slate">{{ $data->waybill_number ?? $data->waybill?->waybill_number ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-line"></i> {{ __('Driver Name') }}</label>
                                <div class="info-value">
                                    <strong>{{ $data->driver_name ?? $data->waybill?->driver?->full_name ?? '-' }}</strong>
                                    @if($data->driver_phone_id)
                                        <small class="text-muted d-block mt-1"><i class="ri-phone-line me-1"></i>{{ $data->driver_phone_id }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-route-line"></i> {{ __('Route (From – To)') }}</label>
                                <div class="info-value">
                                    <i class="ri-road-map-line text-muted me-1"></i>{{ $data->route ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-price-tag-3-line"></i> {{ __('Inspection Type') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-cyan">{{ ucfirst($data->inspection_type ?? '-') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-map-pin-line"></i> {{ __('Inspection Location') }}</label>
                                <div class="info-value">
                                    <i class="ri-map-pin-2-fill text-danger me-1"></i>{{ $data->inspection_location ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-shield-line"></i> {{ __('Inspector Name') }}</label>
                                <div class="info-value">
                                    <i class="ri-verified-badge-line text-primary me-1"></i>{{ $data->inspector_name ?? '-' }}
                                </div>
                            </div>
                        </div>

                        @if(!empty($data->goods_description))
                        <div class="col-12">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-box-3-line"></i> {{ __('Goods Description') }}</label>
                                <div class="info-value">{{ $data->goods_description }}</div>
                            </div>
                        </div>
                        @endif

                        {{-- ============ CARGO VERIFICATION & FINDINGS ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title amber">
                                <i class="ri-scales-3-line"></i> {{ __('Cargo Verification & Quantity Findings') }}
                            </div>
                        </div>

                        @if($data->cargos && $data->cargos->count() > 0)
                            <div class="col-12">
                                <div class="table-responsive border rounded-3 overflow-hidden shadow-sm">
                                    <table class="table table-hover table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-2 px-3">#</th>
                                                <th class="py-2 px-3">{{ __('Cargo') }}</th>
                                                <th class="py-2 px-3">{{ __('Declared') }}</th>
                                                <th class="py-2 px-3">{{ __('Observed') }}</th>
                                                <th class="py-2 px-3">{{ __('Difference') }}</th>
                                                <th class="py-2 px-3">{{ __('Match?') }}</th>
                                                <th class="py-2 px-3">{{ __('Shortage?') }}</th>
                                                <th class="py-2 px-3">{{ __('Haulage?') }}</th>
                                                <th class="py-2 px-3">{{ __('Remarks') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data->cargos as $index => $cargo)
                                                @php
                                                    $diff = $cargo->difference_quantity ?? (abs(($cargo->declared_quantity ?? 0) - ($cargo->observed_quantity ?? 0)));
                                                    $isExcess = ($cargo->observed_quantity ?? 0) > ($cargo->declared_quantity ?? 0);
                                                    $isShortage = ($cargo->observed_quantity ?? 0) < ($cargo->declared_quantity ?? 0);
                                                @endphp
                                                <tr>
                                                    <td class="py-2 px-3 fw-bold text-muted">{{ $index + 1 }}</td>
                                                    <td class="py-2 px-3">
                                                        <strong class="text-dark">{{ $cargo->cargo_description ?? '-' }}</strong>
                                                        @if($cargo->cargo_weight)
                                                            <small class="text-muted d-block">{{ $cargo->cargo_weight }} {{ $cargo->weight_unit ?? 'kg' }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <span class="chip chip-slate">{{ $cargo->declared_quantity ?? 0 }} {{ $cargo->quantity_unit ?? '' }}</span>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <span class="chip chip-blue">{{ $cargo->observed_quantity ?? 0 }} {{ $cargo->quantity_unit ?? '' }}</span>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if($isExcess)
                                                            <span class="badge bg-danger text-white">+{{ $diff }} {{ $cargo->quantity_unit ?? '' }}</span>
                                                        @elseif($isShortage)
                                                            <span class="badge bg-warning text-dark">-{{ $diff }} {{ $cargo->quantity_unit ?? '' }}</span>
                                                        @else
                                                            <span class="badge bg-success text-white">0 (Match)</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <span class="chip {{ ($cargo->goods_match_waybill ?? 'Yes') == 'Yes' ? 'chip-green' : 'chip-red' }}">
                                                            {{ $cargo->goods_match_waybill ?? 'Yes' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <span class="chip {{ ($cargo->illegal_shortage ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                                            {{ $cargo->illegal_shortage ?? 'No' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <span class="chip {{ ($cargo->illegal_haulage ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                                            {{ $cargo->illegal_haulage ?? 'No' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3 small text-muted">
                                                        {{ $cargo->remarks ?: '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="col-md-4">
                                <div class="info-group">
                                    <label class="info-label">{{ __('Declared Quantity') }}</label>
                                    <div class="info-value fw-bold">{{ $data->declared_quantity ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-group">
                                    <label class="info-label">{{ __('Observed Quantity') }}</label>
                                    <div class="info-value fw-bold">{{ $data->observed_quantity ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="info-group">
                                    <label class="info-label">{{ __('Goods Match Waybill?') }}</label>
                                    <div class="info-value">
                                        <span class="chip {{ ($data->goods_match_waybill ?? 'Yes') == 'Yes' ? 'chip-green' : 'chip-red' }}">
                                            <i class="{{ ($data->goods_match_waybill ?? 'Yes') == 'Yes' ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' }} me-1"></i>
                                            {{ $data->goods_match_waybill ?? 'Yes' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="info-group">
                                    <label class="info-label">{{ __('Illegal Shortage Flag') }}</label>
                                    <div class="info-value">
                                        <span class="chip {{ ($data->illegal_shortage ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                            <i class="{{ ($data->illegal_shortage ?? 'No') == 'No' ? 'ri-shield-check-line' : 'ri-alarm-warning-line' }} me-1"></i>
                                            {{ $data->illegal_shortage ?? 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ============ ILLEGAL HAULAGE ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title red">
                                <i class="ri-truck-line"></i> {{ __('Illegal Haulage Findings') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">{{ __('Unauthorised Items?') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->unauthorised_items ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->unauthorised_items ?? 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">{{ __('Illegal Haulage Flag') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->illegal_haulage ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->illegal_haulage ?? 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- ============ DRIVER MISCONDUCT ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title cyan">
                                <i class="ri-user-unfollow-line"></i> {{ __('Driver Misconduct & Physical Security') }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">{{ __('Cargo Properly Secured?') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->cargo_secured ?? 'Yes') == 'Yes' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->cargo_secured ?? 'Yes' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">{{ __('Doors/Tarpaulin Secure?') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->doors_tarpaulin_secure ?? 'Yes') == 'Yes' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->doors_tarpaulin_secure ?? 'Yes' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">{{ __('Visible Damage?') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->visible_damage ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->visible_damage ?? 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">{{ __('Driver Misconduct Flag') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->driver_misconduct ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->driver_misconduct ?? 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- ============ ROUTE DIVERSION ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title slate">
                                <i class="ri-route-line"></i> {{ __('Route Diversion & Driver Compliance Test') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">{{ __('Route Diversion?') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->route_diversion ?? 'No') == 'No' ? 'chip-green' : 'chip-red' }}">
                                        {{ $data->route_diversion ?? 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">{{ __('Breathaliser Test?') }}</label>
                                <div class="info-value">
                                    <span class="chip {{ ($data->breathaliser_test ?? 'Passed') == 'Passed' ? 'chip-green' : (($data->breathaliser_test ?? '') == 'Failed' ? 'chip-red' : 'chip-slate') }}">
                                        <i class="ri-test-tube-line me-1"></i>{{ $data->breathaliser_test ?? 'Passed' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- ============ SUMMARY ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title dark">
                                <i class="ri-file-list-3-line"></i> {{ __('Summary & Remarks') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-box-3-line"></i> {{ __('Goods Status') }}</label>
                                <div class="info-value">{!! $data->goods_badge ?? '-' !!}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-information-line"></i> {{ __('Inspection Status') }}</label>
                                <div class="info-value">{!! $data->status_badge ?? '-' !!}</div>
                            </div>
                        </div>

                        @if(!empty($data->remarks))
                        <div class="col-12">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-sticky-note-line"></i> {{ __('Remarks') }}</label>
                                <div class="info-value">{{ $data->remarks }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ ASSOCIATED INCIDENTS TABLE ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card">
                <div class="card-body p-4">
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile amber">
                                <i class="ri-alarm-warning-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Associated Incident Records') }}</h6>
                                <small class="text-muted">{{ __('Incidents triggered and linked to this Goods Inspection') }}</small>
                            </div>
                        </div>
                        @if(isset($incidentEvaluation['incidents']) && count($incidentEvaluation['incidents']) > 0)
                            <span class="required-badge">
                                <i class="ri-links-line me-1"></i>{{ count($incidentEvaluation['incidents']) }} {{ __('Linked') }}
                            </span>
                        @endif
                    </div>

                    @if(isset($incidentEvaluation['incidents']) && count($incidentEvaluation['incidents']) > 0)
                        <div class="table-responsive">
                            <table class="table modern-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 180px;">{{ __('Incident Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Date & Location') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incidentEvaluation['incidents'] as $inc)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="table-icon-tile">
                                                        <i class="{{ $inc['icon'] ?? 'ri-alert-line' }}"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark">{{ $inc['label'] }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($inc['exists'])
                                                    <span class="chip chip-green">
                                                        <i class="ri-checkbox-circle-fill me-1"></i>{{ __('Created') }}
                                                    </span>
                                                @else
                                                    <span class="chip chip-amber">
                                                        <i class="ri-alert-fill me-1"></i>{{ __('Required') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="text-dark fw-medium">{{ $inc['date'] ?? $data->inspection_date?->format('d/m/Y') ?? '-' }}</div>
                                                    <div class="text-muted"><i class="ri-map-pin-line me-1"></i>{{ $inc['location'] ?? $data->inspection_location ?? '-' }}</div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                @if($inc['exists'] && !empty($inc['show_url']))
                                                    <a href="{{ $inc['show_url'] }}" class="btn btn-sm btn-gradient-blue rounded-pill px-3">
                                                        <i class="ri-eye-line me-1"></i> {{ __('View') }}
                                                    </a>
                                                @elseif($inc['exists'] && !empty($inc['edit_url']))
                                                    <a href="{{ $inc['edit_url'] }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                        <i class="ri-edit-line me-1"></i> {{ __('Edit') }}
                                                    </a>
                                                @else
                                                    <span class="text-muted small fst-italic">{{ __('Not Created Yet') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="ri-shield-check-line"></i>
                            </div>
                            <h6 class="fw-semibold text-dark mb-1">{{ __('No Incidents Required') }}</h6>
                            <p class="small text-muted mb-0">{{ __('All inspection checks passed cleanly without triggering any incident requirements.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===================== RIGHT COLUMN ===================== --}}
        <div class="col-xl-4">

            @if($data->vehicle)
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">
                    <div class="section-header mb-3">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-truck-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Vehicle Information') }}</h6>
                                <small class="text-muted">{{ __('Registered vehicle details') }}</small>
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
                        @if($data->vehicle->make)
                        <div class="side-item">
                            <span class="side-label"><i class="ri-building-2-line"></i> {{ __('Make') }}</span>
                            <span class="side-value">{{ $data->vehicle->make }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Compliance Summary --}}
            <div class="card border-0 shadow-sm rounded-4 section-card">
                <div class="card-body p-4">
                    <div class="section-header mb-3">
                        <div class="section-header-left">
                            <div class="icon-tile green">
                                <i class="ri-shield-keyhole-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Compliance Summary') }}</h6>
                                <small class="text-muted">{{ __('Overall inspection outcome') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="side-list">
                        <div class="side-item">
                            <span class="side-label"><i class="ri-box-3-line"></i> {{ __('Inspection Result') }}</span>
                            <span class="side-value">{!! $data->goods_badge ?? '-' !!}</span>
                        </div>
                        <div class="side-item">
                            <span class="side-label"><i class="ri-information-line"></i> {{ __('Status') }}</span>
                            <span class="side-value">{!! $data->status_badge ?? '-' !!}</span>
                        </div>
                        <div class="side-item">
                            <span class="side-label"><i class="ri-links-line"></i> {{ __('Incidents Attached') }}</span>
                            <span class="side-value">
                                <span class="chip chip-slate">
                                    {{ isset($incidentEvaluation['incidents']) ? count($incidentEvaluation['incidents']) : 0 }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== STYLES ===================== --}}
    <style>
        /* ===== TOP BAR ===== */
        .topbar-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        /* ===== ICON TILES ===== */
        .icon-tile {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        .icon-tile.blue   { background: #e0e7ff; color: #4f46e5; }
        .icon-tile.amber  { background: #fef3c7; color: #d97706; }
        .icon-tile.red    { background: #fee2e2; color: #dc2626; }
        .icon-tile.cyan   { background: #cffafe; color: #0891b2; }
        .icon-tile.green  { background: #dcfce7; color: #16a34a; }
        .icon-tile.slate  { background: #e2e8f0; color: #475569; }
        .icon-tile.dark   { background: #e2e8f0; color: #334155; }

        /* ===== SECTION HEADERS ===== */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .section-header-left {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .section-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #1e293b;
            letter-spacing: -0.01em;
        }
        .required-badge {
            background-color: #e0e7ff;
            color: #4f46e5;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        /* ===== SUBSECTION TITLES ===== */
        .subsection-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #4f46e5;
            padding: 0.5rem 0;
            border-bottom: 2px solid #e0e7ff;
            margin-top: 0.5rem;
        }
        .subsection-title i { margin-right: 0.35rem; }
        .subsection-title.amber  { color: #d97706; border-bottom-color: #fef3c7; }
        .subsection-title.red    { color: #dc2626; border-bottom-color: #fee2e2; }
        .subsection-title.cyan   { color: #0891b2; border-bottom-color: #cffafe; }
        .subsection-title.slate  { color: #475569; border-bottom-color: #e2e8f0; }
        .subsection-title.dark   { color: #334155; border-bottom-color: #cbd5e1; }

        /* ===== INFO GROUPS ===== */
        .info-group {
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
            height: 100%;
        }
        .info-group:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .info-label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .info-label i { font-size: 0.85rem; }
        .info-value {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 500;
            line-height: 1.5;
        }

        /* ===== CHIPS ===== */
        .chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .chip-blue   { background: #e0e7ff; color: #4f46e5; }
        .chip-green  { background: #dcfce7; color: #15803d; }
        .chip-red    { background: #fee2e2; color: #b91c1c; }
        .chip-amber  { background: #fef3c7; color: #b45309; }
        .chip-cyan   { background: #cffafe; color: #0e7490; }
        .chip-slate  { background: #e2e8f0; color: #334155; }

        /* ===== SIDE LISTS (RIGHT COLUMN) ===== */
        .side-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .side-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .side-item:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }
        .side-label {
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .side-label i { font-size: 1rem; color: #94a3b8; }
        .side-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        /* ===== MODERN TABLE ===== */
        .modern-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        .modern-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            padding: 14px 16px;
            border: none;
            border-bottom: 1px solid #e2e8f0;
        }
        .modern-table thead th:first-child { border-top-left-radius: 10px; }
        .modern-table thead th:last-child  { border-top-right-radius: 10px; }
        .modern-table tbody tr {
            transition: background 0.15s ease;
        }
        .modern-table tbody tr:hover {
            background: #f8fafc;
        }
        .modern-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-icon-tile {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #e0e7ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-right: 0.85rem;
            flex-shrink: 0;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
        }
        .empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        /* ===== GRADIENT BUTTON ===== */
        .btn-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 50px;
            padding: 0.55rem 1.25rem;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
        }
        .btn-gradient-blue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }

        /* ===== CARDS ===== */
        .section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: box-shadow 0.2s ease;
        }
        .section-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .info-group { padding: 12px 14px; }
            .icon-tile { width: 40px; height: 40px; font-size: 1.15rem; }
            .modern-table thead th,
            .modern-table tbody td { padding: 12px 10px; }
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            body * { visibility: hidden; }
            .print-content, .print-content * { visibility: visible; }
            .print-content { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
            .btn { display: none !important; }
            @page { margin: 0.5in; }
        }
    </style>
</x-layouts.auth>

