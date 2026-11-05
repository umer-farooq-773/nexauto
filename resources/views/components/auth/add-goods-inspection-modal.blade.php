@props(['vehicles' => [], 'waybills' => []])

<button class="btn btn-sm btn-primary px-4" type="button" data-bs-toggle="modal"
    data-bs-target="#addInspectionModal">
    + Add New Inspection
</button>

<x-auth.modal modal-id="addInspectionModal" modal-title="Create New Goods Inspection Record" size="modal-xl">
    <div class="modal-body p-4">
        <x-auth.add-goods-inspection-form route-action="{{ route('goods_inspection.store') }}" :vehicles="$vehicles" :waybills="$waybills" />
    </div>
</x-auth.modal>

@push('auth_scripts')
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var myModal = new bootstrap.Modal(document.getElementById('addInspectionModal'));
                myModal.show();
            });
        </script>
    @endif
@endpush
