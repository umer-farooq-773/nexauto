<x-layouts.auth page-title="{{ __('Report Management') }}" sub-title="Manage Reports from this page">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* Custom searchable combobox styling (from map-tracking) */
        .custom-combobox {
            position: relative;
            width: 100%;
        }
        .custom-combobox input {
            width: 100%;
            padding: 9px 34px 9px 12px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background: #fff;
            font-size: 14px;
        }
        .custom-combobox input:focus {
            outline: 2px solid #eef2ff;
            border-color: #4f46e5;
        }
        .custom-combobox .cb-clear {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 0;
            background: transparent;
            color: #9599ad;
            font-size: 15px;
            cursor: pointer;
            display: none;
            border-radius: 50%;
        }
        .custom-combobox .cb-clear:hover {
            background: #e6e8f0;
            color: #1a1b2e;
        }
        .custom-combobox .cb-clear.show {
            display: block;
        }
        .custom-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 260px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 4px 14px rgba(17, 17, 40, 0.07);
            z-index: 1000;
        }
        .custom-dropdown.open {
            display: block;
        }
        .dropdown-item-custom {
            padding: 9px 12px;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        .dropdown-item-custom:hover,
        .dropdown-item-custom.active {
            background: #eef2ff;
        }
        .dropdown-item-custom mark {
            background: #fde68a;
            border-radius: 2px;
            padding: 0 2px;
        }
        .dropdown-empty {
            padding: 10px 12px;
            font-size: 12.5px;
            color: #9599ad;
        }

        /* Report table styling */
        .table-wrapper {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1.25rem;
            transition: box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .table-wrapper:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        /* PRIMARY COLOR TABLE HEAD */
        .table thead.table-primary th {
            background: #4f46e5 !important;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 0.875rem 0.75rem;
        }

        /* Pagination styling */
        .table-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0.5rem;
            border-top: 2px solid #e9ecef;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pagination-controls button {
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .pagination-controls button:hover:not(:disabled) {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .pagination-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-controls .page-info {
            padding: 0.375rem 0.75rem;
            background: #f8f9fa;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .pagination-controls select {
            padding: 0.375rem 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .table thead th i.bi-arrow-down-up {
            opacity: 0.5;
            transition: opacity 0.2s;
        }

        .table thead th:hover i.bi-arrow-down-up {
            opacity: 1;
        }

        .table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
            border-color: #e9ecef;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }

        .table-hover tbody tr:hover {
            background-color: #eef2ff;
            transition: background-color 0.2s ease;
        }

        .table-footer {
            padding-top: 0.75rem;
            border-top: 2px solid #dee2e6;
            font-size: 0.8rem;
        }

        /* Badge styling */
        .badge.rounded-pill {
            padding: 0.35em 0.85em;
            font-weight: 500;
        }

        /* Search box in table */
        .table-wrapper input[type="text"] {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            transition: all 0.2s;
        }

        .table-wrapper input[type="text"]:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
            outline: none;
        }

        /* Quick range buttons */
        .quick-range {
            transition: all 0.2s ease;
        }

        .quick-range:hover {
            transform: translateY(-1px);
        }

        .quick-range.active {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        /* Loading state */
        #report-loading {
            text-align: center;
            padding: 3rem 1rem;
        }

        #report-loading .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }

        /* Empty state */
        #report-empty {
            text-align: center;
            padding: 3rem 1rem;
        }

        /* Chart container */
        #report-chart-container {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        /* Map container */
        #wialon-report-map {
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Alert positioning */
        .alert {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Export dropdown */
        .dropdown-menu {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        /* ===== RESPONSIVE FIXES ===== */
        @media (max-width: 992px) {
            .col-lg-3 {
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .table {
                font-size: 0.75rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.5rem 0.4rem;
            }

            .table-pagination {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .pagination-controls {
                justify-content: center;
            }

            .card-body {
                padding: 1rem;
            }

            .table-wrapper {
                padding: 0.75rem;
            }

            .quick-range {
                font-size: 0.7rem;
                padding: 0.25rem 0.4rem;
            }

            .btn-group.w-100 {
                flex-wrap: wrap;
            }

            .btn-group.w-100 .btn {
                flex: 1 0 auto;
                margin-bottom: 0.25rem;
            }

            #report-chart-container {
                padding: 0.75rem;
            }

            #wialon-report-map {
                height: 280px !important;
            }

            .table-responsive {
                border-radius: 0.375rem;
            }
        }

        @media (max-width: 576px) {
            .table thead th,
            .table tbody td {
                padding: 0.35rem 0.25rem;
                font-size: 0.65rem;
            }

            .pagination-controls button {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .pagination-controls .page-info {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .pagination-controls select {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
            }

            .table-wrapper {
                padding: 0.5rem;
                border-radius: 0.5rem;
            }

            .card-body {
                padding: 0.75rem;
            }

            .custom-combobox input {
                font-size: 13px;
                padding: 7px 30px 7px 10px;
            }

            .dropdown-item-custom {
                font-size: 12px;
                padding: 7px 10px;
            }
        }

        /* fix for very small screens */
        @media (max-width: 400px) {
            .quick-range {
                font-size: 0.6rem;
                padding: 0.2rem 0.3rem;
            }

            .btn-group.w-100 .btn {
                font-size: 0.65rem;
            }

            .d-grid.gap-2 .btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.5rem;
            }
        }
    </style>

    <script>
        window.initialWialonData = @json($initialData ?? ['templates' => [], 'vehicles' => [], 'groups' => []]);
    </script>

    <div class="row g-3">
        {{-- ================= FILTER PANEL ================= --}}
        <div class="col-lg-3 col-md-12">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Report Filters') }}</h5>

                    <form id="report-filter-form"
                          data-generate-url="{{ route('reports.generate') }}"
                          data-sync-url="{{ route('reports.sync') }}"
                          data-export-url="{{ route('reports.export') }}">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">{{ __('Template') }}</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary" id="sync-wialon-btn">
                                    <i class="bi bi-arrow-clockwise"></i> {{ __('Sync Wialon') }}
                                </button>
                            </div>
                            <div class="custom-combobox" id="template-combobox">
                                <input
                                    type="text"
                                    id="template-search"
                                    placeholder="{{ __('Search template...') }}"
                                    autocomplete="off"
                                />
                                <button type="button" class="cb-clear" id="template-clear">✕</button>
                                <div id="template-dropdown" class="custom-dropdown"></div>
                            </div>
                            <select id="template_id" name="template_id" required style="display:none">
                                <option value="">{{ __('Select Template') }}</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template['id'] }}" data-object-type="{{ $template['objectType'] }}">{{ $template['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-1" id="object-label">{{ __('Vehicle') }}</label>
                            <div class="custom-combobox" id="object-combobox">
                                <input
                                    type="text"
                                    id="object-search"
                                    placeholder="{{ __('Search vehicle...') }}"
                                    autocomplete="off"
                                />
                                <button type="button" class="cb-clear" id="object-clear">✕</button>
                                <div id="object-dropdown" class="custom-dropdown"></div>
                            </div>
                            <select id="report_object_id" name="report_object_id" required style="display:none">
                                <option value="">{{ __('Select Vehicle') }}</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle['id'] }}">{{ $vehicle['display_name'] }}</option>
                                @endforeach
                            </select>
                            {{-- Backward compatibility field --}}
                            <input type="hidden" id="wialon_unit_id" name="wialon_unit_id" value="">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Quick Range') }}</label>
                            <div class="btn-group w-100 flex-wrap" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm quick-range" data-range="today">{{ __('Today') }}</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-range" data-range="yesterday">{{ __('Yesterday') }}</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-range" data-range="week">{{ __('Week') }}</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-range" data-range="month">{{ __('Month') }}</button>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>Or select custom dates below (max 3 months)
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('From') }}</label>
                            <input type="datetime-local" class="form-control" id="from" name="from" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('To') }}</label>
                            <input type="datetime-local" class="form-control" id="to" name="to" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="generate-btn">
                                {{ __('Execute') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clear-btn">
                                {{ __('Clear') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= RESULTS PANEL ================= --}}
        <div class="col-lg-9 col-md-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h5 class="card-title mb-0">{{ __('Report Result') }}</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-1" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                {{ __('Export') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item export-link" data-format="xlsx" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel me-2" viewBox="0 0 16 16">
                                        <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                    </svg>
                                    Excel (.xlsx)
                                </a></li>
                                <li><a class="dropdown-item export-link" data-format="pdf" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf me-2" viewBox="0 0 16 16">
                                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                        <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                    </svg>
                                    PDF
                                </a></li>
                                <li><a class="dropdown-item export-link" data-format="csv" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filetype-csv me-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM3.517 14.841a1.13 1.13 0 0 0 .401.823c.13.108.289.192.478.252.19.061.411.091.665.091.338 0 .624-.053.859-.158.236-.105.416-.252.539-.44.125-.189.187-.408.187-.656 0-.224-.045-.41-.134-.56a1.001 1.001 0 0 0-.375-.357 2.027 2.027 0 0 0-.566-.21l-.621-.144a.97.97 0 0 1-.404-.176.37.37 0 0 1-.144-.299c0-.156.062-.284.185-.384.125-.101.296-.152.512-.152.143 0 .266.023.37.068a.624.624 0 0 1 .246.181.56.56 0 0 1 .12.258h.75a1.092 1.092 0 0 0-.2-.566 1.21 1.21 0 0 0-.5-.41 1.813 1.813 0 0 0-.78-.152c-.293 0-.551.05-.776.15-.225.099-.4.24-.527.421-.127.182-.19.395-.19.639 0 .201.04.376.122.524.082.149.2.27.352.367.152.095.332.167.539.213l.618.144c.207.049.361.113.463.193a.387.387 0 0 1 .152.326.505.505 0 0 1-.085.29.559.559 0 0 1-.255.193c-.111.047-.249.07-.413.07-.117 0-.223-.013-.32-.04a.838.838 0 0 1-.248-.115.578.578 0 0 1-.255-.384h-.765ZM.806 13.693c0-.248.034-.46.102-.633a.868.868 0 0 1 .302-.399.814.814 0 0 1 .475-.137c.15 0 .283.032.398.097a.7.7 0 0 1 .272.26.85.85 0 0 1 .12.381h.765v-.072a1.33 1.33 0 0 0-.466-.964 1.441 1.441 0 0 0-.489-.272 1.838 1.838 0 0 0-.606-.097c-.356 0-.66.074-.911.223-.25.148-.44.359-.572.632-.13.274-.196.6-.196.979v.498c0 .379.064.704.193.976.131.271.322.48.572.626.25.145.554.217.914.217.293 0 .554-.055.785-.164.23-.11.414-.26.55-.454a1.27 1.27 0 0 0 .226-.674v-.076h-.764a.799.799 0 0 1-.118.363.7.7 0 0 1-.272.25.874.874 0 0 1-.401.087.845.845 0 0 1-.478-.132.833.833 0 0 1-.299-.392 1.699 1.699 0 0 1-.102-.627v-.495Zm8.239 2.238h-.953l-1.338-3.999h.917l.896 3.138h.038l.888-3.138h.879l-1.327 3.999Z"/>
                                    </svg>
                                    CSV
                                </a></li>
                                <li><a class="dropdown-item export-link" data-format="html" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-code me-2" viewBox="0 0 16 16">
                                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                                        <path d="M8.646 6.646a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L10.293 9 8.646 7.354a.5.5 0 0 1 0-.708zm-1.292 0a.5.5 0 0 0-.708 0l-2 2a.5.5 0 0 0 0 .708l2 2a.5.5 0 0 0 .708-.708L5.707 9l1.647-1.646a.5.5 0 0 0 0-.708z"/>
                                    </svg>
                                    HTML
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="report-loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mb-0">Generating report...<br><small class="text-muted">This may take up to 2 minutes for large date ranges</small></p>
                    </div>

                    <div id="report-empty" class="text-center py-5 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-file-earmark-text mb-3" viewBox="0 0 16 16">
                            <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                            <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                        </svg>
                        <p class="mb-0">Select a template, a vehicle, and a date range, then click Execute.</p>
                    </div>

                    {{-- Chart container --}}
                    <div id="report-chart-container" class="d-none mb-4">
                        <canvas id="report-chart" height="90"></canvas>
                    </div>

                    {{-- Table container --}}
                    <div id="report-tables-container" class="d-none"></div>
                </div>
            </div>

            {{-- ================= MAP PANEL (separate component) ================= --}}
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('Map') }}</h5>
                    <div id="wialon-report-map" style="height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- MapLibre GL JS + OSM tiles --}}
    <link href="https://cdn.jsdelivr.net/npm/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

    {{-- Chart.js for the Voltage/fuel-style line charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

    <script src="{{ asset('js/wialon-report-map.js') }}"></script>
    <script src="{{ asset('js/wialon-report.js') }}"></script>

</x-layouts.auth>
