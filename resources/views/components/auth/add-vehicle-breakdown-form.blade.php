@props(['routeAction', 'data' => null, 'vehicles' => [], 'waybills' => [], 'isEdit' => false, 'module' => []])

@php
    $result = $data;
    $isBoggedDown = ($module['module_title'] ?? '') === 'Bogged Down';
@endphp

<x-auth.form form-id="VehicleBreakdownForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating  select2-parent">
        <select name="vehicle_id" id="vehicle_id_breakdown" class="form-select searchable-select" required>
            <option value="">Select Asset</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_breakdown">{{ __('Asset') }} *</label>
    </div>

    <div class="col-md-6 form-floating  select2-parent">
        <select name="waybill_id" id="waybill_id_breakdown" class="form-select searchable-select" required>
            <option value="">Select Waybill</option>
            @foreach($waybills as $waybill)
                <option
                    value="{{ $waybill->id }}"
                    data-vehicle="{{ $waybill->vehicle_id }}"
                    data-status="{{ $waybill->status?->value }}"
                    {{ ($result?->waybill_id ?? old('waybill_id')) == $waybill->id ? 'selected' : '' }}>
                    {{ $waybill->waybill_number }} ({{ $waybill->origin ?: $waybill->route_trip?->origin }} → {{ $waybill->destination ?: $waybill->route_trip?->destination }})
                </option>
            @endforeach
        </select>
        <label for="waybill_id_breakdown">{{ __('Waybill') }} *</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field
            type="date"
            name="breakdown_date"
            id="breakdown_date"
            required="true"
            place=""
            val="{{ $result?->breakdown_date ? \Carbon\Carbon::parse($result->breakdown_date)->format('Y-m-d') : old('breakdown_date') }}"
            extraclasses=""
            label="{{ __($module['date_label'] ?? 'Breakdown Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input 
            name="breakdown_location" 
            id="breakdown_location" 
            label="{{ __($module['location_label'] ?? 'Breakdown Location') }}"
            placeholder="{{ __('Search for location...') }}"
            value="{{ $result?->breakdown_location ?? old('breakdown_location') }}"
            lat="{{ $result?->breakdown_location_lat ?? old('breakdown_location_lat') }}"
            lng="{{ $result?->breakdown_location_lng ?? old('breakdown_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6">
        @if($isBoggedDown)
            <div class="form-floating mb-3">
                <select name="breakdown_type" id="breakdown_type" class="form-select shadow-sm">
                    <option value="">Select Ground Condition</option>
                    @foreach(['Mud', 'Sand', 'Snow', 'Flooded Road', 'Soft Ground', 'Ditch / Shoulder', 'Other'] as $condition)
                        <option value="{{ $condition }}" {{ ($result?->breakdown_type ?? old('breakdown_type')) == $condition ? 'selected' : '' }}>
                            {{ $condition }}
                        </option>
                    @endforeach
                </select>
                <label for="breakdown_type">{{ __($module['type_label'] ?? 'Ground Condition') }}</label>
            </div>
        @else
            <x-auth.input-field
                type="text"
                name="breakdown_type"
                id="breakdown_type"
                required="false"
                place="{{ __($module['type_placeholder'] ?? 'e.g. Engine Failure, Flat Tyre') }}"
                val="{{ $result?->breakdown_type ?? old('breakdown_type') }}"
                extraclasses=""
                label="{{ __($module['type_label'] ?? 'Breakdown Type') }}" />
        @endif
    </div>

    <div class="col-md-6">
        <x-auth.input-field
            type="number"
            name="repair_cost"
            id="repair_cost"
            required="false"
            place="{{ __('0.00') }}"
            val="{{ $result?->repair_cost ?? old('repair_cost') }}"
            extraclasses=""
            label="{{ __($module['cost_label'] ?? 'Repair Cost') }}" />
    </div>

    @if($isEdit)
        <div class="col-md-6">
            <x-auth.input-field
                type="date"
                name="resolved_date"
                id="resolved_date"
                required="false"
                place=""
                val="{{ $result?->resolved_date ? \Carbon\Carbon::parse($result->resolved_date)->format('Y-m-d') : old('resolved_date') }}"
                extraclasses=""
                label="{{ __($module['resolved_date_label'] ?? 'Resolved Date') }}" />
        </div>
    @endif

    <div class="col-md-6">
        <x-auth.input-field
        type="text"
        name="authority"
        id="authority"
        required="true"
        place="{{ __('Authority') }}"
        val="{{ $result?->authority ?? old('authority') }}"
        extraclasses=""
        label="{{ __($module['authority'] ?? 'Authority') }}" />
    </div>

    <div class="col-md-6 form-floating mb-3">
        @php
            $isClientApproved = $result?->client_approved ?? 0;
            $isStatusEditable = $isClientApproved == 1;
        @endphp

        @if ($isEdit && !$isStatusEditable)
            <!-- Show alert and disabled select when client_approved = 0 -->
            <div
                style="background: #fef8e7; border: 1px solid #fa983a; border-radius: 6px; padding: 10px 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
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
            <!-- Hidden field to ensure status value is always submitted -->
            <input type="hidden" name="status" value="{{ $result?->status ?? '0' }}">
            <select id="status_display" class="form-select shadow-sm" disabled>
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Pending
                </option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>In Progress
                </option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>
                    {{ $isBoggedDown ? 'Recovered' : 'Resolved' }}
                </option>
            </select>
        @else
            <select name="status" id="status" class="form-select shadow-sm">
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Pending
                </option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>In Progress
                </option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>
                    {{ $isBoggedDown ? 'Recovered' : 'Resolved' }}
                </option>
            </select>
        @endif
        <label for="status">{{ __('Status') }}</label>
    </div>


    <div class="col-md-12">
        <x-auth.text-area
            name="description"
            id="description"
            required="false"
            place="{{ __($module['description_placeholder'] ?? 'Breakdown details...') }}"
            val="{{ $result?->description ?? old('description') }}"
            extraclasses=""
            label="{{ __($module['description_label'] ?? 'Description') }}" />
    </div>

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="document" class="form-label mb-1 text-muted small ms-1">
                {{ __('Upload Document') }}
            </label>

            <div class="input-group">
                <input type="file" class="form-control shadow-sm bg-white" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">

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
        <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow" btn-type="submit" btn-value="{{ $isEdit ? __('Update Record') : __('Submit') }}" />
    </div>
</x-auth.form>

<div class="modal fade" id="waybillWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 2050;">
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
                <p class="text-muted">Your vehicle's waybill status is not <strong>In Transit</strong>. Please update the waybill status first before creating this incident record.</p>
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
        // Set max date to today for breakdown_date (also used for bogged_date) to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#breakdown_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#breakdown_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        var $waybillSelect = $('#waybill_id_breakdown');
        var $vehicleSelect = $('#vehicle_id_breakdown');
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

        // Waybill status validation on submit - ONLY FOR CREATE, NOT EDIT
        @if(!$isEdit)
        $('#VehicleBreakdownForm').on('submit', function(e) {
            var waybillOption = $('#waybill_id_breakdown option:selected');
            var status = waybillOption.data('status');

            if (status != 2) {
                var modal = $('#waybillWarningModal');
                modal.modal('show');
                modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
                    $('#VehicleBreakdownForm')[0].reset();
                    $('#waybill_id_breakdown').val('').trigger('change');
                    $('#vehicle_id_breakdown').val('').trigger('change');
                });

                e.preventDefault();
                return false;
            }
        });
        @endif
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
