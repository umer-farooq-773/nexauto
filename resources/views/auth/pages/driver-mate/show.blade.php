{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<x-layouts.auth page-title="{{ __('Driver Mate Details') }}" sub-title="">
    {{-- ===================== HEADER ACTION BAR ===================== --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 topbar-card">
                <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-tile blue">
                            <i class="ri-user-shared-line"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ __('Driver Mate Record') }}</h5>
                            <small class="text-muted">
                                {{ __('Name') }}: <span class="fw-semibold text-primary">{{ $data->full_name ?? '—' }}</span>
                                &nbsp;•&nbsp;
                                {{ __('Phone') }}: <span class="fw-semibold">{{ $data->phone_number ?? '—' }}</span>
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
        {{-- ===================== LEFT COLUMN ===================== --}}
        <div class="col-xl-7">

            {{-- ============ MAIN DETAILS CARD ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">

                    {{-- Section Header --}}
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile blue">
                                <i class="ri-user-shared-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Driver Mate Details') }}</h6>
                                <small class="text-muted">{{ __('Personal & Contact Information') }}</small>
                            </div>
                        </div>
                        <span class="required-badge">{{ __('Read Only') }}</span>
                    </div>

                    <div class="row g-3">

                        {{-- ============ PERSONAL INFORMATION ============ --}}
                        <div class="col-12">
                            <div class="subsection-title">
                                <i class="ri-user-line"></i> {{ __('Personal Information') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-user-line"></i> {{ __('Full Name') }}</label>
                                <div class="info-value">{{ $data->full_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-map-pin-line"></i> {{ __('Address') }}</label>
                                <div class="info-value">{{ $data->address ?? '-' }}</div>
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
                                <label class="info-label"><i class="ri-calendar-line"></i> {{ __('Date of Birth') }}</label>
                                <div class="info-value">
                                    @if($data->date_of_birth)
                                        {{ dateFormat($data->date_of_birth) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-id-card-line"></i> {{ __('National ID / Passport Number') }}</label>
                                <div class="info-value">{{ $data->national_id_passport_number ?? '-' }}</div>
                            </div>
                        </div>

                        {{-- ============ EMPLOYMENT ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title cyan">
                                <i class="ri-briefcase-line"></i> {{ __('Employment Information') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-calendar-check-line"></i> {{ __('Date Employed') }}</label>
                                <div class="info-value">
                                    @if($data->date_employed)
                                        {{ dateFormat($data->date_employed) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-information-line"></i> {{ __('Status') }}</label>
                                <div class="info-value">{!! $data->status_badge ?? '-' !!}</div>
                            </div>
                        </div>

                        {{-- ============ EMERGENCY CONTACT ============ --}}
                        <div class="col-12 mt-3">
                            <div class="subsection-title amber">
                                <i class="ri-contacts-line"></i> {{ __('Emergency Contact') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-contacts-line"></i> {{ __('Emergency Contact Name') }}</label>
                                <div class="info-value">{{ $data->emergency_contact_name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label"><i class="ri-phone-fill"></i> {{ __('Emergency Contact Phone') }}</label>
                                <div class="info-value">{{ $data->emergency_contact_phone_number ?? '-' }}</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== RIGHT COLUMN ===================== --}}
        <div class="col-xl-5">

            {{-- ============ DOCUMENTS & PHOTOGRAPH ============ --}}
            <div class="card border-0 shadow-sm rounded-4 section-card mb-4">
                <div class="card-body p-4">

                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile cyan">
                                <i class="ri-image-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Documents & Photograph') }}</h6>
                                <small class="text-muted">{{ __('Driver mate photo and identification card') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center p-2 mb-3">
                        <p class="small fw-bold mb-2 text-muted text-uppercase">{{ __('Driver Mate Photograph') }}</p>
                        <img src="{{ $data->image() }}" alt="{{ $data->first_name ?? 'Driver Mate' }}"
                            class="img-fluid rounded-4 shadow-sm"
                            style="height: 250px; width: 100%; object-fit: cover;">
                    </div>

                    <div class="text-center p-2">
                        <p class="small fw-bold mb-2 text-muted text-uppercase">{{ __('Company ID Card') }}</p>
                        <img src="{{ $data->companyIdCardImage() }}" alt="{{ $data->first_name ?? 'Driver Mate' }} {{ $data->last_name ?? '' }} - {{ __('Company ID Card') }}"
                            class="img-fluid rounded-4 shadow-sm"
                            style="height: 250px; width: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MATCHED & UNMATCHED DRIVERS ===================== --}}
    <div class="row g-4 mt-2">
        {{-- ============ MATCHED DRIVERS ============ --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 section-card">
                <div class="card-body p-4">
                    <div class="section-header mb-4">
                        <div class="section-header-left">
                            <div class="icon-tile green">
                                <i class="ri-user-follow-line"></i>
                            </div>
                            <div>
                                <h6 class="section-title mb-0">{{ __('Driver Name') }}</h6>
                                <small class="text-muted">{{ __('Drivers assigned to this mate') }}</small>
                            </div>
                        </div>
                        <span class="chip chip-green">{{ count($matchedDrivers) }} {{ __('Driver(s)') }}</span>
                    </div>

                    @if(count($matchedDrivers) > 0)
                        <div class="driver-list">
                            @foreach($matchedDrivers as $driver)
                                <div class="driver-item matched">
                                    <div class="driver-avatar green">
                                        <i class="ri-user-line"></i>
                                    </div>
                                    <div class="driver-info">
                                        <div class="driver-name">{{ $driver->full_name }}</div>
                                        <div class="driver-meta">
                                            <span><i class="ri-phone-line"></i> {{ $driver->phone_number ?? '—' }}</span>
                                            <span><i class="ri-id-card-line"></i> {{ $driver->drivers_license_number ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="driver-badge">
                                        <span class="chip chip-green"><i class="ri-check-line"></i> {{ __('Matched') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="ri-user-unfollow-line"></i>
                            <p>{{ __('No drivers matched to this driver mate yet') }}</p>
                        </div>
                    @endif
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

        /* ===== DRIVER LIST ===== */
        .driver-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 8px;
        }
        
        .driver-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .driver-list::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        
        .driver-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        .driver-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .driver-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        
        .driver-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
        
        .driver-item.matched {
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
            border-left: 3px solid #16a34a;
        }
        
        .driver-item.unmatched {
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
            border-left: 3px solid #d97706;
        }

        .driver-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .driver-avatar.green {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .driver-avatar.amber {
            background: #fef3c7;
            color: #d97706;
        }

        .driver-info {
            flex: 1;
            min-width: 0;
        }

        .driver-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1e293b;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .driver-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .driver-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .driver-meta i {
            font-size: 0.85rem;
        }

        .driver-badge {
            flex-shrink: 0;
        }
        
        .driver-badge .chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            padding: 4px 10px;
        }
        
        .driver-badge .chip i {
            font-size: 0.85rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.4;
        }
        
        .empty-state p {
            font-size: 0.9rem;
            margin: 0;
            color: #64748b;
        }

        /* ===== RESPONSIVE - DRIVER LIST ===== */
        @media (max-width: 768px) {
            .driver-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .driver-avatar {
                width: 42px;
                height: 42px;
                font-size: 1.3rem;
            }
            
            .driver-meta {
                flex-direction: column;
                gap: 6px;
            }
            
            .driver-badge {
                width: 100%;
            }
            
            .driver-badge .chip {
                width: 100%;
                justify-content: center;
            }
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
