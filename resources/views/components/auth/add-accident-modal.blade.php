@props(['vehicles' => [], 'waybills' => [], 'hasAllocated' => false])

@php
    $canCreate = count($waybills) > 0;
    $showStatusWarning = !$canCreate && $hasAllocated;
@endphp

<button class="btn btn-sm btn-primary px-4" type="button"
    @if($canCreate)
        data-bs-toggle="modal" data-bs-target="#addAccidentModal"
    @else
        data-bs-toggle="modal" data-bs-target="#waybillWarningModal"
    @endif
>
    + Add New Accident
</button>

<x-auth.modal modal-id="addAccidentModal" modal-title="Create New Accident Record">
    <div class="modal-body p-4">
        <x-auth.add-accident-form route-action="{{ route('accident.store') }}" :vehicles="$vehicles" :waybills="$waybills" />
    </div>
</x-auth.modal>

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
                <h5 class="text-dark fw-bold mb-3">
                    @if($showStatusWarning)
                        No Waybills In Transit
                    @else
                        No Active Waybills
                    @endif
                </h5>
                <p class="text-muted">
                    @if($showStatusWarning)
                        Your waybill incident status is not set to <strong>In Transit</strong>. Kindly update it to In Transit first, after which you will be able to create the Incident & Intervention form.
                    @else
                        There are no active waybills available to record an incident. Please create a waybill and dispatch it first.
                    @endif
                </p>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-secondary px-5 rounded-pill fw-bold" data-bs-dismiss="modal">
                    @if($showStatusWarning)
                        I'll Update It First
                    @else
                        Close
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>

@push('auth_scripts')
    @if ($errors->any() || (session('error') && old('waybill_id')))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var myModal = new bootstrap.Modal(document.getElementById('addAccidentModal'));
            myModal.show();
        });
    </script>
    @endif
@endpush
