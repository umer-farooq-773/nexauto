@props(['routeAction', 'data' => null, 'vehicles' => [], 'waybills' => [], 'isEdit' => false])

@php $result = $data; @endphp

<x-auth.form form-id="ImpoundmentForm" form-action="{{ $routeAction }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6 form-floating  select2-parent">
        <select name="vehicle_id" id="vehicle_id_impound" class="form-select searchable-select" required>
            <option value="">Select Asset</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ ($result?->vehicle_id ?? old('vehicle_id')) == $vehicle->id ? 'selected' : '' }}>
{{ $vehicle->fleet_no }}
                </option>
            @endforeach
        </select>
        <label for="vehicle_id_impound">{{ __('Asset') }} *</label>
    </div>

    <div class="col-md-6 form-floating  select2-parent">
        <select name="waybill_id" id="waybill_id_impound" class="form-select searchable-select" required>
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
        <label for="waybill_id_impound">{{ __('Waybill') }} *</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="impound_date" id="impound_date" required="true"
            place="" val="{{ old('impound_date', optional($result)->impound_date ? \Carbon\Carbon::parse($result->impound_date)->format('Y-m-d') : '') }}"
            extraclasses=""
            label="{{ __('Impound Date') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="number" name="cost" id="cost" required="true"
            place="" val="{{ $result?->cost ?? old('cost') }}" extraclasses="" label="{{ __('Cost') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.geoapify-location-input 
            name="impound_location" 
            id="impound_location" 
            label="{{ __('Impound Location') }}"
            placeholder="{{ __('Search for location (e.g., Lagos Checkpoint)') }}"
            value="{{ $result?->impound_location ?? old('impound_location') }}"
            lat="{{ $result?->impound_location_lat ?? old('impound_location_lat') }}"
            lng="{{ $result?->impound_location_lng ?? old('impound_location_lng') }}"
            :required="true" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="impound_authority" id="impound_authority" required="false"
            place="{{ __('e.g. FRSC, Police') }}" val="{{ $result?->impound_authority ?? old('impound_authority') }}"
            extraclasses="" label="{{ __('Authority') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="reason" id="reason" required="false"
            place="{{ __('Reason for impoundment') }}" val="{{ $result?->reason ?? old('reason') }}"
            extraclasses="" label="{{ __('Reason') }}" />
    </div>

    {{-- Release Date field: Only visible in Edit form, hidden in Create form --}}
    @if($isEdit)
    <div class="col-md-6">
        <x-auth.input-field type="date" name="release_date" id="release_date" required="false"
            place="" val="{{ old('release_date', $result?->release_date?->format('Y-m-d')) }}" extraclasses="" label="{{ __('Release Date') }}" />
    </div>
    @endif

    <div class="col-md-6 form-floating mb-3">
        @php
            $authUser = auth()->user();
            $canEditStatus = $isEdit
                && ($data?->client_approved ?? false)
                && $authUser
                && $authUser->hasRole([\App\Constants\Constants::ADMIN, \App\Constants\Constants::SUPERADMIN, \App\Constants\Constants::CHAMPS_MANAGEMENT]);
        @endphp
        <select name="status" id="status" class="form-select shadow-sm" {{ ($isEdit && !$canEditStatus) ? 'disabled' : '' }}>
            <option value="0" {{ ($result?->status ?? old('status', '0')) == '0' ? 'selected' : '' }}>Impounded</option>
            <option value="1" {{ ($result?->status ?? old('status')) == '1' ? 'selected' : '' }}>Released</option>
        </select>
        <label for="status">{{ __('Status') }}</label>
        @if($isEdit && !$canEditStatus)
            <small class="text-muted ms-1">
                @if(!($data?->client_approved ?? false))
                    Awaiting Client Director approval to unlock status change.
                @else
                    You do not have permission to change the status.
                @endif
            </small>
        @endif
    </div>
    @if($isEdit && !$canEditStatus)
        <input type="hidden" name="status" value="{{ $result?->status ?? '0' }}">
    @endif

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
            btn-type="submit" btn-value="{{ $isEdit ? __('Update Record') : __('Submit') }}" id="impoundSubmitBtn" />
    </div>
</x-auth.form>

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
                <p class="text-muted">Your waybill incident status is not set to <strong>In Transit</strong>. Kindly update it to In Transit first, after which you will be able to create the Incident & Intervention form.</p>
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
// Set max date to today for impound_date to prevent future dates
        var today = new Date().toISOString().split('T')[0];
        $('#impound_date').attr('max', today);

        // Validate on date change - show error if future date selected
        $('#impound_date').on('change', function() {
            var selectedDate = $(this).val();
            var maxDate = $(this).attr('max');

            if (selectedDate > maxDate) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.text-danger').length) {
                    $(this).after('<span class="text-danger" style="font-size: 12px !important;">The impound date cannot be in the future.</span>');
                }
                $(this).val('');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.text-danger').remove();
            }
        });

        // Waybill status validation on submit - ONLY FOR CREATE, NOT EDIT
        @if(!$isEdit)
                $('#ImpoundmentForm').on('submit', function(e) {
                    var waybillOption = $('#waybill_id_impound option:selected');
                    var status = waybillOption.data('status');
                    if (status != 2) {
                        var modal = $('#waybillWarningModal');
                        modal.modal('show');
                        // Reset form after modal closes
                        modal.on('hidden.bs.modal', function() {
                            $('#ImpoundmentForm')[0].reset();
                            // Reset select2
                            $('#waybill_id_impound').val('').trigger('change');
                            $('#vehicle_id_impound').val('').trigger('change');
                        });
                        e.preventDefault();
                        return false;
                    }
                });
        @endif
        var $waybillSelect = $('#waybill_id_impound');
        var $vehicleSelect = $('#vehicle_id_impound');
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

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #212529 !important;
        line-height: 28px;
    }

    .select2-container{
        font-weight: 400 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 58px !important;
    }

    .form-floating > .select2-container + label {
        transform: scale(.85) translateY(-.5rem) translateX(.15rem);
        opacity: .65;
        z-index: 5;
        margin-top: 5px;
    }
</style>
@endpush
