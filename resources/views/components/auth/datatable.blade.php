@push('auth_styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
@endpush

{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<div class="table-responsive scrollable-tbody dt-premium-shell">
    <div class="dt-premium-header">
        <div class="dt-premium-accent"></div>
        <div class="dt-premium-title">
            <i class="ri-table-2"></i>
            <span>{{ __('Data Records') }}</span>
        </div>
    </div>
    <div class="dt-premium-body">
        <table id="{{ $id }}" class="table text-start align-middle mb-0 dt-premium-table">
            {{ $slot }}
        </table>
    </div>
</div>

@if($init ?? true)
@push('auth_scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $("#{{ $id }}").DataTable({
            responsive: false,
            ordering: true,
            order: [],
            select: false,
            paging: false,
            info: false,
            searching: '{{ $search ? true : false }}',
        });
</script>
@endpush
@endif

<style>
    /* ═══════════════════════════════════════════════════════════════
       PREMIUM TABLE SHELL
    ═══════════════════════════════════════════════════════════════ */
    .dt-premium-shell {
        position: relative;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow:
            0 1px 2px rgba(15,23,42,0.04),
            0 8px 24px -8px rgba(15,23,42,0.06);
        transition: box-shadow 0.3s ease;
    }
    .dt-premium-shell:hover {
        box-shadow:
            0 1px 2px rgba(15,23,42,0.05),
            0 16px 40px -12px rgba(15,23,42,0.10);
    }

    /* ── Top accent bar ── */
    .dt-premium-header {
        position: relative;
        padding: 14px 20px 14px 22px;
        background: linear-gradient(180deg, #fbfcfe 0%, #f8fafc 100%);
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dt-premium-accent {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #6366f1 0%, #3b82f6 100%);
    }
    .dt-premium-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        letter-spacing: -0.01em;
        text-transform: uppercase;
    }
    .dt-premium-title i {
        font-size: 16px;
        color: #6366f1;
        background: #eef2ff;
        padding: 5px 6px;
        border-radius: 7px;
    }

    /* ── Body ── */
    .dt-premium-body {
        padding: 0 0 8px;
        overflow-x: auto;
    }
    .dt-premium-body::-webkit-scrollbar { height: 8px; }
    .dt-premium-body::-webkit-scrollbar-track { background: #f1f5f9; }
    .dt-premium-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
        border-radius: 8px;
    }
    .dt-premium-body::-webkit-scrollbar-thumb:hover { background: #64748b; }

    /* ═══════════════════════════════════════════════════════════════
       PREMIUM TABLE
    ═══════════════════════════════════════════════════════════════ */
    .dt-premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
        font-size: 0.875rem;
        color: #1e293b;
        background: #ffffff;
    }

    /* ── Header ── */
    .dt-premium-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #475569;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-weight: 800;
        padding: 14px 16px;
        border: none;
        border-bottom: 2px solid #eef2f7;
        white-space: nowrap;
        vertical-align: middle;
        transition: color 0.2s ease;
    }
    .dt-premium-table thead th:first-child { padding-left: 22px; }
    .dt-premium-table thead th:last-child { padding-right: 22px; }

    /* ── Body cells ── */
    .dt-premium-table tbody td {
        padding: 15px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.15s ease;
    }
    .dt-premium-table tbody td:first-child { padding-left: 22px; }
    .dt-premium-table tbody td:last-child { padding-right: 22px; }

    /* ── Row hover with left indicator ── */
    .dt-premium-table tbody tr {
        position: relative;
        transition: all 0.18s ease;
    }
    .dt-premium-table tbody tr::after {
        content: '';
        position: absolute;
        left: 0; top: 6px; bottom: 6px;
        width: 3px;
        background: linear-gradient(180deg, #6366f1, #3b82f6);
        border-radius: 0 3px 3px 0;
        opacity: 0;
        transform: scaleY(0.4);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dt-premium-table tbody tr:hover {
        background: linear-gradient(90deg, #f8fafc 0%, #fbfdff 100%);
    }
    .dt-premium-table tbody tr:hover::after {
        opacity: 1;
        transform: scaleY(1);
    }
    .dt-premium-table tbody tr:hover td {
        color: #0f172a;
    }
    .dt-premium-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ── Zebra striping (subtle) ── */
    .dt-premium-table tbody tr:nth-child(even) {
        background: #fcfdfe;
    }
    .dt-premium-table tbody tr:nth-child(even):hover {
        background: linear-gradient(90deg, #f8fafc 0%, #fbfdff 100%);
    }

    /* ═══════════════════════════════════════════════════════════════
       SORT INDICATORS
    ═══════════════════════════════════════════════════════════════ */
    .dt-premium-table thead th.sorting,
    .dt-premium-table thead th.sorting_asc,
    .dt-premium-table thead th.sorting_desc {
        cursor: pointer;
        user-select: none;
    }
    .dt-premium-table thead th.sorting:hover,
    .dt-premium-table thead th.sorting_asc:hover,
    .dt-premium-table thead th.sorting_desc:hover {
        color: #4f46e5;
    }
    .dt-premium-table thead th.sorting::after,
    .dt-premium-table thead th.sorting_asc::after,
    .dt-premium-table thead th.sorting_desc::after {
        content: '';
        display: inline-block;
        margin-left: 6px;
        vertical-align: middle;
        font-size: 12px;
        font-family: 'remixicon' !important;
        transition: all 0.2s ease;
    }
    .dt-premium-table thead th.sorting::after {
        content: '\ea4c';
        opacity: .35;
    }
    .dt-premium-table thead th.sorting_asc::after {
        content: '\ea4e';
        color: #4f46e5;
        opacity: 1;
        transform: translateY(-1px);
    }
    .dt-premium-table thead th.sorting_desc::after {
        content: '\ea4d';
        color: #4f46e5;
        opacity: 1;
        transform: translateY(1px);
    }

    /* ═══════════════════════════════════════════════════════════════
       DATATABLES SEARCH INPUT
    ═══════════════════════════════════════════════════════════════ */
    .dt-premium-shell .dataTables_filter {
        padding: 12px 22px 14px;
        display: flex;
        justify-content: flex-end;
    }
    .dt-premium-shell .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        margin: 0;
    }
    .dt-premium-shell .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 14px 8px 36px;
        font-size: 0.85rem;
        color: #1e293b;
        outline: none;
        transition: all 0.25s ease;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E") no-repeat 12px center;
        min-width: 220px;
    }
    .dt-premium-shell .dataTables_filter input:focus {
        border-color: #6366f1;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }

    /* ═══════════════════════════════════════════════════════════════
       SPECIAL CELL TREATMENTS
    ═══════════════════════════════════════════════════════════════ */

    /* Numeric columns right-aligned */
    .dt-premium-table tbody td.text-end,
    .dt-premium-table tbody td[align="right"] {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-feature-settings: 'tnum';
    }

    /* Primary key / first column emphasis */
    .dt-premium-table tbody td:first-child strong,
    .dt-premium-table tbody td:first-child .fw-bold {
        color: #0f172a;
        font-weight: 700;
    }

    /* Badges inside table */
    .dt-premium-table .badge,
    .dt-premium-table .chip {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 50px;
        letter-spacing: 0.2px;
    }

    /* Action buttons inside table */
    .dt-premium-table .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.78rem;
        padding: 5px 12px;
        transition: all 0.2s ease;
    }
    .dt-premium-table .btn-sm {
        padding: 5px 10px;
        font-size: 0.75rem;
    }
    .dt-premium-table .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none;
    }
    .dt-premium-table .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
    }
    .dt-premium-table .btn-outline-primary:hover {
        background: #eff6ff;
    }

    /* ═══════════════════════════════════════════════════════════════
       SCROLLABLE TBODY (when used by parent)
    ═══════════════════════════════════════════════════════════════ */
    .scrollable-tbody {
        overflow-x: auto;
    }

    /* ═══════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
        .dt-premium-header {
            padding: 12px 16px 12px 18px;
        }
        .dt-premium-table thead th,
        .dt-premium-table tbody td {
            padding: 12px 12px;
            font-size: 0.8rem;
        }
        .dt-premium-table thead th:first-child,
        .dt-premium-table tbody td:first-child { padding-left: 16px; }
        .dt-premium-table thead th:last-child,
        .dt-premium-table tbody td:last-child { padding-right: 16px; }
        .dt-premium-shell .dataTables_filter input {
            min-width: 160px;
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       PRINT
    ═══════════════════════════════════════════════════════════════ */
    @media print {
        .dt-premium-shell {
            box-shadow: none;
            border: 1px solid #ddd;
        }
        .dt-premium-header { display: none; }
        .dt-premium-table thead th {
            background: #f8f9fa !important;
            color: #000 !important;
        }
        .dt-premium-table tbody tr::after { display: none; }
    }
</style>
