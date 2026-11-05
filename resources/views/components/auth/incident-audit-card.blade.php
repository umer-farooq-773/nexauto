@props(['data'])

<div class="card border-0 shadow-sm rounded-4 mb-4 incident-audit-card">
    <div class="card-header bg-white border-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <span class="header-icon">
                <i class="fas fa-history"></i>
            </span>
            {{ __('Activity & Approval Log') }}
        </h6>
        @if($data->client_approved)
            <span class="status-pill status-approved">
                <span class="status-dot"></span>
                {{ __('Approved') }}
            </span>
        @else
            <span class="status-pill status-pending">
                <span class="status-dot"></span>
                {{ __('Pending') }}
            </span>
        @endif
    </div>

    <div class="card-body px-4 pb-4 pt-1">
        <div class="audit-timeline">
            {{-- Creation Log Item --}}
            <div class="audit-item">
                <div class="audit-connector"></div>
                <div class="audit-icon-wrap">
                    <span class="audit-icon icon-create">
                        <i class="fas fa-plus"></i>
                    </span>
                </div>
                <div class="audit-content">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <span class="audit-title">{{ __('Incident Created') }}</span>
                        <span class="audit-time">
                            <i class="far fa-clock me-1"></i>
                            {{ $data->created_at ? $data->created_at->format('d M Y, h:i A') : '-' }}
                        </span>
                    </div>
                    <div class="audit-meta">
                        <span class="meta-avatar">
                            {{ strtoupper(substr($data->created_by_name ?? 'S', 0, 1)) }}
                        </span>
                        <div class="meta-info">
                            <span class="meta-name">{{ $data->created_by_name ?? 'System' }}</span>
                            <span class="meta-role">{{ $data->created_by_role ?? 'User' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approval Log Item --}}
            <div class="audit-item">
                <div class="audit-icon-wrap">
                    <span class="audit-icon {{ $data->client_approved ? 'icon-approved' : 'icon-pending' }}">
                        <i class="fas fa-{{ $data->client_approved ? 'check' : 'hourglass-half' }}"></i>
                    </span>
                </div>
                <div class="audit-content">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <span class="audit-title">{{ __('Approval') }}</span>
                        @if($data->client_approved && $data->client_approved_at)
                            <span class="audit-time">
                                <i class="far fa-clock me-1"></i>
                                {{ $data->client_approved_at instanceof \Carbon\Carbon ? $data->client_approved_at->format('d M Y, h:i A') : \Carbon\Carbon::parse($data->client_approved_at)->format('d M Y, h:i A') }}
                            </span>
                        @endif
                    </div>

                    @if($data->client_approved)
                        <div class="audit-meta">
                            <span class="meta-avatar avatar-success">
                                {{ strtoupper(substr($data->approved_by_name ?? 'A', 0, 1)) }}
                            </span>
                            <div class="meta-info">
                                <span class="meta-name">{{ $data->approved_by_name ?? 'Admin' }}</span>
                                <span class="meta-role">{{ $data->approved_by_role ?? 'Client Director' }}</span>
                            </div>
                        </div>
                        @if(!empty($data->client_approved_comment))
                            <div class="audit-note">
                                <i class="fas fa-quote-left"></i>
                                <span>{{ $data->client_approved_comment }}</span>
                            </div>
                        @endif
                    @else
                        <div class="audit-pending">
                            <span class="pending-pulse"></span>
                            {{ __('Awaiting approval from Client Director / Admin') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== Base Card ===== */
    .incident-audit-card {
        background: #ffffff;
        border: 1px solid #eef0f4 !important;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }


    /* ===== Header ===== */
    .incident-audit-card .header-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4f46e5;
        font-size: 13px;
    }

    /* ===== Status Pills ===== */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 50px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-approved {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .status-approved .status-dot {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .status-pending {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .status-pending .status-dot {
        background: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        animation: pulseDot 2s infinite;
    }
    @keyframes pulseDot {
        0%, 100% { box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15); }
        50% { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.08); }
    }

    /* ===== Timeline ===== */
    .audit-timeline {
        position: relative;
        padding-top: 8px;
    }

    .audit-item {
        position: relative;
        display: flex;
        gap: 16px;
        padding-bottom: 28px;
    }
    .audit-item:last-child {
        padding-bottom: 0;
    }

    /* Connector line between items */
    .audit-connector {
        position: absolute;
        left: 19px;
        top: 44px;
        bottom: -4px;
        width: 2px;
        background: linear-gradient(to bottom, #e5e7eb 60%, transparent);
        border-radius: 2px;
    }
    .audit-item:last-child .audit-connector {
        display: none;
    }

    /* ===== Icon ===== */
    .audit-icon-wrap {
        flex-shrink: 0;
        z-index: 1;
    }
    .audit-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        position: relative;
        transition: transform 0.2s ease;
    }
    .audit-item:hover .audit-icon {
        transform: scale(1.05);
    }
    .icon-create {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.15);
    }
    .icon-approved {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #059669;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
    }
    .icon-pending {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        color: #d97706;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
    }

    /* ===== Content ===== */
    .audit-content {
        flex: 1;
        min-width: 0;
        padding-top: 4px;
    }

    .audit-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #111827;
        letter-spacing: -0.1px;
    }

    .audit-time {
        font-size: 0.73rem;
        color: #9ca3af;
        font-weight: 500;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        background: #f9fafb;
        padding: 3px 10px;
        border-radius: 6px;
    }

    /* ===== Meta (avatar + name + role) ===== */
    .audit-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: #f9fafb;
        border-radius: 10px;
        border: 1px solid #f3f4f6;
        transition: background 0.2s ease;
    }
    .audit-item:hover .audit-meta {
        background: #f3f4f6;
    }

    .meta-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        flex-shrink: 0;
        letter-spacing: 0.5px;
    }
    .meta-avatar.avatar-success {
        background: linear-gradient(135deg, #10b981, #34d399);
    }

    .meta-info {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
        min-width: 0;
    }
    .meta-name {
        font-size: 0.82rem;
        font-weight: 600;
        color: #1f2937;
    }
    .meta-role {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: capitalize;
        font-weight: 500;
    }

    /* ===== Approval Note ===== */
    .audit-note {
        margin-top: 10px;
        display: flex;
        gap: 10px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-radius: 10px;
        border-left: 3px solid #10b981;
        font-size: 0.8rem;
        color: #374151;
        line-height: 1.5;
        font-style: italic;
    }
    .audit-note i {
        color: #10b981;
        font-size: 12px;
        margin-top: 3px;
        flex-shrink: 0;
    }

    /* ===== Pending State ===== */
    .audit-pending {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8rem;
        color: #92400e;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px dashed #fcd34d;
        font-weight: 500;
    }
    .pending-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f59e0b;
        display: inline-block;
        animation: pulseDot 2s infinite;
        flex-shrink: 0;
    }

    /* ===== Responsive ===== */
    @media (max-width: 576px) {
        .incident-audit-card .card-header {
            flex-wrap: wrap;
            gap: 10px;
        }
        .audit-item {
            gap: 12px;
        }
        .audit-icon {
            width: 36px;
            height: 36px;
            font-size: 13px;
        }
        .audit-connector {
            left: 17px;
            top: 40px;
        }
        .audit-time {
            font-size: 0.7rem;
        }
    }
</style>
