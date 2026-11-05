{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<x-layouts.auth page-title="{{ __('Accident Details') }}" sub-title="">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2 flex-wrap">
                <x-auth.view-map-button
                    incident-type="accident"
                    :incident-id="$data->id"
                    :waybill-id="$data->waybill_id"
                    :vehicle-id="$data->vehicle_id" />
                <button type="button" onclick="printDetails()" class="btn btn-gradient-blue">
                    <i class="ri-file-pdf-2-line me-1"></i> {{ __('PDF Download') }}
                </button>
                <x-auth.incident-action-buttons
                    :data="$data"
                    approve-route="accident.client_approve"
                    payment-route="accident.payment_process"
                    payment-cost-label="Damage Cost"
                    :payment-cost-amount="$data->damage_cost"
                    :uses-proceed-payment-status="true"
                    modal-target="#statusChangeModal"
                    :remarks="$data->client_approved_comment"
                    table="accidents"
                />
            </div>
        </div>
    </div>

    @include('auth.pages.incident.accident.show-detail')

    <!-- Status Change Modal -->
    @php
        $user = auth()->user();
        $canChangeStatus = $user && $user->hasRole([\App\Constants\Constants::ADMIN, \App\Constants\Constants::SUPERADMIN, \App\Constants\Constants::CHAMPS_MANAGEMENT]);
        $clientApproved = (bool) ($data->client_approved ?? false);
        $paymentApproved = (string) ($data->payment_status ?? '') === '2';
    @endphp
    @if($clientApproved && $paymentApproved && $canChangeStatus)
    <div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="statusChangeModalLabel">
                        <i class="ri-refresh-line text-warning me-2"></i>
                        {{ __('Change Accident Status') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="statusForm" method="POST" action="{{ route('accident.status_update', $data->id) }}">
                    @method('PATCH')
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Status') }}</label>
                            <select name="status" class="form-select" required>
                                <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Remarks (Optional)') }}</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ $data->description ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-warning rounded-pill">
                            <i class="ri-refresh-line me-1"></i>{{ __('Update Status') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <style media="print">
        body * { visibility: hidden; }
        .print-content, .print-content * { visibility: visible; }
        .print-content { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        @page { margin: 1in; }
    </style>

    <style>
        .btn-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none; color: #ffffff; font-weight: 600;
            border-radius: 50px; padding: 0.55rem 1.25rem;
            transition: all 0.25s ease;
        }
        .btn-gradient-blue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff; transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }
    </style>

    <script>
        function printDetails() { window.print(); }
        var statusForm = document.getElementById('statusForm');
        if (statusForm) {
            statusForm.addEventListener('submit', function() {
                setTimeout(function() { location.reload(); }, 1000);
            });
        }
    </script>
</x-layouts.auth>
