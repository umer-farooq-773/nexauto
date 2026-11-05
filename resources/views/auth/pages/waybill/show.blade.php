{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<x-layouts.auth page-title="{{ __('Waybill Details') }}" sub-title="">
    <div class="position-relative">

        {{-- ===================== FLOATING ACTION BUTTON GROUP ===================== --}}
        <div class="position-absolute end-0 me-2" style="top: -33px; z-index: 998;">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <a href="{{ route('waybill.print_full', $data->id) }}"
                   target="_blank"
                   class="btn btn-sm btn-gradient-blue">
                    <i class="ri-file-pdf-2-line me-1"></i> {{ __('PDF (Full)') }}
                </a>
                <a href="{{ route('waybill.print_detail', $data->id) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-primary">
                    <i class="ri-file-text-line me-1"></i>{{ __('Detail Only') }}
                </a>
                <a href="{{ route('waybill.waybill_map_tracking', $data->id) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-primary">
                    <i class="ri-map-2-line me-1"></i>
                    {{ __('View Map') }}
                </a>
            </div>
        </div>

        {{-- ===================== MAIN DETAIL INCLUDE ===================== --}}
        @include('auth.pages.waybill.show-detail')

        {{-- ===================== RELATED INCIDENT RECORDS ===================== --}}
        @if($incidents->isNotEmpty())
        <div class="mt-4">

            <div class="section-header mb-3">
                <div class="section-header-left">
                    <div class="icon-tile amber">
                        <i class="ri-links-line"></i>
                    </div>
                    <div>
                        <h6 class="section-title mb-0">{{ __('Related Records') }}</h6>
                        <small class="text-muted">{{ __('Incident records linked to this waybill.') }}</small>
                    </div>
                </div>
            </div>

            @if($incidents->has('accidents'))
                @include('auth.pages.waybill.partials._accidents', ['records' => $incidents->get('accidents')])
            @endif

            @if($incidents->has('vehicle_breakdowns'))
                @include('auth.pages.waybill.partials._vehicle_breakdowns', ['records' => $incidents->get('vehicle_breakdowns')])
            @endif

            @if($incidents->has('vehicle_bogged_downs'))
                @include('auth.pages.waybill.partials._vehicle_bogged_downs', ['records' => $incidents->get('vehicle_bogged_downs')])
            @endif

            @if($incidents->has('impoundments'))
                @include('auth.pages.waybill.partials._impoundments', ['records' => $incidents->get('impoundments')])
            @endif

            @if($incidents->has('route_diversions'))
                @include('auth.pages.waybill.partials._route_diversions', ['records' => $incidents->get('route_diversions')])
            @endif

            @if($incidents->has('illegal_detentions'))
                @include('auth.pages.waybill.partials._illegal_detentions', ['records' => $incidents->get('illegal_detentions')])
            @endif

            @if($incidents->has('illegal_haulages'))
                @include('auth.pages.waybill.partials._illegal_haulages', ['records' => $incidents->get('illegal_haulages')])
            @endif

            @if($incidents->has('illegal_shortages'))
                @include('auth.pages.waybill.partials._illegal_shortages', ['records' => $incidents->get('illegal_shortages')])
            @endif

            @if($incidents->has('breathaliser_tests'))
                @include('auth.pages.waybill.partials._breathaliser_tests', ['records' => $incidents->get('breathaliser_tests')])
            @endif

            @if($incidents->has('driver_misconducts'))
                @include('auth.pages.waybill.partials._driver_misconducts', ['records' => $incidents->get('driver_misconducts')])
            @endif
        </div>
        @endif
    </div>

    {{-- ===================== STATUS UPDATE MODAL ===================== --}}
    <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="ri-refresh-line text-primary me-2"></i>
                        {{ __('Update Waybill Status') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Action will be populated by JS to avoid MethodNotAllowed on current route -->
                <form id="statusUpdateForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">

                        {{-- Incident warning banner (shown via JS) --}}
                        <div id="incidentWarningBanner" class="alert alert-danger d-none rounded-3" role="alert">
                            <i class="ri-alert-line me-2"></i>
                            <strong>{{ __('Active Incident Intervention') }}</strong>
                            <p class="mb-1 mt-1 small">{{ __('This waybill has unresolved incidents. Status change requires') }} <strong>{{ __('Client Director approval') }}</strong>.</p>
                            <div id="incidentList" class="small"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Select Status') }}</label>
                            <select name="status" id="statusSelect" class="form-select" onchange="toggleStatusFields(this.value)" required>
                                <option value="">-- Select --</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="despatch">{{ __('Despatch') }}</option>
                                <option value="return_transit">{{ __('Return Transit') }}</option>
                                <option value="delivered">{{ __('Delivered') }}</option>
                                <option value="impoundment">{{ __('Impoundment') }}</option>
                                <option value="accident">{{ __('Accident') }}</option>
                            </select>
                        </div>

                        <div id="despatch_fields" class="status-extra-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Despatch Time') }}</label>
                                <input type="datetime-local" name="despatch_time" class="form-control">
                            </div>
                        </div>

                        <div id="delivered_fields" class="status-extra-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Delivery Document (POD)') }}</label>
                                <input type="file" name="delivery_document" class="form-control">
                                <small class="text-muted text-xs">{{ __('Upload signed waybill or receipt') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" id="statusSubmitBtn" class="btn btn-gradient-blue">
                            <i class="ri-refresh-line me-1"></i>{{ __('Update Status') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const updateStatusUrlTemplate = @js(route('waybill.update_status', ['waybill' => 'WAYBILL_ID']));
            const incidentStatusUrlTemplate = @js(route('waybill.incident_status', ['waybill' => 'WAYBILL_ID']));

            function setStatusModalData(id, currentStatus) {
                const form = document.getElementById('statusUpdateForm');
                if (form) {
                    form.action = updateStatusUrlTemplate.replace('WAYBILL_ID', id);
                }

                const statusSelect = document.getElementById('statusSelect');
                if (statusSelect) {
                    // Match integer values to strings used in the select options for ease.
                    const statusMap = {
                        "0": "pending",
                        "1": "pending", // allocated -> pending
                        "2": "despatch",
                        "5": "return_transit",
                        "3": "delivered"
                    };
                    // Handle if status passed is string vs integer matching
                    statusSelect.value = statusMap[currentStatus] || currentStatus || '';
                }

                toggleStatusFields(statusSelect ? statusSelect.value : '');

                // Check incident status via AJAX
                checkIncidentStatus(id);
            }

            function checkIncidentStatus(waybillId) {
                const warningBanner = document.getElementById('incidentWarningBanner');
                const incidentList = document.getElementById('incidentList');
                const submitBtn = document.getElementById('statusSubmitBtn');

                // Reset
                if (warningBanner) warningBanner.classList.add('d-none');
                if (incidentList) incidentList.innerHTML = '';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('disabled');
                }

                const url = incidentStatusUrlTemplate.replace('WAYBILL_ID', waybillId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.has_incidents && !data.is_client_director) {
                            // Show warning banner
                            if (warningBanner) warningBanner.classList.remove('d-none');

                            // Show incident list
                            if (incidentList && data.incidents && data.incidents.length > 0) {
                                incidentList.innerHTML = '<strong>Active incidents:</strong> ' + data.incidents.join(', ');
                            }

                            // Disable submit button
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.classList.add('disabled');
                                submitBtn.title = 'Client Director approval required';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error checking incident status:', error);
                    });
            }

            window.openStatusModal = function (id, currentStatus) {
                setStatusModalData(id, currentStatus);
                const modalEl = document.getElementById('statusUpdateModal');
                if (!modalEl || !window.bootstrap || !bootstrap.Modal) return;
                const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                instance.show();
            };

            window.toggleStatusFields = function (status) {
                document.querySelectorAll('.status-extra-fields').forEach(div => {
                    div.style.display = 'none';
                });
                if (status === 'despatch') {
                    const despatchEl = document.getElementById('despatch_fields');
                    if (despatchEl) despatchEl.style.display = 'block';
                } else if (status === 'delivered') {
                    const deliveredEl = document.getElementById('delivered_fields');
                    if (deliveredEl) deliveredEl.style.display = 'block';
                }
            };

            document.addEventListener('DOMContentLoaded', function () {
                const modalEl = document.getElementById('statusUpdateModal');
                if (!modalEl) return;

                modalEl.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const id = trigger.getAttribute('data-waybill-id');
                    const status = trigger.getAttribute('data-waybill-status');
                    if (!id) return;

                    setStatusModalData(id, status);
                });
            });
        })();
    </script>
    @endpush

    {{-- ===================== STYLES ===================== --}}
    <style>
        .icon-tile {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; flex-shrink: 0;
        }
        .icon-tile.amber { background: #fef3c7; color: #d97706; }

        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap;
        }
        .section-header-left { display: flex; align-items: center; gap: 0.85rem; }
        .section-title { font-weight: 700; font-size: 1.05rem; color: #1e293b; }

        .btn-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none; color: #ffffff; font-weight: 600;
            border-radius: 50px; padding: 0.45rem 1rem;
            transition: all 0.25s ease;
        }
        .btn-gradient-blue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }
    </style>
</x-layouts.auth>
