{{-- resources/views/auth/pages/waybill/pdf/_styles.blade.php --}}
<style>
    @page {
        margin: 28px 32px;
    }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #212529;
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }

    /* Page break control */
    .page-break {
        page-break-after: always;
        border-bottom: 1px dashed #dee2e6;
        margin: 20px 0;
    }

    .page-break:last-child {
        page-break-after: auto;
        border-bottom: none;
    }

    .pdf-header {
        margin-bottom: 14px;
        border-bottom: 2px solid #1a3c6e;
        padding-bottom: 10px;
    }

    .pdf-header .title {
        font-size: 18px;
        font-weight: bold;
        color: #1a3c6e;
        margin: 0 0 2px 0;
    }

    .pdf-header .subtitle {
        font-size: 11px;
        color: #6c757d;
        margin: 0;
    }

    .pdf-card {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        margin-bottom: 16px;
        width: 100%;
        page-break-inside: avoid;
    }

    .pdf-card-header {
        padding: 10px 14px 4px 14px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 6px 6px 0 0;
    }

    .pdf-card-header .card-title {
        font-size: 13px;
        font-weight: bold;
        color: #1a3c6e;
        margin: 0 0 2px 0;
    }

    .pdf-card-header .card-subtitle {
        font-size: 9px;
        color: #6c757d;
        margin: 0;
    }

    .pdf-card-body {
        padding: 12px 14px 14px 14px;
    }

    /* Info grid */
    table.info-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px;
    }

    table.info-grid td.info-cell {
        width: 50%;
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px 10px;
        vertical-align: top;
    }

    table.info-grid td.info-cell.full-width {
        width: 100%;
    }

    .info-label {
        display: block;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: bold;
        margin-bottom: 3px;
    }

    .info-value {
        font-size: 10.5px;
        color: #212529;
        font-weight: 500;
    }

    .info-value.highlight {
        color: #0d6efd;
        font-weight: bold;
    }

    /* Vehicle info list */
    table.vehicle-info {
        width: 100%;
        border-collapse: collapse;
    }

    table.vehicle-info td {
        padding: 6px 0;
        border-bottom: 1px solid #f8f9fa;
        font-size: 10.5px;
    }

    table.vehicle-info tr:last-child td {
        border-bottom: none;
    }

    table.vehicle-info td.v-label {
        color: #6c757d;
        font-size: 9.5px;
        width: 45%;
    }

    table.vehicle-info td.v-value {
        color: #212529;
        font-weight: 500;
        text-align: right;
        width: 55%;
    }

    /* Status badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
    }
    .status-badge.pending { background: #fff3cd; color: #856404; }
    .status-badge.approved { background: #d4edda; color: #155724; }
    .status-badge.processed { background: #cce5ff; color: #004085; }
    .status-badge.resolved { background: #d4edda; color: #155724; }
    .status-badge.progress { background: #cce5ff; color: #004085; }
    .status-badge.secondary { background: #e2e3e5; color: #383d41; }
    .status-badge.despatch { background: #17a2b8; color: #ffffff; }
    .status-badge.return_transit { background: #6f42c1; color: #ffffff; }
    .status-badge.delivered { background: #28a745; color: #ffffff; }
    .status-badge.impoundment { background: #dc3545; color: #ffffff; }
    .status-badge.accident { background: #dc3545; color: #ffffff; }

    /* Incident tables */
    .incident-section {
        margin-top: 18px;
        page-break-inside: avoid;
    }

    .incident-section .section-title {
        font-size: 13px;
        font-weight: bold;
        color: #1a3c6e;
        margin: 0 0 2px 0;
    }

    .incident-section .section-subtitle {
        font-size: 9px;
        color: #6c757d;
        margin: 0 0 10px 0;
    }

    .incident-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
        border: 1px solid #e9ecef;
        page-break-inside: auto;
    }

    .incident-table caption {
        text-align: left;
        font-size: 10.5px;
        font-weight: bold;
        padding: 6px 8px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-bottom: none;
    }

    .incident-table thead {
        display: table-header-group;
    }

    .incident-table th {
        background: #f8f9fa;
        color: #495057;
        font-size: 8.5px;
        text-transform: uppercase;
        text-align: left;
        padding: 6px 8px;
        border: 1px solid #e9ecef;
    }

    .incident-table td {
        font-size: 9.5px;
        padding: 6px 8px;
        border: 1px solid #e9ecef;
    }

    .incident-table tr {
        page-break-inside: avoid;
    }

    .text-muted {
        color: #6c757d;
    }

    .pdf-footer-note {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #e9ecef;
        font-size: 8px;
        color: #adb5bd;
        text-align: center;
    }

    /* Utility classes */
    .mb-1 { margin-bottom: 4px; }
    .mb-2 { margin-bottom: 8px; }
    .mb-3 { margin-bottom: 12px; }
    .mt-1 { margin-top: 4px; }
    .mt-2 { margin-top: 8px; }
    .mt-3 { margin-top: 12px; }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-small { font-size: 9px; }
</style>
