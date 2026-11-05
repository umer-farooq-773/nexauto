@props(['routeAction', 'data' => null, 'vehicles' => [], 'waybills' => [], 'isEdit' => false])

@php $result = $data; @endphp

<x-auth.form form-id="IllegalDetentionForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating  select2-parent">
        <select name="vehicle_id" id="vehicle_id_detention" class="form-select searchable-select" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_detention">{{ __('Asset') }} *</label>
    </div>

    <div class="col-md-6 form-floating  select2-parent">
        <select name="waybill_id" id="waybill_id_detention" class="form-select searchable-select" required>
            <option value="">Select Waybill</option>
            @foreach($waybills as $waybill)
                <option value="{{ $waybill->id }}"
                    data-vehicle="{{ $waybill->vehicle_id }}"
                    data-status="{{ $waybill->status?->value }}"
                    {{ ($result?->waybill_id ?? old('waybill_id')) == $waybill->id ? 'selected' : '' }}>
                     {{ $waybill->waybill_number }} ({{ $waybill->origin ?: $waybill->route_trip?->origin }} → {{ $waybill->destination ?: $waybill->route_trip?->destination }})
                </option>
            @endforeach
        </select>

        {{-- Waybill Warning Modal --}}
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
                        <p class="text-muted">Your vehicle's waybill status is not <strong>In Transit</strong>. Therefore, you cannot create an Incident Intervention form. Please change the waybill status first, then you will be able to create it.</p>
                    </div>
                    <div class="modal-footer border-0 pb-4 justify-content-center">
                        <button type="button" class="btn btn-secondary px-5 rounded-pill fw-bold" data-bs-dismiss="modal">I'll Update It First</button>
                    </div>
                </div>
            </div>
        </div>
        <label for="waybill_id_detention">{{ __('Waybill') }} *</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="detention_date" id="detention_date" required="true"
            place="" val="{{ $result?->detention_date ? \Carbon\Carbon::parse($result->detention_date)->format('Y-m-d') : old('detention_date') }}" extraclasses="" label="{{ __('Detention Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="number" name="cost" id="cost" required="true"
            place="" val="{{ $result?->cost ?? old('cost') }}" extraclasses="" label="{{ __('Cost') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input 
            name="detention_location" 
            id="detention_location" 
            label="{{ __('Detention Location') }}"
            placeholder="{{ __('Search for location (e.g., Lagos Checkpoint)') }}"
            value="{{ $result?->detention_location ?? old('detention_location') }}"
            lat="{{ $result?->detention_location_lat ?? old('detention_location_lat') }}"
            lng="{{ $result?->detention_location_lng ?? old('detention_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="detained_by" id="detained_by" required="false"
            place="{{ __('e.g. FRSC, Police') }}" val="{{ $result?->detained_by ?? old('detained_by') }}"
            extraclasses="" label="{{ __('Detained By') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="reason" id="reason" required="false"
            place="{{ __('Reason for detention') }}" val="{{ $result?->reason ?? old('reason') }}"
            extraclasses="" label="{{ __('Reason') }}" />
    </div>
    <div class="col-md-6">
        <x-auth.input-field type="text" name="authority" id="reason" required="false"
            place="{{ __('Authority') }}" val="{{ $result?->authority ?? old('authority') }}"
            extraclasses="" label="{{ __('Authority') }}" />
    </div>
    @if($isEdit)
    <div class="col-md-6">
        <x-auth.input-field type="date" name="release_date" id="release_date" required="false"
            place="" val="{{ $result?->release_date ? \Carbon\Carbon::parse($result->release_date)->format('Y-m-d') : old('release_date') }}" extraclasses="" label="{{ __('Release Date') }}" />
    </div>
    @endif

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
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Detained</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Released</option>
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
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Detained</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Released</option>
            </select>
        @endif
        <label for="status">{{ __('Status') }}</label>
    </div>

    <div class="col-md-12">
        <x-auth.text-area name="remarks" id="remarks" required="false"
            place="{{ __('Additional remarks...') }}" val="{{ $result?->remarks ?? old('remarks') }}"
            extraclasses="" label="{{ __('Remarks') }}" />
    </div>

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="document" class="form-label mb-1 text-muted small ms-1">
                {{ __('Upload Document') }}
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
        </div>
    </div>

    <div class="col-12 text-center mt-4">
        <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow"
            btn-type="submit" btn-value="{{ $isEdit ? __('Update Record') : __('Submit') }}" />
    </div>
</x-auth.form>



@push('scripts')
<script>
    $(document).ready(function() {
        // Set max date to today for detention_date to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#detention_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#detention_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The detention date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        // Waybill status validation on submit
        // Waybill status validation on submit - ONLY FOR CREATE, NOT EDIT
        @if(!$isEdit)
        $('#IllegalDetentionForm').on('submit', function(e) {
            var waybillOption = $('#waybill_id_detention option:selected');
            var status = String(waybillOption.data('status'));
            if (status !== '2') {
                var modal = $('#waybillWarningModal');
                modal.modal('show');
                modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
                    $('#IllegalDetentionForm')[0].reset();
                    $('#waybill_id_detention').val('').trigger('change');
                    $('#vehicle_id_detention').val('').trigger('change');
                });
                e.preventDefault();
                return false;
            }
        });
        @endif

        var $waybillSelect = $('#waybill_id_detention');
        var $vehicleSelect = $('#vehicle_id_detention');
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

        // Store all waybill options for filtering (for edit mode)
        var allWaybillOptions = $waybillSelect.find('option').clone();

        // Fetch waybills dynamically when vehicle is selected
        $vehicleSelect.on('change', function() {
            var selectedVehicle = $(this).val();
            
            // Clear waybill dropdown
            $waybillSelect.empty().append('<option value="">Select Waybill</option>');
            
            if (!selectedVehicle) {
                $waybillSelect.trigger('change');
                return;
            }

            // Show loading state
            $waybillSelect.prop('disabled', true);
            $waybillSelect.append('<option value="">Loading waybills...</option>').trigger('change');

            // Fetch waybills for selected vehicle via AJAX
            $.ajax({
                url: "{{ route('waybill.in_transit_waybills', ':vehicleId') }}".replace(':vehicleId', selectedVehicle),
                method: 'GET',
                success: function(response) {
                    $waybillSelect.empty().append('<option value="">Select Waybill</option>');
                    
                    if (response.waybills && response.waybills.length > 0) {
                        var firstWaybillId = null;
                        
                        response.waybills.forEach(function(waybill) {
                            var optionText = waybill.waybill_number;
                            if (waybill.fleet_no || waybill.truck_no) {
                                optionText += ' (' + (waybill.fleet_no || '') + (waybill.fleet_no && waybill.truck_no ? ' - ' : '') + (waybill.truck_no || '') + ')';
                            }
                            
                            var option = new Option(optionText, waybill.id, false, false);
                            $(option).attr('data-vehicle', selectedVehicle);
                            $(option).attr('data-status', '2'); // In transit status
                            $waybillSelect.append(option);
                            
                            if (!firstWaybillId) {
                                firstWaybillId = waybill.id;
                            }
                        });
                        
                        // Auto-select first waybill
                        if (firstWaybillId) {
                            $waybillSelect.val(firstWaybillId);
                        }
                    } else {
                        $waybillSelect.append('<option value="">No in-transit waybills found</option>');
                    }
                    
                    $waybillSelect.prop('disabled', false).trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching waybills:', error);
                    $waybillSelect.empty().append('<option value="">Error loading waybills</option>');
                    $waybillSelect.prop('disabled', false).trigger('change');
                }
            });
        });

        // For edit mode: trigger initial filtering if vehicle is already selected
        @if($isEdit && $result?->vehicle_id)
        var initialVehicleId = '{{ $result->vehicle_id }}';
        if (initialVehicleId) {
            // Don't trigger change to avoid overwriting the selected waybill
            // Just filter the existing options
            var currentWaybillId = '{{ $result->waybill_id }}';
            $waybillSelect.empty().append('<option value="">Select Waybill</option>');
            allWaybillOptions.each(function() {
                var vehicleId = $(this).data('vehicle');
                if (vehicleId == initialVehicleId || $(this).val() == '') {
                    $waybillSelect.append($(this).clone());
                }
            });
            if (currentWaybillId) {
                $waybillSelect.val(currentWaybillId).trigger('change');
            }
        }
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

    .select2-container{
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
