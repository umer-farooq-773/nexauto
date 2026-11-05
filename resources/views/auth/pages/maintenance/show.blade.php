<x-layouts.auth page-title="{{ __('Maintenance Details') }}" sub-title="">

    <div class="row mb-4">
        <div class="col-12">

            <div class="d-flex justify-content-end gap-2 flex-wrap">
                <button type="button" onclick="printDetails()" class="btn btn-primary">
                    <i class="fas fa-file-pdf me-1"></i> PDF Download
                </button>
                @if((string)($data->payment_status ?? '') !== '2')
                    <a href="{{ route('maintenance.edit', $data->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> {{ __('Edit Maintenance') }}
                    </a>
                @endif
                <x-auth.incident-action-buttons
                    :data="$data"
                    approve-route="maintenance.client_approve"
                    payment-route="maintenance.payment_process"
                    payment-cost-label="Cost"
                    :payment-cost-amount="$data->cost"
                    :uses-proceed-payment-status="true"
                    modal-target="#statusChangeModal"
                    :remarks="$data->client_approved_comment"
                    table="vehicle_maintenances"
                />
            </div>
        </div>
    </div>
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    @include('auth.pages.maintenance.show-detail')

</x-layouts.auth>

