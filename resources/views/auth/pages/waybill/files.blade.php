<x-layouts.auth page-title="{{ __('Waybill Files') }} - {{ $waybill->waybill_number }}" sub-title="Manage files for this waybill">

    <x-slot:actionButton>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i> {{ __('Upload New File') }}
        </button>
    </x-slot:actionButton>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Files Log Table') }}: {{ $waybill->waybill_number }}" :data="$files">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark bg-light">
                        <th scope="col" class="py-3" style="width: 80px;">S.No</th>
                        <th scope="col" class="py-3">File Name</th>
                        <th scope="col" class="py-3">Attached File</th>
                        <th scope="col" class="py-3">Created At</th>
                        <th scope="col" class="py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $index => $file)
                    <tr>
                        <td class="align-middle fw-bold">{{ $files->firstItem() + $index }}</td>
                        <td class="align-middle">
                            <span class="text-dark">{{ $file->name }}</span>
                        </td>
                        <td class="align-middle">
                            <a href="{{ $file->url }}" target="_blank" class="btn btn-sm btn-light border text-primary rounded-pill px-3">
                                <i class="fa-solid fa-file-pdf me-2"></i>View Document
                            </a>
                        </td>
                        <td class="align-middle">
                            <span class="text-muted">{{ $file->created_at->format('d M Y, h:i A') }}</span>
                        </td>
                        <td class="align-middle text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ $file->url }}" target="_blank">
                                            <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="javascript:void(0);" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#replaceFileModal"
                                            data-file-id="{{ $file->id }}"
                                            data-file-name="{{ $file->name }}">
                                            <i class="fa-solid fa-arrows-rotate me-2 text-success"></i> Replace
                                        </a>
                                    </li>
                                    {{-- <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('waybill.file.destroy', [$waybill->id, $file->id]) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this file?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="fa-solid fa-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li> --}}
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
            
            <div class="mt-4 px-3 pb-3">
                <a href="{{ route('waybill.index') }}" class="btn btn-link text-muted text-decoration-none">
                    <i class="fa-solid fa-chevron-left me-1"></i> {{ __('Back to Waybills') }}
                </a>
            </div>
        </x-all-list>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade shadow-lg" id="uploadFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-bold"><i class="fa-solid fa-upload me-2"></i>{{ __('Upload Waybill File') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('waybill.file.store', $waybill->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Custom File Name') }} (Optional)</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0 px-3" placeholder="e.g. Loading Receipt">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Select File') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-lg bg-light border-0" required>
                            <div class="mt-2 text-muted small"><i class="fa-solid fa-circle-info me-1"></i>Max size: 10MB (PDF, Image, etc.)</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Replace Modal -->
    <div class="modal fade shadow-lg" id="replaceFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-bold"><i class="fa-solid fa-arrows-rotate me-2"></i>{{ __('Replace Current File') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="replaceFileForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-soft-success border-0 mb-4 bg-light">
                            <p class="small text-muted mb-0">Currently replacing: <strong id="replaceFileName" class="text-dark"></strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">{{ __('Select New File') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-lg bg-light border-0" required>
                            <div class="mt-2 text-muted small"><i class="fa-solid fa-triangle-exclamation me-1"></i>New file will overwrite the existing document.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4 text-white shadow-sm">Update File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const replaceModal = document.getElementById('replaceFileModal');
            if (replaceModal) {
                replaceModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const fileId = button.getAttribute('data-file-id');
                    const fileName = button.getAttribute('data-file-name');
                    
                    const form = document.getElementById('replaceFileForm');
                    const nameSpan = document.getElementById('replaceFileName');
                    
                    nameSpan.textContent = fileName;
                    form.action = "{{ route('waybill.file.replace', [$waybill->id, ':fileId']) }}".replace(':fileId', fileId);
                });
            }
        });
    </script>
    @endpush

</x-layouts.auth>
