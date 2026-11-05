@props(['payments'])

@if($payments && $payments->count() > 0)
<div class="payment-history-wrapper mt-4">
    {{-- Header --}}
    <div class="payment-header">
        <div class="header-text">
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="header-icon">
                    <i class="fas fa-receipt"></i>
                </span>
                <h5 class="mb-0 fw-semibold text-dark">
                    {{ __('Payment History') }}
                    <span class="count-badge">{{ $payments->count() }}</span>
                </h5>
            </div>
            <small class="text-muted ps-1">{{ __('Complete payment records with expense breakdown') }}</small>
        </div>
        <div class="payment-stats">
            <div class="stat-badge">
                <span class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
                <div class="stat-content">
                    <span class="stat-label">{{ __('Total Paid') }}</span>
                    <span class="stat-value">{{ number_format($payments->sum('paid_amount'), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Cards --}}
    @foreach($payments as $payment)
    <div class="payment-card {{ !$loop->last ? 'mb-4' : '' }}">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                {{-- Header --}}
                <div class="payment-card-header">
                    <div class="header-left">
                        <span class="meta-chip chip-date">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y, h:i A') : 'N/A' }}
                        </span>
                        <span class="meta-chip chip-user">
                            <i class="fas fa-user"></i>
                            {{ $payment->processed_by_name ?? 'System' }}
                        </span>
                        @if($loop->first)
                        <span class="meta-chip chip-latest">
                            <i class="fas fa-star"></i> {{ __('Latest') }}
                        </span>
                        @endif
                    </div>
                    <div class="header-right">
                        <div class="paid-amount-badge">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>{{ number_format($payment->paid_amount ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Details --}}
                <div class="payment-details-grid">
                    {{-- Left Column --}}
                    <div class="detail-column">
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-credit-card"></i>
                                {{ __('Payment Method') }}
                            </span>
                            <span class="detail-value">
                                <span class="method-badge">
                                    <i class="fas fa-{{ $payment->payment_method == 'cash' ? 'money-bill' : ($payment->payment_method == 'bank_transfer' ? 'university' : ($payment->payment_method == 'credit_card' ? 'credit-card' : 'receipt')) }}"></i>
                                    {{ $payment->payment_method_label ?? $payment->payment_method ?? '-' }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-hashtag"></i>
                                {{ __('Reference Number') }}
                            </span>
                            <span class="detail-value">{{ $payment->reference_number ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-user-tie"></i>
                                {{ __('Paid To') }}
                            </span>
                            <span class="detail-value">{{ $payment->paid_to ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-building"></i>
                                {{ __('Authority') }}
                            </span>
                            <span class="detail-value">{{ $payment->authority ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="detail-column">
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-tag"></i>
                                {{ __('Original Cost') }}
                            </span>
                            <span class="detail-value text-muted">{{ $payment->original_cost ? number_format($payment->original_cost, 2) : '-' }}</span>
                        </div>

                        {{-- Notes Row (stacked layout) --}}
                        <div class="detail-row detail-row-notes">
                            <span class="detail-label">
                                <i class="fas fa-sticky-note"></i>
                                {{ __('Notes') }}
                            </span>
                            <span class="detail-value detail-notes">{{ Str::limit($payment->notes ?? '-', 100) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Expense Breakdown --}}
                @if($payment->expense_details && is_array($payment->expense_details) && count($payment->expense_details) > 0)
                <div class="expense-breakdown">
                    <div class="expense-header">
                        <div class="expense-title">
                            <span class="expense-icon">
                                <i class="fas fa-list-ul"></i>
                            </span>
                            <span class="expense-label">{{ __('Expense Breakdown') }}</span>
                            <span class="count-badge count-sm">{{ count($payment->expense_details) }}</span>
                        </div>
                        <div class="expense-total">
                            <span class="total-label">{{ __('Total') }}</span>
                            <span class="total-value">{{ number_format(collect($payment->expense_details)->sum('price'), 2) }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="expense-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">{{ __('#') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Authority') }}</th>
                                    <th>{{ __('Paid To') }}</th>
                                    <th class="text-end" style="width: 130px;">{{ __('Price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->expense_details as $index => $expense)
                                <tr>
                                    <td class="text-center">
                                        <span class="row-index">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <span class="method-tag {{ $expense['type'] == 'main' ? 'method-main' : 'method-extra' }}">
                                            <i class="fas fa-{{ $expense['type'] == 'main' ? 'home' : 'plus-circle' }}"></i>
                                            {{ ucfirst($expense['payment_method'] ?? 'expense') }}
                                        </span>
                                    </td>
                                    <td class="cell-muted">{{ $expense['reference_number'] }}</td>
                                    <td class="cell-muted">{{ $expense['authority'] ?? '-' }}</td>
                                    <td class="cell-muted">{{ $expense['paid_to'] ?? '-' }}</td>
                                    <td class="text-end">
                                        <span class="price-tag">{{ number_format($expense['price'] ?? 0, 2) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">
                                        <i class="fas fa-calculator me-1"></i>
                                        {{ __('Total Expenses') }}
                                    </td>
                                    <td class="text-end">
                                        <span class="total-price">{{ number_format(collect($payment->expense_details)->sum('price'), 2) }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Notes Section --}}
                @if($payment->notes)
                <div class="notes-section">
                    <div class="notes-header">
                        <i class="fas fa-sticky-note"></i>
                        <span>{{ __('Notes') }}</span>
                    </div>
                    <div class="notes-content">
                        <p>{{ $payment->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Styles --}}
<style>
    /* ===== Wrapper ===== */
    .payment-history-wrapper {
        position: relative;
    }

    /* ===== Header ===== */
    .payment-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
        padding: 20px 24px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        border: 1px solid #eef0f4;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .header-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-size: 15px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }

    .count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 50px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        margin-left: 8px;
        vertical-align: middle;
    }
    .count-badge.count-sm {
        min-width: 20px;
        height: 20px;
        font-size: 0.65rem;
        background: #e5e7eb;
        color: #4b5563;
    }

    /* ===== Stat Badge ===== */
    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #eef0f4;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .stat-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #059669;
        font-size: 14px;
    }
    .stat-content {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        font-weight: 600;
    }
    .stat-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #059669;
    }

    /* ===== Payment Card ===== */
    .payment-card .card {
        background: #ffffff;
        border: 1px solid #eef0f4 !important;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }
    .payment-card .card:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.07) !important;
        transform: translateY(-2px);
    }

    /* ===== Card Header ===== */
    .payment-card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f3f4f6;
    }
    .header-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }
    .meta-chip i {
        font-size: 0.7rem;
    }
    .chip-date {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #e0e7ff;
    }
    .chip-user {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .chip-latest {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: #ffffff;
        border: none;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    /* ===== Paid Amount Badge ===== */
    .paid-amount-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 50px;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #059669;
        border: 1px solid #a7f3d0;
        font-size: 1rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
    }
    .paid-amount-badge i {
        font-size: 0.85rem;
    }

    /* ===== Details Grid ===== */
    .payment-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .detail-column {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        transition: background 0.2s ease, border-color 0.2s ease;
        min-height: 50px;
    }
    .detail-row:hover {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }

    .detail-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        font-weight: 600;
        flex-shrink: 0;
    }
    .detail-label i {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .detail-value {
        font-size: 0.88rem;
        color: #1f2937;
        font-weight: 500;
        text-align: right;
        word-break: break-word;
        max-width: 65%;
    }
    .detail-notes {
        font-weight: 400;
        color: #6b7280;
        font-size: 0.82rem;
        line-height: 1.4;
    }

    /* ===== Method Badge ===== */
    .method-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 50px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .method-badge i {
        font-size: 0.7rem;
    }

    /* ===== Expense Breakdown ===== */
    .expense-breakdown {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
    }

    .expense-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .expense-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .expense-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: #4f46e5;
        font-size: 12px;
    }
    .expense-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
        color: #4b5563;
    }

    .expense-total {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        border-radius: 50px;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        border: 1px solid #c7d2fe;
    }
    .total-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6366f1;
        font-weight: 600;
    }
    .total-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #4f46e5;
    }

    /* ===== Expense Table ===== */
    .expense-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.85rem;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #f3f4f6;
    }

    .expense-table thead th {
        background: #f9fafb;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
        color: #6b7280;
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
        white-space: nowrap;
    }
    .expense-table thead th.text-end {
        text-align: right;
    }
    .expense-table thead th.text-center {
        text-align: center;
    }

    .expense-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f9fafb;
        transition: background 0.15s ease;
    }
    .expense-table tbody tr:last-child td {
        border-bottom: none;
    }
    .expense-table tbody tr:hover td {
        background: #f9fafb;
    }

    .row-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .method-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .method-tag i {
        font-size: 0.65rem;
    }
    .method-main {
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    .method-extra {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .cell-muted {
        color: #6b7280;
        font-size: 0.82rem;
    }

    /* Notes row — stacked layout so long text wraps nicely below the label */
    .detail-row-notes {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .detail-row-notes .detail-label {
        width: 100%;
    }
    .detail-row-notes .detail-value.detail-notes {
        max-width: 100%;
        text-align: left;
        font-weight: 400;
        color: #6b7280;
        font-size: 0.85rem;
        line-height: 1.5;
        white-space: normal;
        word-break: break-word;
    }

    .price-tag {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 8px;
        background: #f0fdf4;
        color: #15803d;
        font-weight: 700;
        font-size: 0.82rem;
        border: 1px solid #bbf7d0;
    }

    /* ===== Table Footer ===== */
    .expense-table tfoot td {
        padding: 14px;
        background: #f9fafb;
        border-top: 1px solid #f3f4f6;
        font-weight: 600;
        font-size: 0.82rem;
        color: #4b5563;
    }
    .total-price {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 8px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.88rem;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }

    /* ===== Notes Section ===== */
    .notes-section {
        margin-top: 20px;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        overflow: hidden;
    }
    .notes-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #6b7280;
    }
    .notes-header i {
        color: #6366f1;
        font-size: 0.75rem;
    }
    .notes-content {
        padding: 14px 16px;
        background: #ffffff;
    }
    .notes-content p {
        margin: 0;
        line-height: 1.6;
        color: #374151;
        font-size: 0.88rem;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .payment-details-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .detail-value {
            max-width: 65%;
        }
        .expense-table thead th:nth-child(5),
        .expense-table tbody td:nth-child(5) {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .payment-header {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
            padding: 16px;
        }
        .payment-stats {
            width: 100%;
        }
        .stat-badge {
            width: 100%;
            justify-content: center;
        }
        .payment-card-header {
            flex-direction: column;
            align-items: stretch;
        }
        .header-left {
            justify-content: flex-start;
        }
        .header-right {
            width: 100%;
        }
        .paid-amount-badge {
            width: 100%;
            justify-content: center;
        }
        .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            padding: 10px 14px;
        }
        .detail-label {
            font-size: 0.65rem;
        }
        .detail-value {
            max-width: 100%;
            text-align: left;
            font-size: 0.85rem;
        }
        .expense-header {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .expense-total {
            justify-content: center;
        }
        .expense-table {
            font-size: 0.78rem;
        }
        .expense-table thead th,
        .expense-table tbody td,
        .expense-table tfoot td {
            padding: 8px 10px;
        }
        .expense-table thead th {
            font-size: 0.62rem;
        }
        .expense-table thead th:nth-child(5),
        .expense-table tbody td:nth-child(5),
        .expense-table thead th:nth-child(6),
        .expense-table tbody td:nth-child(6) {
            display: none;
        }
        .notes-content p {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .payment-card .card-body {
            padding: 16px !important;
        }
        .payment-header {
            padding: 14px;
        }
        .header-icon {
            width: 36px;
            height: 36px;
            font-size: 13px;
        }
        .paid-amount-badge {
            font-size: 0.9rem;
            padding: 6px 16px;
        }
        .stat-badge {
            padding: 8px 16px;
        }
        .stat-value {
            font-size: 0.95rem;
        }
        .expense-table {
            font-size: 0.72rem;
        }
        .expense-table thead th,
        .expense-table tbody td,
        .expense-table tfoot td {
            padding: 6px 8px;
        }
        .meta-chip {
            font-size: 0.68rem;
            padding: 5px 10px;
        }
        .detail-row {
            padding: 8px 12px;
            min-height: auto;
        }
        .detail-value {
            font-size: 0.8rem;
        }
        .notes-content {
            padding: 12px 14px;
        }
        .notes-content p {
            font-size: 0.82rem;
        }
        .total-price {
            font-size: 0.8rem;
            padding: 5px 12px;
        }
    }
</style>
@endif
