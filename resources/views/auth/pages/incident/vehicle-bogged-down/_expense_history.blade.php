@props(['payments'])

@if($payments && $payments->count() > 0)
<div class="payment-history-wrapper mt-4">
    {{-- Header --}}
    <div class="payment-header">
        <div>
            <h5 class="mb-1">
                <i class="fas fa-receipt text-primary me-2"></i>
                {{ __('Payment History') }}
                <span class="badge bg-primary ms-2">{{ $payments->count() }}</span>
            </h5>
            <small class="text-muted">{{ __('Complete payment records with expense breakdown') }}</small>
        </div>
        <div class="payment-stats">
            <span class="stat-badge">
                <i class="fas fa-money-bill-wave me-1"></i>
                {{ __('Total Paid') }}: {{ number_format($payments->sum('paid_amount'), 2) }}
            </span>
        </div>
    </div>

    {{-- Payment Cards --}}
    @foreach($payments as $payment)
    <div class="payment-card {{ !$loop->last ? 'mb-4' : '' }}">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                {{-- Header --}}
                <div class="payment-card-header">
                    <div class="header-left">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y, h:i A') : 'N/A' }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                            <i class="fas fa-user me-1"></i>
                            {{ $payment->processed_by_name ?? 'System' }}
                        </span>
                        @if($loop->first)
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-star me-1"></i> {{ __('Latest') }}
                        </span>
                        @endif
                    </div>
                    <div class="header-right">
                        <span class="paid-amount-badge">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            {{ number_format($payment->paid_amount ?? 0, 2) }}
                        </span>
                    </div>
                </div>

                {{-- Payment Details --}}
                <div class="payment-details-grid">
                    {{-- Left Column --}}
                    <div class="detail-column">
                        <div class="detail-row">
                            <span class="detail-label">{{ __('Payment Method') }}</span>
                            <span class="detail-value">
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3">
                                    <i class="fas fa-{{ $payment->payment_method == 'cash' ? 'money-bill' : ($payment->payment_method == 'bank_transfer' ? 'university' : ($payment->payment_method == 'credit_card' ? 'credit-card' : 'receipt')) }} me-1"></i>
                                    {{ $payment->payment_method_label ?? $payment->payment_method ?? '-' }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">{{ __('Reference Number') }}</span>
                            <span class="detail-value">{{ $payment->reference_number ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">{{ __('Paid To') }}</span>
                            <span class="detail-value">{{ $payment->paid_to ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">{{ __('Authority') }}</span>
                            <span class="detail-value">{{ $payment->authority ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="detail-column">
                        <div class="detail-row">
                            <span class="detail-label">{{ __('Original Cost') }}</span>
                            <span class="detail-value text-muted">{{ $payment->original_cost ? number_format($payment->original_cost, 2) : '-' }}</span>
                        </div>
                        {{-- <div class="detail-row">
                            <span class="detail-label">{{ __('Paid Amount') }}</span>
                            <span class="detail-value text-success fw-bold">{{ number_format($payment->paid_amount ?? 0, 2) }}</span>
                        </div> --}}
                        <div class="detail-row">
                            <span class="detail-label">{{ __('Notes') }}</span>
                            <span class="text-gray-400 align-items-start detail-value fw-bold">{{ Str::limit($payment->notes ?? '',100) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Expense Breakdown --}}
                @if($payment->expense_details && is_array($payment->expense_details) && count($payment->expense_details) > 0)
                <div class="expense-breakdown mt-4 pt-3 border-top">
                    <div class="expense-header">
                        <div class="expense-title">
                            <i class="fas fa-list-ul text-primary me-1"></i>
                            <span class="fw-semibold text-uppercase small text-muted">{{ __('Expense Breakdown') }}</span>
                            <span class="badge bg-secondary ms-2">{{ count($payment->expense_details) }} {{ __('items') }}</span>
                        </div>
                        <div class="expense-total">
                            <span class="text-muted small">
                                <i class="fas fa-calculator me-1"></i>
                                {{ __('Total') }}:
                                <span class="fw-bold text-primary fs-6">
                                    {{ number_format(collect($payment->expense_details)->sum('price'), 2) }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="expense-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">{{ __('#') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Authority') }}</th>
                                    <th>{{ __('Paid To') }}</th>
                                    <th class="text-end" style="width: 130px;">{{ __('Price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->expense_details as $index => $expense)
                                <tr>
                                    <td width="40" class="text-start fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge {{ $expense['type'] == 'main' ? 'bg-primary' : 'bg-secondary' }}">
                                            <i class="fas fa-{{ $expense['type'] == 'main' ? 'home' : 'plus-circle' }} me-1"></i>
                                            {{ ucfirst($expense['payment_method'] ?? 'expense') }}
                                        </span>                                    </td>
                                    <td>{{ $expense['reference_number'] }}</td>
                                    <td class="">{{ $expense['authority'] ?? '-' }}</td>
                                    <td>{{ $expense['paid_to'] ?? '-' }}</td>
                                    <td width='130' class="text-end fw-semibold text-primary">
                                        {{ number_format($expense['price'] ?? 0, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light d-block ms-auto">
                            <div class="">
                                <tr class="">
                                <td colspan="6" class="text-end fw-bold">
                                    <i class="fas fa-calculator me-2"></i>
                                    {{ __('Total Expenses') }}
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format(collect($payment->expense_details)->sum('price'), 2) }}
                                </td>
                            </tr>
                            </div>
                        </tfoot>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Notes Section --}}
                @if($payment->notes)
                <div class="notes-section mt-4 pt-3 border-top">
                    <div class="notes-header">
                        <i class="fas fa-sticky-note text-primary me-2"></i>
                        <span class="fw-semibold text-uppercase small text-muted">{{ __('Notes') }}</span>
                    </div>
                    <div class="notes-content">
                        <p class="mb-0">{{ $payment->notes }}</p>
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
    .payment-history-wrapper {
        position: relative;
    }

    /* Header */
    .payment-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding: 16px 24px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }
    .payment-stats {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .stat-badge {
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        border: 1px solid #dee2e6;
        color: #212529;
    }

    /* Card */
    .payment-card {
        position: relative;
    }
    .hover-card {
        transition: all 0.3s ease;
        border: 1px solid #f1f3f5;
    }
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.08) !important;
        border-color: #dee2e6;
    }

    /* Card Header */
    .payment-card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f1f3f5;
    }
    .header-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .header-right {
        display: flex;
        align-items: center;
    }
    .paid-amount-badge {
        padding: 6px 18px;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Details Grid */
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
        justify-content: space-between;
        align-items: center;
        padding: 10px 16px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: background 0.2s ease;
        min-height: 46px;
    }
    .detail-row:hover {
        background: #e9ecef;
    }
    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        flex-shrink: 0;
    }
    .detail-value {
        font-size: 0.9rem;
        color: #212529;
        font-weight: 500;
        text-align: right;
        word-break: break-word;
        max-width: 65%;
    }

    /* Expense Breakdown */
    .expense-breakdown {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #f1f3f5;
    }
    .expense-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .expense-title {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .expense-total {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 4px 16px;
        border-radius: 50px;
        border: 1px solid #e9ecef;
    }

    /* Expense Table */
    .expense-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
        font-size: 0.85rem;
    }
    .expense-table thead th {
        background: #f8f9fa;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #6c757d;
        padding: 10px 14px;
        border-bottom: 2px solid #e9ecef;
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
        padding: 10px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
        transition: background 0.2s ease;
    }
    .expense-table tbody tr:hover td {
        background: #f8f9fa;
    }
    .expense-table tbody tr:last-child td {
        border-bottom: none;
    }
    .expense-table tfoot td {
        padding: 12px 14px;
        font-weight: 600;
        background: #f8f9fa;
        border-top: 2px solid #e9ecef;
    }
    .expense-table .text-end {
        text-align: right;
    }
    .expense-table .text-center {
        text-align: center;
    }

    /* Notes Section */
    .notes-section {
        background: #f8f9fa;
        padding: 0;
        border-radius: 8px;
        margin-top: 16px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    .notes-header {
        padding: 10px 16px;
        background: #f1f3f5;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .notes-content {
        padding: 12px 16px;
    }
    .notes-content p {
        margin: 0;
        line-height: 1.6;
        color: #212529;
        font-size: 0.9rem;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* Badge */
    .badge.bg-light {
        background: #f8f9fa !important;
        border-color: #dee2e6 !important;
    }
    .badge.bg-light:hover {
        background: #e9ecef !important;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .payment-details-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .detail-row {
            min-height: 40px;
        }
        .detail-value {
            max-width: 70%;
        }
        /* Hide Authority column on tablet */
        .expense-table thead th:nth-child(5),
        .expense-table tbody td:nth-child(5) {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .payment-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
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
            align-items: flex-start;
            gap: 12px;
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
            gap: 4px;
            padding: 8px 14px;
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
            align-items: flex-start;
            gap: 8px;
        }
        .expense-total {
            width: 100%;
            justify-content: center;
        }
        .expense-table {
            font-size: 0.75rem;
        }
        .expense-table thead th,
        .expense-table tbody td,
        .expense-table tfoot td {
            padding: 6px 10px;
        }
        .expense-table thead th {
            font-size: 0.6rem;
        }
        .header-left {
            gap: 6px;
        }
        .header-left .badge {
            font-size: 0.7rem;
            padding: 4px 10px;
        }
        /* Hide less important columns on mobile */
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
        .card-body {
            padding: 16px !important;
        }
        .payment-header {
            padding: 12px;
        }
        .paid-amount-badge {
            font-size: 0.9rem;
            padding: 4px 14px;
        }
        .stat-badge {
            font-size: 0.8rem;
            padding: 6px 14px;
        }
        .expense-table {
            font-size: 0.7rem;
        }
        .expense-table thead th,
        .expense-table tbody td,
        .expense-table tfoot td {
            padding: 4px 8px;
        }
        .badge {
            font-size: 0.65rem !important;
        }
        .detail-row {
            padding: 6px 12px;
        }
        .detail-value {
            font-size: 0.8rem;
        }
        .notes-content {
            padding: 10px 14px;
        }
        .notes-content p {
            font-size: 0.8rem;
        }
    }
</style>
@endif
