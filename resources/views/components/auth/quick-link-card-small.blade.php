<div class="col-md-6 col-xl-3">
    <div
        class="stat-card bg-light rounded-4 d-flex align-items-center p-3 h-100"
        @if ($route !== '#')
            onclick="window.location='{{ $route }}'"
        @endif
        style="cursor: {{ $route !== '#' ? 'pointer' : 'default' }};">

        {{-- Icon badge --}}
        <div class="stat-icon rounded-circle {{ $iconbg }} d-flex align-items-center justify-content-center flex-shrink-0"
            style="width: 52px; height: 52px;">
            <i class="fas {{ $icon }} fa-lg {{ $icontext }}"></i>
        </div>

        {{-- Text --}}
        <div class="ms-3 stat-copy">
            <h4 class="fw-bold m-0 stat-title">{{ $title }}</h4>
            <small class="text-muted stat-desc">{{ $description }}</small>
        </div>

        {{-- Hover chevron (only shows when clickable) --}}
        @if ($route !== '#')
            <i class="fas fa-arrow-right stat-chevron"></i>
        @endif
    </div>
</div>

{{-- Styles — include once per page or in your styles stack --}}
<style>
    /* ═══════════════════════════════════════════════════════════════
       STAT CARD
    ═══════════════════════════════════════════════════════════════ */
    .stat-card {
        position: relative;
        overflow: hidden;
        background: #ffffff !important;
        border: 1px solid #e2e8f0;
        border-radius: 16px !important;
        padding: 1.1rem 1.25rem !important;
        transition:
            transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
            box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1),
            border-color 0.3s ease;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 4px 12px -4px rgba(15, 23, 42, 0.06);
    }

    /* Soft gradient layer (subtle) */
    .stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, rgba(59, 130, 246, 0.03) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    /* Left accent bar (reveals on hover) */
    .stat-card::after {
        content: '';
        position: absolute;
        left: 0;
        top: 12%;
        bottom: 12%;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(180deg, #6366f1 0%, #3b82f6 100%);
        transform: scaleY(0);
        transform-origin: center;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    }

    /* Hover state */
    .stat-card:hover {
        transform: translateY(-4px);
        border-color: #cbd5e1;
        box-shadow:
            0 4px 8px -2px rgba(15, 23, 42, 0.06),
            0 16px 40px -12px rgba(15, 23, 42, 0.14);
    }
    .stat-card:hover::before {
        opacity: 1;
    }
    .stat-card:hover::after {
        transform: scaleY(1);
    }

    /* ── Icon badge ── */
    .stat-icon {
        position: relative;
        z-index: 1;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        box-shadow: 0 4px 12px -4px rgba(15, 23, 42, 0.15);
    }
    .stat-icon i {
        transition: transform 0.3s ease;
    }
    .stat-card:hover .stat-icon {
        transform: scale(1.08) rotate(-4deg);
        box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.22);
    }
    .stat-card:hover .stat-icon i {
        transform: scale(1.05);
    }

    /* ── Text ── */
    .stat-copy {
        position: relative;
        z-index: 1;
        min-width: 0;
        flex: 1;
    }
    .stat-title {
        font-size: 1.5rem;
        line-height: 1.1;
        color: #0f172a;
        letter-spacing: -0.02em;
        transition: color 0.25s ease, transform 0.25s ease;
        font-variant-numeric: tabular-nums;
    }
    .stat-card:hover .stat-title {
        color: #4f46e5;
    }
    .stat-desc {
        display: block;
        margin-top: 2px;
        font-size: 0.8rem;
        font-weight: 500;
        letter-spacing: 0.1px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.25s ease;
    }
    .stat-card:hover .stat-desc {
        color: #475569;
    }

    /* ── Chevron (only for clickable cards) ── */
    .stat-chevron {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%) translateX(-6px);
        font-size: 14px;
        color: #94a3b8;
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }
    .stat-card:hover .stat-chevron {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
        color: #6366f1;
    }

    /* ── Focus state for keyboard accessibility (only when clickable) ── */
    .stat-card[style*="pointer"]:focus-visible {
        outline: 3px solid rgba(99, 102, 241, 0.4);
        outline-offset: 3px;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .stat-card {
            padding: 1rem 1.1rem !important;
        }
        .stat-title {
            font-size: 1.25rem;
        }
        .stat-desc {
            font-size: 0.75rem;
        }
        .stat-chevron {
            right: 1rem;
        }
    }

    /* ── Print ── */
    @media print {
        .stat-card {
            box-shadow: none;
            border: 1px solid #ddd;
            transform: none !important;
        }
        .stat-card::after,
        .stat-chevron {
            display: none;
        }
    }
</style>
