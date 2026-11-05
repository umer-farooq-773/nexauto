<button class="btn btn-sm btn-primary px-4" type="button"
    data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
    + Add New Maintenance
</button>

<x-auth.modal modal-id="addMaintenanceModal" modal-title="Create New Maintenance Record">
    <div class="modal-body p-4">
        <x-auth.add-maintenance-form route-action="{{ route('maintenance.store') }}" :vehicles="$vehicles" :maintenance-types="$maintenanceTypes" />
    </div>
</x-auth.modal>

@push('auth_scripts')
    @if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var myModal = new bootstrap.Modal(document.getElementById('addMaintenanceModal'));
            myModal.show();
        });
    </script>
    @endif
@endpush
