{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<x-layouts.auth page-title="{{__('Asset Details')}}" sub-title="">
    {{-- ===================== HEADER ACTION BAR ===================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 topbar-card">
                <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-tile blue">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ __('Asset Record') }}</h5>
                            <small class="text-muted">
                                {{ __('Fleet') }}: <span class="fw-semibold text-primary">{{ $data->fleet_no ?? '—' }}</span>
                                &nbsp;•&nbsp;
                                {{ __('Truck') }}: <span class="fw-semibold">{{ $data->truck_no ?? '—' }}</span>
                            </small>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        {{-- slot for action buttons if needed later --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 print-content">
        {{-- ===================== MAIN COLUMN ===================== --}}
        <div class="col-xl-12">

            {{-- ============ MAIN DETAILS CARD ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">

                    {{-- Section Header --}}
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-truck-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Asset Details') }}</h6>
                                <small class="text-muted">{{ __('Vehicle, Ownership & Operational Information') }}</small>
                            </div>
                        </div>
                        <span class="required-badge">{{ __('Read Only') }}</span>
                    </div>

                    <div class="row g-3">

                        {{-- ============ ASSET IDENTIFICATION ============ --}}
                        <div class="col-12">
                            <div class="subsection-title">
                                <i class="ri-truck-line"></i> {{ __('Asset Identification') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-hashtag"></i> {{ __('Fleet Number') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-blue">{{ $data->fleet_no ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-truck-line"></i> {{ __('Truck No') }}</label>
                                <div class="info-value">{{ $data->truck_no ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-roadster-line"></i> {{ __('Make/Model') }}</label>
                                <div class="info-value text-primary fw-bold">{{ $data->vehicle_make_model->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-price-tag-3-line"></i> {{ __('Asset Type') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-cyan">{{ $data->vehicle_type ?? $data->vehicle_type()->first()?->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-checkbox-circle-line"></i> {{ __('Status') }}</label>
                                <div class="info-value">
                                    @php
                                        if ($data->status === '1' || $data->status === 1) {
                                            $badge = 'chip-green';
                                            $icon  = 'ri-checkbox-circle-fill';
                                            $text  = __('Active');
                                        } elseif ($data->status === '0' || $data->status === 0) {
                                            $badge = 'chip-red';
                                            $icon  = 'ri-close-circle-fill';
                                            $text  = __('Inactive');
                                        } else {
                                            $badge = 'chip-slate';
                                            $icon  = 'ri-question-line';
                                            $text  = __('Unknown');
                                        }
                                    @endphp

                                    <span class="chip {{ $badge }}">
                                        <i class="{{ $icon }} me-1"></i>{{ $text }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-map-pin-line"></i> {{ __('Base / Site') }}</label>
                                <div class="info-value">
                                    <i class="ri-map-pin-2-fill text-danger me-1"></i>{{ $data->base_site ?? '-' }}
                                </div>
                            </div>
                        </div>

                        {{-- ============ OWNERSHIP & MANAGEMENT ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title cyan">
                                <i class="ri-user-settings-line"></i> {{ __('Ownership & Management') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-star-line"></i> {{ __('Asset Manager Name') }}</label>
                                <div class="info-value">{{ $data->vehicle_manager_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-mail-line"></i> {{ __('Asset Manager Email') }}</label>
                                <div class="info-value">{{ $data->manager_email ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-phone-line"></i> {{ __('Phone Number') }}</label>
                                <div class="info-value">{{ $data->phone_number ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-line"></i> {{ __('Driver') }}</label>
                                <div class="info-value">{{ $data->driver?->full_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-shield-line"></i> {{ __('Client/Asset Owner') }}</label>
                                <div class="info-value">{{ $data->vehicle_owner_name ?? '-' }}</div>
                            </div>
                        </div>

                        {{-- ============ SERVICE & EXPIRY DATES ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title amber">
                                <i class="ri-calendar-line"></i> {{ __('Service & Expiry Dates') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Last Service Date') }}</label>
                                <div class="info-value">{{ dateFormat($data->last_service_date) }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-todo-line"></i> {{ __('Next Service Due') }}</label>
                                <div class="info-value">{{ dateFormat($data->next_service_due_date) }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-shield-check-line"></i> {{ __('Insurance Expiry Date') }}</label>
                                <div class="info-value">{{ dateFormat($data->insurance_expiry_date) }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-file-shield-2-line"></i> {{ __('Asset Expiry Date') }}</label>
                                <div class="info-value">{{ dateFormat($data->vehicle_license_expiry_date) }}</div>
                            </div>
                        </div>

                        {{-- ============ GPS & TRACKING ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title slate">
                                <i class="ri-satellite-line"></i> {{ __('GPS & Tracking Device') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-satellite-dish-line"></i> {{ __('IMEI') }}</label>
                                <div class="info-value">
                                    <span class="chip chip-slate">{{ $data->gps_imei ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-cpu-line"></i> {{ __('Device Type') }}</label>
                                <div class="info-value">{{ $data->gps_device_type?->name ?? '-' }}</div>
                            </div>
                        </div>

                        {{-- ============ RECORD METADATA ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title dark">
                                <i class="ri-file-list-3-line"></i> {{ __('Record Metadata') }}
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

        {{-- <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h6 class="mb-0">
                        <i class="fas fa-truck text-primary me-2"></i>
                        {{ __('Vehicle Information') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="vehicle-info">
                        <div class="vehicle-item">
                            <span class="vehicle-label">{{ __('Vehicle Image') }}</span>
                            <span class="vehicle-value">{{ $data->cargo_weight_kg !== null ? number_format($data->cargo_weight_kg, 1) : '-' }}&nbsp;Dummy Data</span>
                        </div>

                    </div>
                </div>
            </div>
        </div> --}}
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

        /* ===== CARDS ===== */
        .section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: box-shadow 0.2s ease;
        }
        .section-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important;
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

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .info-group { padding: 12px 14px; }
            .icon-tile { width: 40px; height: 40px; font-size: 1.15rem; }
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

