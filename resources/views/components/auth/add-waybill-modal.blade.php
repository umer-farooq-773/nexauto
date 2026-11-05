@can('add_waybill')
    <x-auth.input-button btn-class="btn btn-sm btn-primary px-4 w-auto" btn-type="button"
        btn-value="{{ __('+ Add New Waybill') }}" data-bs-toggle="modal"
        data-bs-target="#addWaybillModal" />

    {{-- Custom modal with scrollable body --}}
    <div class="modal fade" id="addWaybillModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header background-primary px-4 text-white">
                    <h5 class="modal-title fw-bold">Create New Waybill</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <x-auth.add-waybill-form
                        route-action="{{ route('waybill.store') }}"
                        :vehicles="$vehicles"
                        :drivers="$drivers"
                        :route-trips="$routeTrips" />
                </div>
            </div>
        </div>
    </div>

    @push('auth_scripts')
        @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var myModal = new bootstrap.Modal(document.getElementById('addWaybillModal'));
                myModal.show();
            });
        </script>
        @endif
    @endpush
@endcan
