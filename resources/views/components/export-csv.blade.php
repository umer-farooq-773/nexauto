@props([
    'table',
    'columns' => [],
    'exportRoute' => route('export.csv.export'),
    'columnsRoute' => route('export.csv.columns'),
])

@php
    $modalId = 'exportCsvModal_' . \Illuminate\Support\Str::slug($table, '_');
    $formId = 'exportForm_' . \Illuminate\Support\Str::slug($table, '_');
    $tableNameFormatted = ucwords(str_replace('_', ' ', $table));
@endphp

<div>
    <!-- Export Button with standard Bootstrap data attributes -->
    <button type="button"
            class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2"
            data-bs-toggle="modal"
            data-bs-target="#{{ $modalId }}"
            id="btn_{{ $modalId }}">
        <i class="fa-solid fa-file-csv"></i>
        <span>Export CSV</span>
    </button>

    <!-- Modal Dialog -->
    <div class="modal fade export-csv-modal"
         id="{{ $modalId }}"
         tabindex="-1"
         aria-labelledby="{{ $modalId }}Label"
         aria-hidden="true"
         data-table="{{ $table }}">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Modal Header -->
                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center mb-0" id="{{ $modalId }}Label">
                        <i class="fa-solid fa-file-csv text-success me-2 fs-5"></i>
                        Export {{ $tableNameFormatted }} CSV
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Error Alert -->
                    <div class="alert alert-danger alert-dismissible fade show d-none export-error-alert" role="alert">
                        <span class="export-error-text"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <!-- Loading Spinner -->
                    <div class="export-loading-state text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted fw-semibold">Processing your export request...</p>
                    </div>

                    <form id="{{ $formId }}" class="export-csv-form">
                        <!-- Date Range Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">
                                <i class="fa-regular fa-calendar me-2 text-primary"></i>Date Range
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="{{ $modalId }}_from_date" class="form-label small fw-semibold">From Date</label>
                                    <input type="date"
                                           class="form-control export-from-date"
                                           id="{{ $modalId }}_from_date"
                                           name="from_date"
                                           max="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="{{ $modalId }}_to_date" class="form-label small fw-semibold">To Date</label>
                                    <input type="date"
                                           class="form-control export-to-date"
                                           id="{{ $modalId }}_to_date"
                                           name="to_date"
                                           max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="fa-solid fa-circle-info me-1"></i> Leave blank to export across all dates
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Columns Selection -->
                        <div class="mb-2">
                            <h6 class="fw-bold mb-2">
                                <i class="fa-regular fa-square-check me-2 text-primary"></i>Select Columns
                            </h6>

                            <!-- Select All Checkbox -->
                            <div class="form-check mb-3">
                                <input type="checkbox"
                                       class="form-check-input export-select-all"
                                       id="{{ $modalId }}_selectAll"
                                       checked>
                                <label class="form-check-label fw-bold text-dark" for="{{ $modalId }}_selectAll">
                                    Select All
                                </label>
                            </div>

                            <div class="row g-2 export-columns-list">
                                @foreach ($columns as $column)
                                    @php
                                        $formattedCol = ucwords(str_replace('_', ' ', $column));
                                        $colInputId = $modalId . '_col_' . $column;
                                    @endphp
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   class="form-check-input export-column-checkbox"
                                                   id="{{ $colInputId }}"
                                                   name="columns[]"
                                                   value="{{ $column }}"
                                                   checked>
                                            <label class="form-check-label text-truncate d-block" for="{{ $colInputId }}" title="{{ $formattedCol }}">
                                                {{ $formattedCol }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Validation Message -->
                            <div class="text-danger mt-2 small d-none export-validation-error">
                                Please select at least one column.
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success btn-do-export" data-form="#{{ $formId }}" data-modal="#{{ $modalId }}">
                        <span class="btn-text">
                            <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                        </span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Exporting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('auth_scripts')
<script>
$(document).ready(function() {
    const modalEl = document.getElementById('{{ $modalId }}');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    const $modal = $('#{{ $modalId }}');
    const $form = $('#{{ $formId }}');
    const $selectAll = $modal.find('.export-select-all');
    let $colCheckboxes = $modal.find('.export-column-checkbox');
    const $exportBtn = $modal.find('.btn-do-export');
    const $validationError = $modal.find('.export-validation-error');
    const $errorAlert = $modal.find('.export-error-alert');
    const $errorText = $modal.find('.export-error-text');
    const $loadingState = $modal.find('.export-loading-state');

    let activeSortCol = '';
    let activeSortDir = 'desc';
    let columnsLoaded = false;

    function renderColumns(columns) {
        const container = $modal.find('.export-columns-list').empty();

        columns.forEach(function(column) {
            const formattedColumn = column.replace(/_/g, ' ')
                .replace(/\b\w/g, character => character.toUpperCase());
            const inputId = '{{ $modalId }}_col_' + column;
            const item = $('<div>', { class: 'col-md-4 col-sm-6 mb-2' });
            const check = $('<div>', { class: 'form-check' });
            const input = $('<input>', {
                type: 'checkbox',
                class: 'form-check-input export-column-checkbox',
                id: inputId,
                name: 'columns[]',
                value: column,
                checked: true
            });
            const label = $('<label>', {
                class: 'form-check-label text-truncate d-block',
                for: inputId,
                title: formattedColumn,
                text: formattedColumn
            });

            check.append(input, label);
            item.append(check);
            container.append(item);
        });

        $colCheckboxes = $modal.find('.export-column-checkbox');
        $selectAll.prop('checked', columns.length > 0);
    }

    async function loadColumns() {
        if (columnsLoaded) {
            return;
        }

        $exportBtn.prop('disabled', true);

        try {
            const response = await fetch('{{ $columnsRoute }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ table: '{{ $table }}' })
            });
            const result = await response.json();

            if (!response.ok || !result.success || !Array.isArray(result.columns)) {
                throw new Error(result.message || 'Unable to load export columns.');
            }

            renderColumns(result.columns);
            columnsLoaded = true;
        } catch (err) {
            $errorText.text(err.message || 'Unable to load export columns.');
            $errorAlert.removeClass('d-none');
        } finally {
            $exportBtn.prop('disabled', false);
        }
    }

    // Helper to detect table search and sort state from the current page's DataTable
    function detectTableState() {
        let activeSearch = '';

        if ($.fn.DataTable) {
            $('.dataTable, table.dataTable, #maintenanceHistoryTable, table').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    const dt = $(this).DataTable();
                    const dtSearch = dt.search();
                    if (dtSearch && !activeSearch) {
                        activeSearch = dtSearch;
                    }
                    const order = dt.order();
                    if (order && order.length > 0 && !activeSortCol) {
                        const colIdx = order[0][0];
                        activeSortDir = order[0][1] || 'asc';
                        const header = $(dt.column(colIdx).header());
                        activeSortCol = header.data('column') || header.text().trim().toLowerCase().replace(/\s+/g, '_');
                    }
                }
            });
        }

        if (!activeSearch) {
            const $searchInput = $('.dataTables_filter input, input[name="search"], .keyword-search-input, input[type="search"]');
            if ($searchInput.length > 0 && $searchInput.val()) {
                activeSearch = $searchInput.val().trim();
            }
        }

        if (!activeSearch) {
            const urlParams = new URLSearchParams(window.location.search);
            activeSearch = urlParams.get('search') || urlParams.get('q') || '';
        }

        return activeSearch.trim();
    }

    // Auto sync modal state when opened
    $modal.on('show.bs.modal', function() {
        if ($.fn.select2) {
            $('.select2-hidden-accessible').select2('close');
        }
        $errorAlert.addClass('d-none');
        $validationError.addClass('d-none');
        loadColumns();

        // Auto sync URL date filters if not already set
        const urlParams = new URLSearchParams(window.location.search);
        const urlFromDate = urlParams.get('date_from') || urlParams.get('from_date');
        const urlToDate = urlParams.get('date_to') || urlParams.get('to_date');
        if (urlFromDate && !$form.find('.export-from-date').val()) {
            $form.find('.export-from-date').val(urlFromDate);
        }
        if (urlToDate && !$form.find('.export-to-date').val()) {
            $form.find('.export-to-date').val(urlToDate);
        }
    });

    // Toggle all columns
    $selectAll.on('change', function() {
        const isChecked = $(this).is(':checked');
        $modal.find('.export-column-checkbox').prop('checked', isChecked);
        $validationError.addClass('d-none');
    });

    // Update select all state when individual checkboxes change
    $modal.on('change', '.export-column-checkbox', function() {
        const $checkboxes = $modal.find('.export-column-checkbox');
        const total = $checkboxes.length;
        const checkedCount = $checkboxes.filter(':checked').length;
        $selectAll.prop('checked', total === checkedCount);
        $validationError.addClass('d-none');
    });

    // Export CSV submission
    $exportBtn.on('click', async function(e) {
        e.preventDefault();

        const selectedCols = [];
        $modal.find('.export-column-checkbox:checked').each(function() {
            selectedCols.push($(this).val());
        });

        if (selectedCols.length === 0) {
            $validationError.removeClass('d-none');
            return;
        }

        $validationError.addClass('d-none');
        $errorAlert.addClass('d-none');

        // Auto-detect the search keyword directly from the table's search box
        const searchKeyword = detectTableState();
        const fromDate = $form.find('.export-from-date').val();
        const toDate = $form.find('.export-to-date').val();
        const table = '{{ $table }}';

        // UI loading state
        $exportBtn.prop('disabled', true);
        $exportBtn.find('.btn-text').addClass('d-none');
        $exportBtn.find('.btn-spinner').removeClass('d-none');
        $loadingState.removeClass('d-none');
        $form.addClass('d-none');

        try {
            const formData = new FormData();
            formData.append('table', table);
            formData.append('search', searchKeyword);
            formData.append('from_date', fromDate || '');
            formData.append('to_date', toDate || '');

            if (activeSortCol) {
                formData.append('sort_column', activeSortCol);
                formData.append('sort_direction', activeSortDir);
            }

            // Forward any other active URL parameters (category, action, vehicle_id, status, etc.)
            const pageUrlParams = new URLSearchParams(window.location.search);
            pageUrlParams.forEach((val, key) => {
                if (!['search', 'q', 'from_date', 'to_date', 'date_from', 'date_to', 'table', 'columns'].includes(key)) {
                    if (val && !formData.has(key)) {
                        formData.append(key, val);
                    }
                }
            });

            selectedCols.forEach(col => {
                formData.append('columns[]', col);
            });

            const response = await fetch('{{ $exportRoute }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Export failed');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;

            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = `${table}_${new Date().toISOString().split('T')[0]}.csv`;
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (filenameMatch && filenameMatch[1]) {
                    filename = filenameMatch[1].replace(/['"]/g, '');
                }
            }

            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);

            // Hide modal
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) {
                bsModal.hide();
            }

            if (typeof showToaster === 'function') {
                showToaster('success', 'Export completed successfully!', 'Success');
            }
        } catch (err) {
            $errorText.text(err.message || 'An error occurred during export.');
            $errorAlert.removeClass('d-none');
        } finally {
            $exportBtn.prop('disabled', false);
            $exportBtn.find('.btn-text').removeClass('d-none');
            $exportBtn.find('.btn-spinner').addClass('d-none');
            $loadingState.addClass('d-none');
            $form.removeClass('d-none');
        }
    });
});
</script>
@endpush

@push('auth_styles')
<style>
    .export-column-checkbox:checked + label {
        font-weight: 600;
        color: #0d6efd;
    }
    .export-csv-modal .form-check-label {
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.875rem;
        line-height: 1.5;
        word-break: break-word;
    }
    .export-csv-modal .form-check-label:hover {
        color: #0d6efd;
    }
    .export-csv-modal .form-check-input {
        margin-top: 0.25rem;
        flex-shrink: 0;
    }
</style>
@endpush
