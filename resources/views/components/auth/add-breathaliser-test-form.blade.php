@props(['routeAction', 'data' => null, 'drivers' => [], 'vehicles' => [], 'waybills' => [], 'isEdit' => false])

@php $result = $data; @endphp

<x-auth.form form-id="BreathaliserTestForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating  select2-parent">
        <select name="vehicle_id" id="vehicle_id_breathaliser" class="form-select searchable-select" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_breathaliser">{{ __('Asset') }} *</label>
    </div>
    <div class="col-md-6 form-floating  select2-parent">
        <select name="driver_id" id="driver_id_breathaliser" class="form-select searchable-select" required>
            <option value="">Select Driver</option>
            @foreach($drivers as $driver)
                <option value="{{ $driver->id }}"
                    {{ ($result?->driver_id ?? old('driver_id')) == $driver->id ? 'selected' : '' }}>
                    {{ $driver->full_name }}
                </option>
            @endforeach
        </select>
        <label for="driver_id_breathaliser">{{ __('Driver') }} *</label>
    </div>

    <div class="col-md-12 form-floating mb-3">
        <select name="waybill_id" id="waybill_id_breathaliser" class="form-select searchable-select" required>
            <option value="">Select Waybill</option>
            @foreach($waybills as $waybill)
                <option value="{{ $waybill->id }}"
                    data-driver="{{ $waybill->driver_id }}"
                    data-vehicle="{{ $waybill->vehicle_id }}"
                    data-status="{{ $waybill->status?->value }}"
                    {{ ($result?->waybill_id ?? old('waybill_id')) == $waybill->id ? 'selected' : '' }}>
                   {{ $waybill->waybill_number }} ({{ $waybill->route_trip?->trip_name ?? 'N/A'  }})
                </option>
            @endforeach
        </select>
        <label for="waybill_id_breathaliser">{{ __('Waybill') }} *</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="test_date" id="test_date" required="true"
            place="" val="{{ $result?->test_date ? \Carbon\Carbon::parse($result->test_date)->format('Y-m-d') : old('test_date') }}" extraclasses="" label="{{ __('Test Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input
            name="test_location"
            id="test_location"
            label="{{ __('Test Location') }}"
            placeholder="{{ __('Search for location (e.g., Depot Exit Gate)') }}"
            value="{{ $result?->test_location ?? old('test_location') }}"
            lat="{{ $result?->test_location_lat ?? old('test_location_lat') }}"
            lng="{{ $result?->test_location_lng ?? old('test_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="device_number" id="device_number" required="false"
            place="{{ __('e.g. BR-2041') }}" val="{{ $result?->device_number ?? old('device_number') }}"
            extraclasses="" label="{{ __('Device Number') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="number" name="bac_level" id="bac_level" required="false"
            place="{{ __('0.000') }}" val="{{ $result?->bac_level ?? old('bac_level') }}"
            extraclasses="" label="{{ __('BAC Level') }}" step="0.001" min="0" />
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="test_result" id="test_result" class="form-select shadow-sm" required>
            <option value="negative" {{ ($result?->test_result ?? old('test_result', 'negative')) == 'negative' ? 'selected' : '' }}>Negative</option>
            <option value="borderline" {{ ($result?->test_result ?? old('test_result')) == 'borderline' ? 'selected' : '' }}>Borderline</option>
            <option value="positive" {{ ($result?->test_result ?? old('test_result')) == 'positive' ? 'selected' : '' }}>Positive</option>
        </select>
        <label for="test_result">{{ __('Test Result') }}</label>
    </div>

    {{-- Critical Alert for Positive Test Result --}}
    <div class="col-md-12" id="positive_test_alert" style="display: none;">
        <div class="alert alert-danger border-danger shadow-sm" style="background: #f8d7da; border: 2px solid #dc3545 !important; border-radius: 8px; padding: 16px;">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3" style="color: #dc3545; font-size: 24px; margin-top: 2px;"></i>
                <div style="flex: 1;">
                    <h6 class="mb-2" style="color: #721c24; font-weight: 700; font-size: 16px;">
                        <i class="fas fa-ban me-2"></i>CRITICAL: Driver Stand-Down Required
                    </h6>
                    <p class="mb-0" style="color: #721c24; font-size: 14px; line-height: 1.6; font-weight: 500;">
                        This failure means driver must not be allowed to drive the vehicle until otherwise instructed.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 form-floating mb-3">
        @php
            $isClientApproved = $result?->client_approved ?? 0;
            $isStatusEditable = $isClientApproved == 1;
        @endphp

        @if ($isEdit && !$isStatusEditable)
            <!-- Show alert and disabled select when client_approved = 0 -->

            <!-- Hidden field to ensure status value is always submitted -->
            <input type="hidden" name="status" value="{{ $result?->status ?? '0' }}">
            <select id="status_display" class="form-select shadow-sm" disabled>
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Open</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Under Review</option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>Closed</option>
            </select>
            <div
                style="background: #fef8e7; border: 1px solid #fa983a; border-radius: 6px; padding: 10px 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; margin-top:15px">
                <i class="fas fa-info-circle" style="color: #e67e22; font-size: 18px; flex-shrink: 0;"></i>
                <div style="flex: 1;">
                    <span style="color: #d35400; font-weight: 600; font-size: 13px; display: block;">
                        <i class="fas fa-user-check me-1"></i> Approval Required
                    </span>
                    <span style="color: #7f6000; font-size: 12px;">
                        Client director approval pending · Status changes are disabled
                    </span>
                </div>
                <span
                    style="background: #e67e22; color: white; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">
                    Locked
                </span>
            </div>
        @else
            <!-- Status is editable when client_approved = 1 OR when creating new -->
            <select name="status" id="status" class="form-select shadow-sm">
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Open</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Under Review</option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>Closed</option>
            </select>
        @endif
        <label for="status">{{ __('Status') }}</label>
    </div>

    <div class="col-md-12">
        <x-auth.input-field type="text" name="action_taken" id="action_taken_breathaliser" required="false"
            place="{{ __('e.g. Driver stood down, counseling, escalation') }}" val="{{ $result?->action_taken ?? old('action_taken') }}"
            extraclasses="" label="{{ __('Action Taken') }}" />
    </div>

    <div class="col-md-12">
        <x-auth.text-area name="remarks" id="remarks_breathaliser" required="false"
            place="{{ __('Additional remarks about the breathaliser test...') }}"
            val="{{ $result?->remarks ?? old('remarks') }}" extraclasses="" label="{{ __('Remarks') }}" />
    </div>

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="document" class="form-label mb-1 text-muted small ms-1">
                {{ __('Upload Attachment') }}
            </label>

            <div class="input-group">
                <input type="file" class="form-control shadow-sm bg-white" id="document_breathaliser" name="document"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">

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

            <small class="text-muted d-block mt-1">
                {{ __('Accepted files: PDF, JPG, JPEG, PNG, DOC, DOCX. Max size: 10MB.') }}
            </small>
        </div>
    </div>

    <div class="col-12 text-center mt-4">
        <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow"
            btn-type="submit" btn-value="{{ $isEdit ? __('Update Record') : __('Submit') }}" />
    </div>
</x-auth.form>

<div class="modal fade" id="breathaliserWaybillWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 2050;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="ri-error-warning-fill me-2"></i>Status Warning
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="ri-alert-line text-warning display-1"></i>
                </div>
                <h5 class="text-dark fw-bold mb-3">Waybill Not In Transit</h5>
                <p class="text-muted">Your vehicle's waybill status is not <strong>In Transit</strong>. Update the waybill status first, then create the breathaliser test record.</p>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-secondary px-5 rounded-pill fw-bold" data-bs-dismiss="modal">I'll Update It First</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle positive test result alert and red border
        function handleTestResultChange() {
            var testResult = $('#test_result').val();
            var $testResultField = $('#test_result');
            var $alertBox = $('#positive_test_alert');
            
            if (testResult === 'positive') {
                // Add red border to the select field
                $testResultField.addClass('border-danger').css({
                    'border-width': '2px',
                    'border-color': '#dc3545 !important'
                });
                
                // Show the alert message
                $alertBox.slideDown(300);
            } else {
                // Remove red border
                $testResultField.removeClass('border-danger').css({
                    'border-width': '',
                    'border-color': ''
                });
                
                // Hide the alert message
                $alertBox.slideUp(300);
            }
        }
        
        // Trigger on page load (for edit mode)
        handleTestResultChange();
        
        // Trigger on change
        $('#test_result').on('change', handleTestResultChange);

        // Set max date to today for test_date to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#test_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#test_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The test date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        var $form = $('#BreathaliserTestForm');
        var $waybillSelect = $('#waybill_id_breathaliser');
        var $vehicleSelect = $('#vehicle_id_breathaliser');
        var $driverSelect = $('#driver_id_breathaliser');
        var modalParent = $waybillSelect.closest('.modal');

        function syncContextFromWaybill() {
            var selected = $waybillSelect.find('option:selected');
            var vehicleId = selected.data('vehicle');
            var driverId = selected.data('driver');

            if (vehicleId) {
                $vehicleSelect.val(vehicleId).trigger('change.select2');
            }

            if (driverId) {
                $driverSelect.val(driverId).trigger('change.select2');
            }
        }

        $waybillSelect.select2({
            placeholder: 'Select Waybill',
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        $vehicleSelect.select2({
            placeholder: 'Select Asset',
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        $driverSelect.select2({
            placeholder: 'Select Driver',
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        // Store all waybill options for filtering
        var allWaybillOptions = $waybillSelect.find('option').clone();

        $vehicleSelect.on('change', function() {
            var selectedVehicle = $(this).val();
            $waybillSelect.empty();
            var firstMatchVal = '';
            allWaybillOptions.each(function() {
                var vehicleId = $(this).data('vehicle');
                if (!selectedVehicle || !vehicleId || vehicleId == selectedVehicle) {
                    $waybillSelect.append($(this).clone());
                    if (selectedVehicle && vehicleId == selectedVehicle && !firstMatchVal) {
                        firstMatchVal = $(this).val();
                    }
                }
            });
            $waybillSelect.val(firstMatchVal).trigger('change');
        });

        $waybillSelect.on('change', function() {
            syncContextFromWaybill();
        });

        $form.on('submit', function(e) {
            var waybillOption = $waybillSelect.find('option:selected');
            var status = waybillOption.data('status');

            syncContextFromWaybill();

            // Skip waybill status check on edit (waybill already linked)
            @if(!$isEdit)
            if (status != 2) {
                $('#breathaliserWaybillWarningModal').modal('show');
                e.preventDefault();
                return false;
            }
            @endif
        });

        syncContextFromWaybill();
    });
</script>

<style>
    .select2-container--default .select2-selection--single {
        height: 58px !important;
        padding-top: 17px !important;
        border-radius: 0.375rem !important;
        background-color: #fff !important;
        border: 1px solid #dee2e6 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 58px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #212529 !important;
        line-height: 28px;
    }

    .select2-container {
        font-weight: 400 !important;
    }

    .form-floating > .select2-container + label {
        transform: scale(.85) translateY(-.5rem) translateX(.15rem);
        opacity: .65;
        z-index: 5;
        margin-top: 5px;
    }
</style>
@endpush
