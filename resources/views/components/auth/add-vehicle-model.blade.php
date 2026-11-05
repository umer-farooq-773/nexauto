@can('add_vehicle')
    <x-auth.input-button btn-class="btn btn-sm btn-primary px-4" btn-type="submit" btn-value="{{ __('+ Add New Asset') }}" data-bs-toggle="modal"
        data-bs-target="#addVehicleModal" />

    <x-auth.modal modal-id="addVehicleModal" modal-title="Create new asset">
    <div class="modal-body p-4">
        <x-auth.vehicle-form route-action="{{route('vehicle.store')}}"/>
    </div>
</x-auth.modal>

@push('auth_scripts')
    @if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var myModal = new bootstrap.Modal(document.getElementById('addVehicleModal'));
            myModal.show();
        });
    </script>
    @endif
@endpush

@endcan
