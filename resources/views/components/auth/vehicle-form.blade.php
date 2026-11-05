<x-auth.form form-id="VehicleForm" form-action="{{$route}}">
    @if($isEdit)
        @method('PUT')
    @endif

    @php
        // Pre-compute "currently selected" values so the Blade comparisons
        // stay in one place and match the original select logic exactly.
        $currentVehicleTypeId   = old('vehicle_type', old('vehicle_type_id', $result?->vehicle_type_id ?? $result?->vehicle_type));
        $currentMakeModelId     = old('make_model', old('vehicle_make_model_id', $result?->vehicle_make_model_id ?? ''));
        $currentGpsDeviceTypeId = old('gps_device_type_id', \App\Models\GpsDeviceType::where('wialon_hw_type_id', $result?->gps_hw_type_id)->value('id'));
        $currentStatus          = old('status', $result?->status);
    @endphp

    <!-- Hidden GPS Provider -->
    <input type="hidden" name="gps_provider" value="wialon">

    <div class="row g-4">
        <!-- Vehicle Information Section -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary-soft me-3">
                            <i class="fas fa-truck text-primary"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Vehicle Information') }}</h5>
                        <div class="ms-auto">
                            <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2">Required</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="fleet_no" id="fleet_no" class="form-control shadow-sm @error('fleet_no') is-invalid @enderror" placeholder=" " value="{{ old('fleet_no', $result?->fleet_no) }}" required>
                                <label for="fleet_no">{{ __('Fleet No') }}</label>
                                @error('fleet_no')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="truck_no" id="truck_no" class="form-control shadow-sm @error('truck_no') is-invalid @enderror" placeholder=" " value="{{ old('truck_no', $result?->truck_no) }}" required>
                                <label for="truck_no">{{ __('Vehicle Registration Number') }}</label>
                                @error('truck_no')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Vehicle Type — Custom Dropdown -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3 custom-select @error('vehicle_type') is-invalid @enderror" data-field="vehicle_type">
                                <input type="hidden" name="vehicle_type" id="vehicle_type_input" value="{{ $currentVehicleTypeId }}">
                                <button type="button" class="custom-select-toggle form-control shadow-sm" id="vehicle_type_toggle" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="custom-select-value"></span>
                                </button>
                                <label for="vehicle_type_toggle">{{ __('Vehicle Type') }}</label>
                                <ul class="custom-select-menu" role="listbox" aria-labelledby="vehicle_type_toggle">
                                    <li class="custom-select-option is-placeholder" data-value="" role="option">-- Select Vehicle Type --</li>
                                    @foreach(\App\Models\VehicleType::where('status', 1)->orderBy('name')->get() as $type)
                                        <li class="custom-select-option {{ $currentVehicleTypeId == $type->id ? 'is-active' : '' }}" data-value="{{ $type->id }}" role="option">
                                            {{ $type->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @error('vehicle_type')
                                <div class="invalid-feedback d-block mb-3" style="margin-top: -10px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Make/Model — Custom Dropdown -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3 custom-select @error('make_model') is-invalid @enderror" data-field="make_model">
                                <input type="hidden" name="make_model" id="make_model_input" value="{{ $currentMakeModelId }}">
                                <button type="button" class="custom-select-toggle form-control shadow-sm" id="make_model_toggle" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="custom-select-value"></span>
                                </button>
                                <label for="make_model_toggle">{{ __('Make/Model') }}</label>
                                <ul class="custom-select-menu" role="listbox" aria-labelledby="make_model_toggle">
                                    <li class="custom-select-option is-placeholder" data-value="" role="option">-- Select Make/Model --</li>
                                    @foreach(\App\Models\VehicleMakeModel::orderBy('name')->get() as $model)
                                        <li class="custom-select-option {{ $currentMakeModelId == $model->id ? 'is-active' : '' }}" data-value="{{ $model->id }}" role="option">
                                            {{ $model->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @error('make_model')
                                <div class="invalid-feedback d-block mb-3" style="margin-top: -10px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="base_site" id="base_site" class="form-control shadow-sm @error('base_site') is-invalid @enderror" placeholder=" " value="{{ old('base_site', $result?->base_site) }}" required>
                                <label for="base_site">{{ __('Base / Site') }}</label>
                                @error('base_site')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status — Custom Dropdown -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3 custom-select @error('status') is-invalid @enderror" data-field="status">
                                <input type="hidden" name="status" id="status_input" value="{{ $currentStatus }}">
                                <button type="button" class="custom-select-toggle form-control shadow-sm" id="status_toggle" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="custom-select-value"></span>
                                </button>
                                <label for="status_toggle">{{ __('Status') }}</label>
                                <ul class="custom-select-menu" role="listbox" aria-labelledby="status_toggle">
                                    <li class="custom-select-option is-placeholder" data-value="" role="option">Select Status</li>
                                    <li class="custom-select-option {{ $currentStatus == '1' || $currentStatus === 1 ? 'is-active' : '' }}" data-value="1" role="option">{{ __('language.vehicle_status_1') }}</li>
                                    <li class="custom-select-option {{ $currentStatus == '2' || $currentStatus === 2 ? 'is-active' : '' }}" data-value="2" role="option">{{ __('language.vehicle_status_2') }}</li>
                                    <li class="custom-select-option {{ $currentStatus == '0' || $currentStatus === 0 ? 'is-active' : '' }}" data-value="0" role="option">{{ __('language.vehicle_status_0') }}</li>
                                </ul>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block mb-3" style="margin-top: -10px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Manager Section -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-success-soft me-3">
                            <i class="fas fa-user-tie text-success"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Asset Manager Details') }}</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="manager_first_name" id="manager_first_name" class="form-control shadow-sm @error('manager_first_name') is-invalid @enderror" placeholder=" " value="{{ old('manager_first_name', $result?->manager_first_name) }}" required>
                                <label for="manager_first_name">{{ __('First Name') }}</label>
                                @error('manager_first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="manager_last_name" id="manager_last_name" class="form-control shadow-sm @error('manager_last_name') is-invalid @enderror" placeholder=" " value="{{ old('manager_last_name', $result?->manager_last_name) }}" required>
                                <label for="manager_last_name">{{ __('Last Name') }}</label>
                                @error('manager_last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" name="manager_email" id="manager_email" class="form-control shadow-sm @error('manager_email') is-invalid @enderror" placeholder=" " value="{{ old('manager_email', $result?->manager_email) }}" required>
                                <label for="manager_email">{{ __('Email Address') }}</label>
                                @error('manager_email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="phone_number" id="phone_number" class="form-control shadow-sm @error('phone_number') is-invalid @enderror" placeholder=" " value="{{ old('phone_number', $result?->phone_number) }}">
                                <label for="phone_number">{{ __('Phone Number') }}</label>
                                @error('phone_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Format: +[country code][number]</small>
                            <div id="phone_number_error" class="invalid-feedback" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client/Owner Section -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-info-soft me-3">
                            <i class="fas fa-user-circle text-info"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Client / Asset Owner Details') }}</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="owner_first_name" id="owner_first_name" class="form-control shadow-sm @error('owner_first_name') is-invalid @enderror" placeholder=" " value="{{ old('owner_first_name', $result?->owner_first_name) }}" required>
                                <label for="owner_first_name">{{ __('First Name') }}</label>
                                @error('owner_first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" name="owner_last_name" id="owner_last_name" class="form-control shadow-sm @error('owner_last_name') is-invalid @enderror" placeholder=" " value="{{ old('owner_last_name', $result?->owner_last_name) }}" required>
                                <label for="owner_last_name">{{ __('Last Name') }}</label>
                                @error('owner_last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GPS & Tracking Section -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-warning-soft me-3">
                            <i class="fas fa-satellite-dish text-warning"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('GPS & Tracking') }}</h5>
                        @if($isEdit && $result?->gps_unit_id)
                            <div class="ms-auto">
                                <span class="badge bg-success-soft text-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Connected
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        <div class="col-md-6 gps-wialon-field">
                            <div class="form-floating mb-3">
                                <input type="text" name="gps_imei" id="gps_imei" class="form-control shadow-sm @error('gps_imei') is-invalid @enderror" placeholder=" " value="{{ old('gps_imei', $result?->gps_imei) }}">
                                <label for="gps_imei">{{ __('GPS IMEI') }}</label>
                                @error('gps_imei')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Must be exactly 15 digits</small>
                            <div id="gps_imei_error" class="invalid-feedback" style="display: none;"></div>
                        </div>

                        <!-- GPS Device — Custom Dropdown with Filter -->
                        <div class="col-md-6 gps-wialon-field">
                            <div class="form-floating mb-3 custom-select custom-select-with-filter @error('gps_device_type_id') is-invalid @enderror" data-field="gps_device_type_id">
                                <input type="hidden" name="gps_device_type_id" id="gps_device_type_id_input" value="{{ $currentGpsDeviceTypeId }}">
                                <button type="button" class="custom-select-toggle form-control shadow-sm" id="gps_device_type_id_toggle" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="custom-select-value"></span>
                                </button>
                                <label for="gps_device_type_id_toggle">{{ __('GPS Device') }}</label>
                                <div class="custom-select-menu" role="listbox" aria-labelledby="gps_device_type_id_toggle">
                                    <div class="custom-select-filter">
                                        <input type="text" class="custom-select-filter-input" placeholder="Search GPS Device..." autocomplete="off">
                                    </div>
                                    <ul class="custom-select-options-list">
                                        <li class="custom-select-option is-placeholder" data-value="" role="option">-- Select GPS Device --</li>
                                        @foreach(\App\Models\GpsDeviceType::where('status', 1)->orderBy('name')->get() as $device)
                                            <li class="custom-select-option {{ $currentGpsDeviceTypeId == $device->id ? 'is-active' : '' }}" data-value="{{ $device->id }}" role="option">
                                                {{ $device->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @error('gps_device_type_id')
                                <div class="invalid-feedback d-block mb-3" style="margin-top: -10px;">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($isEdit && $result?->gps_unit_id)
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control shadow-sm bg-light" id="gps_unit_id_display" value="{{ $result->gps_unit_id }}" disabled>
                                    <label for="gps_unit_id_display">{{ __('GPS Unit ID (Wialon)') }}</label>
                                </div>
                                <div class="small {{ $result->gps_sync_status === 'failed' ? 'text-danger' : 'text-success' }} ps-1">
                                    <i class="fas fa-sync me-1"></i>Sync status: <strong>{{ ucfirst($result->gps_sync_status ?? 'pending') }}</strong>
                                    @if($result->gps_sync_status === 'failed' && $result->gps_sync_error)
                                        — {{ $result->gps_sync_error }}
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($result?->last_sync_at)
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control shadow-sm bg-light" value="{{ dateFormat($result->last_sync_at, 'Y-m-d H:i') }}" disabled>
                                    <label>{{ __('Last Sync At') }}</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance & Documents Section -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-danger-soft me-3">
                            <i class="fas fa-tools text-danger"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">{{ __('Maintenance & Documents') }}</h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" name="last_service_date" id="last_service_date" class="form-control shadow-sm @error('last_service_date') is-invalid @enderror" placeholder=" " value="{{ old('last_service_date', dateFormat($result?->last_service_date, 'Y-m-d')) }}" required>
                                <label for="last_service_date">{{ __('Last Service Date') }}</label>
                                @error('last_service_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" name="next_service_due_date" id="next_service_due_date" class="form-control shadow-sm @error('next_service_due_date') is-invalid @enderror" placeholder=" " value="{{ old('next_service_due_date', dateFormat($result?->next_service_due_date, 'Y-m-d')) }}" required>
                                <label for="next_service_due_date">{{ __('Next Service Due') }}</label>
                                @error('next_service_due_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" name="insurance_expiry_date" id="insurance_expiry_date" class="form-control shadow-sm @error('insurance_expiry_date') is-invalid @enderror" placeholder=" " value="{{ old('insurance_expiry_date', dateFormat($result?->insurance_expiry_date, 'Y-m-d')) }}" required>
                                <label for="insurance_expiry_date">{{ __('Insurance Expiry Date') }}</label>
                                @error('insurance_expiry_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" name="vehicle_license_expiry_date" id="vehicle_license_expiry_date" class="form-control shadow-sm @error('vehicle_license_expiry_date') is-invalid @enderror" placeholder=" " value="{{ old('vehicle_license_expiry_date', dateFormat($result?->vehicle_license_expiry_date, 'Y-m-d')) }}" required>
                                <label for="vehicle_license_expiry_date">{{ __('Asset License Expiry') }}</label>
                                @error('vehicle_license_expiry_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-12">
            <div class="text-center mt-4">
                <x-auth.input-button btn-class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm" btn-type="submit" btn-value="{{ __('Submit') }}" />
            </div>
        </div>
    </div>
</x-auth.form>

@push('auth_scripts')
<style>
    /* Card Styling */
    .card {
        border: 1px solid #e9ecef !important;
        transition: all 0.3s ease;
        background: #ffffff;
    }
    .card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        border-color: #d0d5dc !important;
    }
    .rounded-4 {
        border-radius: 16px !important;
    }

    /* Icon Circles */
    .icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bg-primary-soft { background: #e8edfd; }
    .bg-success-soft { background: #e6f9ed; }
    .bg-info-soft { background: #e3f4fc; }
    .bg-warning-soft { background: #fef3e2; }
    .bg-danger-soft { background: #fde8e8; }

    .text-primary { color: #4a6cf7 !important; }
    .text-success { color: #28a745 !important; }
    .text-info { color: #17a2b8 !important; }
    .text-warning { color: #e67e22 !important; }
    .text-danger { color: #dc3545 !important; }

    /* Badges */
    .badge {
        font-weight: 500;
        font-size: 0.7rem;
        padding: 0.35rem 0.8rem !important;
    }
    .bg-primary-soft { background: #e8edfd; color: #4a6cf7; }
    .bg-success-soft { background: #e6f9ed; color: #28a745; }

    /* Form Controls - Base (text/date inputs) */
    .form-control {
        height: 48px !important;
        border-radius: 10px;
        padding: 0 16px !important;
        border: 1px solid #e2e8f0;
        background: #fafbfc;
        transition: all 0.3s ease;
        font-size: 0.95rem !important;
        line-height: 1.5 !important;
        color: #1a1a2e !important;
    }

    /* Fix for date inputs */
    input[type="date"].form-control {
        min-height: 48px !important;
        padding: 0 16px !important;
    }

    /* Floating Labels (text inputs) */
    .form-floating {
        position: relative;
    }

    .form-floating > .form-control {
        height: 48px !important;
        padding: 0 16px !important;
    }

    .form-floating > label {
        position: absolute;
        top: 0;
        left: 0;
        padding: 12px 16px !important;
        height: 48px !important;
        font-weight: 500;
        font-size: 0.9rem;
        color: #6c757d;
        pointer-events: none;
        border: none;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
        background: transparent;
        transform-origin: left center;
        z-index: 3;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        transform: scale(0.85) translateY(-12px) translateX(4px);
        color: #4a6cf7;
        background: #ffffff;
        padding: 0 8px !important;
        height: auto !important;
        font-weight: 600;
        z-index: 5;
    }

    .form-floating > .form-control:not(:placeholder-shown) {
        padding-top: 26px !important;
        padding-bottom: 6px !important;
        color: #1a1a2e;
    }

    .form-floating > .form-control::placeholder {
        color: transparent !important;
        opacity: 0 !important;
    }

    /* Fix for disabled inputs */
    .form-control:disabled, .form-control[disabled] {
        background: #f1f3f5 !important;
        cursor: not-allowed;
        opacity: 0.8;
        color: #495057 !important;
    }

    /* Focus states */
    .form-control:focus {
        border-color: #4a6cf7 !important;
        box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1) !important;
        background: #ffffff !important;
    }

    .form-control:hover {
        border-color: #c5cbd5;
    }

    /* ============================================================
       CUSTOM DROPDOWN COMPONENT - FIXED COLORS
       ============================================================ */
    .custom-select {
        position: relative;
    }

    .custom-select-toggle {
        width: 100%;
        height: 48px !important;
        padding: 26px 40px 6px 16px !important;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fafbfc !important; /* Same as form-control */
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        color: #6c757d;
        transition: all 0.3s ease;
        position: relative;
    }

    .custom-select-toggle:hover {
        border-color: #c5cbd5;
        background: #fafbfc !important;
    }

    .custom-select-toggle:focus,
    .custom-select-toggle:focus-visible {
        outline: none;
        border-color: #4a6cf7;
        box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
        background: #ffffff !important;
    }

    .custom-select-value {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #1a1a2e;
    }

    /* Caret icon */
    .custom-select-toggle::after {
        content: '';
        position: absolute;
        right: 16px;
        top: 50%;
        width: 12px;
        height: 12px;
        transform: translateY(-50%);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c757d' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-size: 12px;
        transition: transform 0.2s ease;
    }

    .custom-select.open .custom-select-toggle::after {
        transform: translateY(-50%) rotate(180deg);
    }

    /* Label: always floated — a custom toggle always displays visible
       text (placeholder or selection), same reasoning as native <select> */
    .custom-select > label {
        position: absolute;
        top: 0;
        left: 0;
        transform: scale(0.85) translateY(-12px) translateX(4px);
        color: #6c757d;
        background: #ffffff;
        padding: 0 8px !important;
        height: auto !important;
        font-weight: 600;
        font-size: 0.9rem;
        z-index: 5;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .custom-select.open > label,
    .custom-select.has-value > label {
        color: #4a6cf7;
    }

    .custom-select.has-value .custom-select-value {
        color: #1a1a2e;
    }

    .custom-select.open .custom-select-toggle {
        border-color: #4a6cf7;
        box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
        background: #ffffff !important;
    }

    /* Dropdown menu */
    .custom-select-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        margin: 0;
        padding: 0;
        list-style: none;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(20, 20, 43, 0.12);
        z-index: 1000;
        display: none;
    }

    .custom-select.open .custom-select-menu {
        display: block;
        animation: customSelectFadeIn 0.15s ease;
    }

    @keyframes customSelectFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Filter input container */
    .custom-select-filter {
        padding: 8px;
        border-bottom: 1px solid #e2e8f0;
        display: none;
    }

    .custom-select-with-filter .custom-select-filter {
        display: block;
    }

    .custom-select-filter-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.85rem;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .custom-select-filter-input:focus {
        border-color: #4a6cf7;
        box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.1);
    }

    /* Options list wrapper */
    .custom-select-options-list {
        list-style: none;
        margin: 0;
        padding: 6px;
        max-height: 200px;
        overflow-y: auto;
    }

    .custom-select-options-list::-webkit-scrollbar {
        width: 6px;
    }
    .custom-select-options-list::-webkit-scrollbar-thumb {
        background: #d0d5dc;
        border-radius: 6px;
    }

    .custom-select-option {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #1a1a2e;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .custom-select-option:hover,
    .custom-select-option.is-focused {
        background: #f2f5ff;
    }

    .custom-select-option.is-active {
        background: #eef1ff;
        color: #4a6cf7;
        font-weight: 600;
    }

    .custom-select-option.is-placeholder {
        color: #8a94a6;
    }

    .custom-select-option.is-hidden {
        display: none !important;
    }

    .custom-select-no-results {
        padding: 10px 12px;
        text-align: center;
        color: #8a94a6;
        font-size: 0.85rem;
        font-style: italic;
    }

    /* Validation */
    .invalid-feedback {
        font-size: 0.8rem;
        margin-top: 4px;
        display: none;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
    }
    .custom-select.is-invalid .custom-select-toggle {
        border-color: #dc3545 !important;
    }

    /* Text Muted */
    .text-muted {
        color: #8a94a6 !important;
        font-size: 0.8rem;
    }

    /* Section Header */
    .card-header {
        padding-bottom: 0 !important;
        background: transparent !important;
    }

    /* Submit Button */
    .btn-primary {
        background: #4a6cf7;
        border: 1px solid #4a6cf7;
        transition: all 0.3s ease;
        min-width: 200px;
    }
    .btn-primary:hover {
        background: #3a5bd9;
        border-color: #3a5bd9;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(74, 108, 247, 0.25);
    }

    /* Modal Body */
    .modal-body {
        background: #f8f9fa;
        padding: 24px !important;
    }

    /* Fix for small text spacing */
    .d-block {
        display: block !important;
    }
    .mt-1 {
        margin-top: 0.25rem !important;
    }

    /* Grid gap fix */
    .g-3 {
        --bs-gutter-y: 0.75rem;
    }

    /* Override for x-auth input components */
    .form-floating input.form-control {
        background-color: #fafbfc !important;
    }

    .form-floating input.form-control:focus {
        background-color: #ffffff !important;
    }
</style>

<script>
    (function () {
        var providerInput = document.querySelector('input[name="gps_provider"]');
        var wialonFields = document.querySelectorAll('.gps-wialon-field');
        var vehicleForm = document.getElementById('VehicleForm');
        var phoneNumberInput = document.getElementById('phone_number');
        var gpsImeiInput = document.getElementById('gps_imei');

        // GPS provider is a fixed hidden "wialon" field in this form,
        // so the Wialon-specific fields are always shown.
        function toggleWialonFields() {
            var show = !providerInput || providerInput.value === 'wialon';
            wialonFields.forEach(function (el) {
                el.style.display = show ? '' : 'none';
            });
        }
        toggleWialonFields();

        // Phone number validation
        if (phoneNumberInput) {
            phoneNumberInput.addEventListener('input', function () {
                var value = this.value.trim();
                var errorDiv = document.getElementById('phone_number_error');

                if (value === '') {
                    this.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                    return;
                }

                var phoneRegex = /^\+\d{10,15}$/;

                if (!phoneRegex.test(value)) {
                    this.classList.add('is-invalid');
                    errorDiv.textContent = 'Invalid phone format. Use: +[country code][number] (e.g., +923499088073)';
                    errorDiv.style.display = 'block';
                } else {
                    this.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                }
            });
        }

        // IMEI validation
        if (gpsImeiInput) {
            gpsImeiInput.addEventListener('input', function () {
                var value = this.value.trim();
                var errorDiv = document.getElementById('gps_imei_error');
                var isWialonProvider = !providerInput || providerInput.value === 'wialon';

                if (!isWialonProvider || value === '') {
                    this.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                    return;
                }

                var imeiRegex = /^\d{15}$/;

                if (!imeiRegex.test(value)) {
                    this.classList.add('is-invalid');
                    errorDiv.textContent = 'IMEI must be exactly 15 digits (no spaces or special characters)';
                    errorDiv.style.display = 'block';
                } else {
                    this.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                }
            });
        }

        // Form submission validation
        if (vehicleForm) {
            vehicleForm.addEventListener('submit', function (e) {
                var isValid = true;
                var isWialonProvider = !providerInput || providerInput.value === 'wialon';

                if (phoneNumberInput && phoneNumberInput.value.trim() !== '') {
                    var phoneRegex = /^\+\d{10,15}$/;
                    if (!phoneRegex.test(phoneNumberInput.value.trim())) {
                        phoneNumberInput.classList.add('is-invalid');
                        document.getElementById('phone_number_error').textContent = 'Invalid phone format. Use: +[country code][number]';
                        document.getElementById('phone_number_error').style.display = 'block';
                        isValid = false;
                    }
                }

                if (isWialonProvider && gpsImeiInput && gpsImeiInput.value.trim() !== '') {
                    var imeiRegex = /^\d{15}$/;
                    if (!imeiRegex.test(gpsImeiInput.value.trim())) {
                        gpsImeiInput.classList.add('is-invalid');
                        document.getElementById('gps_imei_error').textContent = 'IMEI must be exactly 15 digits';
                        document.getElementById('gps_imei_error').style.display = 'block';
                        isValid = false;
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    var firstInvalid = vehicleForm.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        if (firstInvalid.focus) firstInvalid.focus();
                    }
                }
            });
        }

        /* ============================================================
           CUSTOM DROPDOWN INITIALIZER
           ============================================================ */
        function initCustomSelects() {
            var wrappers = document.querySelectorAll('.custom-select');

            wrappers.forEach(function (wrapper) {
                var hiddenInput = wrapper.querySelector('input[type="hidden"]');
                var toggle = wrapper.querySelector('.custom-select-toggle');
                var valueSpan = wrapper.querySelector('.custom-select-value');
                var menu = wrapper.querySelector('.custom-select-menu');
                var optionsList = wrapper.querySelector('.custom-select-options-list') || menu;
                var options = Array.prototype.slice.call(wrapper.querySelectorAll('.custom-select-option'));
                var filterInput = wrapper.querySelector('.custom-select-filter-input');
                var hasFilter = wrapper.classList.contains('custom-select-with-filter');

                function closeMenu() {
                    wrapper.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                    if (filterInput) {
                        filterInput.value = '';
                        filterOptions('');
                    }
                }

                function openMenu() {
                    document.querySelectorAll('.custom-select.open').forEach(function (other) {
                        if (other !== wrapper && other._closeCustomSelect) {
                            other._closeCustomSelect();
                        }
                    });
                    wrapper.classList.add('open');
                    toggle.setAttribute('aria-expanded', 'true');
                    var active = wrapper.querySelector('.custom-select-option.is-active:not(.is-hidden)') || options[0];
                    if (active) active.scrollIntoView({ block: 'nearest' });
                    
                    // Focus filter input if available
                    if (filterInput) {
                        setTimeout(function() {
                            filterInput.focus();
                        }, 100);
                    }
                }

                wrapper._closeCustomSelect = closeMenu;

                function filterOptions(searchText) {
                    var searchLower = searchText.toLowerCase().trim();
                    var visibleCount = 0;
                    var noResultsMsg = wrapper.querySelector('.custom-select-no-results');

                    options.forEach(function (option) {
                        if (option.classList.contains('is-placeholder')) {
                            option.classList.remove('is-hidden');
                            return;
                        }

                        var text = option.textContent.toLowerCase().trim();
                        if (searchLower === '' || text.indexOf(searchLower) !== -1) {
                            option.classList.remove('is-hidden');
                            visibleCount++;
                        } else {
                            option.classList.add('is-hidden');
                        }
                    });

                    // Show/hide "no results" message
                    if (visibleCount === 0 && searchLower !== '') {
                        if (!noResultsMsg) {
                            noResultsMsg = document.createElement('li');
                            noResultsMsg.className = 'custom-select-no-results';
                            noResultsMsg.textContent = 'No matching devices found';
                            optionsList.appendChild(noResultsMsg);
                        } else {
                            noResultsMsg.style.display = 'block';
                        }
                    } else {
                        if (noResultsMsg) {
                            noResultsMsg.style.display = 'none';
                        }
                    }
                }

                function selectOption(li) {
                    options.forEach(function (o) {
                        o.classList.remove('is-active');
                        o.setAttribute('aria-selected', 'false');
                    });
                    li.classList.add('is-active');
                    li.setAttribute('aria-selected', 'true');

                    hiddenInput.value = li.getAttribute('data-value');
                    valueSpan.textContent = li.textContent.trim();

                    if (li.classList.contains('is-placeholder')) {
                        wrapper.classList.remove('has-value');
                    } else {
                        wrapper.classList.add('has-value');
                    }

                    wrapper.classList.remove('is-invalid');
                }

                // Initialize from any server-marked selection, else placeholder
                var preselected = wrapper.querySelector('.custom-select-option.is-active');
                var placeholder = wrapper.querySelector('.custom-select-option.is-placeholder');
                if (preselected) {
                    selectOption(preselected);
                } else if (placeholder) {
                    valueSpan.textContent = placeholder.textContent.trim();
                    hiddenInput.value = '';
                }

                toggle.addEventListener('click', function () {
                    if (wrapper.classList.contains('open')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                options.forEach(function (li) {
                    li.addEventListener('click', function () {
                        selectOption(li);
                        closeMenu();
                        toggle.focus();
                    });
                });

                // Filter input event listener
                if (filterInput) {
                    filterInput.addEventListener('input', function () {
                        filterOptions(this.value);
                    });

                    filterInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') {
                            closeMenu();
                            toggle.focus();
                        } else if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            var firstVisible = wrapper.querySelector('.custom-select-option:not(.is-hidden):not(.is-placeholder)');
                            if (firstVisible) {
                                selectOption(firstVisible);
                                firstVisible.scrollIntoView({ block: 'nearest' });
                            }
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            var firstVisible = wrapper.querySelector('.custom-select-option:not(.is-hidden):not(.is-placeholder)');
                            if (firstVisible) {
                                selectOption(firstVisible);
                                closeMenu();
                                toggle.focus();
                            }
                        }
                    });

                    // Prevent menu from closing when clicking on filter input
                    filterInput.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                }

                toggle.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        if (wrapper.classList.contains('open')) {
                            closeMenu();
                        } else {
                            openMenu();
                        }
                    } else if (e.key === 'Escape') {
                        closeMenu();
                    } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (!wrapper.classList.contains('open')) {
                            openMenu();
                            return;
                        }
                        var activeIndex = options.findIndex(function (o) {
                            return o.classList.contains('is-active');
                        });
                        var nextIndex;
                        if (e.key === 'ArrowDown') {
                            nextIndex = activeIndex === -1 ? 0 : Math.min(options.length - 1, activeIndex + 1);
                        } else {
                            nextIndex = activeIndex === -1 ? 0 : Math.max(0, activeIndex - 1);
                        }
                        selectOption(options[nextIndex]);
                        options[nextIndex].scrollIntoView({ block: 'nearest' });
                    }
                });
            });

            // Close any open dropdown when clicking outside of it
            document.addEventListener('click', function (e) {
                document.querySelectorAll('.custom-select.open').forEach(function (wrapper) {
                    if (!wrapper.contains(e.target)) {
                        if (wrapper._closeCustomSelect) {
                            wrapper._closeCustomSelect();
                        }
                    }
                });
            });
        }

        initCustomSelects();
    })();
</script>
@endpush
