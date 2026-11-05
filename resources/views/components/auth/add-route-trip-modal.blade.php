@can('add_route_trip')
    <x-auth.input-button btn-class="btn btn-sm btn-primary px-4" btn-type="button"
        btn-value="{{ __('+ Add New Route') }}" data-bs-toggle="modal"
        data-bs-target="#addRouteTripModal" />

    <x-auth.modal modal-id="addRouteTripModal" modal-title="Create New Trip & Route">
        <div class="modal-body p-4">
            <x-auth.add-route-trip-form route-action="{{ route('route_trip.store') }}" />
        </div>
    </x-auth.modal>

    @push('auth_scripts')
        @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var myModal = new bootstrap.Modal(document.getElementById('addRouteTripModal'));
                myModal.show();
            });
        </script>
        @endif
    @endpush
@endcan
