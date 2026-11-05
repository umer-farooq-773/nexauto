@can('add_driver_mate')
    <x-auth.input-button btn-class="btn btn-sm btn-primary px-4" btn-type="button" btn-value="{{ __('+ Add New Driver Mate') }}" data-bs-toggle="modal"
        data-bs-target="#addDriverMateModal" />

    <x-auth.modal modal-id="addDriverMateModal" modal-title="Create New Driver Mate">
        <div class="modal-body p-4">
            <x-auth.add-driver-mate-form route-action="{{ route('driver_mate.store') }}" />
        </div>
    </x-auth.modal>

    @push('auth_scripts')
        @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var myModal = new bootstrap.Modal(document.getElementById('addDriverMateModal'));
                myModal.show();
            });
        </script>
        @endif
    @endpush
@endcan
