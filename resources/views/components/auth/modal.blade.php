<div class="modal fade" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    role="dialog" aria-labelledby="{{ $modalId }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header background-primary px-4 text-white">
                <h5 class="modal-title fw-bold">{{ $modalTitle }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    id="modalCloseButton"></button>
            </div>
            {{ $slot }}

        </div>
    </div>
</div>