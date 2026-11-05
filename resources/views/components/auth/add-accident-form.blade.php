@props(['routeAction', 'data' => null, 'vehicles' => [], 'waybills' => [], 'isEdit' => false])

@php $result = $data; @endphp

<x-auth.form form-id="AccidentForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating select2-parent">
        <select name="vehicle_id" id="vehicle_id_accident" class="form-select searchable-select" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_accident">{{ __('Vehicle') }} *</label>
    </div>

    <div class="col-md-6 form-floating select2-parent">
        <select name="waybill_id" id="waybill_id_accident" class="form-select searchable-select" required>
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

        {{-- Damage Cost (0) Confirmation Modal --}}
        <div class="modal fade" id="damageCostConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 2051;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-4">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="ri-information-line me-2"></i>Damage Cost Confirmation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="ri-alert-line text-primary display-1"></i>
                        </div>
                        <h5 class="text-dark fw-bold mb-3">You entered 0 for Damage Cost</h5>
                        <p class="text-muted">Do you want to proceed submitting the form?</p>
                    </div>
                    <div class="modal-footer border-0 pb-4 justify-content-center gap-2">
                        <button type="button" id="damageCostCancelBtn" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="damageCostProceedBtn" class="btn btn-primary px-4 rounded-pill fw-bold">Proceed</button>
                    </div>
                </div>
            </div>
        </div>

        <label for="waybill_id_accident">{{ __('Waybill') }} *</label>

    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="accident_date" id="accident_date" required="true"
            place="" val="{{ $result?->accident_date ? \Carbon\Carbon::parse($result->accident_date)->format('Y-m-d') : old('accident_date') }}" extraclasses="" label="{{ __('Accident Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input
            name="accident_location"
            id="accident_location"
            label="{{ __('Accident Location') }}"
            placeholder="{{ __('Search for location (e.g., Enugu Highway)') }}"
            value="{{ $result?->accident_location ?? old('accident_location') }}"
            lat="{{ $result?->accident_location_lat ?? old('accident_location_lat') }}"
            lng="{{ $result?->accident_location_lng ?? old('accident_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="accident_type_detail" id="accident_type_detail" required="false"
            place="{{ __('e.g. Collision, Rollover') }}" val="{{ $result?->accident_type_detail ?? old('accident_type_detail') }}"
            extraclasses="" label="{{ __('Accident Type (Details)') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="number" name="damage_cost" id="damage_cost" required="false"
            place="{{ __('0.00') }}" val="{{ $result?->damage_cost ?? old('damage_cost') }}" extraclasses="" label="{{ __('Cost ( if unknown enter 0)') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="authority" id="authority" required="false"
            place="{{ __('Authority') }}" val="{{ $result?->authority ?? old('authority') }}" extraclasses="" label="{{ __('Authority') }}" />
    </div>
@if($isEdit)
    <div class="col-md-6">
        <x-auth.input-field type="date" name="resolved_date" id="resolved_date" required="false"
            place="" val="{{ $result?->resolved_date ? \Carbon\Carbon::parse($result->resolved_date)->format('Y-m-d') : old('resolved_date') }}" extraclasses="" label="{{ __('Resolved Date') }}" />
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
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Resolved</option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>In Progress</option>
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
                <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Resolved</option>
                <option value="2" {{ ($result?->status ?? old('status')) == '2' ? 'selected' : '' }}>In Progress</option>
            </select>
        @endif
        <label for="status">{{ __('Status') }}</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="accident_type" id="nature_of_accident" class="form-select shadow-sm" required>
            <option value="">-- Select Nature --</option>
            <option value="Loaded" {{ ($result?->accident_type ?? old('accident_type')) == 'Loaded' ? 'selected' : '' }}>Loaded</option>
            <option value="Unloaded" {{ ($result?->accident_type ?? old('accident_type')) == 'Unloaded' ? 'selected' : '' }}>Unloaded</option>
        </select>
        <label for="nature_of_accident">{{ __('Nature of Accident') }} *</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="driver_status" id="driver_status" class="form-select shadow-sm" required>
            <option value="" disabled {{ !($result?->driver_status ?? old('driver_status')) ? 'selected' : '' }}>-- Select Status --</option>
            <option value="none" {{ ($result?->driver_status ?? old('driver_status')) == 'none' ? 'selected' : '' }}>None (Safe)</option>
            <option value="minor" {{ ($result?->driver_status ?? old('driver_status')) == 'minor' ? 'selected' : '' }}>Minor Injury</option>
            <option value="moderate" {{ ($result?->driver_status ?? old('driver_status')) == 'moderate' ? 'selected' : '' }}>Moderate Injury</option>
            <option value="severe" {{ ($result?->driver_status ?? old('driver_status')) == 'severe' ? 'selected' : '' }}>Severe Injury</option>
            <option value="fatal" {{ ($result?->driver_status ?? old('driver_status')) == 'fatal' ? 'selected' : '' }}>Fatal</option>
        </select>
        <label for="driver_status">{{ __('Status of Driver') }} *</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="motor_mate_status" id="motor_mate_status" class="form-select shadow-sm" required>
            <option value="" disabled {{ !($result?->motor_mate_status ?? old('motor_mate_status')) ? 'selected' : '' }}>-- Select Status --</option>
            <option value="none" {{ ($result?->motor_mate_status ?? old('motor_mate_status')) == 'none' ? 'selected' : '' }}>None (Safe)</option>
            <option value="minor" {{ ($result?->motor_mate_status ?? old('motor_mate_status')) == 'minor' ? 'selected' : '' }}>Minor Injury</option>
            <option value="moderate" {{ ($result?->motor_mate_status ?? old('motor_mate_status')) == 'moderate' ? 'selected' : '' }}>Moderate Injury</option>
            <option value="severe" {{ ($result?->motor_mate_status ?? old('motor_mate_status')) == 'severe' ? 'selected' : '' }}>Severe Injury</option>
            <option value="fatal" {{ ($result?->motor_mate_status ?? old('motor_mate_status')) == 'fatal' ? 'selected' : '' }}>Fatal</option>
        </select>
        <label for="motor_mate_status">{{ __('Status of Motor Mate') }} *</label>
    </div>

    <div class="col-md-6 form-floating mb-3" id="content_div" style="display: none;">
        <select name="content" id="content" class="form-select shadow-sm">
            <option value="">-- Select Content --</option>
            <option value="NBL" {{ ($result?->content ?? old('content')) == 'NBL' ? 'selected' : '' }}>NBL</option>
            <option value="NBC" {{ ($result?->content ?? old('content')) == 'NBC' ? 'selected' : '' }}>NBC</option>
            <option value="FMCG" {{ ($result?->content ?? old('content')) == 'FMCG' ? 'selected' : '' }}>FMCG</option>
            <option value="Pharmaceuticals" {{ ($result?->content ?? old('content')) == 'Pharmaceuticals' ? 'selected' : '' }}>Pharmaceuticals</option>
            <option value="Machineries" {{ ($result?->content ?? old('content')) == 'Machineries' ? 'selected' : '' }}>Machineries</option>
            <option value="PMS" {{ ($result?->content ?? old('content')) == 'PMS' ? 'selected' : '' }}>PMS</option>
            <option value="AGO" {{ ($result?->content ?? old('content')) == 'AGO' ? 'selected' : '' }}>AGO</option>
        </select>
        <label for="content">{{ __('Content') }}</label>
    </div>

    <div class="col-md-6 form-floating mb-3" id="damage_div" style="display: none;">
        <select name="damage_extend" id="damage_extend" class="form-select shadow-sm">
            <option value="">-- Select Damage --</option>
            <option value="Product collapse" {{ ($result?->damage_extend ?? old('damage_extend')) == 'Product collapse' ? 'selected' : '' }}>Product collapse</option>
            <option value="Severe damage" {{ ($result?->damage_extend ?? old('damage_extend')) == 'Severe damage' ? 'selected' : '' }}>Severe damage</option>
            <option value="Moderate damage" {{ ($result?->damage_extend ?? old('damage_extend')) == 'Moderate damage' ? 'selected' : '' }}>Moderate damage</option>
            <option value="Minor damage" {{ ($result?->damage_extend ?? old('damage_extend')) == 'Minor damage' ? 'selected' : '' }}>Minor damage</option>
            <option value="No damage" {{ ($result?->damage_extend ?? old('damage_extend')) == 'No damage' ? 'selected' : '' }}>No damage</option>
        </select>
        <label for="damage_extend">{{ __('Damage Extend') }}</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="is_fatality" id="is_fatality" class="form-select shadow-sm" required>
            <option value="no" {{ ($result?->is_fatality ?? old('is_fatality')) == 'no' ? 'selected' : '' }}>No</option>
            <option value="yes" {{ ($result?->is_fatality ?? old('is_fatality')) == 'yes' ? 'selected' : '' }}>Yes</option>
        </select>
        <label for="is_fatality">{{ __('Fatality') }} *</label>
    </div>

    <div class="col-md-6 form-floating mb-3" id="fatality_details_div" style="display: none;">
        <select name="fatality_type" id="fatality_type" class="form-select shadow-sm">
            <option value="">-- Select Party --</option>
            <option value="internal_staff" {{ ($result?->fatality_type ?? old('fatality_type')) == 'internal_staff' ? 'selected' : '' }}>Internal Staff</option>
            <option value="third_party" {{ ($result?->fatality_type ?? old('fatality_type')) == 'third_party' ? 'selected' : '' }}>Third Party</option>
        </select>
        <label for="fatality_type">{{ __('Fatality To') }}</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="accident_nature_detail" id="accident_nature_detail" class="form-select shadow-sm" required>
            <option value="" disabled {{ !($result?->accident_nature_detail ?? old('accident_nature_detail')) ? 'selected' : '' }}>-- Select Detail --</option>
            <option value="Lone accident" {{ ($result?->accident_nature_detail ?? old('accident_nature_detail')) == 'Lone accident' ? 'selected' : '' }}>Lone accident</option>
            <option value="Collision by a third party" {{ ($result?->accident_nature_detail ?? old('accident_nature_detail')) == 'Collision by a third party' ? 'selected' : '' }}>Collision by a third party</option>
            <option value="Collision to third party" {{ ($result?->accident_nature_detail ?? old('accident_nature_detail')) == 'Collision to third party' ? 'selected' : '' }}>Collision to third party</option>
            <option value="Liability undetermined" {{ ($result?->accident_nature_detail ?? old('accident_nature_detail')) == 'Liability undetermined' ? 'selected' : '' }}>Liability undetermined</option>
        </select>
        <label for="accident_nature_detail">{{ __('Nature of Accident Detail') }} *</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="is_damage_asset" id="is_damage_asset" class="form-select shadow-sm" required>
            <option value="no" {{ ($result?->is_damage_asset ?? old('is_damage_asset')) == 'no' ? 'selected' : '' }}>No</option>
            <option value="yes" {{ ($result?->is_damage_asset ?? old('is_damage_asset')) == 'yes' ? 'selected' : '' }}>Yes</option>
        </select>
        <label for="is_damage_asset">{{ __('Damage Asset') }} *</label>
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="is_injury" id="is_injury" class="form-select shadow-sm" required>
            <option value="no" {{ ($result?->is_injury ?? old('is_injury')) == 'no' ? 'selected' : '' }}>No</option>
            <option value="yes" {{ ($result?->is_injury ?? old('is_injury')) == 'yes' ? 'selected' : '' }}>Yes</option>
        </select>
        <label for="is_injury">{{ __('3rd parties Injury') }} *</label>
    </div>
    <div class="col-md-12">
        <x-auth.text-area name="description" id="description" required="false"
            place="{{ __('Accident report...') }}" val="{{ $result?->description ?? old('description') }}"
            extraclasses="" label="{{ __('Comments') }}" />
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
        // Set max date to today for accident_date to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#accident_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#accident_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The accident date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        // -------- Damage Cost (0) confirmation modal --------
        // Prevent double-submit when user clicks Proceed
        var allowDamageCostSubmit = false;

        $('#AccidentForm').on('submit', function(e) {
            // If Proceed already clicked, allow submission
            if (allowDamageCostSubmit) {
                return true;
            }

            var raw = $('#damage_cost').val();
            var damageCost = parseFloat(raw);

            // Treat empty/invalid as 0 (per your rule)
            if (isNaN(damageCost)) damageCost = 0;

            if (damageCost === 0) {
                e.preventDefault();

                // Show modal and stop submit
                $('#damageCostConfirmModal').modal('show');
                return false;
            }

            return true;
        });

        $('#damageCostConfirmModal').on('show.bs.modal', function() {
            // Reset flag each time modal is opened from normal submit
            allowDamageCostSubmit = false;
        });

        // Cancel: close modal and focus cursor back on #damage_cost
        $(document).on('click', '#damageCostCancelBtn', function(e) {
            e.preventDefault();
            $('#damageCostConfirmModal').modal('hide');
            setTimeout(function() {
                $('#damage_cost').trigger('focus');
            }, 150);
        });

        // Proceed: close modal and submit the form
        $(document).on('click', '#damageCostProceedBtn', function(e) {
            e.preventDefault();
            allowDamageCostSubmit = true;
            $('#damageCostConfirmModal').modal('hide');
            setTimeout(function() {
                $('#AccidentForm')[0].submit();
            }, 150);
        });

        // Waybill status validation on submit
        // Waybill status validation on submit - ONLY FOR CREATE, NOT EDIT
        @if(!$isEdit)
        $('#AccidentForm').on('submit', function(e) {
            var waybillOption = $('#waybill_id_accident option:selected');
            var status = String(waybillOption.data('status'));
            if (status !== '2') {
                var modal = $('#waybillWarningModal');
                modal.modal('show');
                // Reset form after modal closes
                modal.on('hidden.bs.modal', function() {
                    $('#AccidentForm')[0].reset();
                    // Reset select2
                    $('#waybill_id_accident').val('').trigger('change');
                    $('#vehicle_id_accident').val('').trigger('change');
                });
                e.preventDefault();
                return false;
            }
        });
        @endif

        var $waybillSelect = $('#waybill_id_accident');
        var $vehicleSelect = $('#vehicle_id_accident');
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

        // Filter waybills by selected vehicle and auto-select first match
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

        // Toggle Loaded fields
        function toggleLoadedFields($scope) {
            $scope = $scope && $scope.length ? $scope : $(document);
            var val = $scope.find('select#nature_of_accident').val();
            if (val === 'Loaded') {
                $scope.find('#content_div').fadeIn();
                $scope.find('#damage_div').fadeIn();
            } else {
                $scope.find('#content_div').fadeOut();
                $scope.find('#damage_div').fadeOut();
                $scope.find('#content, #damage_extend').val('');
            }
        }

        $('select#nature_of_accident').on('change', function() {
            toggleLoadedFields($(this).closest('form'));
        });
        toggleLoadedFields($('select#nature_of_accident').closest('form'));

        // Toggle Fatality fields
        function toggleFatalityFields() {
            var val = $('#is_fatality').val();
            if (val === 'yes') {
                $('#fatality_details_div').fadeIn();
                $('#fatality_type').attr('required', true);
            } else {
                $('#fatality_details_div').fadeOut();
                $('#fatality_type').attr('required', false).val('');
            }
        }
        $('#is_fatality').on('change', toggleFatalityFields);
        toggleFatalityFields();
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
