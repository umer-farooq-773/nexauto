@props(['routeAction', 'data' => null, 'drivers' => [], 'vehicles' => [], 'waybills' => [], 'isEdit' => false])

@php $result = $data; @endphp

<x-auth.form form-id="DriverMisconductForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating select2-parent">
        <select name="vehicle_id" id="vehicle_id_misconduct" class="form-select searchable-select" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_misconduct">{{ __('Asset') }} *</label>
    </div>
    <div class="col-md-6 form-floating select2-parent">
        <select name="driver_id" id="driver_id_misconduct" class="form-select searchable-select" required>
            <option value="">Select Driver</option>
            @foreach($drivers as $driver)
                <option value="{{ $driver->id }}"
                    {{ ($result?->driver_id ?? old('driver_id')) == $driver->id ? 'selected' : '' }}>
                    {{ $driver->full_name }}
                </option>
            @endforeach
        </select>
        <label for="driver_id_misconduct">{{ __('Driver') }} *</label>
    </div>

    <div class="col-md-12 form-floating mb-3">
        <select name="waybill_id" id="waybill_id_misconduct" class="form-select searchable-select" required>
            <option value="">Select Waybill</option>
            @foreach($waybills as $waybill)
                <option value="{{ $waybill->id }}"
                    data-driver="{{ $waybill->driver_id }}"
                    data-vehicle="{{ $waybill->vehicle_id }}"
                    data-status="{{ $waybill->status?->value }}"
                    data-route-trip="{{ $waybill->route_trip?->trip_name ?? '' }}"
                    {{ ($result?->waybill_id ?? old('waybill_id')) == $waybill->id ? 'selected' : '' }}>
                    {{ $waybill->waybill_number }} ({{ $waybill->route_trip?->trip_name ?? 'N/A'  }})
                </option>
            @endforeach
        </select>
        <label for="waybill_id_misconduct">{{ __('Waybill') }} *</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="misconduct_date" id="misconduct_date" required="true"
            place="" val="{{ $result?->misconduct_date ? \Carbon\Carbon::parse($result->misconduct_date)->format('Y-m-d') : old('misconduct_date') }}" extraclasses="" label="{{ __('Misconduct Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input 
            name="misconduct_location" 
            id="misconduct_location" 
            label="{{ __('Misconduct Location') }}"
            placeholder="{{ __('Search for location (e.g., Benin Corridor)') }}"
            value="{{ $result?->misconduct_location ?? old('misconduct_location') }}"
            lat="{{ $result?->misconduct_location_lat ?? old('misconduct_location_lat') }}"
            lng="{{ $result?->misconduct_location_lng ?? old('misconduct_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="misconduct_type" id="misconduct_type" required="true"
            place="{{ __('e.g. Reckless driving, route violation') }}" val="{{ $result?->misconduct_type ?? old('misconduct_type') }}"
            extraclasses="" label="{{ __('Misconduct Type') }}" />
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="severity" id="severity_misconduct" class="form-select shadow-sm">
            <option value="minor" {{ ($result?->severity ?? old('severity', 'minor')) == 'minor' ? 'selected' : '' }}>Minor</option>
            <option value="moderate" {{ ($result?->severity ?? old('severity')) == 'moderate' ? 'selected' : '' }}>Moderate</option>
            <option value="severe" {{ ($result?->severity ?? old('severity')) == 'severe' ? 'selected' : '' }}>Severe</option>
            <option value="critical" {{ ($result?->severity ?? old('severity')) == 'critical' ? 'selected' : '' }}>Critical</option>
        </select>
        <label for="severity_misconduct">{{ __('Severity') }}</label>
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
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>Resolved</option>
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
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>Resolved</option>
            </select>
        @endif
        <label for="status">{{ __('Status') }}</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="action_taken" id="action_taken" required="false"
            place="{{ __('e.g. Verbal warning, suspension') }}" val="{{ $result?->action_taken ?? old('action_taken') }}"
            extraclasses="" label="{{ __('Action Taken') }}" />
    </div>

    <div class="col-md-12">
        <x-auth.text-area name="remarks" id="remarks" required="false"
            place="{{ __('Detailed notes about the misconduct...') }}"
            val="{{ $result?->remarks ?? old('remarks') }}" extraclasses="" label="{{ __('Remarks') }}" />
    </div>

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="document" class="form-label mb-1 text-muted small ms-1">
                {{ __('Upload Attachment') }}
            </label>

            <div class="input-group">
                <input type="file" class="form-control shadow-sm bg-white" id="document" name="document"
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

<div class="modal fade" id="driverMisconductWaybillWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 2050;">
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
                <p class="text-muted">Your vehicle's waybill status is not <strong>In Transit</strong>. Update the waybill status first, then create the driver misconduct record.</p>
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
        // Set max date to today for misconduct_date to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#misconduct_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#misconduct_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The misconduct date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        var $form = $('#DriverMisconductForm');
        var $waybillSelect = $('#waybill_id_misconduct');
        var $vehicleSelect = $('#vehicle_id_misconduct');
        var $driverSelect = $('#driver_id_misconduct');
        var modalParent = $waybillSelect.closest('.modal');

        function syncContextFromWaybill() {
            var selected = $waybillSelect.find('option:selected');
            var vehicleId = selected.data('vehicle');
            var driverId = selected.data('driver');
            var routeTrip = selected.data('route-trip');

            if (vehicleId) {
                $vehicleSelect.val(vehicleId).trigger('change.select2');
            }

            if (driverId) {
                $driverSelect.val(driverId).trigger('change.select2');
            }

            if (routeTrip) {
                $('#route_trip_text').text(routeTrip);
                $('#route_trip_display_wrap').show();
            } else {
                $('#route_trip_text').text('—');
                $('#route_trip_display_wrap').hide();
            }
        }

        $waybillSelect.select2({
            placeholder: 'Select Waybill',
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        $vehicleSelect.select2({
            placeholder: 'Select Vehicle',
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

            if (status != 2) {
                $('#driverMisconductWaybillWarningModal').modal('show');
                e.preventDefault();
                return false;
            }
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
