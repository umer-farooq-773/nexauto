@props(['routeAction', 'data' => null, 'vehicles' => [], 'waybills' => [], 'isEdit' => false])

@php $result = $data; @endphp

<x-auth.form form-id="VehicleBoggedDownForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating select2-parent">
        <select name="vehicle_id" id="vehicle_id_bogged" class="form-select searchable-select" required>
            <option value="">Select Asset</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_bogged">{{ __('Asset') }} *</label>
    </div>

    <div class="col-md-6 form-floating select2-parent">
        <select name="waybill_id" id="waybill_id_bogged" class="form-select searchable-select" required>
            <option value="">Select Waybill</option>
            @foreach($waybills as $waybill)
                <option
                    value="{{ $waybill->id }}"
                    data-vehicle="{{ $waybill->vehicle_id }}"
                    data-status="{{ $waybill->status?->value }}"
                    data-route-trip="{{ $waybill->route_trip?->trip_name ?? '' }}"
                    {{ ($result?->waybill_id ?? old('waybill_id')) == $waybill->id ? 'selected' : '' }}>
                    {{ $waybill->waybill_number }} ({{ $waybill->route_trip?->trip_name ?? 'N/A'  }})
                </option>
            @endforeach
        </select>
        <label for="waybill_id_bogged">{{ __('Waybill') }} *</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="bogged_date" id="bogged_date" required="true"
            place="" val="{{ $result?->bogged_date ? \Carbon\Carbon::parse($result->bogged_date)->format('Y-m-d') : old('bogged_date') }}" extraclasses="" label="{{ __('Bogged Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input 
            name="stuck_location" 
            id="stuck_location" 
            label="{{ __('Stuck Location') }}"
            placeholder="{{ __('Search location...') }}"
            value="{{ $result?->stuck_location ?? old('stuck_location') }}"
            lat="{{ $result?->stuck_location_lat ?? old('stuck_location_lat') }}"
            lng="{{ $result?->stuck_location_lng ?? old('stuck_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="ground_condition" id="ground_condition" class="form-select shadow-sm">
            <option value="">Select Ground Condition</option>
            @foreach(['Mud', 'Sand', 'Snow', 'Flooded Road', 'Soft Ground', 'Ditch / Shoulder', 'Other'] as $condition)
                <option value="{{ $condition }}" {{ ($result?->ground_condition ?? old('ground_condition')) == $condition ? 'selected' : '' }}>
                    {{ $condition }}
                </option>
            @endforeach
        </select>
        <label for="ground_condition">{{ __('Ground Condition') }}</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="number" name="recovery_cost" id="recovery_cost" required="false"
            place="{{ __('0.00') }}" val="{{ $result?->recovery_cost ?? old('recovery_cost') }}" extraclasses="" label="{{ __('Recovery Cost') }}" />
    </div>

    @if($isEdit)
    <div class="col-md-6">
        <x-auth.input-field type="date" name="recovered_date" id="recovered_date" required="false"
            place="" val="{{ $result?->recovered_date ? \Carbon\Carbon::parse($result->recovered_date)->format('Y-m-d') : old('recovered_date') }}" extraclasses="" label="{{ __('Recovered Date') }}" />
    </div>
    @endif

    <div class="col-md-6">
        <x-auth.input-field type="text" name="authority" id="authority" required="true"
            place="" val="{{ $result?->authority ?? old('authority') }}" extraclasses="" label="{{ __('Authority') }}" />
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
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Stuck/Pending</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Recovered</option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>Recovery In Progress</option>
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
            <select name="status" id="status" class="form-select shadow-sm">
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Stuck/Pending</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Recovered</option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>Recovery In Progress</option>
            </select>
        @endif
        <label for="status">{{ __('Status') }}</label>
    </div>

    <div class="col-md-12">
        <x-auth.text-area name="description" id="description_bogged" required="false"
            place="{{ __('Describe how and where the vehicle got stuck...') }}" val="{{ $result?->description ?? old('description') }}"
            extraclasses="" label="{{ __('Recovery Notes') }}" />
    </div>

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="document" class="form-label mb-1 text-muted small ms-1">
                {{ __('Upload Document') }}
            </label>

            <div class="input-group">
                <input type="file" class="form-control shadow-sm bg-white" id="document_bogged" name="document"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">

                @if ($result?->document_url)
                    <a href="{{ $result->document_url }}" target="_blank" class="btn btn-outline-primary d-flex align-items-center">
                        <i class="ri-eye-line me-1"></i> {{ __('View') }}
                    </a>
                @endif
            </div>

            @error('document')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 text-center mt-4">
        <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow"
            btn-type="submit" btn-value="{{ $isEdit ? __('Update Record') : __('Submit') }}" />
    </div>
</x-auth.form>

<div class="modal fade" id="waybillBoggedWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 2050;">
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
                <p class="text-muted">Your vehicle's waybill status is not <strong>In Transit</strong>. Please update the waybill status first before creating this bogged down record.</p>
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
        var $waybillSelect = $('#waybill_id_bogged');
        var $vehicleSelect = $('#vehicle_id_bogged');
        var modalParent = $waybillSelect.closest('.modal');

        $waybillSelect.select2({
            placeholder: "Select Waybill",
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        $vehicleSelect.select2({
            placeholder: "Select Asset",
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
            var routeTrip = $(this).find('option:selected').data('route-trip');
            if (routeTrip) {
                $('#route_trip_text').text(routeTrip);
                $('#route_trip_display_wrap').show();
            } else {
                $('#route_trip_text').text('—');
                $('#route_trip_display_wrap').hide();
            }
        });

        // Sync on page load (edit mode)
        $waybillSelect.trigger('change');

        // Set max date to today for bogged_date to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#bogged_date').attr('max', today);
        $('#recovered_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#bogged_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The bogged date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        $('#VehicleBoggedDownForm').on('submit', function(e) {
            var waybillOption = $('#waybill_id_bogged option:selected');
            var status = waybillOption.data('status');

            // Skip waybill status check on edit (waybill already linked)
            @if(!$isEdit)
            if (status != 2) {
                $('#waybillBoggedWarningModal').modal('show');
                e.preventDefault();
                return false;
            }
            @endif
        });
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

    /* Fix z-index for geoapify suggestions in modal */
    .modal .geoapify-suggestions {
        z-index: 2060 !important;
    }
</style>
@endpush
