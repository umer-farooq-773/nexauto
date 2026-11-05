<x-layouts.auth page-title="{{ __('Impoundment Details') }}" sub-title="">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <x-auth.view-map-button
                    incident-type="impoundment"
                    :incident-id="$data->id"
                    :waybill-id="$data->waybill_id"
                    :vehicle-id="$data->vehicle_id" />
                <button type="button" onclick="printDetails()" class="btn btn-primary">
                    <i class="fas fa-file-pdf me-1"></i> PDF Download
                </button>
                <x-auth.incident-action-buttons :data="$data" approve-route="impoundment.client_approve"
                    payment-route="impoundment.payment_process" payment-cost-label="Cost" :payment-cost-amount="$data->cost"
                    :remarks="$data->remarks"
                    table="impoundment"
                    :uses-proceed-payment-status="true" modal-target="#statusChangeModal" />
            </div>
        </div>
    </div>



    @include('auth.pages.incident.impoundment.show-detail')


<script>
$(document).ready(function() {
    $('.status-select').on('change', function() {
        var status = $(this).val();
        var id = $(this).data('id');

        $.ajax({
            url: '/status/' + id,
            type: 'POST',
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    // Show success message or update UI
                    alert('Status updated successfully!');
                }
            },
            error: function(xhr) {
                alert('Error updating status!');
                console.log(xhr.responseText);
            }
        });
    });
});
</script>
</x-layouts.auth>
