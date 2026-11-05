{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

@props(['routeAction', 'data' => null, 'vehicles' => [], 'waybills' => [], 'isEdit' => false, 'incidentEvaluation' => null])

@php
    $result = $data;
    $savedInspectionCargos = null;
    if ($result && $result->cargos && $result->cargos->count() > 0) {
        $savedInspectionCargos = $result->cargos->toArray();
    } elseif ($result && $result->declared_quantity !== null) {
        $savedInspectionCargos = [[
            'id' => null,
            'waybill_cargo_id' => null,
            'cargo_description' => $result->goods_description ?? $result->waybill?->cargo_description ?? '',
            'declared_quantity' => $result->declared_quantity,
            'observed_quantity' => $result->observed_quantity,
            'difference_quantity' => $result->difference_quantity,
            'quantity_unit' => $result->waybill?->quantity_unit ?? 'pcs',
            'cargo_weight' => $result->waybill?->cargo_weight ?? '',
            'weight_unit' => $result->waybill?->weight_unit ?? 'kg',
            'volume_unit' => $result->waybill?->volume_unit ?? 'm3',
            'goods_match_waybill' => $result->goods_match_waybill ?? 'Yes',
            'illegal_shortage' => $result->illegal_shortage ?? 'No',
            'illegal_haulage' => $result->illegal_haulage ?? 'No',
            'remarks' => $result->remarks ?? '',
        ]];
    }
@endphp

<x-auth.form form-id="GoodsInspectionForm" form-action="{{ $routeAction }}" enctype="false">

    {{-- ============ VALIDATION ERRORS ============ --}}
    @if (isset($errors) && $errors->any())
        <div class="col-12 mb-3">
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 gi-alert" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3 gi-alert-icon">
                        <i class="ri-error-warning-line"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block mb-2">{{ __('Please fix the following errors:') }}</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="inspection_id" id="inspection_id" value="{{ $result?->id ?? old('inspection_id') }}">

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 1: ASSET & WAYBILL DISPATCH
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">

                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-circle blue">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Asset & Waybill Dispatch Information') }}</h6>
                            <small class="text-muted">{{ __('Select asset and waybill to auto-fill details') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 form-floating">
                        <select name="vehicle_id" id="vehicle_id_impound" class="form-select searchable-select" required>
                            <option value="">Select Asset</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    data-fleet="{{ $vehicle->fleet_no }}"
                                    data-truck="{{ $vehicle->lorry_no ?? $vehicle->vehicle_lorry_number ?? '' }}"
                                    data-driver_name="{{ $vehicle->driver_name ?? '' }}"
                                    data-driver_phone_id="{{ $vehicle->driver_phone_id ?? '' }}"
                                    data-route="{{ $vehicle->route ?? '' }}"
                                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->fleet_no }}
                                </option>
                            @endforeach
                        </select>
                        <label for="vehicle_id_impound">{{ __('Asset (Fleet No)') }} <span class="text-danger">*</span></label>
                    </div>

                    <div class="col-md-6 form-floating">
                        <select name="waybill_id" id="waybill_id_impound" class="form-select searchable-select">
                            <option value="">Select Waybill</option>
                            @foreach($waybills as $waybill)
                                @php
                                    $origin = $waybill->origin ?: $waybill->route_trip?->origin;
                                    $dest = $waybill->destination ?: $waybill->route_trip?->destination;
                                    $routeStr = ($origin && $dest) ? "{$origin} → {$dest}" : ($origin ?: $dest ?: '');
                                    $driverName = $waybill->driver?->full_name ?? '';
                                    $driverPhone = $waybill->driver?->phone_number ?? $waybill->driver?->national_id_passport_number ?? $waybill->driver?->drivers_license_number ?? '';

                                    $cargosData = ($waybill->cargos && $waybill->cargos->count() > 0)
                                        ? $waybill->cargos->map(function($c) {
                                            return [
                                                'id' => $c->id,
                                                'cargo_description' => $c->cargo_description,
                                                'quantity' => $c->quantity,
                                                'quantity_unit' => $c->quantity_unit ?? 'pcs',
                                                'cargo_weight' => $c->cargo_weight,
                                                'weight_unit' => $c->weight_unit ?? 'kg',
                                                'volume_unit' => $c->volume_unit ?? 'm3',
                                            ];
                                        })->values()->toArray()
                                        : [
                                            [
                                                'id' => null,
                                                'cargo_description' => $waybill->cargo_description ?? '',
                                                'quantity' => $waybill->quantity ?? 0,
                                                'quantity_unit' => $waybill->quantity_unit ?? 'pcs',
                                                'cargo_weight' => $waybill->cargo_weight ?? '',
                                                'weight_unit' => $waybill->weight_unit ?? 'kg',
                                                'volume_unit' => $waybill->volume_unit ?? 'm3',
                                            ]
                                        ];
                                @endphp
                                <option value="{{ $waybill->id }}"
                                    data-vehicle="{{ $waybill->vehicle_id }}"
                                    data-waybill_number="{{ $waybill->waybill_number }}"
                                    data-driver_name="{{ $driverName }}"
                                    data-driver_phone_id="{{ $driverPhone }}"
                                    data-route="{{ $routeStr }}"
                                    data-origin="{{ $origin }}"
                                    data-destination="{{ $dest }}"
                                    data-goods_description="{{ $waybill->cargo_description ?? '' }}"
                                    data-status="{{ $waybill->status?->value }}"
                                    data-quantity="{{ $waybill->quantity }}"
                                    data-cargos="{{ json_encode($cargosData) }}"
                                    {{ ($result?->waybill_id ?? old('waybill_id')) == $waybill->id ? 'selected' : '' }}>
                                     {{ $waybill->waybill_number }} ({{ $routeStr ?: 'No Route Specified' }})
                                </option>
                            @endforeach
                        </select>
                        <label for="waybill_id_impound">{{ __('Waybill') }}</label>
                    </div>

                    <input type="hidden" name="waybill_number" id="waybill_number" value="{{ $result?->waybill_number ?? old('waybill_number') }}">
                    <input type="hidden" name="fleet_no" id="fleet_no" value="{{ $result?->fleet_no ?? old('fleet_no') }}">
                    <input type="hidden" name="vehicle_lorry_number" id="vehicle_lorry_number" value="{{ $result?->vehicle_lorry_number ?? old('vehicle_lorry_number') }}">

                    <div class="col-md-6">
                        <x-auth.input-field type="text" name="driver_name" id="driver_name" required="false"
                            place="{{ __('Auto-filled from Waybill / Asset') }}" val="{{ $result?->driver_name ?? old('driver_name') }}" extraclasses="shadow-sm" label="{{ __('Driver Name') }}" />
                    </div>

                    <div class="col-md-6">
                        <x-auth.input-field type="text" name="driver_phone_id" id="driver_phone_id" required="false"
                            place="{{ __('Auto-filled from Waybill / Asset') }}" val="{{ $result?->driver_phone_id ?? old('driver_phone_id') }}" extraclasses="shadow-sm" label="{{ __('Driver Phone / ID') }}" />
                    </div>

                    <div class="col-md-6">
                        <x-auth.input-field type="text" name="route" id="route" required="false"
                            place="{{ __('Origin to Destination') }}" val="{{ $result?->route ?? old('route') }}" extraclasses="shadow-sm" label="{{ __('Route (From – To)') }}" />
                    </div>

                    <div class="col-md-6">
                        <x-auth.input-field type="text" name="goods_description" id="goods_description" required="false"
                            place="{{ __('Auto-filled from Waybill') }}" val="{{ $result?->goods_description ?? old('goods_description') }}" extraclasses="shadow-sm" label="{{ __('Goods Description') }}" />
                    </div>

                    <div class="col-md-3">
                        <x-auth.input-field type="date" name="inspection_date" id="inspection_date" required="true" place=""
                            val="{{ $result?->inspection_date?->format('Y-m-d') ?? old('inspection_date', date('Y-m-d')) }}" extraclasses="shadow-sm"
                            label="{{ __('Inspection Date') }} *" />
                    </div>

                    <div class="col-md-3">
                        <x-auth.input-field type="time" name="inspection_time" id="inspection_time" required="false"
                            val="{{ $result?->inspection_time ?? old('inspection_time', date('H:i')) }}" extraclasses="shadow-sm" label="{{ __('Inspection Time') }}" />
                    </div>

                    <div class="col-md-6 form-floating">
                        <select name="inspection_type" id="inspection_type" class="form-select shadow-sm" required>
                            <option value="">{{ __('Select Type') }}</option>
                            <option value="Pre-Departure" {{ ($result?->inspection_type ?? old('inspection_type', 'Pre-Departure')) == 'Pre-Departure' ? 'selected' : '' }}>{{ __('Pre-Departure') }}</option>
                            <option value="Post-Arrival" {{ ($result?->inspection_type ?? old('inspection_type')) == 'Post-Arrival' ? 'selected' : '' }}>{{ __('Post-Arrival') }}</option>
                            <option value="Random" {{ ($result?->inspection_type ?? old('inspection_type')) == 'Random' ? 'selected' : '' }}>{{ __('Random') }}</option>
                        </select>
                        <label for="inspection_type">{{ __('Inspection Type') }} <span class="text-danger">*</span></label>
                    </div>

                    <div class="col-md-12 geoapify-wrapper">
                        <x-auth.geoapify-location-input
                            name="inspection_location"
                            id="inspection_location"
                            label="{{ __('Inspection Location') }}"
                            placeholder="{{ __('Search for location (e.g. Terminal A, Benin Corridor)') }}"
                            value="{{ $result?->inspection_location ?? old('inspection_location') }}"
                            lat="{{ $result?->inspection_location_lat ?? old('inspection_location_lat') }}"
                            lng="{{ $result?->inspection_location_lng ?? old('inspection_location_lng') }}"
                            :required="false" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 2: CARGO VERIFICATION
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">
                <div class="section-header mb-3">
                    <div class="section-header-left">
                        <div class="icon-circle amber">
                            <i class="ri-scales-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Cargo Verification & Quantity Check') }}</h6>
                            <small class="text-muted">{{ __('Inspect each cargo item declared on the selected waybill') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Required') }}</span>
                </div>

                {{-- Summary Bar --}}
                <div id="cargo-summary-bar" class="cargo-summary-bar mb-3 d-none">
                    <div class="row align-items-center text-center g-3">
                        <div class="col-sm-3 border-end cargo-summary-cell">
                            <div class="cargo-summary-label">{{ __('Cargo Items') }}</div>
                            <div class="cargo-summary-value text-dark" id="summary-total-items">0</div>
                        </div>
                        <div class="col-sm-3 border-end cargo-summary-cell">
                            <div class="cargo-summary-label">{{ __('Total Declared') }}</div>
                            <div class="cargo-summary-value text-primary" id="summary-total-declared">0</div>
                        </div>
                        <div class="col-sm-3 border-end cargo-summary-cell">
                            <div class="cargo-summary-label">{{ __('Total Observed') }}</div>
                            <div class="cargo-summary-value text-info" id="summary-total-observed">0</div>
                        </div>
                        <div class="col-sm-3 cargo-summary-cell">
                            <div class="cargo-summary-label">{{ __('Cargo Status') }}</div>
                            <div id="summary-overall-badge" class="bg-secondary">Pending</div>
                        </div>
                    </div>
                </div>

                {{-- Dynamic Cargo Items Container --}}
                <div id="inspection-cargos-container" class="d-flex flex-column gap-3">
                    <div class="empty-state-cargo text-center p-5" id="no-waybill-placeholder">
                        <div class="empty-state-icon">
                            <i class="ri-truck-line"></i>
                        </div>
                        <h6 class="mt-3 mb-1 fw-bold text-dark">{{ __('No Cargo Loaded') }}</h6>
                        <p class="text-muted small mb-0">{{ __('Please select a Waybill above to load cargo items for inspection.') }}</p>
                    </div>
                </div>

                <input type="hidden" name="declared_quantity" id="declared_quantity" value="{{ $result?->declared_quantity ?? old('declared_quantity', 0) }}">
                <input type="hidden" name="observed_quantity" id="observed_quantity" value="{{ $result?->observed_quantity ?? old('observed_quantity', 0) }}">
                <input type="hidden" name="goods_match_waybill" id="goods_match_waybill" value="{{ $result?->goods_match_waybill ?? old('goods_match_waybill', 'Yes') }}">
                <input type="hidden" name="illegal_shortage" id="illegal_shortage" value="{{ $result?->illegal_shortage ?? old('illegal_shortage', 'No') }}">
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 3: ILLEGAL HAULAGE CHECK
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">
                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-circle red">
                            <i class="ri-truck-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Illegal Haulage Check') }}</h6>
                            <small class="text-muted">{{ __('Check for unauthorised cargo or excess haulage') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 form-floating">
                        <select name="unauthorised_items" id="unauthorised_items" class="form-select shadow-sm">
                            <option value="No" {{ ($result?->unauthorised_items ?? old('unauthorised_items', 'No')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                            <option value="Yes" {{ ($result?->unauthorised_items ?? old('unauthorised_items')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        </select>
                        <label for="unauthorised_items">{{ __('Unauthorised Items (Not listed on Waybill)?') }}</label>
                    </div>

                    <div class="col-md-6 form-floating">
                        <select name="illegal_haulage" id="illegal_haulage" class="form-select shadow-sm">
                            <option value="No" {{ ($result?->illegal_haulage ?? old('illegal_haulage', 'No')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                            <option value="Yes" {{ ($result?->illegal_haulage ?? old('illegal_haulage')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        </select>
                        <label for="illegal_haulage">{{ __('Illegal Haulage Flag') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 4: DRIVER MISCONDUCT & SECURITY
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">
                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-circle cyan">
                            <i class="ri-user-unfollow-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Driver Misconduct & Physical Security') }}</h6>
                            <small class="text-muted">{{ __('Assess cargo security and driver compliance') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-4 form-floating">
                        <select name="cargo_secured" id="cargo_secured" class="form-select shadow-sm">
                            <option value="Yes" {{ ($result?->cargo_secured ?? old('cargo_secured', 'Yes')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                            <option value="No" {{ ($result?->cargo_secured ?? old('cargo_secured')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                        </select>
                        <label for="cargo_secured">{{ __('Cargo Properly Secured?') }}</label>
                    </div>

                    <div class="col-md-4 form-floating">
                        <select name="doors_tarpaulin_secure" id="doors_tarpaulin_secure" class="form-select shadow-sm">
                            <option value="Yes" {{ ($result?->doors_tarpaulin_secure ?? old('doors_tarpaulin_secure', 'Yes')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                            <option value="No" {{ ($result?->doors_tarpaulin_secure ?? old('doors_tarpaulin_secure')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                        </select>
                        <label for="doors_tarpaulin_secure">{{ __('Doors/Tarpaulin Secure?') }}</label>
                    </div>

                    <div class="col-md-4 form-floating">
                        <select name="visible_damage" id="visible_damage" class="form-select shadow-sm">
                            <option value="No" {{ ($result?->visible_damage ?? old('visible_damage', 'No')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                            <option value="Yes" {{ ($result?->visible_damage ?? old('visible_damage')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        </select>
                        <label for="visible_damage">{{ __('Visible Damage?') }}</label>
                    </div>

                    <div class="col-md-12 form-floating">
                        <select name="driver_misconduct" id="driver_misconduct" class="form-select shadow-sm">
                            <option value="No" {{ ($result?->driver_misconduct ?? old('driver_misconduct', 'No')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                            <option value="Yes" {{ ($result?->driver_misconduct ?? old('driver_misconduct')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        </select>
                        <label for="driver_misconduct">{{ __('Driver Misconduct?') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 5: ROUTE DIVERSION & BREATHALISER
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">
                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-circle slate">
                            <i class="ri-route-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Route Diversion & Driver Compliance Test') }}</h6>
                            <small class="text-muted">{{ __('Verify route adherence and conduct breathaliser test') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 form-floating">
                        <select name="route_diversion" id="route_diversion" class="form-select shadow-sm">
                            <option value="No" {{ ($result?->route_diversion ?? old('route_diversion', 'No')) == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                            <option value="Yes" {{ ($result?->route_diversion ?? old('route_diversion')) == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        </select>
                        <label for="route_diversion">{{ __('Route Diversion?') }}</label>
                    </div>

                    <div class="col-md-6 form-floating">
                        <select name="breathaliser_test" id="breathaliser_test" class="form-select shadow-sm">
                            <option value="Passed" {{ ($result?->breathaliser_test ?? old('breathaliser_test', 'Passed')) == 'Passed' ? 'selected' : '' }}>{{ __('Passed') }}</option>
                            <option value="Failed" {{ ($result?->breathaliser_test ?? old('breathaliser_test')) == 'Failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                            <option value="Not Tested" {{ ($result?->breathaliser_test ?? old('breathaliser_test')) == 'Not Tested' ? 'selected' : '' }}>{{ __('Not Tested') }}</option>
                        </select>
                        <label for="breathaliser_test">{{ __('Breathaliser Test?') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 6: INSPECTION STATUS & REMARKS
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">
                <div class="section-header mb-4">
                    <div class="section-header-left">
                        <div class="icon-circle dark">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Inspection Status & Remarks') }}</h6>
                            <small class="text-muted">{{ __('Final inspection outcome and additional notes') }}</small>
                        </div>
                    </div>
                    <span class="required-badge">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-12 form-floating">
                        <select name="goods_status" id="goods_status" class="form-select shadow-sm text-primary fw-bold" required>
                            <option value="cleared" {{ ($result?->goods_status ?? old('goods_status', 'cleared')) == 'cleared' ? 'selected' : '' }}>{{ __('Cleared / Safe') }}</option>
                            <option value="issues" {{ ($result?->goods_status ?? old('goods_status')) == 'issues' ? 'selected' : '' }}>{{ __('Issues Found') }}</option>
                        </select>
                        <label for="goods_status">{{ __('Inspection Status') }} <span class="text-danger">*</span></label>
                    </div>

                    <input type="hidden" name="status" value="1">
                    <input type="hidden" name="inspector_name" value="{{ auth()->user()->full_name ?? auth()->user()->name ?? 'Inspector' }}">

                    <div class="col-md-12">
                        <x-auth.text-area name="remarks" id="remarks" required="false"
                            place="{{ __('Additional remarks or findings...') }}" val="{{ $result?->remarks ?? old('remarks') }}" extraclasses="shadow-sm"
                            label="{{ __('Remarks') }}" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 7: INCIDENT ACTION CENTER
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="col-12 mb-3" id="incident-requirements-container" style="display: none;">
        <div class="card border-0 shadow-lg rounded-4 incident-card">
            <div class="card-body p-4">

                <div class="section-header mb-3">
                    <div class="section-header-left">
                        <div class="icon-circle amber pulse-icon">
                            <i class="ri-alarm-warning-fill"></i>
                        </div>
                        <div>
                            <h6 class="section-title mb-0">{{ __('Incident Status & Action Center') }}</h6>
                            <small class="text-muted">{{ __('Auto-detected incidents based on your checklist') }}</small>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" id="incident-requirements-count">0 Detected</span>
                </div>

                <p class="text-muted small mb-3 d-flex align-items-start gap-2">
                    <i class="ri-information-line mt-1" style="flex-shrink: 0;"></i>
                    <span>{{ __('The following incident records are triggered by your checklist. Please create each required incident record below before saving the inspection report.') }}</span>
                </p>

                <div id="incident-requirements-list" class="d-flex flex-column gap-2">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>
    </div>

    {{-- ============ SUBMIT BUTTON ============ --}}
    <div class="col-12 text-center mt-3 mb-4">
        <button type="submit" id="saveInspectionBtn" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow-lg submit-btn">
            <i class="ri-save-line me-2"></i> {{ __('Save Inspection Report') }}
        </button>
        <div id="saveInspectionNotice" class="mt-2"></div>
    </div>
</x-auth.form>

{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS — only cargo card HTML template changed
═══════════════════════════════════════════════════════════════ --}}
@push('scripts')
    <script>
        $(document).ready(function() {
            var modalParent = $('#addInspectionModal');
            var $vehicleSelect = $('#vehicle_id_impound');
            var $waybillSelect = $('#waybill_id_impound');
            var isSyncing = false;
            var isAutoSetting = false;

            var existingInspectionCargos = @json($savedInspectionCargos);
            var serverEvaluation = @json($incidentEvaluation ?? null);
            var existingIncidentsMap = {};

            if (serverEvaluation && serverEvaluation.incidents) {
                serverEvaluation.incidents.forEach(function(item) {
                    if (item.exists) {
                        existingIncidentsMap[item.key] = {
                            id: item.incident_id,
                            edit_url: item.edit_url,
                            show_url: item.show_url,
                            label: item.label
                        };
                    }
                });
            }

            var allWaybillOptions = [];
            $waybillSelect.find('option').each(function() {
                allWaybillOptions.push($(this).clone());
            });

            $vehicleSelect.select2({
                placeholder: "Select Asset",
                allowClear: true,
                width: '100%',
                dropdownParent: modalParent.length ? modalParent : $(document.body)
            });

            $waybillSelect.select2({
                placeholder: "Select Waybill",
                allowClear: true,
                width: '100%',
                dropdownParent: modalParent.length ? modalParent : $(document.body)
            });

            var INCIDENT_META = {
                'illegal_haulage': { label: 'Illegal Haulage', icon: 'ri-truck-line', btnClass: 'btn-danger' },
                'illegal_shortage': { label: 'Illegal Shortage', icon: 'ri-scales-line', btnClass: 'btn-warning' },
                'route_diversion': { label: 'Route Diversion', icon: 'ri-route-line', btnClass: 'btn-info' },
                'breathaliser_test': { label: 'Breathaliser Test', icon: 'ri-test-tube-line', btnClass: 'btn-danger' },
                'driver_misconduct': { label: 'Driver Misconduct', icon: 'ri-user-unfollow-line', btnClass: 'btn-danger' }
            };

            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                return String(text)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function renderInspectionCargos(cargos) {
                var $container = $('#inspection-cargos-container');
                var $summaryBar = $('#cargo-summary-bar');
                $container.empty();

                if (!cargos || cargos.length === 0) {
                    $summaryBar.addClass('d-none');
                    $container.html(`
                        <div class="empty-state-cargo text-center p-5" id="no-waybill-placeholder">
                            <div class="empty-state-icon">
                                <i class="ri-truck-line"></i>
                            </div>
                            <h6 class="mt-3 mb-1 fw-bold text-dark">{{ __('No Cargo Loaded') }}</h6>
                            <p class="text-muted small mb-0">{{ __('Please select a Waybill above to load cargo items for inspection.') }}</p>
                        </div>
                    `);
                    recalculateCargos();
                    return;
                }

                $summaryBar.removeClass('d-none');

                cargos.forEach(function(item, idx) {
                    var saved = null;
                    if (existingInspectionCargos && existingInspectionCargos.length > 0) {
                        saved = existingInspectionCargos.find(function(s) {
                            return s.waybill_cargo_id && item.id && s.waybill_cargo_id == item.id;
                        }) || existingInspectionCargos[idx];
                    }

                    var cargoId = saved ? (saved.id || '') : '';
                    var waybillCargoId = item.id || (saved ? saved.waybill_cargo_id : '') || '';
                    var description = item.cargo_description || (saved ? saved.cargo_description : '') || 'Cargo Item #' + (idx + 1);
                    var declaredQty = item.quantity !== undefined && item.quantity !== null ? item.quantity : (saved ? saved.declared_quantity : 0);
                    var observedQty = saved && saved.observed_quantity !== undefined && saved.observed_quantity !== null ? saved.observed_quantity : '';
                    var diffQty = saved && saved.difference_quantity !== undefined && saved.difference_quantity !== null ? saved.difference_quantity : '';
                    var qtyUnit = item.quantity_unit || (saved ? saved.quantity_unit : 'pcs') || 'pcs';
                    var weight = item.cargo_weight !== undefined && item.cargo_weight !== null ? item.cargo_weight : (saved ? saved.cargo_weight : '');
                    var weightUnit = item.weight_unit || (saved ? saved.weight_unit : 'kg') || 'kg';
                    var volumeUnit = item.volume_unit || (saved ? saved.volume_unit : 'm3') || 'm3';
                    var goodsMatch = saved ? (saved.goods_match_waybill || 'Yes') : 'Yes';
                    var illShortage = saved ? (saved.illegal_shortage || 'No') : 'No';
                    var illHaulage = saved ? (saved.illegal_haulage || 'No') : 'No';
                    var remarks = saved ? (saved.remarks || '') : '';

                    var cardHtml = `
                        <div class="cargo-inspection-card" data-index="${idx}">
                            <div class="cargo-card-header">
                                <div class="cargo-header-left">
                                    <span class="cargo-index-badge">#${idx + 1}</span>
                                    <div class="cargo-title-group">
                                        <strong class="cargo-title-text">${escapeHtml(description)}</strong>
                                        <div class="cargo-meta-row">
                                            ${weight ? `<span class="cargo-meta-pill"><i class="ri-scales-line"></i>${weight} ${escapeHtml(weightUnit)}</span>` : ''}
                                            ${volumeUnit ? `<span class="cargo-meta-pill"><i class="ri-drop-line"></i>${escapeHtml(volumeUnit)}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                                <span class="cargo-diff-badge cargo-diff-pending">Pending</span>
                            </div>
                            <div class="card-body p-3">
                                <input type="hidden" name="cargos[${idx}][id]" value="${escapeHtml(cargoId)}">
                                <input type="hidden" name="cargos[${idx}][waybill_cargo_id]" value="${escapeHtml(waybillCargoId)}">
                                <input type="hidden" name="cargos[${idx}][cargo_description]" value="${escapeHtml(description)}">
                                <input type="hidden" name="cargos[${idx}][quantity_unit]" value="${escapeHtml(qtyUnit)}">
                                <input type="hidden" name="cargos[${idx}][cargo_weight]" value="${escapeHtml(weight)}">
                                <input type="hidden" name="cargos[${idx}][weight_unit]" value="${escapeHtml(weightUnit)}">
                                <input type="hidden" name="cargos[${idx}][volume_unit]" value="${escapeHtml(volumeUnit)}">

                                <div class="row g-2">
                                    <div class="col-md-4 form-floating">
                                        <input type="number" step="any" class="form-control bg-light cargo-declared-qty"
                                            name="cargos[${idx}][declared_quantity]" id="declared_qty_${idx}"
                                            value="${declaredQty}" readonly placeholder="0">
                                        <label for="declared_qty_${idx}">{{ __('Declared Quantity') }} (${escapeHtml(qtyUnit)})</label>
                                    </div>

                                    <div class="col-md-4 form-floating">
                                        <input type="number" step="any" class="form-control fw-bold border-primary cargo-observed-qty"
                                            name="cargos[${idx}][observed_quantity]" id="observed_qty_${idx}"
                                            value="${observedQty}" placeholder="0" required>
                                        <label for="observed_qty_${idx}">{{ __('Observed Quantity') }} (${escapeHtml(qtyUnit)}) <span class="text-danger">*</span></label>
                                    </div>

                                    <div class="col-md-4 form-floating">
                                        <input type="number" step="any" class="form-control bg-light fw-bold cargo-diff-qty"
                                            name="cargos[${idx}][difference_quantity]" id="diff_qty_${idx}"
                                            value="${diffQty}" readonly placeholder="0">
                                        <label for="diff_qty_${idx}">{{ __('Difference Quantity') }} (${escapeHtml(qtyUnit)})</label>
                                    </div>

                                    <div class="col-md-4 form-floating">
                                        <select name="cargos[${idx}][goods_match_waybill]" id="goods_match_${idx}" class="form-select cargo-goods-match">
                                            <option value="Yes" ${goodsMatch === 'Yes' ? 'selected' : ''}>{{ __('Yes') }}</option>
                                            <option value="No" ${goodsMatch === 'No' ? 'selected' : ''}>{{ __('No') }}</option>
                                        </select>
                                        <label for="goods_match_${idx}">{{ __('Goods Match Waybill?') }}</label>
                                    </div>

                                    <div class="col-md-4 form-floating">
                                        <select name="cargos[${idx}][illegal_shortage]" id="ill_shortage_${idx}" class="form-select cargo-ill-shortage">
                                            <option value="No" ${illShortage === 'No' ? 'selected' : ''}>{{ __('No') }}</option>
                                            <option value="Yes" ${illShortage === 'Yes' ? 'selected' : ''}>{{ __('Yes') }}</option>
                                        </select>
                                        <label for="ill_shortage_${idx}">{{ __('Illegal Shortage?') }}</label>
                                    </div>

                                    <div class="col-md-4 form-floating">
                                        <select name="cargos[${idx}][illegal_haulage]" id="ill_haulage_${idx}" class="form-select cargo-ill-haulage">
                                            <option value="No" ${illHaulage === 'No' ? 'selected' : ''}>{{ __('No') }}</option>
                                            <option value="Yes" ${illHaulage === 'Yes' ? 'selected' : ''}>{{ __('Yes') }}</option>
                                        </select>
                                        <label for="ill_haulage_${idx}">{{ __('Illegal Haulage?') }}</label>
                                    </div>

                                    <div class="col-12">
                                        <input type="text" class="form-control form-control-sm rounded-2 cargo-remarks"
                                            name="cargos[${idx}][remarks]" value="${escapeHtml(remarks)}"
                                            placeholder="{{ __('Optional discrepancy remarks for this cargo item...') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $container.append(cardHtml);
                });

                recalculateCargos();
            }

            function recalculateCargos(triggerCard, triggerSource) {
                if (isAutoSetting) return;
                isAutoSetting = true;

                var totalDeclared = 0;
                var totalObserved = 0;
                var hasAnyShortage = false;
                var hasAnyHaulage = false;
                var allObserved = true;
                var count = 0;

                $('#inspection-cargos-container .cargo-inspection-card').each(function() {
                    count++;
                    var $card = $(this);
                    var decVal = parseFloat($card.find('.cargo-declared-qty').val());
                    var dec = !isNaN(decVal) ? decVal : 0;
                    totalDeclared += dec;

                    var obsStr = $card.find('.cargo-observed-qty').val();
                    var $diffInput = $card.find('.cargo-diff-qty');
                    var $diffBadge = $card.find('.cargo-diff-badge');
                    var $matchSelect = $card.find('.cargo-goods-match');
                    var $shortageSelect = $card.find('.cargo-ill-shortage');
                    var $haulageSelect = $card.find('.cargo-ill-haulage');

                    if (obsStr !== '' && obsStr !== null && obsStr !== undefined) {
                        var obs = parseFloat(obsStr);
                        if (!isNaN(obs)) {
                            totalObserved += obs;
                            var diff = Math.round(Math.abs(dec - obs) * 100) / 100;
                            $diffInput.val(diff);

                            if (triggerSource !== 'manual_dropdown' || triggerCard !== $card[0]) {
                                if (obs > dec) {
                                    $haulageSelect.val('Yes');
                                    $shortageSelect.val('No');
                                    $matchSelect.val('Yes');
                                } else if (obs < dec) {
                                    $shortageSelect.val('Yes');
                                    $matchSelect.val('No');
                                    $haulageSelect.val('No');
                                } else {
                                    $shortageSelect.val('No');
                                    $haulageSelect.val('No');
                                    $matchSelect.val('Yes');
                                }
                            }

                            if ($haulageSelect.val() === 'Yes' || obs > dec) {
                                hasAnyHaulage = true;
                                $diffBadge.text('Excess (+' + diff + ')')
                                          .removeClass('cargo-diff-pending bg-secondary bg-success bg-warning bg-danger')
                                          .addClass('cargo-diff-danger');
                            } else if ($shortageSelect.val() === 'Yes' || $matchSelect.val() === 'No' || obs < dec) {
                                hasAnyShortage = true;
                                $diffBadge.text('Shortage (-' + diff + ')')
                                          .removeClass('cargo-diff-pending bg-secondary bg-success bg-warning bg-danger')
                                          .addClass('cargo-diff-warning');
                            } else {
                                $diffBadge.text('Exact Match (0)')
                                          .removeClass('cargo-diff-pending bg-secondary bg-success bg-warning bg-danger')
                                          .addClass('cargo-diff-success');
                            }
                        }
                    } else {
                        allObserved = false;
                        $diffInput.val('');
                        $diffBadge.text('Pending')
                                  .removeClass('cargo-diff-success cargo-diff-warning cargo-diff-danger bg-secondary bg-success bg-warning bg-danger')
                                  .addClass('cargo-diff-pending');

                        if ($haulageSelect.val() === 'Yes') hasAnyHaulage = true;
                        if ($shortageSelect.val() === 'Yes' || $matchSelect.val() === 'No') hasAnyShortage = true;
                    }
                });

                if ($('#unauthorised_items').val() === 'Yes') {
                    hasAnyHaulage = true;
                }

                $('#summary-total-items').text(count);
                $('#summary-total-declared').text(totalDeclared);
                $('#summary-total-observed').text(totalObserved);

                var $summaryBadge = $('#summary-overall-badge');
                if (count === 0) {
                    $summaryBadge.text('No Items').removeClass('sbadge-info sbadge-success sbadge-warning sbadge-danger').addClass('sbadge-muted');
                } else if (hasAnyShortage && hasAnyHaulage) {
                    $summaryBadge.text('Shortage & Haulage Found')
                                 .removeClass('sbadge-muted sbadge-info sbadge-success sbadge-warning')
                                 .addClass('sbadge-danger');
                } else if (hasAnyHaulage) {
                    $summaryBadge.text('Haulage / Excess Detected')
                                 .removeClass('sbadge-muted sbadge-info sbadge-success sbadge-warning')
                                 .addClass('sbadge-danger');
                } else if (hasAnyShortage) {
                    $summaryBadge.text('Shortage Detected')
                                 .removeClass('sbadge-muted sbadge-info sbadge-success sbadge-danger')
                                 .addClass('sbadge-warning');
                } else if (allObserved) {
                    $summaryBadge.text('All Cargos Match')
                                 .removeClass('sbadge-muted sbadge-info sbadge-warning sbadge-danger')
                                 .addClass('sbadge-success');
                } else {
                    $summaryBadge.text('Inspection in Progress')
                                 .removeClass('sbadge-muted sbadge-info sbadge-success sbadge-danger')
                                 .addClass('sbadge-info');
                }

                $('#declared_quantity').val(totalDeclared);
                $('#observed_quantity').val(totalObserved);
                $('#illegal_shortage').val(hasAnyShortage ? 'Yes' : 'No');
                $('#illegal_haulage').val(hasAnyHaulage ? 'Yes' : 'No');
                $('#goods_match_waybill').val(hasAnyShortage ? 'No' : 'Yes');

                var doorsSecure = $('#doors_tarpaulin_secure').val();
                var cargoSecured = $('#cargo_secured').val();
                if (doorsSecure === 'No' || cargoSecured === 'No') {
                    $('#driver_misconduct').val('Yes');
                }

                var hasAnyIssue = hasAnyShortage || hasAnyHaulage ||
                                  ($('#route_diversion').val() === 'Yes') ||
                                  ($('#driver_misconduct').val() === 'Yes') ||
                                  ($('#unauthorised_items').val() === 'Yes') ||
                                  ($('#cargo_secured').val() === 'No') ||
                                  ($('#doors_tarpaulin_secure').val() === 'No') ||
                                  ($('#visible_damage').val() === 'Yes') ||
                                  (['failed', 'positive', 'unsafe'].indexOf(($('#breathaliser_test').val() || '').toLowerCase().trim()) !== -1);

                if (hasAnyIssue) {
                    $('#goods_status').val('issues');
                } else {
                    $('#goods_status').val('cleared');
                }

                isAutoSetting = false;
                updateIncidentRequirements();
            }

            function getClientRequiredIncidents() {
                var required = [];
                var hasShortage = false;
                var hasHaulage = false;

                $('#inspection-cargos-container .cargo-inspection-card').each(function() {
                    var $card = $(this);
                    var decVal = parseFloat($card.find('.cargo-declared-qty').val());
                    var obsVal = parseFloat($card.find('.cargo-observed-qty').val());
                    var goodsMatch = $card.find('.cargo-goods-match').val();
                    var illShortage = $card.find('.cargo-ill-shortage').val();
                    var illHaulage = $card.find('.cargo-ill-haulage').val();

                    if (!isNaN(decVal) && !isNaN(obsVal)) {
                        if (obsVal > decVal) hasHaulage = true;
                        if (obsVal < decVal) hasShortage = true;
                    }
                    if (illShortage === 'Yes' || goodsMatch === 'No') hasShortage = true;
                    if (illHaulage === 'Yes') hasHaulage = true;
                });

                if ($('#unauthorised_items').val() === 'Yes' || $('#illegal_haulage').val() === 'Yes') {
                    hasHaulage = true;
                }
                if ($('#illegal_shortage').val() === 'Yes' || $('#goods_match_waybill').val() === 'No') {
                    hasShortage = true;
                }

                if (hasHaulage) required.push('illegal_haulage');
                if (hasShortage) required.push('illegal_shortage');
                if ($('#route_diversion').val() === 'Yes') required.push('route_diversion');

                var breath = ($('#breathaliser_test').val() || '').toLowerCase().trim();
                if (breath === 'failed' || breath === 'positive' || breath === 'unsafe') required.push('breathaliser_test');

                if ($('#driver_misconduct').val() === 'Yes' ||
                    $('#doors_tarpaulin_secure').val() === 'No' ||
                    $('#cargo_secured').val() === 'No') {
                    required.push('driver_misconduct');
                }

                return Array.from(new Set(required));
            }

            function updateIncidentRequirements() {
                var required = getClientRequiredIncidents();
                var $container = $('#incident-requirements-container');
                var $list = $('#incident-requirements-list');
                var $countBadge = $('#incident-requirements-count');
                var $saveBtn = $('#saveInspectionBtn');
                var $saveNotice = $('#saveInspectionNotice');

                $list.empty();

                if (required.length === 0) {
                    $container.slideUp(200);
                    $saveBtn.prop('disabled', false).removeClass('disabled opacity-50');
                    $saveNotice.html('');
                    return;
                }

                $container.slideDown(200);
                var createdCount = 0;

                required.forEach(function(key) {
                    var meta = INCIDENT_META[key] || { label: key, icon: 'ri-alert-line', btnClass: 'btn-warning' };
                    var existing = existingIncidentsMap[key];

                    if (existing) {
                        createdCount++;
                        var itemHtml = `
                            <div class="d-flex justify-content-between align-items-center bg-white border border-success border-opacity-50 p-3 px-4 rounded-3 shadow-sm incident-item">
                                <div class="d-flex align-items-center">
                                    <i class="${meta.icon} text-success me-3 fs-4"></i>
                                    <div>
                                        <span class="fw-bold text-dark">${meta.label}</span>
                                        <span class="badge bg-success ms-2"><i class="ri-check-line me-1"></i>Incident Record Created</span>
                                    </div>
                                </div>
                                <a href="${existing.show_url || existing.edit_url}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-medium">
                                    <i class="ri-eye-line me-1"></i> View Incident
                                </a>
                            </div>
                        `;
                        $list.append(itemHtml);
                    } else {
                        var createBtnHtml = `
                            <div class="d-flex justify-content-between align-items-center bg-white border border-warning p-3 px-4 rounded-3 shadow-sm incident-item">
                                <div class="d-flex align-items-center">
                                    <i class="${meta.icon} text-warning me-3 fs-4"></i>
                                    <div>
                                        <span class="fw-bold text-dark">${meta.label}</span>
                                        <span class="badge bg-warning text-dark ms-2"><i class="ri-information-line me-1"></i>Action Required</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold btn-create-incident text-dark shadow-sm" data-type="${key}">
                                    <i class="ri-add-circle-line me-1"></i> Create ${meta.label} Now
                                </button>
                            </div>
                        `;
                        $list.append(createBtnHtml);
                    }
                });

                $countBadge.text(required.length + ' Incident(s) Detected (' + createdCount + '/' + required.length + ' Created)');

                if (createdCount < required.length) {
                    $saveBtn.prop('disabled', true).addClass('disabled opacity-50');
                    var remaining = required.length - createdCount;
                    $saveNotice.html(`
                        <div class="alert alert-warning py-2 px-3 d-inline-flex align-items-center rounded-pill shadow-sm border-0 mb-0">
                            <i class="ri-error-warning-fill text-warning me-2 fs-5"></i>
                            <span class="small fw-semibold text-dark">Please create all ${remaining} remaining incident record(s) above before saving the inspection report.</span>
                        </div>
                    `);
                } else {
                    $saveBtn.prop('disabled', false).removeClass('disabled opacity-50');
                    $saveNotice.html(`
                        <div class="alert alert-success py-2 px-3 d-inline-flex align-items-center rounded-pill shadow-sm border-0 mb-0">
                            <i class="ri-checkbox-circle-fill text-success me-2 fs-5"></i>
                            <span class="small fw-semibold text-success">All required incident records (${createdCount}/${required.length}) have been created. You can now save the report.</span>
                        </div>
                    `);
                }
            }

            function syncWaybillData() {
                var $opt = $waybillSelect.find(':selected');
                if ($opt.length && $opt.val()) {
                    $('#waybill_number').val($opt.data('waybill_number') || '');
                    $('#driver_name').val($opt.data('driver_name') || '');
                    $('#driver_phone_id').val($opt.data('driver_phone_id') || '');
                    $('#route').val($opt.data('route') || '');
                    $('#goods_description').val($opt.data('goods_description') || '');

                    var rawCargos = $opt.data('cargos');
                    var cargos = [];
                    if (typeof rawCargos === 'string') {
                        try { cargos = JSON.parse(rawCargos); } catch(e) { cargos = []; }
                    } else if (Array.isArray(rawCargos)) {
                        cargos = rawCargos;
                    }

                    renderInspectionCargos(cargos);
                } else {
                    $('#waybill_number').val('');
                    var $vOpt = $vehicleSelect.find(':selected');
                    if ($vOpt.length && $vOpt.val()) {
                        if ($vOpt.data('driver_name')) $('#driver_name').val($vOpt.data('driver_name'));
                        if ($vOpt.data('driver_phone_id')) $('#driver_phone_id').val($vOpt.data('driver_phone_id'));
                        if ($vOpt.data('route')) $('#route').val($vOpt.data('route'));
                    }
                    renderInspectionCargos([]);
                }
            }

            $vehicleSelect.on('change', function(e) {
                if (isSyncing) return;
                isSyncing = true;

                var selectedVehicleId = $vehicleSelect.val();
                var $vehicleOption = $vehicleSelect.find(':selected');
                var currentWaybillId = $waybillSelect.val();

                var fleetNo = $vehicleOption.data('fleet') || '';
                var truckNo = $vehicleOption.data('truck') || '';

                $('#fleet_no').val(fleetNo);
                $('#vehicle_lorry_number').val(truckNo);

                $waybillSelect.empty();
                $waybillSelect.append('<option value="">Select Waybill</option>');

                var matchingWaybillVal = '';
                var matchCount = 0;

                allWaybillOptions.forEach(function($opt) {
                    if (!$opt.val()) return;
                    var optVehicleId = $opt.data('vehicle');

                    if (!selectedVehicleId || !optVehicleId || optVehicleId == selectedVehicleId) {
                        $waybillSelect.append($opt.clone());
                        if (selectedVehicleId && optVehicleId == selectedVehicleId) {
                            matchCount++;
                            if (!matchingWaybillVal) {
                                matchingWaybillVal = $opt.val();
                            }
                        }
                    }
                });

                var targetWaybillVal = '';
                if (currentWaybillId && $waybillSelect.find('option[value="' + currentWaybillId + '"]').length) {
                    targetWaybillVal = currentWaybillId;
                } else if (matchCount === 1) {
                    targetWaybillVal = matchingWaybillVal;
                }

                $waybillSelect.val(targetWaybillVal).trigger('change.select2');
                syncWaybillData();
                isSyncing = false;
            });

            $waybillSelect.on('change', function() {
                if (isSyncing) return;
                isSyncing = true;

                var selectedWaybillId = $waybillSelect.val();
                var $selected = $waybillSelect.find(':selected');

                if (selectedWaybillId) {
                    var waybillVehicleId = $selected.data('vehicle');
                    if (waybillVehicleId && $vehicleSelect.val() != waybillVehicleId) {
                        $vehicleSelect.val(waybillVehicleId).trigger('change.select2');
                        var $vOpt = $vehicleSelect.find(':selected');
                        $('#fleet_no').val($vOpt.data('fleet') || '');
                        $('#vehicle_lorry_number').val($vOpt.data('truck') || '');
                    }
                }

                syncWaybillData();
                isSyncing = false;
            });

            $(document).on('input change', '.cargo-observed-qty', function() {
                var card = $(this).closest('.cargo-inspection-card')[0];
                recalculateCargos(card, 'observed_quantity');
            });

            $(document).on('change', '.cargo-goods-match, .cargo-ill-shortage, .cargo-ill-haulage', function() {
                var card = $(this).closest('.cargo-inspection-card')[0];
                recalculateCargos(card, 'manual_dropdown');
            });

            $('#unauthorised_items, #illegal_haulage, #doors_tarpaulin_secure, #cargo_secured, #visible_damage, #route_diversion, #breathaliser_test, #driver_misconduct').on('change', function() {
                recalculateCargos();
            });

            $('#GoodsInspectionForm').on('submit', function(e) {
                var required = getClientRequiredIncidents();
                var createdCount = 0;
                required.forEach(function(key) {
                    if (existingIncidentsMap[key]) createdCount++;
                });

                if (required.length > 0 && createdCount < required.length) {
                    e.preventDefault();
                    var remaining = required.length - createdCount;
                    alert('Please create all ' + remaining + ' required incident record(s) above before saving the inspection report.');
                    return false;
                }
            });

            $(document).on('click', '.btn-create-incident', function(e) {
                e.preventDefault();
                var incidentType = $(this).data('type');
                var $btn = $(this);
                var originalHtml = $btn.html();

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Creating...');

                var formData = new FormData($('#GoodsInspectionForm')[0]);
                formData.append('incident_type', incidentType);

                $.ajax({
                    url: '{{ route('goods_inspection.create_incident') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.inspection_id) $('#inspection_id').val(res.inspection_id);

                        if (res.evaluation && res.evaluation.incidents) {
                            res.evaluation.incidents.forEach(function(item) {
                                if (item.exists) {
                                    existingIncidentsMap[item.key] = {
                                        id: item.incident_id,
                                        edit_url: item.edit_url,
                                        show_url: item.show_url,
                                        label: item.label
                                    };
                                }
                            });
                        }

                        updateIncidentRequirements();

                        if (res.edit_url) window.open(res.edit_url, '_blank');
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(originalHtml);
                        var errorMsg = 'An error occurred while creating the incident.';
                        if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                        alert(errorMsg);
                    }
                });
            });

            if ($waybillSelect.val()) {
                syncWaybillData();
            } else if ($vehicleSelect.val()) {
                $vehicleSelect.trigger('change');
            } else {
                updateIncidentRequirements();
            }
        });
    </script>

    {{-- ═══════════════════════════════════════════════════════════════
         STYLES — Final Premium Polish
    ═══════════════════════════════════════════════════════════════ --}}
    <style>
        /* ═══════════════════════════════════════════════════════════
           SECTION CARDS
        ═══════════════════════════════════════════════════════════ */
        .section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 16px !important;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .section-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .section-card:hover {
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.06) !important;
            border-color: #cbd5e1 !important;
        }
        .section-card:hover::before { opacity: 1; }

        /* ═══════════════════════════════════════════════════════════
           SECTION HEADER
        ═══════════════════════════════════════════════════════════ */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .section-header-left { display: flex; align-items: center; gap: 0.85rem; }
        .section-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #1e293b;
            letter-spacing: -0.01em;
        }

        /* ═══════════════════════════════════════════════════════════
           ICON CIRCLES
        ═══════════════════════════════════════════════════════════ */
        .icon-circle {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .section-card:hover .icon-circle { transform: scale(1.05) rotate(-3deg); }
        .icon-circle.blue   { background-color: #e0e7ff; color: #4f46e5; }
        .icon-circle.amber  { background-color: #fef3c7; color: #d97706; }
        .icon-circle.red    { background-color: #fee2e2; color: #dc2626; }
        .icon-circle.cyan   { background-color: #cffafe; color: #0891b2; }
        .icon-circle.slate  { background-color: #e2e8f0; color: #475569; }
        .icon-circle.dark   { background-color: #e2e8f0; color: #334155; }

        .pulse-icon { animation: pulseGlow 2s infinite; }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4); }
            50%      { box-shadow: 0 0 0 8px rgba(217, 119, 6, 0); }
        }

        /* ═══════════════════════════════════════════════════════════
           UNIFIED BADGE SYSTEM
           REQUIRED, cargo index, cargo diff, cargo status — one DNA
        ═══════════════════════════════════════════════════════════ */
        .required-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
            color: #4f46e5;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 6px 16px;
            border-radius: 50px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            white-space: nowrap;
            border: 1px solid #c7d2fe;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.12);
            min-height: 30px;
        }

        .cargo-index-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 30px;
            padding: 0 12px;
            border-radius: 50px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 8px rgba(59, 130, 246, 0.3);
            flex-shrink: 0;
        }

        .cargo-diff-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
            min-height: 30px;
            border: 1px solid transparent;
            transition: all 0.25s ease;
        }

        .cargo-diff-pending {
            background: #f1f5f9;
            color: #64748b;
            border-color: #e2e8f0;
        }
        .cargo-diff-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #15803d;
            border-color: #86efac;
        }
        .cargo-diff-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #b45309;
            border-color: #fcd34d;
        }
        .cargo-diff-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #b91c1c;
            border-color: #fca5a5;
        }

        /* Legacy class compatibility — in case JS still uses bg-* */
        .cargo-diff-badge.bg-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border-color: #e2e8f0 !important;
        }
        .cargo-diff-badge.bg-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }
        .cargo-diff-badge.bg-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
            color: #b45309 !important;
            border-color: #fcd34d !important;
        }
        .cargo-diff-badge.bg-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
            color: #b91c1c !important;
            border-color: #fca5a5 !important;
        }

        /* ═══════════════════════════════════════════════════════════
           FORM INPUTS
        ═══════════════════════════════════════════════════════════ */
        .form-floating > .form-control,
        .form-floating > .form-select {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
            height: 58px;
            font-size: 0.95rem;
            background: #f8fafc;
        }
        .form-floating > .form-control:hover,
        .form-floating > .form-select:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }
        .form-floating > label { color: #64748b; font-weight: 500; }

        /* ═══════════════════════════════════════════════════════════
           CARGO SUMMARY BAR — clean top accent, no side bar
        ═══════════════════════════════════════════════════════════ */
        .cargo-summary-bar {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px !important;
            position: relative;
            overflow: hidden;
        }
        .cargo-summary-bar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
        }
        .cargo-summary-cell { padding: 4px 12px; }
        .cargo-summary-label {
            font-size: 0.68rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.6px;
            margin-bottom: 4px;
        }
        .cargo-summary-value {
            font-size: 1.35rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        /* Status badge on summary bar */
        #summary-overall-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 16px !important;
            border-radius: 50px !important;
            font-size: 0.72rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-top: 6px;
            border: 1px solid transparent;
            transition: all 0.25s ease;
            min-height: 30px;
            min-width: 120px;
        }
        #summary-overall-badge.sbadge-muted {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border-color: #e2e8f0 !important;
        }
        #summary-overall-badge.sbadge-info {
            background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%) !important;
            color: #0e7490 !important;
            border-color: #67e8f9 !important;
        }
        #summary-overall-badge.sbadge-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }
        #summary-overall-badge.sbadge-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
            color: #b45309 !important;
            border-color: #fcd34d !important;
        }
        #summary-overall-badge.sbadge-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
            color: #b91c1c !important;
            border-color: #fca5a5 !important;
        }
        /* Legacy bg-* compat */
        #summary-overall-badge.bg-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border-color: #e2e8f0 !important;
        }
        #summary-overall-badge.bg-info {
            background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%) !important;
            color: #0e7490 !important;
            border-color: #67e8f9 !important;
        }
        #summary-overall-badge.bg-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }
        #summary-overall-badge.bg-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
            color: #b45309 !important;
            border-color: #fcd34d !important;
        }
        #summary-overall-badge.bg-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
            color: #b91c1c !important;
            border-color: #fca5a5 !important;
        }

        /* ═══════════════════════════════════════════════════════════
           EMPTY STATE
        ═══════════════════════════════════════════════════════════ */
        .empty-state-cargo {
            background: linear-gradient(135deg, #fafbff 0%, #f8fafc 100%);
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
        }
        .empty-state-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0e7ff 0%, #cffafe 100%);
            color: #4f46e5;
            font-size: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        /* ═══════════════════════════════════════════════════════════
           CARGO INSPECTION CARDS
        ═══════════════════════════════════════════════════════════ */
        .cargo-inspection-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .cargo-inspection-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.08);
        }

        .cargo-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            border-bottom: 1px solid #e2e8f0;
        }
        .cargo-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .cargo-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex-wrap: wrap;
        }
        .cargo-title-text {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cargo-meta-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .cargo-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 50px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }
        .cargo-meta-pill i {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .cargo-inspection-card .card-body { padding: 16px 18px; }

        /* ═══════════════════════════════════════════════════════════
           SELECT2
        ═══════════════════════════════════════════════════════════ */
        .select2-container--default .select2-selection--single {
            height: 58px !important;
            padding-top: 17px !important;
            border-radius: 10px !important;
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            transition: all 0.2s ease;
        }
        .select2-container--default .select2-selection--single:hover {
            border-color: #cbd5e1 !important;
            background-color: #ffffff !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 58px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            line-height: 28px !important;
            padding-left: 14px !important;
            font-weight: 500;
        }
        .form-floating > .select2-container + label {
            transform: scale(.85) translateY(-.5rem) translateX(.15rem);
            opacity: .65;
            z-index: 5;
            margin-top: 5px;
        }
        
        /* Fix Select2 dropdown z-index to appear above other sections */
        .select2-container {
            z-index: 99999 !important;
        }
        
        .select2-dropdown {
            z-index: 99999 !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12) !important;
        }
        
        .select2-container--open {
            z-index: 99999 !important;
        }
        
        /* Ensure parent has proper stacking */
        .form-floating:has(.select2-container) {
            position: relative;
            z-index: 100;
        }
        
        .section-card:has(.select2-container) {
            overflow: visible !important;
        }

        /* ═══════════════════════════════════════════════════════════
           GEOAPIFY
        ═══════════════════════════════════════════════════════════ */
        .geoapify-wrapper,
        .col-md-12:has(> .geoapify-autocomplete-input),
        .col-md-12:has(> .geoapify-autocomplete-container) {
            position: relative;
            z-index: 9999 !important;
        }
        
        /* Ensure parent section doesn't create stacking context issues */
        .section-card {
            position: relative;
            z-index: 1;
        }
        
        .section-card:has(.geoapify-wrapper) {
            z-index: 9998 !important;
            overflow: visible !important;
        }
        
        .geoapify-autocomplete-input {
            width: 100% !important;
            height: 58px !important;
            border-radius: 10px !important;
            border: 1.5px solid #e2e8f0 !important;
            padding: 0 14px !important;
            font-size: 0.95rem !important;
            background: #f8fafc !important;
            transition: all 0.2s ease;
        }
        .geoapify-autocomplete-input:hover {
            border-color: #cbd5e1 !important;
            background: #ffffff !important;
        }
        .geoapify-autocomplete-input:focus {
            border-color: #6366f1 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
            outline: none !important;
        }
        .geoapify-autocomplete-items {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 99999 !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12) !important;
            max-height: 260px !important;
            overflow-y: auto !important;
            margin-top: 4px !important;
            padding: 4px 0 !important;
        }
        .geoapify-autocomplete-items .geoapify-autocomplete-item,
        .geoapify-autocomplete-items div {
            padding: 10px 14px !important;
            cursor: pointer !important;
            font-size: 0.9rem !important;
            color: #334155 !important;
            transition: background 0.15s ease !important;
            border: none !important;
        }
        .geoapify-autocomplete-items .geoapify-autocomplete-item:hover,
        .geoapify-autocomplete-items div:hover { background: #f1f5f9 !important; }
        .geoapify-autocomplete-items .geoapify-autocomplete-item.active,
        .geoapify-autocomplete-items div.active {
            background: #e0e7ff !important;
            color: #1e40af !important;
        }

        /* ═══════════════════════════════════════════════════════════
           INCIDENT CARD
        ═══════════════════════════════════════════════════════════ */
        .incident-card {
            border: 2px solid transparent !important;
            background: linear-gradient(#fff, #fff) padding-box,
                        linear-gradient(135deg, #f59e0b, #dc2626) border-box;
            border-radius: 16px !important;
        }
        .incident-item {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.3s ease-out;
        }
        .incident-item:hover {
            transform: translateX(6px);
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.08) !important;
        }

        /* ═══════════════════════════════════════════════════════════
           SUBMIT BUTTON
        ═══════════════════════════════════════════════════════════ */
        .submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 2px 4px rgba(59, 130, 246, 0.25),
                0 6px 16px -4px rgba(59, 130, 246, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        .submit-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, 0.35) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
            pointer-events: none;
        }
        .submit-btn:hover::after { transform: translateX(100%); }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 4px 8px rgba(59, 130, 246, 0.3),
                0 12px 28px -6px rgba(59, 130, 246, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; }

        /* ═══════════════════════════════════════════════════════════
           VALIDATION ALERT
        ═══════════════════════════════════════════════════════════ */
        .gi-alert {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 4px solid #dc2626 !important;
        }
        .gi-alert-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        /* ═══════════════════════════════════════════════════════════
           RESPONSIVE
        ═══════════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .card-body { padding: 1rem !important; }
            .icon-circle { width: 40px; height: 40px; font-size: 1.15rem; }
            .section-header { flex-wrap: wrap; gap: 0.5rem; }
            .section-title { font-size: 0.95rem; }
            .submit-btn { width: 100%; padding: 0.875rem 2rem !important; }
            .cargo-summary-value { font-size: 1.05rem; }
            .cargo-card-header { padding: 12px 14px; }
            .cargo-title-text { font-size: 0.88rem; }
        }

        /* ═══════════════════════════════════════════════════════════
           ANIMATIONS
        ═══════════════════════════════════════════════════════════ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ═══════════════════════════════════════════════════════════
           PRINT
        ═══════════════════════════════════════════════════════════ */
        @media print {
            .section-card { box-shadow: none; border: 1px solid #ddd; }
            .section-card::before,
            .cargo-summary-bar::before { display: none; }
            .icon-circle { transform: none !important; }
            .submit-btn { box-shadow: none; background: #f1f5f9; color: #334155; }
            .submit-btn::after { display: none; }
        }
    </style>
@endpush
