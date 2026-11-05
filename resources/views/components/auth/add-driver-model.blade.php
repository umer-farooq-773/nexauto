@can('add_driver')
    <x-auth.input-button btn-class="btn btn-sm btn-primary px-4" btn-type="submit" btn-value="{{ __('+ Add New Driver') }}" data-bs-toggle="modal"
        data-bs-target="#addDriverModal" />

    <x-auth.modal modal-id="addDriverModal" modal-title="Create new driver">
    <div class="modal-body p-4">
        <x-auth.add-driver-form route-action="{{route('driver.store')}}"/>
    </div>
</x-auth.modal>

@push('auth_scripts')
    @if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var myModal = new bootstrap.Modal(document.getElementById('addDriverModal'));
            myModal.show();
        });
    </script>
    @endif
@endpush

@endcan
