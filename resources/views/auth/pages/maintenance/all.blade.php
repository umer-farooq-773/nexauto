<x-layouts.auth page-title="{{ __('Maintenance Management') }}" sub-title="Manage Vehicle Maintenance Records">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-maintenance-modal :vehicles="$data['vehicles']" :maintenance-types="$data['maintenanceTypes']" />

            <x-export-csv
                table="vehicle_maintenances"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />

        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-wrench" title="{{ $data['total_count'] }}" description="Total Records"
            route="maintenance.index" iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['completed_count'] }}" description="Completed"
            route="maintenance.index" iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-clock" title="{{ $data['inprogress_count'] }}" description="In Progress"
            route="maintenance.index" iconbg="bg-warning" icontext="text-dark" />

        <x-auth.quick-link-card-small icon="fa-circle-xmark" title="{{ $data['pending_count'] }}" description="Pending"
            route="maintenance.index" iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Maintenance Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Driver Name</th>
                        <th scope="col">Vehicle</th>
                        <th scope="col">Maintenance Date</th>
                        <th scope="col">Next Due Date</th>
                        <th scope="col">Cost</th>
                        <th scope="col">Service Provider</th>
                        <th scope="col">Maintenance Types</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $record)
                    <tr>
                        <td>
                            @php
                                $driverNames = [];
                                if ($record->vehicle && $record->vehicle->drivers) {
                                    $driverNames = $record->vehicle->drivers->pluck('full_name')->filter()->toArray();
                                }
                            @endphp
                            {{ !empty($driverNames) ? implode(', ', $driverNames) : '-' }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center flex-wrap">
                                <span>{{ $record->vehicle?->fleet_no ?? '-' }}</span>

                                @if ($record->vehicle?->truck_no)
                                    <span class="text-muted">-</span>
                                    <span>{{ $record->vehicle->truck_no }}</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $record->maintenance_date ? \Carbon\Carbon::parse($record->maintenance_date)->format('d-m-Y') : '-' }}</td>

                        <td>{{ $record->next_maintenance_date ? \Carbon\Carbon::parse($record->next_maintenance_date)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $record->cost ? number_format($record->cost, 2) : '-' }}</td>
                        <td>{{ $record->service_provider ?? '-' }}</td>
                        <td width="">
                            @if($record->maintenanceTypes && $record->maintenanceTypes->count())
                                <div class="maintenance-trigger">
                                    <span class="maintenance-chip {{ $record->maintenanceTypes->count() > 0 ? 'maintenance-has-items' : 'maintenance-empty' }}">
                                        <i class="fa-solid fa-wrench"></i>
                                        <span class="maintenance-chip-text">Maintenance</span>
                                        <span class="maintenance-count">{{ $record->maintenanceTypes->count() }}</span>
                                        <i class="fa-solid fa-chevron-down maintenance-caret"></i>
                                    </span>
                                    <div class="maintenance-panel">
                                        <div class="maintenance-panel-header">
                                            <i class="fa-solid fa-wrench"></i>
                                            <span>Maintenance Types</span>
                                        </div>
                                        <div class="maintenance-panel-body">
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($record->maintenanceTypes as $type)
                                                    <span class="mtype-pill">
                                                        <i class="fa-solid fa-wrench"></i>
                                                        {{ $type->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="maintenance-clean">
                                    <i class="fa-solid fa-circle-check"></i> No Maintenance
                                </span>
                            @endif
                        </td>
                        <td>
                            <x-status-button
                                    status="{{ strtolower($record->status ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$record->status  ?? ''"
                                    table="vehicle_maintenances"
                                />
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu" >
                                    <li>
                                        <a class="dropdown-item" href="{{ route('maintenance.show', $record->id) }}">
                                            <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                        </a>
                                    </li>
                                    <li>
                                        @if((string)($record->payment_status ?? '') !== '2')
                                            <a class="dropdown-item" href="{{ route('maintenance.edit', $record->id) }}">
                                                <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                            </a>
                                        @else
                                            <span class="dropdown-item text-muted" title="{{ __('Locked - Payment Processed') }}" style="cursor: not-allowed; opacity: 0.6;">
                                                <i class="fa-solid fa-lock me-2 text-secondary"></i> Edit (Locked)
                                            </span>
                                        @endif
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('maintenance.file.index', $record->id) }}">
                                            <i class="fa-solid fa-folder-open me-2 text-info"></i> Files
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('maintenance.destroy', $record->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fa-solid fa-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
        </x-all-list>
    </div>

<style>
/* ========== MAINTENANCE CHIP ========== */
.maintenance-trigger {
    display: inline-block;
    position: relative;
}

.maintenance-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    border: 2px solid transparent;
    letter-spacing: 0.3px;
}

/* Active state with border + background */
.maintenance-has-items {
    background: #fff5f5;
    color: #dc3545;
    border-color: #dc3545;
}

.maintenance-has-items:hover {
    background: #dc3545;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.maintenance-has-items:hover .maintenance-count {
    background: rgba(255, 255, 255, 0.35);
    color: #fff;
}

/* Empty state - green border */
.maintenance-empty {
    background: #f0fdf4;
    color: #16a34a;
    border-color: #16a34a;
}

.maintenance-empty:hover {
    background: #16a34a;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

/* Count badge */
.maintenance-count {
    background: rgba(220, 53, 69, 0.15);
    padding: 0 10px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 11px;
    color: inherit;
    transition: all 0.25s ease;
}

.maintenance-has-items .maintenance-count {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}

.maintenance-empty .maintenance-count {
    background: rgba(22, 163, 74, 0.15);
    color: #16a34a;
}

/* Caret icon */
.maintenance-caret {
    font-size: 10px;
    margin-left: 2px;
    transition: transform 0.25s ease;
}

.maintenance-trigger.is-open .maintenance-caret {
    transform: rotate(180deg);
}

/* ========== PANEL ========== */
.maintenance-panel {
    display: none;
    position: fixed;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    min-width: 280px;
    max-width: 400px;
    z-index: 9999;
    border: 1px solid #e5e7eb;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    transform: translateY(5px);
}

.maintenance-panel.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

/* Panel Header */
.maintenance-panel-header {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f5;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fafbfc;
    border-radius: 12px 12px 0 0;
    color: #1a1a2e;
}

.maintenance-panel-header i {
    color: #dc3545;
    font-size: 16px;
}

/* Panel Body */
.maintenance-panel-body {
    padding: 14px 16px;
    max-height: 300px;
    overflow-y: auto;
}

/* ========== CLEAN STATE ========== */
.maintenance-clean {
    color: #16a34a;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    font-size: 13px;
    background: #f0fdf4;
    padding: 4px 12px;
    border-radius: 20px;
    border: 2px solid #16a34a;
}

/* ========== MAINTENANCE TYPE PILLS ========== */
.mtype-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    background: #f1f3f5;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    color: #1a1a2e;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.mtype-pill:hover {
    background: #e9ecef;
    border-color: #dc3545;
    transform: translateY(-1px);
}

.mtype-pill i {
    color: #dc3545;
    font-size: 10px;
}

/* ========== SCROLLBAR ========== */
.maintenance-panel-body::-webkit-scrollbar {
    width: 4px;
}

.maintenance-panel-body::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 10px;
}

.maintenance-panel-body::-webkit-scrollbar-thumb {
    background: #dc3545;
    border-radius: 10px;
}

/* Creates an invisible area between chip and panel */
.maintenance-trigger::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    height: 15px;
    pointer-events: none;
}
</style>

<script>
    (function() {
        var hideTimer = null;
        var activeTrigger = null;

        function getElements(trigger) {
            return {
                chip: trigger.querySelector('.maintenance-chip'),
                panel: trigger.querySelector('.maintenance-panel'),
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

        // FIX: showPanel is now idempotent. If this trigger's panel is
        // already open, we just cancel the pending hide and stop —
        // we never re-measure/re-hide/re-show it. That re-measure-while-hidden
        // cycle (panel briefly display:none -> 0x0 rect -> repositioned wrong)
        // was exactly what caused it to "jump" to the left when the mouse
        // moved from the chip into the panel or back.
        function showPanel(trigger) {
            clearTimeout(hideTimer);

            var els = getElements(trigger);
            if (!els.panel) return;

            var alreadyOpenForThisTrigger =
                activeTrigger === trigger && els.panel.classList.contains('show');

            if (alreadyOpenForThisTrigger) {
                // Just keep it open, don't touch position/visibility.
                return;
            }

            if (activeTrigger && activeTrigger !== trigger) {
                hidePanel(activeTrigger);
            }

            activeTrigger = trigger;
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
            }, 300);
        }

        function cancelHide() {
            clearTimeout(hideTimer);
        }

        // ===== Handle hover on chip / trigger / panel =====
        document.addEventListener('mouseenter', function(e) {
            var panel = e.target.closest && e.target.closest('.maintenance-panel');
            var trigger = e.target.closest && e.target.closest('.maintenance-trigger');

            // Entering the panel itself: just keep it alive, never reposition.
            // (Handled generically by showPanel's "already open" guard below too,
            // but we short-circuit here to skip the extra work.)
            if (panel) {
                cancelHide();
                var owner = panel.closest('.maintenance-trigger');
                if (owner) {
                    activeTrigger = owner;
                    owner.classList.add('is-open');
                }
                return;
            }

            if (trigger) {
                cancelHide();
                showPanel(trigger);
            }
        }, true);

        // ===== Handle leaving chip and panel =====
        document.addEventListener('mouseleave', function(e) {
            var trigger = e.target.closest && e.target.closest('.maintenance-trigger');
            if (trigger) {
                var relatedTarget = e.relatedTarget;
                if (relatedTarget) {
                    var isMovingToPanel = relatedTarget.closest && relatedTarget.closest('.maintenance-panel');
                    if (isMovingToPanel) {
                        cancelHide();
                        return;
                    }
                }
                scheduleHide(trigger);
            }

            var panel = e.target.closest && e.target.closest('.maintenance-panel');
            if (panel) {
                var relatedTarget2 = e.relatedTarget;
                var owner = panel.closest('.maintenance-trigger');

                if (relatedTarget2) {
                    var isMovingToTrigger = relatedTarget2.closest && relatedTarget2.closest('.maintenance-trigger');
                    var isMovingToChip = relatedTarget2.closest && relatedTarget2.closest('.maintenance-chip');

                    if (isMovingToTrigger || isMovingToChip) {
                        cancelHide();
                        return;
                    }
                }

                if (owner) {
                    scheduleHide(owner);
                }
            }
        }, true);

        // ===== Click support =====
        document.addEventListener('click', function(e) {
            if (e.target.closest && e.target.closest('.maintenance-panel a')) {
                return;
            }

            var chip = e.target.closest && e.target.closest('.maintenance-chip');
            if (chip) {
                var trigger = chip.closest('.maintenance-trigger');
                if (!trigger) return;
                var els = getElements(trigger);
                if (!els.panel) return;
                e.preventDefault();
                if (els.panel.classList.contains('show')) {
                    hidePanel(trigger);
                } else {
                    activeTrigger = null; // force a clean open/measure
                    showPanel(trigger);
                }
                return;
            }

            if (!e.target.closest('.maintenance-panel') && activeTrigger) {
                hidePanel(activeTrigger);
            }
        });

        // ===== Scroll/Resize: reposition an already-open panel only =====
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

</x-layouts.auth>
