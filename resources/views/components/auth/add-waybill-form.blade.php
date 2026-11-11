<x-auth.form form-id="WaybillForm" form-action="{{ $route }}" enctype="true">
    @php
        $initialDriverRows = old('drivers');
        if (!is_array($initialDriverRows) || $initialDriverRows === []) {
            if ($result?->driverPlans?->isNotEmpty()) {
                $initialDriverRows = $result->driverPlans->map(fn ($plan) => [
                    'driver_id' => $plan->driver_id,
                    'duration_minutes' => $plan->planned_seconds !== null ? max(1, (int) ceil($plan->planned_seconds / 60)) : '',
                ])->all();
            } else {
                $initialDriverRows = [[
                    'driver_id' => old('driver_id', $result?->driver_id),
                    'duration_minutes' => '',
                ]];
            }
        }
        $initialDriverRows = array_values($initialDriverRows);
        if ($initialDriverRows === []) $initialDriverRows = [['driver_id' => old('driver_id', $result?->driver_id), 'duration_minutes' => '']];
        $firstDriverId = $initialDriverRows[0]['driver_id'] ?? old('driver_id', $result?->driver_id);
    @endphp
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4 waybill-modern-form">

        @if (isset($errors) && $errors->any())
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="ri-error-warning-line me-1"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <input type="hidden" name="waybill_number" value="{{ $nextWaybillNumber }}">

        {{-- Hidden route persistence fields --}}
        <input type="hidden" name="origin_lat" id="origin_lat" value="{{ $result?->origin_lat ?? old('origin_lat') }}">
        <input type="hidden" name="origin_lng" id="origin_lng" value="{{ $result?->origin_lng ?? old('origin_lng') }}">
        <input type="hidden" name="destination_lat" id="destination_lat" value="{{ $result?->destination_lat ?? old('destination_lat') }}">
        <input type="hidden" name="destination_lng" id="destination_lng" value="{{ $result?->destination_lng ?? old('destination_lng') }}">
        <input type="hidden" name="checkpoints" id="checkpoints_input"
            value="{{ $result?->checkpoints ? (is_string($result->checkpoints) ? $result->checkpoints : json_encode($result->checkpoints)) : old('checkpoints', '[]') }}">
        <input type="hidden" name="route_geometry" id="route_geometry_input"
            value="{{ $result?->route_geometry ? (is_string($result->route_geometry) ? $result->route_geometry : json_encode($result->route_geometry)) : old('route_geometry', '[]') }}">
        <input type="hidden" name="wialon_route_id" id="wialon_route_id" value="{{ $result?->wialon_route_id ?? old('wialon_route_id') }}">
        <input type="hidden" name="route_source" id="route_source" value="{{ $result?->route_source ?? old('route_source', 'geoapify') }}">
        <input type="hidden" name="driver_id" id="driver_id" value="{{ $firstDriverId }}">

        {{-- ================= SECTION 1: Vehicle & Driver ================= --}}
        <div class="col-12">
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <span class="form-section-icon">
                            <i class="fa-solid fa-truck"></i>
                        </span>
                        <div>
                            <h6 class="form-section-title">{{ __('Vehicle & Driver') }}</h6>
                            <small class="form-section-subtitle">{{ __('Assign the asset and driver for this waybill') }}</small>
                        </div>
                    </div>
                    <span class="badge-required-pill">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 modern-field">
                        <label for="vehicle_id">{{ __('Asset') }} <span class="text-danger">*</span></label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                            <option value="">-- Select Vehicle --</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    data-has-gps="{{ !empty($vehicle->gps_unit_id) ? '1' : '0' }}"
                                    data-min-trip-seconds="{{ $vehicle->min_trip_seconds ?? '' }}"
                                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->fleet_no }} ({{ $vehicle->truck_no }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 modern-field">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="mb-0">{{ __('Drivers (in driving order)') }} <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="btn-add-driver">
                                <i class="ri-user-add-line me-1"></i>{{ __('Add Driver') }}
                            </button>
                        </div>
                        <p class="small text-muted mb-3">{{ __('One driver: time is optional. Several drivers: set minutes for each, the last driver can stay empty (until the trip ends).') }}</p>
                        <div id="waybill-driver-rows" class="d-flex flex-column gap-2">
                            @foreach ($initialDriverRows as $index => $driverRow)
                                <div class="waybill-driver-row border rounded-3 p-3 bg-light-subtle" data-index="{{ $index }}">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-auto"><span class="badge bg-primary rounded-pill driver-sequence">{{ $index + 1 }}</span></div>
                                        <div class="col-md modern-field">
                                            <label>{{ __('Driver') }}</label>
                                            <select class="form-select waybill-driver-select" name="drivers[{{ $index }}][driver_id]" required>
                                                <option value="">-- Select Driver --</option>
                                                @foreach ($drivers as $driver)
                                                    <option value="{{ $driver->id }}" @selected(($driverRow['driver_id'] ?? '') == $driver->id)>{{ $driver->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 modern-field">
                                            <label>{{ __('Planned time (minutes)') }}</label>
                                            <input type="number" class="form-control driver-duration" name="drivers[{{ $index }}][duration_minutes]" min="1" step="1" value="{{ $driverRow['duration_minutes'] ?? '' }}" placeholder="{{ __('Optional') }}">
                                        </div>
                                        <div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm btn-remove-driver" title="{{ __('Remove driver') }}"><i class="ri-delete-bin-line"></i></button></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="driver-time-limit-feedback" class="small mt-2" aria-live="polite"></div>
                        <div id="assigned-driver-quick-add" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 2: Route Selection ================= --}}
        <div class="col-12">
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <span class="form-section-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <div>
                            <h6 class="form-section-title">{{ __('Route Selection') }}</h6>
                            <small class="form-section-subtitle">{{ __('Geoapify autocomplete & real-time routing') }}</small>
                        </div>
                    </div>
                    <span class="badge-required-pill">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    {{-- Origin + Destination --}}
                    <div class="col-md-6 position-relative modern-field">
                        <label for="origin">
                            <i class="ri-map-pin-line text-success me-1"></i>{{ __('Origin') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="origin" name="origin" placeholder="Search origin location"
                            value="{{ $result?->origin ?? old('origin') }}" autocomplete="off" required>
                        <div id="origin-suggestions" class="geoapify-autocomplete-dropdown shadow-lg rounded-3 border bg-white position-absolute w-100" style="display: none; z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                    </div>

                    <div class="col-md-6 position-relative modern-field">
                        <label for="destination">
                            <i class="ri-map-pin-2-line text-danger me-1"></i>{{ __('Destination') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="destination" name="destination" placeholder="Search destination location"
                            value="{{ $result?->destination ?? old('destination') }}" autocomplete="off" required>
                        <div id="destination-suggestions" class="geoapify-autocomplete-dropdown shadow-lg rounded-3 border bg-white position-absolute w-100" style="display: none; z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                    </div>

                    {{-- Checkpoints --}}
                    <div class="col-12">
                        <div class="checkpoint-inner-card">
                            <label class="form-label fw-semibold text-dark mb-2 d-flex align-items-center justify-content-between">
                                <span><i class="ri-route-fill text-primary me-1"></i> {{ __('Route Checkpoints') }} <span class="badge-optional-pill ms-2">{{ __('Optional') }}</span></span>
                                <small class="text-muted fw-normal">{{ __('Search & add intermediate stops to the route') }}</small>
                            </label>

                            <div class="row g-2 align-items-center position-relative">
                                <div class="col-md-9 position-relative modern-field mb-0">
                                    <input type="text" class="form-control" id="checkpoint_search" placeholder="Search location to add as checkpoint..." autocomplete="off">
                                    <div id="checkpoint-suggestions" class="geoapify-autocomplete-dropdown shadow-lg rounded-3 border bg-white position-absolute w-100" style="display: none; z-index: 1050; max-height: 230px; overflow-y: auto; top: 100%;"></div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-1 rounded-3 add-checkpoint-modern-btn" id="add-checkpoint-btn">
                                        <i class="ri-add-line fs-5"></i> {{ __('Add') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3" id="checkpoints-list-container" style="display: none;">
                                <div class="small fw-semibold text-muted mb-2">{{ __('Route Sequence') }}:</div>
                                <ul class="list-group shadow-sm rounded-3" id="checkpoints-list"></ul>
                            </div>
                        </div>
                    </div>

                    {{-- Distance + Duration --}}
                    <div class="col-md-6 modern-field">
                        <label for="distance" class="d-flex align-items-center">
                            {{ __('Distance (KM)') }}
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 ms-1" id="distance-badge"
                                style="font-size: 0.65rem;">
                                <i class="ri-calculator-line"></i> Real-time
                            </span>
                        </label>
                        <input type="number" step="0.01" class="form-control" id="distance" name="distance" placeholder="1200"
                            value="{{ $result?->distance ?? old('distance') }}" autocomplete="off">
                    </div>

                    <div class="col-md-6 modern-field">
                        <label for="estimated_duration" class="d-flex align-items-center">
                            {{ __('Estimated Duration') }}
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 ms-1" id="duration-badge"
                                style="font-size: 0.65rem;">
                                <i class="ri-time-line"></i> Real-time
                            </span>
                        </label>
                        <input type="text" class="form-control" id="estimated_duration" name="estimated_duration"
                            placeholder="18 Hours" value="{{ $result?->estimated_duration ?? old('estimated_duration') }}"
                            autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        {{-- Route Leaflet / MapLibre Map Container --}}
        <div class="col-12" id="route-map-card">
            <div class="card border-0 shadow-sm route-card">
                <div class="card-header route-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="route-icon-wrapper rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-map fs-5"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold" id="route-map-title">{{ __('Waybill Route Map') }}</h6>
                            <small class="text-muted" id="route-map-subtitle">{{ __('Real-time map powered by MapLibre GL JS & Geoapify') }}</small>
                        </div>
                    </div>
                    <span class="badge route-distance-badge px-3 py-2 rounded-pill fw-semibold" id="route-distance-badge">
                        <i class="ri-route-line me-1"></i>0.00 KM
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    {{-- Status Alert Box --}}
                    <div class="alert border-0 shadow-sm d-flex align-items-start mb-3 p-3 rounded-3 alert-info" id="route-map-alert" style="display: none;">
                        <i class="ri-information-line me-2 fs-5 mt-1 flex-shrink-0" id="route-map-alert-icon"></i>
                        <span id="route-map-alert-msg"></span>
                    </div>

                    {{-- Checkpoints Flow Timeline Bar --}}
                    <div id="checkpoints-flow-wrapper" class="mb-3" style="display: none;">
                        <label class="form-label text-muted small fw-semibold mb-2">
                            <i class="ri-node-tree me-1"></i>{{ __('Path Sequence') }}
                        </label>
                        <div id="checkpoints-flow" class="d-flex flex-wrap align-items-center gap-2 bg-light p-2.5 rounded-3 border overflow-auto" style="max-height: 120px;"></div>
                    </div>

                    {{-- Leaflet Map Element with Loader --}}
                    <div class="position-relative map-wrapper rounded-3 overflow-hidden border" id="map-wrapper" style="height: 380px; width: 100%;">
                        <div id="waybill-route-map" class="w-100 h-100"></div>
                        <div id="map-skeleton-loader" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light d-none" style="z-index: 1000;">
                            <div class="text-center">
                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                    <span class="visually-hidden">Calculating route...</span>
                                </div>
                                <div class="small text-muted" id="map-loader-text">Calculating route geometry...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 3: Schedule ================= --}}
        <div class="col-12">
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <span class="form-section-icon">
                            <i class="fa-solid fa-calendar-days"></i>
                        </span>
                        <div>
                            <h6 class="form-section-title">{{ __('Schedule') }}</h6>
                            <small class="form-section-subtitle">{{ __('Dispatch date and estimated departure time') }}</small>
                        </div>
                    </div>
                    <span class="badge-required-pill">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 modern-field">
                        <label for="dispatch_date">{{ __('Dispatch Date') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('dispatch_date') is-invalid @enderror"
                            id="dispatch_date" name="dispatch_date"
                            value="{{ $result?->dispatch_date?->format('Y-m-d') ?? old('dispatch_date', date('Y-m-d')) }}" required>
                        @error('dispatch_date')
                            <span class="text-danger" style="font-size:12px">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 modern-field">
                        <label for="estimated_dispatch_time">{{ __('Estimated Dispatch Time') }}</label>
                        <input type="time" class="form-control @error('estimated_dispatch_time') is-invalid @enderror"
                            id="estimated_dispatch_time" name="estimated_dispatch_time"
                            value="{{ $result?->estimated_dispatch_time ?? old('estimated_dispatch_time') }}">
                        @error('estimated_dispatch_time')
                            <span class="text-danger" style="font-size:12px">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 4: Cargo Details (Dynamic Addons) ================= --}}
        @php
            $cargosList = old('cargos');
            if ($cargosList === null) {
                if ($result && $result->relationLoaded('cargos') && $result->cargos->isNotEmpty()) {
                    $cargosList = $result->cargos->toArray();
                } elseif ($result && (!empty($result->cargo_description) || isset($result->quantity) || isset($result->cargo_weight_kg))) {
                    $cargosList = [[
                        'cargo_description' => $result->cargo_description,
                        'quantity' => $result->quantity,
                        'quantity_unit' => $result->quantity_unit,
                        'cargo_weight_kg' => $result->cargo_weight_kg,
                        'weight_unit' => $result->weight_unit,
                        'volume_unit' => $result->volume_unit,
                    ]];
                } else {
                    $cargosList = [[
                        'cargo_description' => '',
                        'quantity' => '',
                        'quantity_unit' => '',
                        'cargo_weight_kg' => '',
                        'weight_unit' => '',
                        'volume_unit' => '',
                    ]];
                }
            }
            $quantityUnits = [
                'PCS' => 'Piece (PCS)', 'UNIT' => 'Unit (UNIT)', 'ITEM' => 'Item (ITEM)',
                'PKG' => 'Package (PKG)', 'CTN' => 'Carton (CTN)', 'BOX' => 'Box (BOX)',
                'CS' => 'Case (CS)', 'CRT' => 'Crate (CRT)', 'BAG' => 'Bag (BAG)',
                'SACK' => 'Sack (SACK)', 'BDL' => 'Bundle (BDL)', 'BAL' => 'Bale (BAL)',
                'PLT' => 'Pallet (PLT)', 'SKD' => 'Skid (SKD)', 'DRM' => 'Drum (DRM)',
                'BRL' => 'Barrel (BRL)', 'ROL' => 'Roll (ROL)', 'COIL' => 'Coil (COIL)',
                'REL' => 'Reel (REL)', 'CNTR' => 'Container (CNTR)', 'CYL' => 'Cylinder (CYL)',
            ];
            $weightUnits = ['KG' => 'Kilogram (KG)', 'MT' => 'Metric Ton (MT)', 'G' => 'Gram (G)', 'LB' => 'Pound (LB)', 'QTL' => 'Quintal (QTL)'];
            $volumeUnits = ['CBM' => 'Cubic Metre (CBM)', 'CFT' => 'Cubic Foot (CFT)', 'L' => 'Litre (L)', 'GAL' => 'Gallon (GAL)'];
        @endphp

        <div class="col-12">
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <span class="form-section-icon">
                            <i class="fa-solid fa-box"></i>
                        </span>
                        <div>
                            <h6 class="form-section-title">{{ __('Cargo Details') }}</h6>
                            <small class="form-section-subtitle">{{ __('What is being shipped, quantities, and weights (Add multiple cargo items as needed)') }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm" id="btn-add-cargo-item">
                            <i class="ri-add-line me-1"></i> {{ __('Add Cargo Item') }}
                        </button>
                        <span class="badge-optional-pill">{{ __('Add-ons') }}</span>
                    </div>
                </div>

                <div id="cargo-items-wrapper" class="d-flex flex-column gap-3">
                    @foreach ($cargosList as $index => $cargoItem)
                        <div class="cargo-item-card border rounded-3 p-3 bg-light-subtle position-relative shadow-sm" data-index="{{ $index }}">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="fw-bold text-dark d-flex align-items-center">
                                    <i class="ri-box-3-line text-primary me-2 fs-5"></i>
                                    <span class="cargo-item-title">{{ __('Cargo Item') }} #<span class="cargo-item-number">{{ $index + 1 }}</span></span>
                                </span>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1 btn-remove-cargo" title="{{ __('Delete Item') }}" {{ count($cargosList) <= 1 ? 'style=display:none;' : '' }}>
                                    <i class="ri-delete-bin-line me-1"></i> {{ __('Delete') }}
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 modern-field">
                                    <label>{{ __('Cargo Description') }}</label>
                                    <input type="text" class="form-control" name="cargos[{{ $index }}][cargo_description]"
                                        placeholder="Electronics, Furniture..."
                                        value="{{ $cargoItem['cargo_description'] ?? '' }}" autocomplete="off">
                                </div>

                                <div class="col-md-3 modern-field">
                                    <label>{{ __('Quantity') }}</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="cargos[{{ $index }}][quantity]"
                                        placeholder="50" value="{{ $cargoItem['quantity'] ?? '' }}"
                                        autocomplete="off">
                                </div>

                                <div class="col-md-3 modern-field">
                                    <label>{{ __('Quantity Unit') }}</label>
                                    <select class="form-select" name="cargos[{{ $index }}][quantity_unit]">
                                        <option value="">{{ __('Select Quantity Unit') }}</option>
                                        @foreach ($quantityUnits as $value => $label)
                                            <option value="{{ $value }}" {{ ($cargoItem['quantity_unit'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 modern-field">
                                    <label>{{ __('Cargo Weight') }}</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="cargos[{{ $index }}][cargo_weight_kg]"
                                        placeholder="500" value="{{ $cargoItem['cargo_weight_kg'] ?? '' }}"
                                        autocomplete="off">
                                </div>

                                <div class="col-md-4 modern-field">
                                    <label>{{ __('Weight Unit') }}</label>
                                    <select class="form-select cargo-weight-unit" name="cargos[{{ $index }}][weight_unit]">
                                        <option value="">{{ __('Select Weight Unit') }}</option>
                                        @foreach ($weightUnits as $value => $label)
                                            <option value="{{ $value }}" {{ ($cargoItem['weight_unit'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 modern-field">
                                    <label>{{ __('Volume Unit') }}</label>
                                    <select class="form-select cargo-volume-unit" name="cargos[{{ $index }}][volume_unit]" {{ empty($cargoItem['weight_unit']) ? 'disabled' : '' }}>
                                        <option value="">{{ __('Select Volume Unit') }}</option>
                                        @foreach ($volumeUnits as $value => $label)
                                            <option value="{{ $value }}" {{ ($cargoItem['volume_unit'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold" id="btn-add-cargo-item-bottom">
                        <i class="ri-add-circle-line me-1 fs-5 align-middle"></i> {{ __('+ Add Another Cargo Item') }}
                    </button>
                </div>
            </div>
        </div>


        {{-- ================= SECTION 5: Status & Document ================= --}}
        <div class="col-12">
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <span class="form-section-icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </span>
                        <div>
                            <h6 class="form-section-title">{{ __('Status & Document') }}</h6>
                            <small class="form-section-subtitle">{{ __('Current status and supporting paperwork') }}</small>
                        </div>
                    </div>
                    <span class="badge-required-pill">{{ __('Required') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 modern-field">
                        <label for="status_id">{{ __('Status') }} <span class="text-danger">*</span></label>
                        <select name="status_id" id="status_id" class="form-select" required>
                            <option value="">{{ __('Select Status') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ ($result?->status_id ?? old('status_id')) == $status->id ? 'selected' : '' }}>
                                    {{ $status->badge_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 modern-field">
                        <label for="document">{{ __('Upload Document') }}</label>
                        <div class="input-group">
                            <input type="file" class="form-control bg-white" id="document" name="document"
                                accept=".pdf,.jpg,.jpeg,.png">

                            @if ($result?->document_url)
                                <a href="{{ $result->document_url }}" target="_blank"
                                    class="btn btn-outline-primary d-flex align-items-center">
                                    <i class="ri-eye-line me-1"></i> {{ __('View') }}
                                </a>
                            @endif
                        </div>
                        @error('document')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SECTION 6: Notes ================= --}}
        <div class="col-12">
            <div class="form-section-card">
                <div class="form-section-header">
                    <div class="form-section-header-left">
                        <span class="form-section-icon">
                            <i class="fa-solid fa-note-sticky"></i>
                        </span>
                        <div>
                            <h6 class="form-section-title">{{ __('Notes') }}</h6>
                            <small class="form-section-subtitle">{{ __('Anything else worth mentioning') }}</small>
                        </div>
                    </div>
                    <span class="badge-optional-pill">{{ __('Optional') }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-12 modern-field mb-0">
                        <textarea class="form-control" id="notes" name="notes" placeholder="Additional notes..."
                            style="height: 100px">{{ $result?->notes ?? old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-center mt-2">
            <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm"
                btn-type="submit" btn-value="{{ $isEdit ? __('Update Waybill') : __('Create Waybill') }}" />
        </div>

    </div>
</x-auth.form>
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" />
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">
<style>
        /* ==========================================================
           Modern Section Cards — flat, professional (no gradients)
           Matches the "Create new asset" reference layout
        ========================================================== */
        .waybill-modern-form .form-section-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.06), 0 1px 2px rgba(16, 24, 40, 0.04);
            border: 1px solid #e9edf3;
        }

        .waybill-modern-form .form-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eef1f6;
            flex-wrap: wrap;
        }

        .waybill-modern-form .form-section-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .waybill-modern-form .form-section-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef1ff;
            color: #4f7dff;
            font-size: 17px;
        }

        .waybill-modern-form .form-section-title {
            font-weight: 700;
            font-size: 15px;
            color: #1e293b;
            margin: 0;
        }

        .waybill-modern-form .form-section-subtitle {
            color: #94a3b8;
            font-size: 12.5px;
        }

        .waybill-modern-form .badge-required-pill,
        .waybill-modern-form .badge-optional-pill {
            font-weight: 600;
            font-size: 12px;
            padding: 6px 16px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .waybill-modern-form .badge-required-pill {
            background: #eef1ff;
            color: #4f7dff;
        }

        .waybill-modern-form .badge-optional-pill {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ==========================================================
           Modern Fields
        ========================================================== */
        .waybill-modern-form .modern-field {
            margin-bottom: 4px;
        }

        .waybill-modern-form .modern-field label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 7px;
            letter-spacing: 0.2px;
        }

        .waybill-modern-form .modern-field .form-control,
        .waybill-modern-form .modern-field .form-select {
            background: #f8fafc;
            border: 1.5px solid #e9edf3;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 14px;
            color: #1e293b;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .waybill-modern-form .modern-field .form-control::placeholder {
            color: #a3adc2;
        }

        .waybill-modern-form .modern-field .form-control:focus,
        .waybill-modern-form .modern-field .form-select:focus {
            background: #ffffff;
            border-color: #4f7dff;
            box-shadow: 0 0 0 3px rgba(79, 125, 255, 0.12);
        }

        .waybill-modern-form .modern-field .input-group .form-control {
            border-radius: 10px 0 0 10px;
        }

        .waybill-modern-form .modern-field .input-group .btn {
            border-radius: 0 10px 10px 0;
        }

        /* Checkpoints inner card */
        .waybill-modern-form .checkpoint-inner-card {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 16px;
        }

        .waybill-modern-form .add-checkpoint-modern-btn {
            height: 100%;
            min-height: 46px;
            border-radius: 10px !important;
        }

        /* ==========================================================
           Select2 restyle to match modern-field look
        ========================================================== */
        .waybill-modern-form .select2-container .select2-selection--single {
            background: #f8fafc !important;
            border: 1.5px solid #e9edf3 !important;
            border-radius: 10px !important;
            height: 46px !important;
            display: flex;
            align-items: center;
            padding: 0 6px 0 16px;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .waybill-modern-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px !important;
            padding-left: 0 !important;
            color: #1e293b;
            font-size: 14px;
        }

        .waybill-modern-form .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            right: 10px;
        }

        .waybill-modern-form .select2-container--default.select2-container--open .select2-selection--single,
        .waybill-modern-form .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #4f7dff !important;
            box-shadow: 0 0 0 3px rgba(79, 125, 255, 0.12);
            background: #ffffff !important;
        }

        .select2-dropdown {
            border-radius: 10px !important;
            border: 1px solid #e8edf4 !important;
            box-shadow: 0 8px 24px rgba(16, 24, 40, 0.1) !important;
            overflow: hidden;
        }

        .select2-search--dropdown {
            padding: 10px !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 8px !important;
            border: 1.5px solid #e8edf4 !important;
            padding: 8px 12px !important;
            outline: none !important;
        }

        .select2-search--dropdown .select2-search__field:focus {
            border-color: #4f7dff !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f7dff !important;
        }

        .select2-results__option {
            padding: 9px 16px !important;
            font-size: 14px;
        }

        /* ==========================================================
           Geoapify autocomplete dropdown polish
        ========================================================== */
        .geoapify-autocomplete-dropdown {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, 0.1) !important;
            border: 1px solid #e8edf4 !important;
            margin-top: 6px;
            overflow: hidden;
        }

        .geoapify-suggestion-item {
            padding: 11px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f4f7fb;
            transition: background 0.15s ease;
        }

        .geoapify-suggestion-item:last-child {
            border-bottom: none;
        }

        .geoapify-suggestion-item:hover {
            background: #f4f6fa;
        }

        .geoapify-suggestion-loading {
            padding: 14px 16px;
            color: #64748b;
            font-size: 13px;
        }

        .geoapify-suggestion-empty {
            padding: 14px 16px;
            color: #94a3b8;
            font-size: 13px;
            text-align: center;
        }

        /* ==========================================================
           Map / route / marker styling — flattened
        ========================================================== */
        .map-wrapper {
            width: 100%;
            height: 380px;
            border-radius: 14px;
            overflow: hidden;
            background: #f8fafc;
            position: relative;
            z-index: 0;
            border: 1px solid #e9edf3;
        }

        #waybill-route-map {
            width: 100%;
            height: 100%;
            border-radius: 14px;
            position: relative;
            z-index: 1;
        }

        .map-wrapper .leaflet-container {
            width: 100% !important;
            height: 100% !important;
        }

        .maplibregl-map {
            width: 100% !important;
            height: 100% !important;
            border-radius: 14px;
        }

        .maplibregl-ctrl-attrib {
            font-size: 10px;
            background: rgba(255, 255, 255, 0.8);
        }

        .marker-origin-container,
        .marker-destination-container,
        .marker-checkpoint-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }

        .marker-origin {
            width: 38px;
            height: 38px;
            background: #16a34a;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            position: relative;
        }

        .marker-origin-label {
            background: #16a34a;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .marker-destination {
            width: 38px;
            height: 38px;
            background: #dc2626;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            position: relative;
        }

        .marker-destination-label {
            background: #dc2626;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .marker-checkpoint {
            width: 36px;
            height: 36px;
            background: #4f7dff;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(79, 125, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            position: relative;
        }

        .marker-checkpoint-label {
            background: #4f7dff;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            margin-top: 3px;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        #checkpoints-flow {
            background: #f8fafc !important;
            border: 1px dashed #cbd5e1 !important;
            padding: 16px !important;
        }

        #checkpoints-flow .badge {
            transition: transform 0.15s ease;
        }

        #checkpoints-flow .badge:hover {
            transform: translateY(-2px);
        }

        .maplibregl-popup-content {
            padding: 12px 16px !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 16px rgba(16, 24, 40, 0.12) !important;
            font-family: inherit !important;
        }

        .maplibregl-popup-content b {
            font-size: 13px;
            color: #1e293b;
        }

        .maplibregl-popup-tip {
            border-top-color: white !important;
        }

        .checkpoint-item {
            transition: background 0.15s ease;
        }

        .checkpoint-item:hover {
            background: #f8fafc;
        }

        .route-card {
            border-radius: 16px !important;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.06), 0 1px 2px rgba(16, 24, 40, 0.04) !important;
            border: 1px solid #e9edf3 !important;
        }

        .route-header {
            background: #f8fafc;
            border-bottom: 1px solid #eef1f6 !important;
        }

        .route-icon-wrapper {
            width: 42px;
            height: 42px;
            background: #eef1ff;
            color: #4f7dff;
        }

        .route-distance-badge {
            background: #4f7dff;
            color: white;
            font-size: 0.85rem;
        }

        .waybill-modern-form .btn-primary {
            background: #4f7dff !important;
            border: none !important;
            padding: 12px 40px !important;
            font-weight: 600 !important;
            box-shadow: none !important;
            transition: background 0.15s ease;
        }

        .waybill-modern-form .btn-primary:hover {
            background: #3d68e6 !important;
        }
</style>

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script>
        // Wait for MapLibre to be fully loaded before initializing
        function waitForMapLibre(callback) {
            if (typeof maplibregl !== 'undefined') {
                callback();
            } else {
                setTimeout(function() {
                    waitForMapLibre(callback);
                }, 100);
            }
        }

        $(document).ready(function() {
            var $modal = $('#addWaybillModal');
            var dropdownTarget = $modal.length ? $modal : $(document.body);

            // Select2 Initializations
            $('#vehicle_id').select2({
                placeholder: "Select Asset",
                allowClear: true,
                width: '100%',
                dropdownParent: dropdownTarget
            });

            function initDriverSelect($select) {
                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        placeholder: "Select Driver",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: dropdownTarget
                    });
                }
            }
            $('.waybill-driver-select').each(function() { initDriverSelect($(this)); });

            $('#status_id').select2({
                placeholder: "Select Status",
                allowClear: true,
                width: '100%',
                dropdownParent: dropdownTarget
            });

            // Dynamic Cargo Items repeater handlers
            var quantityUnitOptions = @json($quantityUnits);
            var weightUnitOptions = @json($weightUnits);
            var volumeUnitOptions = @json($volumeUnits);

            function updateCargoItemNumbers() {
                var $cards = $('#cargo-items-wrapper .cargo-item-card');
                $cards.each(function(idx) {
                    $(this).attr('data-index', idx);
                    $(this).find('.cargo-item-number').text(idx + 1);
                    $(this).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        if (name) {
                            var newName = name.replace(/cargos\[\d+\]/, 'cargos[' + idx + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });

                if ($cards.length <= 1) {
                    $cards.find('.btn-remove-cargo').hide();
                } else {
                    $cards.find('.btn-remove-cargo').show();
                }
            }

            function addCargoItem() {
                var $wrapper = $('#cargo-items-wrapper');
                var newIndex = $wrapper.find('.cargo-item-card').length;

                var qOptionsHtml = '<option value="">Select Quantity Unit</option>';
                $.each(quantityUnitOptions, function(val, lbl) {
                    qOptionsHtml += '<option value="' + val + '">' + lbl + '</option>';
                });

                var wOptionsHtml = '<option value="">Select Weight Unit</option>';
                $.each(weightUnitOptions, function(val, lbl) {
                    wOptionsHtml += '<option value="' + val + '">' + lbl + '</option>';
                });

                var vOptionsHtml = '<option value="">Select Volume Unit</option>';
                $.each(volumeUnitOptions, function(val, lbl) {
                    vOptionsHtml += '<option value="' + val + '">' + lbl + '</option>';
                });

                var itemHtml = `
                    <div class="cargo-item-card border rounded-3 p-3 bg-light-subtle position-relative shadow-sm" data-index="${newIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="fw-bold text-dark d-flex align-items-center">
                                <i class="ri-box-3-line text-primary me-2 fs-5"></i>
                                <span class="cargo-item-title">{{ __('Cargo Item') }} #<span class="cargo-item-number">${newIndex + 1}</span></span>
                            </span>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1 btn-remove-cargo" title="{{ __('Delete Item') }}">
                                <i class="ri-delete-bin-line me-1"></i> {{ __('Delete') }}
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 modern-field">
                                <label>{{ __('Cargo Description') }}</label>
                                <input type="text" class="form-control" name="cargos[${newIndex}][cargo_description]"
                                    placeholder="Electronics, Furniture..." value="" autocomplete="off">
                            </div>

                            <div class="col-md-3 modern-field">
                                <label>{{ __('Quantity') }}</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="cargos[${newIndex}][quantity]"
                                    placeholder="50" value="" autocomplete="off">
                            </div>

                            <div class="col-md-3 modern-field">
                                <label>{{ __('Quantity Unit') }}</label>
                                <select class="form-select" name="cargos[${newIndex}][quantity_unit]">
                                    ${qOptionsHtml}
                                </select>
                            </div>

                            <div class="col-md-4 modern-field">
                                <label>{{ __('Cargo Weight') }}</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="cargos[${newIndex}][cargo_weight_kg]"
                                    placeholder="500" value="" autocomplete="off">
                            </div>

                            <div class="col-md-4 modern-field">
                                <label>{{ __('Weight Unit') }}</label>
                                <select class="form-select cargo-weight-unit" name="cargos[${newIndex}][weight_unit]">
                                    ${wOptionsHtml}
                                </select>
                            </div>

                            <div class="col-md-4 modern-field">
                                <label>{{ __('Volume Unit') }}</label>
                                <select class="form-select cargo-volume-unit" name="cargos[${newIndex}][volume_unit]" disabled>
                                    ${vOptionsHtml}
                                </select>
                            </div>
                        </div>
                    </div>
                `;

                $wrapper.append(itemHtml);
                updateCargoItemNumbers();
            }

            $(document).on('click', '#btn-add-cargo-item, #btn-add-cargo-item-bottom', function(e) {
                e.preventDefault();
                addCargoItem();
            });

            $(document).on('click', '.btn-remove-cargo', function(e) {
                e.preventDefault();
                var $card = $(this).closest('.cargo-item-card');
                if ($('#cargo-items-wrapper .cargo-item-card').length > 1) {
                    $card.slideUp(150, function() {
                        $(this).remove();
                        updateCargoItemNumbers();
                    });
                }
            });

            $(document).on('change', '.cargo-weight-unit', function() {
                var $card = $(this).closest('.cargo-item-card');
                var hasWeight = Boolean($(this).val());
                var $vol = $card.find('.cargo-volume-unit');
                $vol.prop('disabled', !hasWeight);
                if (!hasWeight) {
                    $vol.val('');
                }
            });

            updateCargoItemNumbers();


            var waybillMap = null;
            var mapLayerGroup = null;

            // Checkpoints State Array: [{ name: "...", lat: 12.34, lng: 56.78, sequence: 1 }]
            var checkpointsList = [];
            var pendingCheckpointObj = null;

            // Parse initial checkpoints if available
            try {
                var rawCp = $('#checkpoints_input').val();
                if (rawCp) {
                    checkpointsList = typeof rawCp === 'string' ? JSON.parse(rawCp) : rawCp;
                    if (!Array.isArray(checkpointsList)) checkpointsList = [];
                }
            } catch (e) {
                checkpointsList = [];
            }

            renderCheckpointsUI();

            // Ordered waybill driver plan repeater.
            function updateDriverTimeLimit() {
                var $durations = $('#waybill-driver-rows .driver-duration');
                var totalMinutes = 0;
                $durations.each(function() {
                    var value = Number($(this).val());
                    if (Number.isFinite(value) && value > 0) totalMinutes += value;
                    this.setCustomValidity('');
                });

                var $feedback = $('#driver-time-limit-feedback');
                if (totalMinutes > 0) {
                    $feedback.text('Total planned driving time: ' + totalMinutes + ' min').removeClass('text-danger').addClass('text-muted');
                } else {
                    $feedback.text('').removeClass('text-danger text-muted');
                }
            }

            function updateDriverRows() {
                var $rows = $('#waybill-driver-rows .waybill-driver-row');
                $rows.each(function(index) {
                    var $row = $(this);
                    $row.attr('data-index', index);
                    $row.find('.driver-sequence').text(index + 1);
                    $row.find('.waybill-driver-select').attr('name', 'drivers[' + index + '][driver_id]');
                    $row.find('.driver-duration').attr('name', 'drivers[' + index + '][duration_minutes]')
                        .prop('required', $rows.length > 1 && index < $rows.length - 1);
                    $row.find('.btn-remove-driver').toggle($rows.length > 1);
                    initDriverSelect($row.find('.waybill-driver-select'));
                });

                $('#driver_id').val($rows.first().find('.waybill-driver-select').val() || '');
                refreshDriverOptions();
                updateDriverTimeLimit();
            }

            function refreshDriverOptions() {
                var selected = [];
                $('#waybill-driver-rows .waybill-driver-select').each(function() {
                    if ($(this).val()) selected.push(String($(this).val()));
                });
                $('#waybill-driver-rows .waybill-driver-select').each(function() {
                    var current = String($(this).val() || '');
                    $(this).find('option').each(function() {
                        var optionId = String(this.value || '');
                        $(this).prop('disabled', optionId !== '' && optionId !== current && selected.indexOf(optionId) !== -1);
                    });
                    $(this).trigger('change.select2');
                });
                renderAssignedDriverQuickAdd();
            }

            function addDriverRow(driverId) {
                var $row = $('<div class="waybill-driver-row border rounded-3 p-3 bg-light-subtle"><div class="row g-2 align-items-end"><div class="col-auto"><span class="badge bg-primary rounded-pill driver-sequence"></span></div><div class="col-md modern-field"><label>{{ __('Driver') }}</label><select class="form-select waybill-driver-select" required><option value="">-- Select Driver --</option></select></div><div class="col-md-3 modern-field"><label>{{ __('Planned time (minutes)') }}</label><input type="number" class="form-control driver-duration" min="1" step="1" placeholder="{{ __('Optional') }}"></div><div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm btn-remove-driver" title="{{ __('Remove driver') }}"><i class="ri-delete-bin-line"></i></button></div></div></div>');
                var $select = $row.find('.waybill-driver-select');
                @foreach ($drivers as $driver)
                    $select.append($('<option>').val(@js((string) $driver->id)).text(@js($driver->full_name)));
                @endforeach
                $('#waybill-driver-rows').append($row);
                initDriverSelect($select);
                if (driverId) $select.val(String(driverId));
                updateDriverRows();
            }

            function renderAssignedDriverQuickAdd() {
                var $area = $('#assigned-driver-quick-add').empty();
                var selected = $('#waybill-driver-rows .waybill-driver-select').map(function() { return String($(this).val() || ''); }).get();
                $.each(window.waybillAssignedDrivers || [], function(_, driver) {
                    if (driver.assigned && selected.indexOf(String(driver.id)) === -1) {
                        $('<button type="button" class="btn btn-sm btn-light border rounded-pill">')
                            .text('+ ' + driver.name).attr('data-driver-id', driver.id).appendTo($area);
                    }
                });
            }

            $(document).off('change.waybillDriverPlan', '.waybill-driver-select').on('change.waybillDriverPlan', '.waybill-driver-select', function() {
                if (!$(this).data('auto-filling')) $(this).data('auto-filled', false);
                updateDriverRows();
            });
            $(document).off('click.waybillDriverPlan', '#btn-add-driver').on('click.waybillDriverPlan', '#btn-add-driver', function() { addDriverRow(); });
            $(document).off('input.waybillDriverPlan', '.driver-duration').on('input.waybillDriverPlan', '.driver-duration', updateDriverTimeLimit);
            $(document).off('click.waybillDriverPlan', '.btn-remove-driver').on('click.waybillDriverPlan', '.btn-remove-driver', function() {
                var $select = $(this).closest('.waybill-driver-row').find('.waybill-driver-select');
                if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
                $(this).closest('.waybill-driver-row').remove();
                updateDriverRows();
            });
            $(document).off('click.waybillDriverPlan', '#assigned-driver-quick-add button').on('click.waybillDriverPlan', '#assigned-driver-quick-add button', function() {
                addDriverRow($(this).attr('data-driver-id'));
            });

            function loadVehicleDriverState() {
                var vehicleId = $('#vehicle_id').val();
                if (!vehicleId) {
                    window.waybillAssignedDrivers = [];
                    renderAssignedDriverQuickAdd();
                    return;
                }

                var getDriversUrl = "{{ route('waybill.vehicle_drivers', ['vehicleId' => ':id']) }}".replace(':id', vehicleId);
                $.get(getDriversUrl, function(drivers) {
                    window.waybillAssignedDrivers = (drivers || []).filter(function(driver) { return driver.assigned === true; });
                    var $first = $('#waybill-driver-rows .waybill-driver-select').first();
                    var isEmptyOrAutomatic = !$first.val() || $first.data('auto-filled') === true;
                    if (window.waybillAssignedDrivers.length > 0 && isEmptyOrAutomatic) {
                        $first.data('auto-filled', true).data('auto-filling', true).val(String(window.waybillAssignedDrivers[0].id)).trigger('change');
                        $first.data('auto-filling', false);
                    } else if (window.waybillAssignedDrivers.length === 0 && $first.data('auto-filled') === true) {
                        $first.data('auto-filled', false).data('auto-filling', true).val('').trigger('change');
                        $first.data('auto-filling', false);
                    }
                    updateDriverRows();
                });
            }

            $('#vehicle_id').off('change.waybillDriverPlan').on('change.waybillDriverPlan', loadVehicleDriverState);
            updateDriverRows();

            // If there's no modal wrapper (dedicated add/edit page), it's safe
            // to load immediately. Inside a modal, this runs from the
            // shown.bs.modal handler below instead — see loadInitialFormState().
            if (!$modal.length) {
                loadVehicleDriverState();
            }

            // -------------------------------------------------------------
            // Requirement 3 & 4: Geoapify Location Autocomplete Search
            // (now with a "Google-style" loading state in the dropdown)
            // -------------------------------------------------------------
            function setupGeoapifyAutocomplete($input, $dropdown, onSelectCallback) {
                var timer = null;
                var activeRequest = null;

                $input.on('input', function() {
                    var text = $(this).val().trim();
                    clearTimeout(timer);

                    if (activeRequest && activeRequest.abort) {
                        activeRequest.abort();
                        activeRequest = null;
                    }

                    if (text.length < 2) {
                        $dropdown.hide().empty();
                        return;
                    }

                    // Show a realistic loading state immediately while the user is still typing
                    $dropdown.html(
                        '<div class="geoapify-suggestion-loading d-flex align-items-center">' +
                        '<span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>' +
                        '<span>Searching locations...</span>' +
                        '</div>'
                    ).show();

                    timer = setTimeout(function() {
                        activeRequest = $.get("{{ route('geoapify.autocomplete') }}", { text: text }, function(res) {
                            if (res.success && res.results && res.results.length > 0) {
                                var html = '';
                                $.each(res.results, function(idx, item) {
                                    html += '<div class="geoapify-suggestion-item" data-json=\'' + JSON.stringify(item).replace(/'/g, "&#39;") + '\'>';
                                    html += '<div class="fw-medium text-dark"><i class="ri-map-pin-2-fill text-primary me-1"></i>' + item.name + '</div>';
                                    html += '<small class="text-muted" style="font-size: 0.75rem;">' + item.formatted + '</small>';
                                    html += '</div>';
                                });
                                $dropdown.html(html).show();
                            } else {
                                $dropdown.html('<div class="geoapify-suggestion-empty"><i class="ri-search-line me-1"></i>No locations found</div>').show();
                            }
                        }).fail(function(jqXHR, textStatus) {
                            if (textStatus === 'abort') return;
                            $dropdown.hide().empty();
                        });
                    }, 300);
                });

                $dropdown.on('click', '.geoapify-suggestion-item', function() {
                    var item = $(this).data('json');
                    $input.val(item.name || item.formatted);
                    $dropdown.hide().empty();
                    onSelectCallback(item);
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest($input).length && !$(e.target).closest($dropdown).length) {
                        $dropdown.hide();
                    }
                });
            }

            // Origin Autocomplete
            setupGeoapifyAutocomplete($('#origin'), $('#origin-suggestions'), function(item) {
                $('#origin_lat').val(item.lat);
                $('#origin_lng').val(item.lng);
                recalculateRouteAndMap();
            });

            // Destination Autocomplete
            setupGeoapifyAutocomplete($('#destination'), $('#destination-suggestions'), function(item) {
                $('#destination_lat').val(item.lat);
                $('#destination_lng').val(item.lng);
                recalculateRouteAndMap();
            });

            // Checkpoint Autocomplete
            setupGeoapifyAutocomplete($('#checkpoint_search'), $('#checkpoint-suggestions'), function(item) {
                pendingCheckpointObj = {
                    name: item.name || item.formatted,
                    lat: item.lat,
                    lng: item.lng
                };
            });

            // Add Checkpoint Button Handler
            $('#add-checkpoint-btn').on('click', function() {
                var searchVal = $('#checkpoint_search').val().trim();
                if (!searchVal) return;

                var cpObj = pendingCheckpointObj;
                if (!cpObj || cpObj.name !== searchVal) {
                    // Quick fallback if user typed free text without selecting suggestion
                    $.get("{{ route('geoapify.autocomplete') }}", { text: searchVal }, function(res) {
                        if (res.success && res.results && res.results.length > 0) {
                            var topRes = res.results[0];
                            addCheckpoint({
                                name: searchVal,
                                lat: topRes.lat,
                                lng: topRes.lng
                            });
                        } else {
                            alert("Location coordinates not found for '" + searchVal + "'. Please select a valid location from the search dropdown.");
                        }
                    });
                } else {
                    addCheckpoint(cpObj);
                }
            });

            function addCheckpoint(cp) {
                checkpointsList.push({
                    name: cp.name,
                    lat: cp.lat,
                    lng: cp.lng,
                    sequence: checkpointsList.length + 1
                });

                $('#checkpoint_search').val('');
                pendingCheckpointObj = null;
                renderCheckpointsUI();
                recalculateRouteAndMap();
            }

            // Render Checkpoints Sequence List UI
            function renderCheckpointsUI() {
                var $container = $('#checkpoints-list-container');
                var $list = $('#checkpoints-list');
                $list.empty();

                if (checkpointsList.length === 0) {
                    $container.hide();
                    $('#checkpoints_input').val('[]');
                    return;
                }

                // Update sequences
                $.each(checkpointsList, function(i, cp) {
                    cp.sequence = i + 1;
                });

                $('#checkpoints_input').val(JSON.stringify(checkpointsList));

                $.each(checkpointsList, function(idx, cp) {
                    var li = '<li class="list-group-item checkpoint-item d-flex justify-content-between align-items-center py-2 px-3">';
                    li += '<div class="d-flex align-items-center gap-2">';
                    li += '<span class="badge bg-primary rounded-pill px-2.5 py-1 fw-bold">CP ' + cp.sequence + '</span>';
                    li += '<span class="fw-medium text-dark small">' + cp.name + '</span>';
                    li += '</div>';

                    li += '<div class="d-flex align-items-center gap-1">';

                    if (idx > 0) {
                        li += '<button type="button" class="btn btn-sm btn-light p-1 move-cp-btn" data-idx="' + idx + '" data-dir="-1" title="Move Up">' +
                            '<i class="ri-arrow-up-s-line"></i>' +
                            '</button>';
                    }

                    if (idx < checkpointsList.length - 1) {
                        li += '<button type="button" class="btn btn-sm btn-light p-1 move-cp-btn" data-idx="' + idx + '" data-dir="1" title="Move Down">' +
                            '<i class="ri-arrow-down-s-line"></i>' +
                            '</button>';
                    }

                    li += '<button type="button" class="btn btn-sm btn-outline-danger p-1 remove-cp-btn ms-1" data-idx="' + idx + '" title="Remove">' +
                        '<i class="ri-delete-bin-line"></i>' +
                        '</button>';

                    li += '</div>';
                    li += '</div>';
                    li += '</li>';

                    $list.append(li);
                });

                $container.show();
            }

            // Move / Remove Checkpoint Handlers
            $('#checkpoints-list').on('click', '.remove-cp-btn', function() {
                var idx = $(this).data('idx');
                checkpointsList.splice(idx, 1);
                renderCheckpointsUI();
                recalculateRouteAndMap();
            });

            $('#checkpoints-list').on('click', '.move-cp-btn', function() {
                var idx = parseInt($(this).data('idx'));
                var dir = parseInt($(this).data('dir'));
                var targetIdx = idx + dir;

                if (targetIdx >= 0 && targetIdx < checkpointsList.length) {
                    var temp = checkpointsList[idx];
                    checkpointsList[idx] = checkpointsList[targetIdx];
                    checkpointsList[targetIdx] = temp;
                    renderCheckpointsUI();
                    recalculateRouteAndMap();
                }
            });

            // -------------------------------------------------------------
            // Requirement 3 & 4: Geoapify Routing API & MapLibre GL JS Real-time Map
            // -------------------------------------------------------------
            function recalculateRouteAndMap() {
                var originText = $('#origin').val().trim();
                var originLat = parseFloat($('#origin_lat').val());
                var originLng = parseFloat($('#origin_lng').val());

                var destText = $('#destination').val().trim();
                var destLat = parseFloat($('#destination_lat').val());
                var destLng = parseFloat($('#destination_lng').val());

                if (!originText || !destText || isNaN(originLat) || isNaN(destLat) || isNaN(originLng) || isNaN(destLng)) {
                    // Missing endpoints - clear or draw individual markers if available
                    updateMapDisplay(null, checkpointsList);
                    return;
                }

                var waypoints = [];
                waypoints.push({ name: originText, lat: originLat, lng: originLng });

                $.each(checkpointsList, function(i, cp) {
                    waypoints.push({ name: cp.name, lat: cp.lat, lng: cp.lng });
                });

                waypoints.push({ name: destText, lat: destLat, lng: destLng });

                $('#map-skeleton-loader').removeClass('d-none');

                $.ajax({
                    url: "{{ route('geoapify.routing') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        waypoints: waypoints
                    },
                    success: function(res) {
                        $('#map-skeleton-loader').addClass('d-none');
                        if (res.success) {
                            $('#distance').val(res.distance_km);
                            $('#estimated_duration').val(res.estimated_duration);
                            $('#route_geometry_input').val(JSON.stringify(res.route_coordinates || []));
                            $('#route-distance-badge').html('<i class="ri-route-line me-1"></i>' + res.distance_km + ' KM');

                            updateMapDisplay(res, waypoints);
                        } else {
                            $('#route-map-alert').removeClass('alert-info').addClass('alert-warning').show();
                            $('#route-map-alert-msg').text(res.message || 'Unable to calculate Geoapify route.');
                        }
                    },
                    error: function() {
                        $('#map-skeleton-loader').addClass('d-none');
                    }
                });
            }

            function updateMapDisplay(routeRes, waypoints) {
                if (typeof maplibregl === 'undefined') {
                    console.error('MapLibre GL JS library not loaded yet');
                    // Retry after a delay
                    setTimeout(function() {
                        updateMapDisplay(routeRes, waypoints);
                    }, 500);
                    return;
                }

                // Check if container exists and is visible
                var mapContainer = document.getElementById('waybill-route-map');
                if (!mapContainer) {
                    console.error('Map container not found in DOM');
                    return;
                }

                if (!waybillMap) {
                    try {
                        console.log('Initializing MapLibre map...');
                        waybillMap = new maplibregl.Map({
                            container: 'waybill-route-map',
                            style: {
                                version: 8,
                                sources: {
                                    'osm-tiles': {
                                        type: 'raster',
                                        tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                                        tileSize: 256,
                                        attribution: '&copy; OpenStreetMap contributors'
                                    }
                                },
                                layers: [{
                                    id: 'osm-tiles-layer',
                                    type: 'raster',
                                    source: 'osm-tiles',
                                    minzoom: 0,
                                    maxzoom: 19
                                }]
                            },
                            center: [69.3451, 30.3753],
                            zoom: 5
                        });

                        waybillMap.addControl(new maplibregl.NavigationControl(), 'top-right');
                        waybillMap.addControl(new maplibregl.ScaleControl(), 'bottom-left');

                        mapLayerGroup = []; // Store markers for cleanup

                        // Wait for map to fully load before continuing
                        waybillMap.on('load', function() {
                            console.log('MapLibre map loaded successfully');
                        });

                        console.log('MapLibre map initialized successfully');
                    } catch (e) {
                        console.error('MapLibre GL JS init failed:', e);
                        $('#route-map-alert').removeClass('alert-info').addClass('alert-danger').show();
                        $('#route-map-alert-msg').text('Failed to initialize map: ' + e.message);
                        return;
                    }
                }

                // Clear existing markers
                if (mapLayerGroup && mapLayerGroup.length > 0) {
                    mapLayerGroup.forEach(function(marker) {
                        marker.remove();
                    });
                    mapLayerGroup = [];
                }

                // Remove existing route layer and source if present
                if (waybillMap.getLayer('route-line-shadow')) waybillMap.removeLayer('route-line-shadow');
                if (waybillMap.getLayer('route-line-main')) waybillMap.removeLayer('route-line-main');
                if (waybillMap.getLayer('route-line-highlight')) waybillMap.removeLayer('route-line-highlight');
                if (waybillMap.getSource('route')) waybillMap.removeSource('route');

                var boundsCoordinates = [];

                // Render Route Line Geometry
                if (routeRes && routeRes.route_coordinates && routeRes.route_coordinates.length > 0) {
                    // Convert [lat, lng] to [lng, lat] for GeoJSON
                    var geojsonCoords = routeRes.route_coordinates.map(function(coord) {
                        return [coord[1], coord[0]]; // [lng, lat]
                    });

                    waybillMap.addSource('route', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            geometry: {
                                type: 'LineString',
                                coordinates: geojsonCoords
                            }
                        }
                    });

                    // Shadow layer
                    waybillMap.addLayer({
                        id: 'route-line-shadow',
                        type: 'line',
                        source: 'route',
                        paint: {
                            'line-color': '#1e3a8a',
                            'line-width': 8,
                            'line-opacity': 0.35
                        }
                    });

                    // Main route layer
                    waybillMap.addLayer({
                        id: 'route-line-main',
                        type: 'line',
                        source: 'route',
                        paint: {
                            'line-color': '#2563eb',
                            'line-width': 5,
                            'line-opacity': 0.95
                        }
                    });

                    // Highlight layer
                    waybillMap.addLayer({
                        id: 'route-line-highlight',
                        type: 'line',
                        source: 'route',
                        paint: {
                            'line-color': '#93c5fd',
                            'line-width': 2,
                            'line-opacity': 0.9,
                            'line-dasharray': [2, 2]
                        }
                    });

                    boundsCoordinates = geojsonCoords;
                }

                // Render Markers: Origin (Green), Destination (Red), Checkpoints (Numbered Blue)
                var originLat = parseFloat($('#origin_lat').val());
                var originLng = parseFloat($('#origin_lng').val());
                var originName = $('#origin').val().trim() || 'Origin';

                if (!isNaN(originLat) && !isNaN(originLng)) {
                    // Create Origin marker container with label
                    var originContainer = document.createElement('div');
                    originContainer.className = 'marker-origin-container';

                    var originEl = document.createElement('div');
                    originEl.className = 'marker-origin';
                    originEl.innerHTML = '&#128640;';

                    var originLabel = document.createElement('div');
                    originLabel.className = 'marker-origin-label';
                    originLabel.textContent = 'START';

                    originContainer.appendChild(originEl);
                    originContainer.appendChild(originLabel);

                    var originMarker = new maplibregl.Marker({ element: originContainer, anchor: 'bottom' })
                        .setLngLat([originLng, originLat])
                        .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<b>🚀 START (Origin):</b><br/>' + originName))
                        .addTo(waybillMap);

                    mapLayerGroup.push(originMarker);
                    boundsCoordinates.push([originLng, originLat]);
                }

                $.each(checkpointsList, function(idx, cp) {
                    if (!isNaN(cp.lat) && !isNaN(cp.lng)) {
                        // Create Checkpoint marker container with label
                        var cpContainer = document.createElement('div');
                        cpContainer.className = 'marker-checkpoint-container';

                        var cpEl = document.createElement('div');
                        cpEl.className = 'marker-checkpoint';
                        cpEl.textContent = (idx + 1);

                        var cpLabel = document.createElement('div');
                        cpLabel.className = 'marker-checkpoint-label';
                        cpLabel.textContent = 'CHECKPOINT ' + (idx + 1);

                        cpContainer.appendChild(cpEl);
                        cpContainer.appendChild(cpLabel);

                        var cpMarker = new maplibregl.Marker({ element: cpContainer, anchor: 'bottom' })
                            .setLngLat([cp.lng, cp.lat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<b>📍 CHECKPOINT ' + (idx + 1) + ':</b><br/>' + cp.name))
                            .addTo(waybillMap);

                        mapLayerGroup.push(cpMarker);
                        boundsCoordinates.push([cp.lng, cp.lat]);
                    }
                });

                var destLat = parseFloat($('#destination_lat').val());
                var destLng = parseFloat($('#destination_lng').val());
                var destName = $('#destination').val().trim() || 'Destination';

                if (!isNaN(destLat) && !isNaN(destLng)) {
                    // Create Destination marker container with label
                    var destContainer = document.createElement('div');
                    destContainer.className = 'marker-destination-container';

                    var destEl = document.createElement('div');
                    destEl.className = 'marker-destination';
                    destEl.innerHTML = '&#127937;';

                    var destLabel = document.createElement('div');
                    destLabel.className = 'marker-destination-label';
                    destLabel.textContent = 'END';

                    destContainer.appendChild(destEl);
                    destContainer.appendChild(destLabel);

                    var destMarker = new maplibregl.Marker({ element: destContainer, anchor: 'bottom' })
                        .setLngLat([destLng, destLat])
                        .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML('<b>🏁 END (Destination):</b><br/>' + destName))
                        .addTo(waybillMap);

                    mapLayerGroup.push(destMarker);
                    boundsCoordinates.push([destLng, destLat]);
                }

                // Render Checkpoints Flow Timeline Bar
                if (boundsCoordinates.length >= 2) {
                    var flowHtml = '';

                    // START (Origin)
                    flowHtml += '<div class="d-flex flex-column align-items-center">';
                    flowHtml += '<span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 12px;">';
                    flowHtml += '<i class="ri-rocket-2-fill me-1"></i>START';
                    flowHtml += '</span>';
                    flowHtml += '<small class="text-success fw-semibold mt-1" style="font-size: 10px;">' + originName + '</small>';
                    flowHtml += '</div>';

                    // Checkpoints
                    $.each(checkpointsList, function(i, cp) {
                        flowHtml += '<i class="ri-arrow-right-line text-primary fs-4 mx-1"></i>';
                        flowHtml += '<div class="d-flex flex-column align-items-center">';
                        flowHtml += '<span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 11px;">';
                        flowHtml += '<i class="ri-map-pin-2-fill me-1"></i>CP ' + (i + 1);
                        flowHtml += '</span>';
                        flowHtml += '<small class="text-primary fw-semibold mt-1" style="font-size: 10px;">' + cp.name + '</small>';
                        flowHtml += '</div>';
                    });

                    // END (Destination)
                    flowHtml += '<i class="ri-arrow-right-line text-primary fs-4 mx-1"></i>';
                    flowHtml += '<div class="d-flex flex-column align-items-center">';
                    flowHtml += '<span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 12px;">';
                    flowHtml += '<i class="ri-flag-fill me-1"></i>END';
                    flowHtml += '</span>';
                    flowHtml += '<small class="text-danger fw-semibold mt-1" style="font-size: 10px;">' + destName + '</small>';
                    flowHtml += '</div>';

                    $('#checkpoints-flow').html(flowHtml);
                    $('#checkpoints-flow-wrapper').show();

                    // Fit bounds to show all markers and route
                    var bounds = boundsCoordinates.reduce(function(bounds, coord) {
                        return bounds.extend(coord);
                    }, new maplibregl.LngLatBounds(boundsCoordinates[0], boundsCoordinates[0]));

                    waybillMap.fitBounds(bounds, {
                        padding: { top: 50, bottom: 50, left: 50, right: 50 },
                        maxZoom: 14
                    });
                } else if (boundsCoordinates.length === 1) {
                    waybillMap.setCenter(boundsCoordinates[0]);
                    waybillMap.setZoom(12);
                    $('#checkpoints-flow-wrapper').hide();
                } else {
                    $('#checkpoints-flow-wrapper').hide();
                }
            }

            // -------------------------------------------------------------
            // Fix: MapLibre (and Select2's driver dropdown refresh) cannot
            // measure a hidden element. Since this same partial renders for
            // both "Add" and "Edit" inside a Bootstrap modal, the map
            // container is still display:none at document.ready time, so
            // the map/markers were initializing against a 0x0 box and never
            // showing the origin/destination/checkpoints on edit + reload.
            // We now (re)build everything after the modal has finished its
            // shown transition, when the container has real dimensions.
            // -------------------------------------------------------------
            function loadInitialMapState() {
                // Wait for MapLibre to be loaded before initializing the map
                waitForMapLibre(function() {
                    var hasOrigin = $('#origin_lat').val() && $('#destination_lat').val();
                    var hasRouteGeometry = $('#route_geometry_input').val();

                    console.log('Loading initial map state', {
                        hasOrigin: hasOrigin,
                        hasRouteGeometry: hasRouteGeometry ? 'yes' : 'no',
                        checkpointsCount: checkpointsList.length
                    });

                    if (hasOrigin) {
                        // Give the DOM a moment to render the container with proper dimensions
                        setTimeout(function() {
                            // If we have existing route geometry, use it directly without API call
                            if (hasRouteGeometry && hasRouteGeometry !== '[]') {
                                try {
                                    var savedGeometry = JSON.parse(hasRouteGeometry);
                                    var distance = $('#distance').val();
                                    var duration = $('#estimated_duration').val();

                                    if (savedGeometry && savedGeometry.length > 0) {
                                        console.log('Using saved route geometry', {
                                            points: savedGeometry.length,
                                            distance: distance,
                                            duration: duration
                                        });

                                        // Build waypoints array
                                        var waypoints = [];
                                        var originText = $('#origin').val();
                                        var originLat = parseFloat($('#origin_lat').val());
                                        var originLng = parseFloat($('#origin_lng').val());
                                        waypoints.push({ name: originText, lat: originLat, lng: originLng });

                                        $.each(checkpointsList, function(i, cp) {
                                            waypoints.push({ name: cp.name, lat: cp.lat, lng: cp.lng });
                                        });

                                        var destText = $('#destination').val();
                                        var destLat = parseFloat($('#destination_lat').val());
                                        var destLng = parseFloat($('#destination_lng').val());
                                        waypoints.push({ name: destText, lat: destLat, lng: destLng });

                                        // Create a fake response object with saved data
                                        var savedRouteData = {
                                            success: true,
                                            distance_km: distance || '0',
                                            estimated_duration: duration || 'N/A',
                                            route_coordinates: savedGeometry
                                        };

                                        // Update distance badge
                                        if (distance) {
                                            $('#route-distance-badge').html('<i class="ri-route-line me-1"></i>' + distance + ' KM');
                                        }

                                        // Draw the map with saved data
                                        updateMapDisplay(savedRouteData, waypoints);
                                        return;
                                    }
                                } catch (e) {
                                    console.error('Failed to parse saved route geometry:', e);
                                }
                            }

                            // No saved geometry or parsing failed, recalculate from API
                            console.log('Recalculating route from API');
                            recalculateRouteAndMap();
                        }, 100);
                    } else {
                        // Even without route data, initialize the empty map
                        setTimeout(function() {
                            console.log('Initializing empty map');
                            updateMapDisplay(null, checkpointsList);
                        }, 100);
                    }
                });
            }

            function loadInitialFormState() {
                loadVehicleDriverState();
                loadInitialMapState();
            }

            if (!$modal.length) {
                // Dedicated add/edit page (no modal wrapper) — container is
                // already visible, so load immediately.
                setTimeout(function() {
                    loadInitialMapState();
                }, 200); // Small delay to ensure DOM is fully rendered
            } else if ($modal.hasClass('show')) {
                // Edge case: script re-injected while the modal is already open.
                setTimeout(loadInitialFormState, 300);
            }

            // Namespaced so re-injecting this partial into the same modal
            // (switching between Add and Edit) doesn't stack duplicate handlers.
            $modal.off('shown.bs.modal.waybillForm').on('shown.bs.modal.waybillForm', function() {
                setTimeout(function() {
                    if (waybillMap) {
                        waybillMap.resize();
                    }
                    loadInitialFormState();
                }, 300); // Increased delay for modal animation
            });
        });
    </script>
@endpush
