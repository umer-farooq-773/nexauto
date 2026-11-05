<x-layouts.auth page-title="{{ __('Waybill Management') }}" sub-title="Manage Waybills from this page">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-waybill-modal :vehicles="$data['vehicles']" :drivers="$data['drivers']" :route-trips="$data['route_trips']" />

            <x-export-csv
                table="waybills"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />

        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-file-invoice-dollar" title="{{ $data['total_count'] }}"
            description="Total Waybills" route="waybill.index" iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-hourglass-half" title="{{ $data['awaiting_count'] }}"
            description="Awaiting" route="waybill.index" iconbg="bg-warning" icontext="text-dark" />

        <x-auth.quick-link-card-small icon="fa-truck-loading" title="{{ $data['allocated_count'] }}"
            description="Allocated" route="waybill.index" iconbg="bg-info" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['delivered_count'] }}"
            description="Delivered" route="waybill.index" iconbg="bg-success" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Waybill Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Waybill No</th>
                        <th scope="col">Driver</th>
                        <th scope="col">Vehicle</th>
                        <th scope="col">Route</th>
                        <th scope="col">Dispatch Date</th>
                        <th scope="col">Estimated Dispatch Time</th>
                        <th scope="col">Waybill Status</th>
                        <th scope="col" width="140">Incident Status</th>
                        @canany(['view_waybill', 'edit_waybill', 'delete_waybill'])
                            <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $waybill)
                        <tr>
                            <td><a href="{{ route('waybill.show', $waybill->id) }}">{{ $waybill->waybill_number }}</a></td>
                            <td>{{ $waybill->driver?->full_name ?? '-' }}</td>
                            <td>{{ $waybill->vehicle?->fleet_no ?? '-' }} - {{ $waybill->vehicle?->truck_no ?? '' }}</td>
                            <td>
                                @if ($waybill->origin && $waybill->destination)
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                    {{ $waybill->origin }}
                                    <i class="fas fa-arrow-right text-muted mx-1"></i>
                                    {{ $waybill->destination }}
                                @else
                                    <i class="fas fa-minus-circle text-muted me-1"></i> -
                                @endif
                            </td>
                            <td>{{ $waybill->dispatch_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $waybill->estimated_dispatch_time ? \Carbon\Carbon::parse($waybill->estimated_dispatch_time)->format('h:i A') : '-' }}</td>
                            <td>
                                <x-status-button
                                    status="{{ strtolower($waybill->status_label ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$waybill->status_label  ?? ''"
                                    table="waybill"
                                />
                            </td>
                            <td width="135">
                                @php($incidentHtml = $waybill->incident_status_badge ?? '')
                                @if (trim($incidentHtml) !== '')
                                    <div class="incident-luxury-trigger">
                                        <span
                                            class="incident-luxury-chip {{ (int) $waybill->incident_count > 0 ? 'incident-warning' : 'incident-success' }}">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span class="incident-luxury-chip-text">Incidents</span>
                                            <span class="incident-luxury-count">{{ $waybill->incident_count }}</span>
                                            <i class="fa-solid fa-chevron-down incident-luxury-caret"></i>
                                        </span>
                                        <div class="incident-luxury-panel">
                                            <div class="incident-luxury-panel-header">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                <span>Incident Statuses</span>
                                            </div>
                                            <div class="incident-luxury-panel-body">
                                                {!! $incidentHtml !!}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="incident-luxury-clean">
                                        <i class="fa-solid fa-circle-check"></i> No Incidents
                                    </span>
                                @endif
                            </td>
                            @canany(['view_waybill', 'edit_waybill', 'delete_waybill'])
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm border dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu shadow">
                                            @can('view_waybill')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('waybill.show', $waybill->id) }}">
                                                        <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('edit_waybill')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('waybill.edit', $waybill->id) }}">
                                                        <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('waybill.file.index', $waybill->id) }}">
                                                        <i class="fa-solid fa-folder-open me-2 text-info"></i> Files
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_waybill')
                                                <li>
                                                    <form action="{{ route('waybill.destroy', $waybill->id) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this waybill?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fa-solid fa-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
        </x-all-list>
    </div>

    <div class="modal fade" id="waybillFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="waybillFileModalTitle">{{ __('Waybill Document') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="waybillFileEmpty" class="text-muted">No document uploaded for this waybill.</div>

                    <div id="waybillFilePreview" style="display:none;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="small text-muted" id="waybillFileName"></div>
                            <a class="btn btn-sm btn-outline-primary" id="waybillFileDownload" href="#"
                                target="_blank" rel="noopener">
                                Download
                            </a>
                        </div>

                        <img id="waybillFileImg" src="" alt="Waybill Document"
                            style="display:none; max-width:100%; height:auto;" />
                        <iframe id="waybillFilePdf" src=""
                            style="display:none; width:100%; height:70vh; border:1px solid #eee; border-radius:10px;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Update Waybill Status') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="statusUpdateForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">

                        {{-- Incident warning banner (shown via JS) --}}
                        <div id="incidentWarningBanner" class="alert alert-danger d-none" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <strong>Active Incident Intervention</strong>
                            <p class="mb-1 mt-1 small">This waybill has unresolved incidents. Status change requires
                                <strong>Client Director approval</strong>.
                            </p>
                            <div id="incidentList" class="small"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Select Status') }}</label>
                            <select name="status" id="statusSelect" class="form-select"
                                onchange="toggleStatusFields(this.value)" required>
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
                                <label class="form-label">{{ __('Despatch Time') }}</label>
                                <input type="datetime-local" name="despatch_time" class="form-control">
                            </div>
                        </div>

                        <div id="delivered_fields" class="status-extra-fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Delivery Document (POD)') }}</label>
                                <input type="file" name="delivery_document" class="form-control">
                                <small class="text-muted text-xs">Upload signed waybill or receipt</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="statusSubmitBtn" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* ---- Collapsed trigger chip shown in the table cell ---- */
        .incident-luxury-trigger {
            position: relative;
            display: inline-block;
        }

        /* Base chip styles - will be overridden by conditional classes */
        .incident-luxury-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 600;
            letter-spacing: 0.2px;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(20, 20, 30, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            white-space: nowrap;
        }

        /* Default blue styling when no conditional class is applied */
        .incident-luxury-chip:not(.incident-warning):not(.incident-success) {
            color: #fff;
            background: #0b3d91;
        }

        /* Warning state - when incidents > 0 */
        .incident-luxury-chip.incident-warning {
            color: #856404;
            background: #fff3cd;
            border-color: #ffc107;
        }

        .incident-luxury-chip.incident-warning i.fa-triangle-exclamation {
            color: #856404;
        }

        .incident-luxury-chip.incident-warning .incident-luxury-count {
            color: #856404;
            background: #ffc107;
        }

        /* Success state - when incidents == 0 */
        .incident-luxury-chip.incident-success {
            color: #0f6848;
            background: #d4edda;
            border-color: #28a745;
        }

        .incident-luxury-chip.incident-success i.fa-triangle-exclamation {
            color: #0f6848;
        }

        .incident-luxury-chip.incident-success .incident-luxury-count {
            color: #0f6848;
            background: #28a745;
        }

        /* Shared icon styles */
        .incident-luxury-chip i.fa-triangle-exclamation {
            font-size: 12px;
        }

        .incident-luxury-caret {
            font-size: 9px;
            opacity: 0.65;
            transition: transform 0.2s ease;
        }

        .incident-luxury-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1;
            border-radius: 999px;
            letter-spacing: 0;
        }

        /* Hover effects for each state */
        .incident-luxury-trigger:hover .incident-luxury-chip:not(.incident-warning):not(.incident-success) {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(20, 20, 30, 0.32);
            background: #3a7cf0;
        }

        .incident-luxury-trigger:hover .incident-luxury-chip.incident-warning {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4);
            background: #ffe69b;
        }

        .incident-luxury-trigger:hover .incident-luxury-chip.incident-success {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(40, 167, 69, 0.3);
            background: #b8e6c0;
        }

        /* Rest of your existing CSS remains the same... */
        .incident-luxury-trigger.is-open .incident-luxury-caret {
            transform: rotate(180deg);
        }

        .incident-luxury-clean {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 600;
            color: #1c6b43;
            background: linear-gradient(135deg, #eafff2 0%, #d9f7e6 100%);
            border: 1px solid rgba(28, 107, 67, 0.15);
            border-radius: 999px;
            white-space: nowrap;
        }

        /* ---- The expanded luxury panel ---- */
        .incident-luxury-panel {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            width: 360px;
            max-width: 92vw;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(14px) saturate(160%);
            -webkit-backdrop-filter: blur(14px) saturate(160%);
            border-radius: 18px;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.6) inset,
                0 24px 60px rgba(15, 15, 25, 0.22),
                0 4px 14px rgba(15, 15, 25, 0.12);
            border: 1px solid rgba(15, 15, 25, 0.06);
            opacity: 0;
            visibility: hidden;
            transform: scale(0.96) translateY(6px);
            transform-origin: top center;
            transition: opacity 0.18s cubic-bezier(.2, .8, .2, 1), transform 0.18s cubic-bezier(.2, .8, .2, 1), visibility 0.18s;
            pointer-events: none;
            overflow: hidden;
        }

        .incident-luxury-panel.show {
            opacity: 1;
            visibility: visible;
            transform: scale(1) translateY(0);
            pointer-events: auto;
        }

        .incident-luxury-panel-header {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            font-weight: 700;
            font-size: 13.5px;
            letter-spacing: 0.3px;
            color: #1f2430;
            background: linear-gradient(135deg, #fffdf6 0%, #fff7e6 100%);
        }

        .incident-luxury-panel-header::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 0;
            height: 2px;
            background: linear-gradient(90deg, #f6c453, #f0a93a 60%, transparent);
            border-radius: 2px;
        }

        .incident-luxury-panel-header i {
            color: #d99b1a;
            font-size: 14px;
        }

        .incident-luxury-panel-body {
            padding: 14px 18px 16px;
            max-height: 360px;
            overflow-y: auto;
            font-size: 13px;
            line-height: 1.9;
        }

        .incident-luxury-panel-body::-webkit-scrollbar {
            width: 5px;
        }

        .incident-luxury-panel-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .incident-luxury-panel-body::-webkit-scrollbar-thumb {
            background: rgba(15, 15, 25, 0.15);
            border-radius: 10px;
        }

        .incident-luxury-panel-body::-webkit-scrollbar-thumb:hover {
            background: rgba(15, 15, 25, 0.28);
        }

        .incident-luxury-panel-body .badge {
            font-size: 11.5px !important;
            padding: 6px 12px !important;
            border-radius: 999px !important;
            font-weight: 600 !important;
            letter-spacing: 0.2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            display: inline-block;
            margin: 3px 2px;
        }

        .incident-luxury-panel-body br {
            display: block;
            content: "";
            margin-top: 4px;
        }
    </style>

    @push('scripts')
        <script>
            (function() {
                var hideTimer = null;
                var activeTrigger = null;

                function getElements(trigger) {
                    return {
                        chip: trigger.querySelector('.incident-luxury-chip'),
                        panel: trigger.querySelector('.incident-luxury-panel'),
                    };
                }

                function positionPanel(trigger, panel) {
                    var rect = trigger.getBoundingClientRect();
                    var panelRect = panel.getBoundingClientRect();
                    var gap = 10;

                    var top = rect.top + (rect.height / 2) - (panelRect.height / 2);
                    var left = rect.left - panelRect.width - gap;

                    if (left < 8) {
                        left = rect.right + gap;
                    }
                    if (left + panelRect.width > window.innerWidth - 8) {
                        left = window.innerWidth - panelRect.width - 8;
                    }
                    if (top < 8) top = 8;
                    if (top + panelRect.height > window.innerHeight - 8) {
                        top = window.innerHeight - panelRect.height - 8;
                    }

                    panel.style.top = top + 'px';
                    panel.style.left = left + 'px';
                }

                function showPanel(trigger) {
                    clearTimeout(hideTimer);

                    if (activeTrigger && activeTrigger !== trigger) {
                        hidePanel(activeTrigger);
                    }

                    activeTrigger = trigger;
                    var els = getElements(trigger);
                    if (!els.panel) return;

                    trigger.classList.add('is-open');
                    els.panel.style.visibility = 'hidden';
                    els.panel.classList.remove('show');

                    requestAnimationFrame(function() {
                        positionPanel(trigger, els.panel);
                        els.panel.style.visibility = '';
                        els.panel.classList.add('show');
                    });
                }

                function hidePanel(trigger) {
                    var els = getElements(trigger);
                    if (els.panel) els.panel.classList.remove('show');
                    trigger.classList.remove('is-open');
                    if (activeTrigger === trigger) activeTrigger = null;
                }

                function scheduleHide(trigger) {
                    clearTimeout(hideTimer);
                    hideTimer = setTimeout(function() {
                        hidePanel(trigger);
                    }, 160); // grace period so the cursor can travel from chip to panel
                }

                function cancelHide() {
                    clearTimeout(hideTimer);
                }

                document.addEventListener('mouseenter', function(e) {
                    var trigger = e.target.closest && e.target.closest('.incident-luxury-trigger');
                    if (trigger) showPanel(trigger);

                    var panel = e.target.closest && e.target.closest('.incident-luxury-panel');
                    if (panel) cancelHide();
                }, true);

                document.addEventListener('mouseleave', function(e) {
                    var trigger = e.target.closest && e.target.closest('.incident-luxury-trigger');
                    if (trigger) scheduleHide(trigger);

                    var panel = e.target.closest && e.target.closest('.incident-luxury-panel');
                    if (panel) {
                        var owner = panel.closest('.incident-luxury-trigger');
                        if (owner) scheduleHide(owner);
                    }
                }, true);

                // Touch / click support
                document.addEventListener('click', function(e) {
                    // Real incident links inside the panel must navigate normally —
                    // never intercept these.
                    if (e.target.closest && e.target.closest('.incident-luxury-panel a')) {
                        return;
                    }

                    // Only the chip itself toggles the panel open/closed.
                    var chip = e.target.closest && e.target.closest('.incident-luxury-chip');
                    if (chip) {
                        var trigger = chip.closest('.incident-luxury-trigger');
                        if (!trigger) return;
                        var els = getElements(trigger);
                        if (!els.panel) return;
                        e.preventDefault();
                        if (els.panel.classList.contains('show')) {
                            hidePanel(trigger);
                        } else {
                            showPanel(trigger);
                        }
                        return;
                    }

                    if (!e.target.closest('.incident-luxury-panel') && activeTrigger) {
                        hidePanel(activeTrigger);
                    }
                });

                window.addEventListener('scroll', function() {
                    if (activeTrigger) {
                        var els = getElements(activeTrigger);
                        if (els.panel && els.panel.classList.contains('show')) {
                            positionPanel(activeTrigger, els.panel);
                        }
                    }
                }, true);

                window.addEventListener('resize', function() {
                    if (activeTrigger) {
                        var els = getElements(activeTrigger);
                        if (els.panel && els.panel.classList.contains('show')) {
                            positionPanel(activeTrigger, els.panel);
                        }
                    }
                });
            })();
        </script>
    @endpush

</x-layouts.auth>
