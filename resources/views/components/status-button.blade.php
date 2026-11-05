@props([
    'status' => 'pending',
    'count' => 0,
    'label' => 'Status',
    'icon' => 'fa-solid fa-circle-info',
    'html' => null,
    'table' => 'waybill',
])

@php
    // Status mapping based on table type
    $statusConfig = [

        // ── Waybill ──────────────────────────────────────────────────────
        'waybill' => [
            'awaiting'       => ['label' => 'Awaiting',       'class' => 'status-awaiting',  'icon' => 'fa-solid fa-hourglass-start'],
            'allocated'      => ['label' => 'Allocated',      'class' => 'status-allocated', 'icon' => 'fa-solid fa-user-check'],
            'in_transit'     => ['label' => 'In Transit',     'class' => 'status-transit',   'icon' => 'fa-solid fa-truck'],
            'return_transit' => ['label' => 'Return Transit', 'class' => 'status-return',    'icon' => 'fa-solid fa-truck-ramp-box'],
            'delivered'      => ['label' => 'Delivered',      'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            'completed'      => ['label' => 'Completed',      'class' => 'status-completed', 'icon' => 'fa-solid fa-flag-checkered'],
        ],

        // ── Vehicles ─────────────────────────────────────────────────────
        'vehicles' => [
            '1' => ['label' => 'Active',        'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            '2' => ['label' => 'Serviceable',   'class' => 'status-allocated', 'icon' => 'fa-solid fa-wrench'],
            '0' => ['label' => 'Unserviceable', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-circle-xmark'],
        ],

        // ── Driver Details ────────────────────────────────────────────────
        'driver_details' => [
            '0' => ['label' => 'Inactive', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-user-slash'],
            '1' => ['label' => 'Active',   'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check']
            ,
        ],

        // ── Driver Mates ──────────────────────────────────────────────────
        'driver_mates' => [
            '0' => ['label' => 'Active',   'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            '1' => ['label' => 'Inactive', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-user-slash'],
        ],

        // ── Vehicle Maintenance ───────────────────────────────────────────
        'vehicle_maintenances' => [
            '0' => ['label' => 'Pending',     'class' => 'status-awaiting',  'icon' => 'fa-solid fa-hourglass-half'],
            '1' => ['label' => 'Completed',   'class' => 'status-delivered', 'icon' => 'fa-solid fa-screwdriver-wrench'],
            '2' => ['label' => 'In Progress', 'class' => 'status-allocated', 'icon' => 'fa-solid fa-gear'],
        ],

        // ── Impoundments ──────────────────────────────────────────────────
        'impoundments' => [
            '0' => ['label' => 'Impounded', 'class' => 'status-impounded', 'icon' => 'fa-solid fa-lock'],
            '1' => ['label' => 'Released',  'class' => 'status-released',  'icon' => 'fa-solid fa-lock-open'],
        ],

        // ── Illegal Detentions ────────────────────────────────────────────
        'illegal_detentions' => [
            '0' => ['label' => 'Detained', 'class' => 'status-impounded', 'icon' => 'fa-solid fa-handcuffs'],
            '1' => ['label' => 'Released', 'class' => 'status-released',  'icon' => 'fa-solid fa-lock-open'],
        ],

        // ── Vehicle Breakdowns ────────────────────────────────────────────
        'vehicle_breakdowns' => [
            '0' => ['label' => 'Pending',     'class' => 'status-awaiting',  'icon' => 'fa-solid fa-hourglass-half'],
            '1' => ['label' => 'Resolved',    'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            '2' => ['label' => 'In Progress', 'class' => 'status-allocated', 'icon' => 'fa-solid fa-gear'],
        ],

        // ── Vehicle Bogged Down ───────────────────────────────────────────
        'vehicle_bogged_down' => [
            '0' => ['label' => 'Stuck/Pending',       'class' => 'status-awaiting',  'icon' => 'fa-solid fa-circle-exclamation'],
            '1' => ['label' => 'Recovered',            'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            '2' => ['label' => 'Recovery In Progress', 'class' => 'status-allocated', 'icon' => 'fa-solid fa-truck-pickup'],
        ],

        // ── Accidents ─────────────────────────────────────────────────────
        'accidents' => [
            '0' => ['label' => 'Pending',     'class' => 'status-awaiting',  'icon' => 'fa-solid fa-hourglass-half'],
            '1' => ['label' => 'Resolved',    'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            '2' => ['label' => 'In Progress', 'class' => 'status-allocated', 'icon' => 'fa-solid fa-gear'],
        ],

        // ── Route Diversions ──────────────────────────────────────────────
        'route_diversions' => [
            '0' => ['label' => 'Active Diversion', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-code-fork'],
            '1' => ['label' => 'Completed',         'class' => 'status-delivered', 'icon' => 'fa-solid fa-flag-checkered'],
            '2' => ['label' => 'Cancelled',         'class' => 'status-default',   'icon' => 'fa-solid fa-ban'],
        ],

        // ── Illegal Haulage — status ──────────────────────────────────────
        'illegal_haulages' => [
            '0' => ['label' => 'Open',     'class' => 'status-impounded', 'icon' => 'fa-solid fa-triangle-exclamation'],
            '1' => ['label' => 'Reviewed', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-magnifying-glass'],
            '2' => ['label' => 'Resolved', 'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
        ],

        // ── Illegal Haulage — severity ────────────────────────────────────
        'illegal_haulages_severity' => [
            'minor'    => ['label' => 'Minor',    'class' => 'sev-minor',    'icon' => 'fa-solid fa-circle-info'],
            'moderate' => ['label' => 'Moderate', 'class' => 'sev-moderate', 'icon' => 'fa-solid fa-circle-exclamation'],
            'severe'   => ['label' => 'Severe',   'class' => 'sev-severe',   'icon' => 'fa-solid fa-triangle-exclamation'],
            'critical' => ['label' => 'Critical', 'class' => 'sev-critical', 'icon' => 'fa-solid fa-fire'],
        ],

        // ── Illegal Shortage — status ─────────────────────────────────────
        'illegal_shortages' => [
            '0' => ['label' => 'Open',         'class' => 'status-impounded', 'icon' => 'fa-solid fa-triangle-exclamation'],
            '1' => ['label' => 'Under Review', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-magnifying-glass'],
            '2' => ['label' => 'Resolved',     'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
        ],

        // ── Illegal Shortage — severity ───────────────────────────────────
        'illegal_shortages_severity' => [
            'minor'    => ['label' => 'Minor',    'class' => 'sev-minor',    'icon' => 'fa-solid fa-circle-info'],
            'moderate' => ['label' => 'Moderate', 'class' => 'sev-moderate', 'icon' => 'fa-solid fa-circle-exclamation'],
            'severe'   => ['label' => 'Severe',   'class' => 'sev-severe',   'icon' => 'fa-solid fa-triangle-exclamation'],
            'critical' => ['label' => 'Critical', 'class' => 'sev-critical', 'icon' => 'fa-solid fa-fire'],
        ],

        // ── Driver Misconduct — status ────────────────────────────────────
        'driver_misconducts' => [
            '0' => ['label' => 'Open',         'class' => 'status-impounded', 'icon' => 'fa-solid fa-triangle-exclamation'],
            '1' => ['label' => 'Under Review',  'class' => 'status-awaiting',  'icon' => 'fa-solid fa-magnifying-glass'],
            '2' => ['label' => 'Resolved',      'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
        ],

        // ── Driver Misconduct — severity ──────────────────────────────────
        'driver_misconducts_severity' => [
            'minor'    => ['label' => 'Minor',    'class' => 'sev-minor',    'icon' => 'fa-solid fa-circle-info'],
            'moderate' => ['label' => 'Moderate', 'class' => 'sev-moderate', 'icon' => 'fa-solid fa-circle-exclamation'],
            'severe'   => ['label' => 'Severe',   'class' => 'sev-severe',   'icon' => 'fa-solid fa-triangle-exclamation'],
            'critical' => ['label' => 'Critical', 'class' => 'sev-critical', 'icon' => 'fa-solid fa-fire'],
        ],

        // ── Breathaliser — status ─────────────────────────────────────────
        'breathaliser_tests' => [
            '0' => ['label' => 'Open',     'class' => 'status-impounded', 'icon' => 'fa-solid fa-triangle-exclamation'],
            '1' => ['label' => 'Reviewed', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-magnifying-glass'],
            '2' => ['label' => 'Closed',   'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
        ],

        // ── Breathaliser — test_result ────────────────────────────────────
        'breathaliser_tests_result' => [
            'negative'   => ['label' => 'Negative',   'class' => 'status-delivered', 'icon' => 'fa-solid fa-circle-check'],
            'borderline' => ['label' => 'Borderline', 'class' => 'status-awaiting',  'icon' => 'fa-solid fa-circle-half-stroke'],
            'positive'   => ['label' => 'Positive',   'class' => 'status-impounded', 'icon' => 'fa-solid fa-circle-xmark'],
        ],

        // ── Goods Inspections — inspection status ─────────────────────────
        'goods_inspections' => [
            '0' => ['label' => 'Pending',   'class' => 'status-awaiting',  'icon' => 'fa-solid fa-hourglass-half'],
            '1' => ['label' => 'Completed', 'class' => 'status-delivered', 'icon' => 'fa-solid fa-clipboard-check'],
        ],

        // ── Goods Inspections — inspection_type ───────────────────────────
        'goods_inspections_type' => [
            'pre-departure' => ['label' => 'Pre-Departure', 'class' => 'insp-predeparture', 'icon' => 'fa-solid fa-truck-arrow-right'],
            'post-arrival'  => ['label' => 'Post-Arrival',  'class' => 'insp-postarrival',  'icon' => 'fa-solid fa-box-open'],
            'random'        => ['label' => 'Random',        'class' => 'insp-random',        'icon' => 'fa-solid fa-shuffle'],
        ],

        // ── Goods Inspections — goods_status ──────────────────────────────
        'goods_inspections_goods' => [
            'cleared' => ['label' => 'Cleared', 'class' => 'goods-cleared', 'icon' => 'fa-solid fa-boxes-stacked'],
            'issues'  => ['label' => 'Issues',  'class' => 'goods-issues',  'icon' => 'fa-solid fa-box-open'],
        ],

    ];

    // Get status configuration
    $config = $statusConfig[$table] ?? $statusConfig['waybill'];
    $statusKey = strtolower(str_replace(' ', '_', $status));
    $statusInfo = $config[$statusKey] ?? ['label' => ucfirst($status), 'class' => 'status-default', 'icon' => 'fa-solid fa-circle'];

    // Build chip classes
    $chipClasses = 'status-luxury-chip ' . ($statusInfo['class'] ?? 'status-default');
@endphp

<span class="{{ $chipClasses }}">
    <i class="{{ $statusInfo['icon'] }}"></i>
    <span class="status-luxury-chip-text">{{ $statusInfo['label'] }}</span>
    @if($count > 0)
        <span class="status-luxury-count">{{ $count }}</span>
    @endif
</span>

<style>
.status-luxury-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 14px;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.2px;
    border-radius: 999px;
    white-space: nowrap;
    border: 1.5px solid transparent;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    cursor: default;
}

/* ── Awaiting — amber/yellow ── */
.status-awaiting {
    background: #fff3cd;
    color: #856404;
    border-color: #ffc107;
}
.status-awaiting .status-luxury-count {
    background: #ffc107;
    color: #856404;
}
.status-awaiting:hover {
    background: #ffe69b;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.35);
    transform: translateY(-1px);
}

/* ── Allocated — cyan/info ── */
.status-allocated {
    background: #cff4fc;
    color: #055160;
    border-color: #0dcaf0;
}
.status-allocated .status-luxury-count {
    background: #0dcaf0;
    color: #055160;
}
.status-allocated:hover {
    background: #a6edf7;
    box-shadow: 0 4px 12px rgba(13, 202, 240, 0.35);
    transform: translateY(-1px);
}

/* ── In Transit — blue ── */
.status-transit {
    background: #cfe2ff;
    color: #084298;
    border-color: #0d6efd;
}
.status-transit .status-luxury-count {
    background: #0d6efd;
    color: #fff;
}
.status-transit:hover {
    background: #9ec5fe;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.35);
    transform: translateY(-1px);
}

/* ── Return Transit — purple ── */
.status-return {
    background: #e8d8ff;
    color: #4a2080;
    border-color: #6f42c1;
}
.status-return .status-luxury-count {
    background: #6f42c1;
    color: #fff;
}
.status-return:hover {
    background: #d0b0ff;
    box-shadow: 0 4px 12px rgba(111, 66, 193, 0.35);
    transform: translateY(-1px);
}

/* ── Delivered — green ── */
.status-delivered {
    background: #d4edda;
    color: #0f6848;
    border-color: #28a745;
}
.status-delivered .status-luxury-count {
    background: #28a745;
    color: #fff;
}
.status-delivered:hover {
    background: #b8e6c0;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.35);
    transform: translateY(-1px);
}

/* ── Completed — teal ── */
.status-completed {
    background: #d0f7ef;
    color: #0a5c4e;
    border-color: #20c997;
}
.status-completed .status-luxury-count {
    background: #20c997;
    color: #fff;
}
.status-completed:hover {
    background: #a3eedd;
    box-shadow: 0 4px 12px rgba(32, 201, 151, 0.35);
    transform: translateY(-1px);
}

/* ── Default fallback ── */
.status-default {
    background: #e2e3e5;
    color: #383d41;
    border-color: #6c757d;
}
.status-default .status-luxury-count {
    background: #6c757d;
    color: #fff;
}

/* ── Impounded — red/danger ── */
.status-impounded {
    background: #f8d7da;
    color: #842029;
    border-color: #dc3545;
}
.status-impounded .status-luxury-count {
    background: #dc3545;
    color: #fff;
}
.status-impounded:hover {
    background: #f1aeb5;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35);
    transform: translateY(-1px);
}

/* ── Released — green ── */
.status-released {
    background: #d4edda;
    color: #0f6848;
    border-color: #28a745;
}
.status-released .status-luxury-count {
    background: #28a745;
    color: #fff;
}
.status-released:hover {
    background: #b8e6c0;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.35);
    transform: translateY(-1px);
}

/* ── Goods Inspection — type pills ── */
.insp-predeparture {
    background: #e0f0ff;
    color: #084298;
    border-color: #4895ef;
}
.insp-predeparture:hover {
    background: #b8d9ff;
    box-shadow: 0 4px 12px rgba(72,149,239,0.3);
    transform: translateY(-1px);
}

.insp-postarrival {
    background: #e8d8ff;
    color: #4a2080;
    border-color: #7b5ea7;
}
.insp-postarrival:hover {
    background: #d0b0ff;
    box-shadow: 0 4px 12px rgba(123,94,167,0.3);
    transform: translateY(-1px);
}

.insp-random {
    background: #d0f7ef;
    color: #0a5c4e;
    border-color: #20c997;
}
.insp-random:hover {
    background: #a3eedd;
    box-shadow: 0 4px 12px rgba(32,201,151,0.3);
    transform: translateY(-1px);
}

/* ── Goods Inspection — goods_status pills ── */
.goods-cleared {
    background: #d4edda;
    color: #0f6848;
    border-color: #28a745;
}
.goods-cleared:hover {
    background: #b8e6c0;
    box-shadow: 0 4px 12px rgba(40,167,69,0.3);
    transform: translateY(-1px);
}

.goods-issues {
    background: #f8d7da;
    color: #842029;
    border-color: #dc3545;
}
.goods-issues:hover {
    background: #f1aeb5;
    box-shadow: 0 4px 12px rgba(220,53,69,0.3);
    transform: translateY(-1px);
}

/* ── Severity: Minor — blue-grey ── */
.sev-minor {
    background: #e2e3e5;
    color: #383d41;
    border-color: #6c757d;
}
.sev-minor:hover {
    background: #ced4da;
    box-shadow: 0 4px 12px rgba(108,117,125,0.3);
    transform: translateY(-1px);
}

/* ── Severity: Moderate — blue/info ── */
.sev-moderate {
    background: #cff4fc;
    color: #055160;
    border-color: #0dcaf0;
}
.sev-moderate:hover {
    background: #a6edf7;
    box-shadow: 0 4px 12px rgba(13,202,240,0.3);
    transform: translateY(-1px);
}

/* ── Severity: Severe — orange/warning ── */
.sev-severe {
    background: #fff3cd;
    color: #856404;
    border-color: #ffc107;
}
.sev-severe:hover {
    background: #ffe69b;
    box-shadow: 0 4px 12px rgba(255,193,7,0.3);
    transform: translateY(-1px);
}

/* ── Severity: Critical — red/danger ── */
.sev-critical {
    background: #f8d7da;
    color: #842029;
    border-color: #dc3545;
}
.sev-critical:hover {
    background: #f1aeb5;
    box-shadow: 0 4px 12px rgba(220,53,69,0.3);
    transform: translateY(-1px);
}

/* ── Count badge ── */
.status-luxury-count {
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

@media (max-width: 768px) {
    .status-luxury-chip {
        font-size: 11px;
        padding: 5px 10px;
        gap: 5px;
    }
}
</style>
