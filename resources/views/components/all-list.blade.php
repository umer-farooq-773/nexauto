<x-auth.card card-header="{{ $title }}">
    @if (isset($headerCustom))
        <x-slot name="headerCustom">
            <div class="all-list-header-custom">
                {{ $headerCustom }}
            </div>
        </x-slot>
    @endif

    <div class="all-list-body">
        {{ $slot }}
    </div>

    @if (is_object($allData) && method_exists($allData, 'links'))
        <div class="all-list-pagination">
            {{ $allData->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</x-auth.card>

<style>
    /* ═══════════════════════════════════════════════════════════════
       HEADER SLOT — gradient pills for action buttons
    ═══════════════════════════════════════════════════════════════ */
    .all-list-header-custom {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .all-list-header-custom .btn {
        position: relative;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.82rem;
        letter-spacing: 0.2px;
        padding: 0.5rem 1.15rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .all-list-header-custom .btn::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.25) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    .all-list-header-custom .btn:hover::after {
        transform: translateX(100%);
    }

    /* Primary / gradient CTA */
    .all-list-header-custom .btn-primary,
    .all-list-header-custom .btn-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none;
        color: #ffffff;
        box-shadow:
            0 2px 4px rgba(59, 130, 246, 0.2),
            inset 0 1px 0 rgba(255,255,255,0.2);
    }
    .all-list-header-custom .btn-primary:hover,
    .all-list-header-custom .btn-gradient-blue:hover {
        transform: translateY(-2px);
        box-shadow:
            0 10px 24px rgba(59, 130, 246, 0.35),
            inset 0 1px 0 rgba(255,255,255,0.25);
        color: #ffffff;
    }
    .all-list-header-custom .btn-primary:active,
    .all-list-header-custom .btn-gradient-blue:active {
        transform: translateY(0);
    }

    /* Outline variant */
    .all-list-header-custom .btn-outline-primary {
        border: 1.5px solid #c7d2fe;
        color: #4f46e5;
        background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
    }
    .all-list-header-custom .btn-outline-primary:hover {
        border-color: #6366f1;
        background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.18);
    }

    /* Danger variant */
    .all-list-header-custom .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }
    .all-list-header-custom .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(239, 68, 68, 0.35);
    }

    /* Success variant */
    .all-list-header-custom .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }
    .all-list-header-custom .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(16, 185, 129, 0.35);
    }

    /* Warning variant */
    .all-list-header-custom .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
    }
    .all-list-header-custom .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(245, 158, 11, 0.35);
        color: #ffffff;
    }

    /* Icon-only buttons */
    .all-list-header-custom .btn-icon-only {
        width: 38px;
        height: 38px;
        padding: 0;
        justify-content: center;
        border-radius: 50%;
    }

    /* ═══════════════════════════════════════════════════════════════
       BODY
    ═══════════════════════════════════════════════════════════════ */
    .all-list-body {
        position: relative;
    }
    .all-list-body > *:not(:last-child) {
        margin-bottom: 0;
    }

    /* ═══════════════════════════════════════════════════════════════
       PAGINATION — modern pills with gradient active state
    ═══════════════════════════════════════════════════════════════ */
    .all-list-pagination {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: center;
    }

    .all-list-pagination .pagination {
        margin-bottom: 0;
        gap: 6px;
        flex-wrap: wrap;
    }

    .all-list-pagination .page-link {
        border: 1px solid #e2e8f0;
        border-radius: 10px !important;
        color: #64748b;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 7px 13px;
        min-width: 38px;
        text-align: center;
        transition: all 0.2s ease;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        box-shadow: 0 1px 2px rgba(15,23,42,0.03);
    }

    .all-list-pagination .page-link:hover {
        background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%);
        border-color: #a5b4fc;
        color: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(99, 102, 241, 0.18);
    }

    .all-list-pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
        border-color: #4f46e5;
        color: #ffffff;
        box-shadow:
            0 6px 16px rgba(99, 102, 241, 0.35),
            inset 0 1px 0 rgba(255,255,255,0.2);
        transform: translateY(-1px);
    }
    .all-list-pagination .page-item.active .page-link:hover {
        transform: translateY(-1px);
    }

    .all-list-pagination .page-item.disabled .page-link {
        background: #f8fafc;
        color: #cbd5e1;
        border-color: #f1f5f9;
        box-shadow: none;
        transform: none;
        cursor: not-allowed;
    }

    /* First/Prev/Next/Last arrows */
    .all-list-pagination .page-link svg,
    .all-list-pagination .page-link i {
        vertical-align: middle;
    }

    /* ═══════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 640px) {
        .all-list-header-custom {
            justify-content: stretch;
        }
        .all-list-header-custom .btn {
            flex: 1;
            justify-content: center;
        }
        .all-list-pagination .page-link {
            padding: 6px 10px;
            font-size: 0.8rem;
            min-width: 34px;
        }
    }
</style>
