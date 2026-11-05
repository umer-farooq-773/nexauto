<style>
.select2-container--default .select2-dropdown {
    max-width: 100% !important;
    box-sizing: border-box !important;
}
.select2-container--default .select2-selection--single {
    height: 58px !important;
    padding-top: 17px !important;
    border-radius: 0.375rem !important;
    background-color: #fff !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 58px !important;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: rgb(117, 117, 117);
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #838383 !important;
    line-height: 28px;
}

.select2-container{
    font-weight: 400 !important;
    width: stretch !important;
}

/* Category Radio Cards */
.m-cat-card {
    cursor: pointer;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 6px;
    text-align: center;
    transition: all 0.2s ease;
    user-select: none;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.m-cat-card input {
    display: none;
}
.m-cat-card .m-cat-icon {
    font-size: 1.5rem;
    line-height: 1;
}
.m-cat-card .m-cat-name {
    font-size: 0.78rem;
    font-weight: 700;
    color: #475569;
}
.m-cat-card:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.m-cat-card:has(input:checked) {
    background: #eff6ff;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}
.m-cat-card:has(input:checked) .m-cat-name {
    color: #1d4ed8;
    font-weight: 800;
}

/* Inline Item Creator Box */
.item-creator-box {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    transition: all 0.3s ease;
}
.item-creator-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: #ffffff;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 12px 18px;
}
</style>

<x-auth.form form-id="MaintenanceForm" form-action="{{ $route }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        {{-- Vehicle Selector --}}
        <div class="col-md-6 form-floating mb-3">
            <select name="vehicle_id" id="vehicle_id" class="form-select shadow-sm searchable-select" required>
                <option value="">-- Select Vehicle --</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}"
                        {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->fleet_no }} - {{ $vehicle->truck_no }}
                    </option>
                @endforeach
            </select>
            <label for="vehicle_id">{{ __('Select Vehicle') }} *</label>
        </div>

        @php
        $selectedMaintenanceTypeIds = old('maintenance_type_ids', []);
        if ($isEdit && isset($result) && $result->maintenanceTypes) {
            $selectedMaintenanceTypeIds = $result->maintenanceTypes->pluck('id')->toArray();
        }
        @endphp

        {{-- Maintenance Types Multi-Select --}}
        <div class="col-md-6 mb-3">
            <x-auth.multi-select
                name="maintenance_type_ids"
                id="maintenance_type_ids"
                placeholder="{{ __('Select Maintenance Types') }}"
                :loop-data="$maintenanceTypes"
                :existing-id="$selectedMaintenanceTypeIds"
                selectclass="shadow-sm"
            />
            @error('maintenance_type_ids')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Maintenance Date --}}
        <div class="col-md-6 mb-3">
            <x-auth.input-field type="date" name="maintenance_date" id="maintenance_date" required="true"
                place="" val="{{ $result?->maintenance_date ? ($result->maintenance_date instanceof \DateTime ? $result->maintenance_date->format('Y-m-d') : $result->maintenance_date) : old('maintenance_date', date('Y-m-d')) }}" extraclasses="" label="{{ __('Maintenance Date') }} *" />
        </div>

        {{-- Expected Completion Date --}}
        <div class="col-md-6 mb-3">
            <x-auth.input-field type="date" name="expected_completion_date" id="expected_completion_date" required="false"
                place="" val="{{ $result?->expected_completion_date ? ($result->expected_completion_date instanceof \DateTime ? $result->expected_completion_date->format('Y-m-d') : $result->expected_completion_date) : old('expected_completion_date') }}" extraclasses="" label="{{ __('Expected Completion Date') }}" />
        </div>

        {{-- Odometer --}}
        <div class="col-md-6 mb-3">
            <x-auth.input-field type="text" name="odometer" id="odometer" required="false"
                place="{{ __('e.g. 245,820 km') }}" val="{{ $result?->odometer ?? old('odometer') }}" extraclasses="" label="{{ __('Current Odometer (km)') }}" />
        </div>

        {{-- Next Maintenance Date --}}
        <div class="col-md-6 mb-3">
            <x-auth.input-field type="date" name="next_maintenance_date" id="next_maintenance_date" required="false"
                place="" val="{{ $result?->next_maintenance_date ? ($result->next_maintenance_date instanceof \DateTime ? $result->next_maintenance_date->format('Y-m-d') : $result->next_maintenance_date) : old('next_maintenance_date') }}" extraclasses="" label="{{ __('Next Maintenance Date') }}" />
        </div>

        {{-- Service Provider --}}
        <div class="col-md-6 mb-3">
            <x-auth.input-field type="text" name="service_provider" id="service_provider" required="false"
                place="{{ __('e.g. ABC Workshop / AutoFix') }}" val="{{ $result?->service_provider ?? old('service_provider') }}"
                extraclasses="" label="{{ __('Service Provider / Workshop') }}" />
        </div>

        {{-- Priority --}}
        <div class="col-md-6 form-floating mb-3">
            <select name="priority" id="priority" class="form-select shadow-sm">
                @php $curPriority = $result?->priority ?? old('priority', 'Routine'); @endphp
                <option value="Routine" {{ $curPriority == 'Routine' ? 'selected' : '' }}>Routine</option>
                <option value="Scheduled" {{ $curPriority == 'Scheduled' ? 'selected' : '' }}>Scheduled Maintenance</option>
                <option value="Medium" {{ $curPriority == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Urgent" {{ $curPriority == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="Emergency" {{ $curPriority == 'Emergency' ? 'selected' : '' }}>Emergency</option>
            </select>
            <label for="priority">{{ __('Priority') }}</label>
        </div>

        {{-- Estimated Cost --}}
        <div class="col-md-6 mb-3">
            <x-auth.input-field type="number" step="0.01" name="cost" id="cost" required="false"
                place="{{ __('0.00') }}" val="{{ $result?->cost ?? old('cost') }}" extraclasses="" label="{{ __('Estimated Cost') }}" />
        </div>

        {{-- Status --}}
        <div class="col-md-6 form-floating mb-3">
            @php
                $isClientApproved = $result?->client_approved ?? 0;
                $isStatusEditable = $isClientApproved == 1;
            @endphp

            @if ($isEdit && !$isStatusEditable)
                <input type="hidden" name="_incident_old_status" value="{{ $result?->status ?? '0' }}">
                <input type="hidden" name="status" value="{{ $result?->status ?? '0' }}">
                <select name="status_display" id="status" class="form-select shadow-sm" disabled>
                    <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Pending</option>
                    <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>In Progress</option>
                    <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Completed</option>
                </select>
                <div style="background: #fef8e7; border: 1px solid #fa983a; border-radius: 6px; padding: 10px 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; margin-top:15px">
                    <i class="fas fa-info-circle" style="color: #e67e22; font-size: 18px; flex-shrink: 0;"></i>
                    <div style="flex: 1;">
                        <span style="color: #d35400; font-weight: 600; font-size: 13px; display: block;">
                            <i class="fas fa-user-check me-1"></i> Approval Required
                        </span>
                        <span style="color: #7f6000; font-size: 12px;">
                            Client director approval pending · Status changes are disabled
                        </span>
                    </div>
                    <span style="background: #e67e22; color: white; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">
                        Locked
                    </span>
                </div>
            @else
                <input type="hidden" name="_incident_old_status" value="{{ $result?->status ?? '2' }}">
                <select name="status" id="status" class="form-select shadow-sm"
                    {{ $isEdit && $isStatusEditable ? '' : ($isEdit ? 'disabled' : '') }}>
                    <option value="2" {{ ($result?->status ?? old('status', '2')) == '2' ? 'selected' : '' }}>Open / In Progress</option>
                    <option value="0" {{ ($result?->status ?? old('status')) == '0' ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Completed</option>
                </select>
            @endif
            <label for="status">{{ __('Status') }}</label>
        </div>

        {{-- Uploaded Files in Edit Mode --}}
        @if($isEdit && !empty($result) && $result->maintenanceFiles && $result->maintenanceFiles->count())
        <div class="col-md-12 mb-3">
            <label class="form-label text-muted small ms-1">{{ __('Uploaded Files') }}</label>
            <div class="d-flex flex-wrap gap-2">
                @foreach($result->maintenanceFiles as $file)
                    <div class="d-flex align-items-center gap-2 border rounded px-3 py-2 bg-light">
                        <i class="ri-file-line text-primary"></i>
                        <span class="small text-truncate" style="max-width:180px">{{ $file->name }}</span>
                        <a href="{{ Storage::url($file->path) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="ri-eye-line"></i>
                        </a>
                        <a href="{{ Storage::url($file->path) }}"
                           download="{{ $file->name }}"
                           class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="ri-download-line"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Initial Photo Upload --}}
        <div class="col-md-12 mb-3">
            <div class="form-group">
                <label for="maintenance_files" class="form-label mb-1 text-muted small ms-1">
                    {{ __('Upload Initial Photos / Images') }}
                    <span class="text-muted">(Images only: JPG, PNG, WEBP — max 5MB each)</span>
                </label>
                <input type="file"
                       class="form-control shadow-sm bg-white"
                       id="maintenance_files"
                       name="maintenance_files[]"
                       accept="image/jpeg,image/png,image/webp,image/jpg"
                       multiple>
                @error('maintenance_files')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('maintenance_files.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Description / Notes --}}
        <div class="col-md-12 mb-3">
            <x-auth.text-area name="description" id="description" required="false"
                place="{{ __('Full service and inspection description...') }}" val="{{ $result?->description ?? old('description') }}"
                extraclasses="" label="{{ __('Description / Service Notes') }}" />
        </div>

        {{-- =========================================================================
             SECTION: DYNAMIC MAINTENANCE ITEMS & COMPONENTS (INLINE CREATOR PANEL)
             ========================================================================= --}}
        <div class="col-12 mb-4">
            <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-light py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                            <i class="ri-tools-line fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">{{ __('Maintenance Items & Component Records') }}</h6>
                            <small class="text-muted">{{ __('Add tyres, batteries, telematics, brakes, or engine parts serviced/replaced') }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary fw-bold font-monospace px-3 py-1" id="formItemsCountBadge">
                            0 Items Added
                        </span>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" id="btnToggleItemCreator" onclick="toggleItemCreatorPanel(true)">
                            <i class="ri-add-circle-line"></i>
                            <span id="btnToggleItemCreatorText">{{ __('+ Add Maintenance Item') }}</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">

                    {{-- INLINE ITEM CREATOR & EDITOR PANEL --}}
                    <div id="itemCreatorPanel" class="item-creator-box mb-4 d-none">
                        <div class="item-creator-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-tools-fill fs-5" id="creatorHeaderIcon"></i>
                                <h6 class="mb-0 fw-bold" id="creatorPanelTitle">{{ __('Add Maintenance Item / Component') }}</h6>
                            </div>
                            <button type="button" class="btn btn-sm btn-close btn-close-white" onclick="toggleItemCreatorPanel(false)" aria-label="Close"></button>
                        </div>

                        <div class="p-3 p-md-4">
                            <input type="hidden" id="creator_item_edit_index" value="-1">
                            <input type="hidden" id="creator_item_existing_id" value="">

                            {{-- Step 1: Category Picker --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="ri-function-line text-primary me-1"></i> {{ __('Component Category') }} <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    <div class="col-4 col-md-2">
                                        <label class="m-cat-card">
                                            <input type="radio" name="creator_category" value="tyre" checked onchange="switchCreatorCategory('tyre')">
                                            <div class="m-cat-icon text-primary"><i class="ri-disc-line"></i></div>
                                            <div class="m-cat-name">Tyre</div>
                                        </label>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="m-cat-card">
                                            <input type="radio" name="creator_category" value="battery" onchange="switchCreatorCategory('battery')">
                                            <div class="m-cat-icon text-warning"><i class="ri-battery-2-charge-line"></i></div>
                                            <div class="m-cat-name">Battery</div>
                                        </label>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="m-cat-card">
                                            <input type="radio" name="creator_category" value="telematics" onchange="switchCreatorCategory('telematics')">
                                            <div class="m-cat-icon text-info"><i class="ri-radar-line"></i></div>
                                            <div class="m-cat-name">Telematics</div>
                                        </label>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="m-cat-card">
                                            <input type="radio" name="creator_category" value="brake" onchange="switchCreatorCategory('brake')">
                                            <div class="m-cat-icon text-danger"><i class="ri-stop-circle-line"></i></div>
                                            <div class="m-cat-name">Brakes</div>
                                        </label>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="m-cat-card">
                                            <input type="radio" name="creator_category" value="engine" onchange="switchCreatorCategory('engine')">
                                            <div class="m-cat-icon text-secondary"><i class="ri-settings-4-line"></i></div>
                                            <div class="m-cat-name">Engine</div>
                                        </label>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="m-cat-card">
                                            <input type="radio" name="creator_category" value="general" onchange="switchCreatorCategory('general')">
                                            <div class="m-cat-icon text-dark"><i class="ri-tools-line"></i></div>
                                            <div class="m-cat-name">General</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="opacity-10 my-3">

                            {{-- Category Specific Input Containers --}}
                            <div id="creator-category-fields">
                                {{-- 1. TYRE FIELDS --}}
                                <div class="creator-cat-section" id="creator-fields-tyre">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select id="creator_tyre_action" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="rotate">Rotate</option>
                                                <option value="repair">Repair</option>
                                                <option value="inspect">Inspect / Pressure Check</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Axle') }} <span class="text-danger">*</span></label>
                                            <select id="creator_tyre_axle" class="form-select">
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
                                            <select id="creator_tyre_position" class="form-select">
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
                                            <input type="text" id="creator_tyre_size" class="form-control" placeholder="e.g. 315/80 R22.5">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Brand') }}</label>
                                            <select id="creator_tyre_brand" class="form-select">
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
                                            <input type="text" id="creator_tyre_model" class="form-control" placeholder="e.g. X Multi D">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Tyre ID / Serial') }}</label>
                                            <input type="text" id="creator_tyre_old_serial" class="form-control font-monospace" placeholder="e.g. TYR-000457">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Tyre ID / Serial') }}</label>
                                            <input type="text" id="creator_tyre_new_serial" class="form-control font-monospace" placeholder="e.g. TYR-000892">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Tread Depth (mm)') }}</label>
                                            <input type="number" step="0.1" id="creator_tyre_tread" class="form-control font-monospace" placeholder="e.g. 10.5 mm" max="1000">
                                            <small class="text-muted">Typical new: 8-12mm</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Reason for Replacement') }}</label>
                                            <select id="creator_tyre_reason" class="form-select">
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

                                {{-- 2. BATTERY FIELDS --}}
                                <div class="creator-cat-section d-none" id="creator-fields-battery">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Battery Position') }} <span class="text-danger">*</span></label>
                                            <select id="creator_battery_location" class="form-select">
                                                <option value="Main Battery 1" selected>Main Battery 1</option>
                                                <option value="Main Battery 2">Main Battery 2</option>
                                                <option value="Auxiliary Battery">Auxiliary Battery</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select id="creator_battery_action" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="recharge">Recharge</option>
                                                <option value="inspect">Test / Inspect</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Battery Serial Number') }}</label>
                                            <input type="text" id="creator_battery_old_serial" class="form-control font-monospace" placeholder="e.g. BAT-00124">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Battery Serial Number') }}</label>
                                            <input type="text" id="creator_battery_new_serial" class="form-control font-monospace" placeholder="e.g. BAT-00876">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Manufacturer / Brand') }}</label>
                                            <input type="text" id="creator_battery_brand" class="form-control" placeholder="e.g. Exide, Bosch, Varta">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Battery Rating / Voltage') }}</label>
                                            <input type="text" id="creator_battery_size" class="form-control" placeholder="e.g. 12V 180Ah / 24V">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <select id="creator_battery_reason" class="form-select">
                                                <option value="Failed" selected>Failed</option>
                                                <option value="Weak / Low Voltage">Weak / Low Voltage</option>
                                                <option value="Dead Cell">Dead Cell</option>
                                                <option value="Scheduled Replacement">Scheduled Replacement</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. TELEMATICS FIELDS --}}
                                <div class="creator-cat-section d-none" id="creator-fields-telematics">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Equipment Type') }} <span class="text-danger">*</span></label>
                                            <select id="creator_telematics_item_name" class="form-select">
                                                <option value="GPS Tracker" selected>GPS Tracker</option>
                                                <option value="Fuel Level Sensor (FLS)">Fuel Level Sensor (FLS)</option>
                                                <option value="Dashcam / Camera">Dashcam / Camera</option>
                                                <option value="Temperature Sensor">Temperature Sensor</option>
                                                <option value="Panic Button">Panic Button</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select id="creator_telematics_action" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="install">Install New</option>
                                                <option value="calibrate">Calibrate</option>
                                                <option value="repair">Repair / Check</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Location on Vehicle') }}</label>
                                            <select id="creator_telematics_location" class="form-select">
                                                <option value="Cab / Dashboard" selected>Cab / Dashboard</option>
                                                <option value="Fuel Tank Left">Fuel Tank Left</option>
                                                <option value="Fuel Tank Right">Fuel Tank Right</option>
                                                <option value="Windshield">Windshield</option>
                                                <option value="Cargo Area">Cargo Area</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Serial / IMEI Number') }}</label>
                                            <input type="text" id="creator_telematics_old_serial" class="form-control font-monospace" placeholder="e.g. 863XXXXXXXXXXXX">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Serial / IMEI Number') }}</label>
                                            <input type="text" id="creator_telematics_new_serial" class="form-control font-monospace" placeholder="e.g. 863YYYYYYYYYYYY">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Manufacturer') }}</label>
                                            <input type="text" id="creator_telematics_brand" class="form-control" placeholder="e.g. Teltonika, Ruptela">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Model') }}</label>
                                            <input type="text" id="creator_telematics_model" class="form-control" placeholder="e.g. FMC920, FMB125">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <select id="creator_telematics_reason" class="form-select">
                                                <option value="Faulty" selected>Faulty</option>
                                                <option value="Hardware Upgrade">Hardware Upgrade</option>
                                                <option value="Missing / Tampered">Missing / Tampered</option>
                                                <option value="Periodic Calibration">Periodic Calibration</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- 4. BRAKES / ENGINE / GENERAL FIELDS --}}
                                <div class="creator-cat-section d-none" id="creator-fields-general">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Component Name / Part') }} <span class="text-danger">*</span></label>
                                            <input type="text" id="creator_general_item_name" class="form-control" placeholder="e.g. Brake Pads, Oil Filter, Alternator">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Action') }} <span class="text-danger">*</span></label>
                                            <select id="creator_general_action" class="form-select">
                                                <option value="replace" selected>Replace</option>
                                                <option value="service">Service / Flush</option>
                                                <option value="repair">Repair</option>
                                                <option value="inspect">Inspect</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Location / Position') }}</label>
                                            <input type="text" id="creator_general_location" class="form-control" placeholder="e.g. Front Axle, Engine Bay">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Brand / Manufacturer') }}</label>
                                            <input type="text" id="creator_general_brand" class="form-control" placeholder="e.g. Bosch, Knorr-Bremse">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('Old Part / Serial Number') }}</label>
                                            <input type="text" id="creator_general_old_serial" class="form-control font-monospace" placeholder="e.g. PN-10293">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ __('New Part / Serial Number') }}</label>
                                            <input type="text" id="creator_general_new_serial" class="form-control font-monospace" placeholder="e.g. PN-994820">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">{{ __('Reason') }}</label>
                                            <input type="text" id="creator_general_reason" class="form-control" placeholder="e.g. Worn down, Leaking, Scheduled">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="opacity-10 my-3">

                            {{-- Common Fields: Quantity, Unit Cost, Total Cost, Photo, Notes --}}
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-stack-line text-secondary me-1"></i> {{ __('Quantity') }}</label>
                                    <input type="number" step="1" min="1" id="creator_common_quantity" class="form-control font-monospace" value="1" placeholder="1" oninput="calculateCreatorCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-price-tag-2-line text-primary me-1"></i> {{ __('Unit Cost') }}</label>
                                    <input type="number" step="0.01" min="0" id="creator_common_unit_cost" class="form-control font-monospace" placeholder="0.00" oninput="calculateCreatorCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold"><i class="ri-money-dollar-circle-line text-success me-1"></i> {{ __('Total Cost') }}</label>
                                    <input type="number" step="0.01" min="0" id="creator_common_cost" class="form-control font-monospace" placeholder="0.00">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold"><i class="ri-attachment-line text-primary me-1"></i> {{ __('Photo / Document') }}</label>
                                    <input type="file" id="creator_common_photo" class="form-control" accept="image/*,.pdf,.doc,.docx">
                                    <small class="text-muted" id="creatorPhotoHelp">{{ __('Optional attachment (max 10MB)') }}</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Notes / Remarks') }}</label>
                                    <input type="text" id="creator_common_notes" class="form-control" placeholder="{{ __('Optional remarks or technician notes...') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                                <button type="button" class="btn btn-light rounded-pill px-4" onclick="toggleItemCreatorPanel(false)">{{ __('Cancel') }}</button>
                                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSaveCreatorItem" onclick="saveCreatorItem()">
                                    <i class="ri-check-line me-1"></i> <span id="btnSaveCreatorItemText">{{ __('Add Item to Record') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div id="formItemsEmptyState" class="text-center py-4 px-3 bg-light rounded-4 border border-dashed">
                        <div class="text-muted mb-2"><i class="ri-disc-line" style="font-size: 2.4rem; opacity: 0.6;"></i></div>
                        <h6 class="fw-bold text-dark mb-1">{{ __('No maintenance items added to this record yet') }}</h6>
                        <p class="text-muted small mb-3">{{ __('Add tyre replacements, battery changes, GPS/FLS telematics, brakes, or engine components.') }}</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" onclick="toggleItemCreatorPanel(true)">
                            <i class="ri-add-line me-1"></i> {{ __('+ Add First Item') }}
                        </button>
                    </div>

                    {{-- Table of added items --}}
                    <div id="formItemsTableContainer" class="table-responsive rounded-3 border d-none">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;" id="formItemsTable">
                            <thead class="bg-light text-dark fw-bold">
                                <tr>
                                    <th style="width: 35px;">#</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Location / Axle') }}</th>
                                    <th>{{ __('Item / Spec') }}</th>
                                    <th>{{ __('Old Serial') }}</th>
                                    <th>{{ __('New Serial') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Cost') }}</th>
                                    <th class="text-center">{{ __('Photo') }}</th>
                                    <th class="text-center" style="width: 80px;">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="formItemsTableBody">
                                {{-- Populated dynamically via JS --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Hidden container for item form inputs --}}
                    <div id="formItemsHiddenInputs"></div>
                    <div id="formDeletedItemsInputs"></div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="col-12 text-center mt-2 mb-3">
            <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow"
                btn-type="submit" btn-value="{{ $isEdit ? __('Update Maintenance Record') : __('Create Maintenance Record') }}" />
        </div>
    </div>
</x-auth.form>

@push('scripts')
<script>
    // In-memory items list
    let maintenanceFormItems = [];
    let deletedFormItemIds = [];
    let itemPhotoFiles = {}; // Maps item index to File object

    // Preload existing items if in edit mode
    @if(isset($result) && $result->items && $result->items->count())
        @foreach($result->items as $exItem)
            maintenanceFormItems.push({
                id: "{{ $exItem->id }}",
                category: "{{ $exItem->category }}",
                action: "{{ $exItem->action }}",
                location: "{{ $exItem->location }}",
                axle: "{{ $exItem->axle }}",
                position: "{{ $exItem->position }}",
                item_name: "{{ $exItem->item_name }}",
                brand: "{{ $exItem->brand }}",
                model: "{{ $exItem->model }}",
                size: "{{ $exItem->size }}",
                old_serial_no: "{{ $exItem->old_serial_no }}",
                new_serial_no: "{{ $exItem->new_serial_no }}",
                tread_depth_mm: "{{ $exItem->tread_depth_mm }}",
                reason: "{{ $exItem->reason }}",
                quantity: "{{ $exItem->quantity ?: 1 }}",
                unit_cost: "{{ $exItem->unit_cost }}",
                cost: "{{ $exItem->cost }}",
                notes: "{{ $exItem->notes }}",
                photo_url: "{{ $exItem->photo_url }}"
            });
        @endforeach
    @endif

    $(document).ready(function() {
        var modalParent = $('#vehicle_id').closest('.modal');
        $('#vehicle_id').select2({
            placeholder: "Select Vehicle",
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        // Initial render of items
        renderFormItemsTable();
    });

    function calculateCreatorCost() {
        const qty = parseFloat($('#creator_common_quantity').val()) || 1;
        const unitCost = parseFloat($('#creator_common_unit_cost').val());
        if (!isNaN(unitCost)) {
            $('#creator_common_cost').val((qty * unitCost).toFixed(2));
        }
    }

    function toggleItemCreatorPanel(show = true, editIndex = -1) {
        const panel = $('#itemCreatorPanel');
        if (show) {
            setupCreatorForm(editIndex);
            panel.removeClass('d-none');
            panel[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            $('#btnToggleItemCreator').addClass('d-none');
        } else {
            panel.addClass('d-none');
            $('#btnToggleItemCreator').removeClass('d-none');
            $('#creator_item_edit_index').val('-1');
            $('#creator_item_existing_id').val('');
        }
    }

    function switchCreatorCategory(cat) {
        $('.creator-cat-section').addClass('d-none');

        if (cat === 'tyre') {
            $('#creator-fields-tyre').removeClass('d-none');
            $('#creatorHeaderIcon').attr('class', 'ri-disc-line');
        } else if (cat === 'battery') {
            $('#creator-fields-battery').removeClass('d-none');
            $('#creatorHeaderIcon').attr('class', 'ri-battery-2-charge-line');
        } else if (cat === 'telematics') {
            $('#creator-fields-telematics').removeClass('d-none');
            $('#creatorHeaderIcon').attr('class', 'ri-radar-line');
        } else {
            $('#creator-fields-general').removeClass('d-none');
            $('#creatorHeaderIcon').attr('class', 'ri-tools-line');
            if (cat === 'brake') {
                if (!$('#creator_general_item_name').val()) $('#creator_general_item_name').val('Brake Pads');
            } else if (cat === 'engine') {
                if (!$('#creator_general_item_name').val()) $('#creator_general_item_name').val('Engine Oil / Filter');
            }
        }
    }

    function setupCreatorForm(editIndex = -1) {
        $('#creator_item_edit_index').val(editIndex);

        if (editIndex >= 0 && maintenanceFormItems[editIndex]) {
            // Edit existing item
            const item = maintenanceFormItems[editIndex];
            $('#creator_item_existing_id').val(item.id || '');
            $('#creatorPanelTitle').text("{{ __('Edit Maintenance Item') }}");
            $('#btnSaveCreatorItemText').text("{{ __('Update Item in Record') }}");

            const cat = item.category || 'tyre';
            $(`input[name="creator_category"][value="${cat}"]`).prop('checked', true);
            switchCreatorCategory(cat);

            $('#creator_common_quantity').val(item.quantity || 1);
            $('#creator_common_unit_cost').val((item.unit_cost !== null && item.unit_cost !== undefined) ? item.unit_cost : '');
            $('#creator_common_cost').val(item.cost || '');
            $('#creator_common_notes').val(item.notes || '');
            $('#creator_common_photo').val('');
            if (item.photo_url) {
                $('#creatorPhotoHelp').text("{{ __('Existing file present. Choose new file to replace.') }}");
            } else {
                $('#creatorPhotoHelp').text("{{ __('Optional attachment (max 10MB)') }}");
            }

            if (cat === 'tyre') {
                if (item.action) $('#creator_tyre_action').val(item.action);
                if (item.axle) $('#creator_tyre_axle').val(item.axle);
                if (item.position) $('#creator_tyre_position').val(item.position);
                $('#creator_tyre_size').val(item.size || '');
                if (item.brand) $('#creator_tyre_brand').val(item.brand);
                $('#creator_tyre_model').val(item.model || '');
                $('#creator_tyre_old_serial').val(item.old_serial_no || '');
                $('#creator_tyre_new_serial').val(item.new_serial_no || '');
                $('#creator_tyre_tread').val(item.tread_depth_mm || '');
                if (item.reason) $('#creator_tyre_reason').val(item.reason);
            } else if (cat === 'battery') {
                if (item.location) $('#creator_battery_location').val(item.location);
                if (item.action) $('#creator_battery_action').val(item.action);
                $('#creator_battery_old_serial').val(item.old_serial_no || '');
                $('#creator_battery_new_serial').val(item.new_serial_no || '');
                $('#creator_battery_brand').val(item.brand || '');
                $('#creator_battery_size').val(item.size || '');
                if (item.reason) $('#creator_battery_reason').val(item.reason);
            } else if (cat === 'telematics') {
                if (item.item_name) $('#creator_telematics_item_name').val(item.item_name);
                if (item.action) $('#creator_telematics_action').val(item.action);
                if (item.location) $('#creator_telematics_location').val(item.location);
                $('#creator_telematics_old_serial').val(item.old_serial_no || '');
                $('#creator_telematics_new_serial').val(item.new_serial_no || '');
                $('#creator_telematics_brand').val(item.brand || '');
                $('#creator_telematics_model').val(item.model || '');
                if (item.reason) $('#creator_telematics_reason').val(item.reason);
            } else {
                $('#creator_general_item_name').val(item.item_name || '');
                if (item.action) $('#creator_general_action').val(item.action);
                $('#creator_general_location').val(item.location || '');
                $('#creator_general_brand').val(item.brand || '');
                $('#creator_general_old_serial').val(item.old_serial_no || '');
                $('#creator_general_new_serial').val(item.new_serial_no || '');
                $('#creator_general_reason').val(item.reason || '');
            }
        } else {
            // New item
            $('#creator_item_existing_id').val('');
            $('#creatorPanelTitle').text("{{ __('Add Maintenance Item / Component') }}");
            $('#btnSaveCreatorItemText').text("{{ __('Add Item to Record') }}");

            $('input[name="creator_category"][value="tyre"]').prop('checked', true);
            switchCreatorCategory('tyre');

            $('#creator_common_quantity').val(1);
            $('#creator_common_unit_cost').val('');
            $('#creator_common_cost').val('');
            $('#creator_common_notes').val('');
            $('#creator_common_photo').val('');
            $('#creatorPhotoHelp').text("{{ __('Optional attachment (max 10MB)') }}");

            $('#creator_tyre_size').val('');
            $('#creator_tyre_model').val('');
            $('#creator_tyre_old_serial').val('');
            $('#creator_tyre_new_serial').val('');
            $('#creator_tyre_tread').val('');

            $('#creator_battery_old_serial').val('');
            $('#creator_battery_new_serial').val('');
            $('#creator_battery_brand').val('');
            $('#creator_battery_size').val('');

            $('#creator_telematics_old_serial').val('');
            $('#creator_telematics_new_serial').val('');
            $('#creator_telematics_brand').val('');
            $('#creator_telematics_model').val('');

            $('#creator_general_item_name').val('');
            $('#creator_general_location').val('');
            $('#creator_general_brand').val('');
            $('#creator_general_old_serial').val('');
            $('#creator_general_new_serial').val('');
            $('#creator_general_reason').val('');
        }
    }

    function saveCreatorItem() {
        const editIndex = parseInt($('#creator_item_edit_index').val());
        const existingId = $('#creator_item_existing_id').val();
        const category = $('input[name="creator_category"]:checked').val() || 'tyre';
        const quantity = parseInt($('#creator_common_quantity').val()) || 1;
        const unitCost = $('#creator_common_unit_cost').val();
        const cost = $('#creator_common_cost').val();
        const notes = $('#creator_common_notes').val();
        const photoInput = document.getElementById('creator_common_photo');

        let itemData = {
            id: existingId || '',
            category: category,
            quantity: quantity,
            unit_cost: unitCost,
            cost: cost,
            notes: notes,
            photo_url: editIndex >= 0 && maintenanceFormItems[editIndex] ? maintenanceFormItems[editIndex].photo_url : null
        };

        if (category === 'tyre') {
            itemData.action = $('#creator_tyre_action').val();
            itemData.axle = $('#creator_tyre_axle').val();
            itemData.position = $('#creator_tyre_position').val();
            itemData.location = itemData.axle + ' – ' + itemData.position;
            itemData.size = $('#creator_tyre_size').val();
            itemData.brand = $('#creator_tyre_brand').val();
            itemData.model = $('#creator_tyre_model').val();
            itemData.item_name = 'Tyre (' + (itemData.brand || '') + ' ' + (itemData.size || '') + ')';
            itemData.old_serial_no = $('#creator_tyre_old_serial').val();
            itemData.new_serial_no = $('#creator_tyre_new_serial').val();
            itemData.tread_depth_mm = $('#creator_tyre_tread').val();
            itemData.reason = $('#creator_tyre_reason').val();
        } else if (category === 'battery') {
            itemData.location = $('#creator_battery_location').val();
            itemData.action = $('#creator_battery_action').val();
            itemData.item_name = 'Battery';
            itemData.old_serial_no = $('#creator_battery_old_serial').val();
            itemData.new_serial_no = $('#creator_battery_new_serial').val();
            itemData.brand = $('#creator_battery_brand').val();
            itemData.size = $('#creator_battery_size').val();
            itemData.reason = $('#creator_battery_reason').val();
        } else if (category === 'telematics') {
            itemData.item_name = $('#creator_telematics_item_name').val();
            itemData.action = $('#creator_telematics_action').val();
            itemData.location = $('#creator_telematics_location').val();
            itemData.old_serial_no = $('#creator_telematics_old_serial').val();
            itemData.new_serial_no = $('#creator_telematics_new_serial').val();
            itemData.brand = $('#creator_telematics_brand').val();
            itemData.model = $('#creator_telematics_model').val();
            itemData.reason = $('#creator_telematics_reason').val();
        } else {
            itemData.item_name = $('#creator_general_item_name').val() || (category.charAt(0).toUpperCase() + category.slice(1));
            itemData.action = $('#creator_general_action').val();
            itemData.location = $('#creator_general_location').val();
            itemData.brand = $('#creator_general_brand').val();
            itemData.old_serial_no = $('#creator_general_old_serial').val();
            itemData.new_serial_no = $('#creator_general_new_serial').val();
            itemData.reason = $('#creator_general_reason').val();
        }

        let targetIndex = editIndex;
        if (editIndex >= 0) {
            maintenanceFormItems[editIndex] = itemData;
        } else {
            targetIndex = maintenanceFormItems.length;
            maintenanceFormItems.push(itemData);
        }

        // Store selected photo file if any
        if (photoInput && photoInput.files && photoInput.files.length > 0) {
            itemPhotoFiles[targetIndex] = photoInput.files[0];
            itemData.has_new_photo = true;
        }

        toggleItemCreatorPanel(false);
        renderFormItemsTable();
    }

    function removeFormItem(index) {
        if (!confirm("{{ __('Remove this item from the record?') }}")) return;

        const item = maintenanceFormItems[index];
        if (item && item.id) {
            deletedFormItemIds.push(item.id);
        }

        maintenanceFormItems.splice(index, 1);
        delete itemPhotoFiles[index];

        // Re-index photos
        let newPhotoFiles = {};
        maintenanceFormItems.forEach((_, idx) => {
            if (itemPhotoFiles[idx]) newPhotoFiles[idx] = itemPhotoFiles[idx];
        });
        itemPhotoFiles = newPhotoFiles;

        renderFormItemsTable();
    }

    function renderFormItemsTable() {
        const tbody = $('#formItemsTableBody');
        tbody.empty();

        const hiddenContainer = $('#formItemsHiddenInputs');
        hiddenContainer.empty();

        const deletedContainer = $('#formDeletedItemsInputs');
        deletedContainer.empty();

        // Render deleted item IDs
        deletedFormItemIds.forEach(id => {
            deletedContainer.append(`<input type="hidden" name="deleted_item_ids[]" value="${id}">`);
        });

        $('#formItemsCountBadge').text(`${maintenanceFormItems.length} Items Added`);

        if (maintenanceFormItems.length === 0) {
            $('#formItemsEmptyState').removeClass('d-none');
            $('#formItemsTableContainer').addClass('d-none');
            return;
        }

        $('#formItemsEmptyState').addClass('d-none');
        $('#formItemsTableContainer').removeClass('d-none');

        let totalItemsCost = 0;

        maintenanceFormItems.forEach((item, index) => {
            if (item.cost) totalItemsCost += parseFloat(item.cost) || 0;

            const categoryBadge = getCategoryBadgeHtml(item.category);
            const actionBadge = getActionBadgeHtml(item.action);
            const specText = (item.brand || '') + ' ' + (item.size || item.model || item.item_name || '');
            const oldSerial = item.old_serial_no ? `<span class="badge bg-danger-subtle text-danger border font-monospace">${item.old_serial_no}</span>` : '–';
            const newSerial = item.new_serial_no ? `<span class="badge bg-success-subtle text-success border font-monospace">${item.new_serial_no}</span>` : '–';
            const costFormatted = item.cost ? parseFloat(item.cost).toFixed(2) : '–';
            const unitCostBreakdown = item.unit_cost ? `<div class="text-muted small" style="font-size: 0.75rem;">(${item.quantity || 1} &times; ${parseFloat(item.unit_cost).toFixed(2)})</div>` : '';
            const photoIndicator = (itemPhotoFiles[index] || item.photo_url)
                ? `<i class="ri-image-line text-success fs-6" title="File attached"></i>`
                : `<span class="text-muted">–</span>`;

            const row = `
                <tr>
                    <td class="fw-bold text-muted">${index + 1}</td>
                    <td>${categoryBadge}</td>
                    <td>${actionBadge}</td>
                    <td class="fw-semibold text-dark">${item.location || item.axle || '–'}</td>
                    <td>
                        <span class="fw-bold text-dark">${specText.trim() || item.item_name}</span>
                        ${item.tread_depth_mm ? `<span class="badge bg-secondary-subtle text-secondary ms-1 font-monospace">${item.tread_depth_mm}mm</span>` : ''}
                    </td>
                    <td>${oldSerial}</td>
                    <td>${newSerial}</td>
                    <td>${item.reason || '–'}</td>
                    <td class="font-monospace text-primary fw-semibold">${costFormatted}${unitCostBreakdown}</td>
                    <td class="text-center">${photoIndicator}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="toggleItemCreatorPanel(true, ${index})" title="Edit Item">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeFormItem(${index})" title="Remove Item">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);

            // Append hidden form inputs
            hiddenContainer.append(`
                <input type="hidden" name="items[${index}][id]" value="${item.id || ''}">
                <input type="hidden" name="items[${index}][category]" value="${item.category || ''}">
                <input type="hidden" name="items[${index}][action]" value="${item.action || ''}">
                <input type="hidden" name="items[${index}][location]" value="${item.location || ''}">
                <input type="hidden" name="items[${index}][axle]" value="${item.axle || ''}">
                <input type="hidden" name="items[${index}][position]" value="${item.position || ''}">
                <input type="hidden" name="items[${index}][item_name]" value="${item.item_name || ''}">
                <input type="hidden" name="items[${index}][brand]" value="${item.brand || ''}">
                <input type="hidden" name="items[${index}][model]" value="${item.model || ''}">
                <input type="hidden" name="items[${index}][size]" value="${item.size || ''}">
                <input type="hidden" name="items[${index}][old_serial_no]" value="${item.old_serial_no || ''}">
                <input type="hidden" name="items[${index}][new_serial_no]" value="${item.new_serial_no || ''}">
                <input type="hidden" name="items[${index}][tread_depth_mm]" value="${item.tread_depth_mm || ''}">
                <input type="hidden" name="items[${index}][reason]" value="${item.reason || ''}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity || 1}">
                <input type="hidden" name="items[${index}][unit_cost]" value="${item.unit_cost || ''}">
                <input type="hidden" name="items[${index}][cost]" value="${item.cost || ''}">
                <input type="hidden" name="items[${index}][notes]" value="${item.notes || ''}">
            `);

            // Append file input if new photo was uploaded
            if (itemPhotoFiles[index]) {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = `item_photos[${index}]`;
                fileInput.style.display = 'none';
                const dt = new DataTransfer();
                dt.items.add(itemPhotoFiles[index]);
                fileInput.files = dt.files;
                hiddenContainer[0].appendChild(fileInput);
            }
        });

        // Auto-suggest cost in the main Estimated Cost field if empty
        const costField = $('#cost');
        if (totalItemsCost > 0 && (!costField.val() || parseFloat(costField.val()) === 0)) {
            costField.val(totalItemsCost.toFixed(2));
        }
    }

    function getCategoryBadgeHtml(cat) {
        switch ((cat || '').toLowerCase()) {
            case 'tyre':
                return '<span class="badge bg-primary text-white"><i class="ri-disc-line me-1"></i> Tyre</span>';
            case 'battery':
                return '<span class="badge bg-warning text-dark"><i class="ri-battery-2-charge-line me-1"></i> Battery</span>';
            case 'telematics':
                return '<span class="badge bg-info text-white"><i class="ri-radar-line me-1"></i> Telematics</span>';
            case 'brake':
                return '<span class="badge bg-danger text-white"><i class="ri-stop-circle-line me-1"></i> Brake</span>';
            case 'engine':
                return '<span class="badge bg-secondary text-white"><i class="ri-settings-4-line me-1"></i> Engine</span>';
            default:
                return `<span class="badge bg-dark text-white"><i class="ri-tools-line me-1"></i> ${cat || 'General'}</span>`;
        }
    }

    function getActionBadgeHtml(act) {
        switch ((act || '').toLowerCase()) {
            case 'replace':
                return '<span class="badge bg-success-subtle text-success border border-success-subtle">Replaced</span>';
            case 'install':
                return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">Installed</span>';
            case 'rotate':
                return '<span class="badge bg-info-subtle text-info border border-info-subtle">Rotated</span>';
            case 'repair':
                return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Repaired</span>';
            case 'inspect':
                return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inspected</span>';
            case 'calibrate':
                return '<span class="badge bg-purple-subtle text-dark border border-purple-subtle">Calibrated</span>';
            default:
                return `<span class="badge bg-light text-dark border">${act || 'Service'}</span>`;
        }
    }
</script>
@endpush
