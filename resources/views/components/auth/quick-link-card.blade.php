<div class="col-sm-6 col-xl-3">
    <div
        onclick="window.location='{{ $route }}'"
        class="mini-stat-card bg-light rounded-4 d-flex align-items-center py-3 px-3 h-100"
        style="cursor: pointer;">

        {{-- Icon badge --}}
        <div class="icon-box mini-stat-icon {{ $iconbg }} {{ $icontext }} text-primary d-flex align-items-center justify-content-center flex-shrink-0">
            <i class="fas {{ $icon }} fa-2x"></i>
        </div>

        {{-- Text --}}
        <div class="ms-3 mini-stat-copy">
            <p class="mb-2 mini-stat-label">{{ $title }}</p>
            <h6 class="mb-0 mini-stat-value">{{ $description }}</h6>
        </div>

        {{-- Chevron --}}
        <i class="fas fa-arrow-right mini-stat-chevron"></i>
    </div>
</div>

{{-- Styles --}}
<style>
    /* ═══════════════════════════════════════════════════════════════
       MINI STAT CARD
    ═══════════════════════════════════════════════════════════════ */
    .mini-stat-card {
        position: relative;
        overflow: hidden;
        background: #ffffff !important;
        border: 1px solid #e2e8f0;
        border-radius: 16px !important;
        padding: 0.95rem 1.1rem !important;
        transition:
            transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
            box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1),
            border-color 0.3s ease;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 4px 12px -4px rgba(15, 23, 42, 0.06);
    }

    /* Subtle gradient tint layer */
    .mini-stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.035) 0%, rgba(59, 130, 246, 0.035) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    /* Left accent bar */
    .mini-stat-card::after {
        content: '';
        position: absolute;
        left: 0;
        top: 15%;
        bottom: 15%;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(180deg, #6366f1 0%, #3b82f6 100%);
        transform: scaleY(0);
        transform-origin: center;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    }

    /* Hover */
    .mini-stat-card:hover {
        transform: translateY(-4px);
        border-color: #cbd5e1;
        box-shadow:
            0 4px 8px -2px rgba(15, 23, 42, 0.06),
            0 16px 40px -12px rgba(15, 23, 42, 0.14);
    }
    .mini-stat-card:hover::before {
        opacity: 1;
    }
    .mini-stat-card:hover::after {
        transform: scaleY(1);
    }

    /* ── Icon badge ── */
    .mini-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px !important;
        box-shadow: 0 4px 12px -4px rgba(15, 23, 42, 0.15);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        position: relative;
        z-index: 1;
    }
    .mini-stat-icon i {
        transition: transform 0.3s ease;
    }
    .mini-stat-card:hover .mini-stat-icon {
        transform: scale(1.08) rotate(-4deg);
        box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.22);
    }
    .mini-stat-card:hover .mini-stat-icon i {
        transform: scale(1.05);
    }

    /* ── Copy ── */
    .mini-stat-copy {
        position: relative;
        z-index: 1;
        min-width: 0;
        flex: 1;
    }

    .mini-stat-label {
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b !important;
        margin-bottom: 6px !important;
        transition: color 0.25s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mini-stat-card:hover .mini-stat-label {
        color: #4f46e5 !important;
    }

    .mini-stat-value {
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.2;
        letter-spacing: -0.01em;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Chevron ── */
    .mini-stat-chevron {
        position: absolute;
        right: 1.1rem;
        top: 50%;
        transform: translateY(-50%) translateX(-6px);
        font-size: 13px;
        color: #94a3b8;
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        z-index: 1;
    }
    .mini-stat-card:hover .mini-stat-chevron {
        opacity: 1;
        transform: translateY(-50%) translateX(0);
        color: #6366f1;
    }

    /* Keyboard focus */
    .mini-stat-card:focus-visible {
        outline: 3px solid rgba(99, 102, 241, 0.4);
        outline-offset: 3px;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .mini-stat-card {
            padding: 0.85rem 1rem !important;
        }
        .mini-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px !important;
        }
        .mini-stat-icon i {
            font-size: 1.35rem !important;
        }
        .mini-stat-value {
            font-size: 1.05rem !important;
        }
        .mini-stat-label {
            font-size: 0.72rem !important;
        }
        .mini-stat-chevron {
            right: 0.9rem;
        }
    }

    /* ── Print ── */
    @media print {
        .mini-stat-card {
            box-shadow: none;
            border: 1px solid #ddd;
            transform: none !important;
        }
        .mini-stat-card::after,
        .mini-stat-chevron {
            display: none;
        }
    }
</style>
