@props(['payments'])

@if($payments && $payments->count() > 0)
<div class="payment-history-wrapper mt-4">

    {{-- Header --}}
    <div class="payment-header">
        <div class="section-header-left">
            <div class="icon-tile green">
                <i class="ri-receipt-line"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    {{ __('Payment History') }}
                    <span class="chip chip-blue ms-2">{{ $payments->count() }}</span>
                </h5>
                <small class="text-muted">{{ __('Complete payment records with expense breakdown') }}</small>
            </div>
        </div>
        <div class="payment-stats">
            <span class="stat-badge">
                <i class="ri-money-dollar-circle-line me-1"></i>
                {{ __('Total Paid') }}: {{ number_format($payments->sum('paid_amount'), 2) }}
            </span>
        </div>
    </div>

    {{-- Payment Cards --}}
    @foreach($payments as $payment)
    <div class="payment-card {{ !$loop->last ? 'mb-4' : '' }}">
        <div class="card border-0 shadow-sm rounded-4 section-card">
            <div class="card-body p-4">
                {{-- Header --}}
                <div class="payment-card-header">
                    <div class="header-left">
                        <span class="chip chip-blue">
                            <i class="ri-calendar-2-line me-1"></i>
                            {{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y, h:i A') : 'N/A' }}
                        </span>
                        <span class="chip chip-slate">
                            <i class="ri-user-line me-1"></i>
                            {{ $payment->processed_by_name ?? 'System' }}
                        </span>
                        @if($loop->first)
                        <span class="chip chip-green">
                            <i class="ri-star-fill me-1"></i> {{ __('Latest') }}
                        </span>
                        @endif
                    </div>
                    <div class="header-right">
                        <span class="paid-amount-badge">
                            <i class="ri-money-dollar-circle-line me-1"></i>
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
                                <span class="chip chip-cyan">
                                    <i class="ri-{{ $payment->payment_method == 'cash' ? 'money-dollar-circle-line' : ($payment->payment_method == 'bank_transfer' ? 'bank-line' : ($payment->payment_method == 'credit_card' ? 'bank-card-line' : 'receipt-line')) }} me-1"></i>
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
                            <i class="ri-list-unordered text-primary me-1"></i>
                            <span class="fw-semibold text-uppercase small text-muted">{{ __('Expense Breakdown') }}</span>
                            <span class="chip chip-slate ms-2">{{ count($payment->expense_details) }} {{ __('items') }}</span>
                        </div>
                        <div class="expense-total">
                            <span class="text-muted small">
                                <i class="ri-calculator-line me-1"></i>
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
                                        <span class="chip {{ $expense['type'] == 'main' ? 'chip-blue' : 'chip-slate' }}">
                                            <i class="ri-{{ $expense['type'] == 'main' ? 'home-line' : 'add-circle-line' }} me-1"></i>
                                            {{ ucfirst($expense['payment_method'] ?? 'expense') }}
                                        </span>
                                    </td>
                                    <td>{{ $expense['reference_number'] }}</td>
                                    <td>{{ $expense['authority'] ?? '-' }}</td>
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
                                    <i class="ri-calculator-line me-2"></i>
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
                        <i class="ri-sticky-note-line text-primary me-2"></i>
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
    .payment-history-wrapper { position: relative; }

    .icon-tile {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; flex-shrink: 0;
    }
    .icon-tile.green { background: #dcfce7; color: #16a34a; }
    .section-header-left { display: flex; align-items: center; gap: 0.85rem; }

    .chip {
        display: inline-flex; align-items: center;
        padding: 5px 12px; border-radius: 50px;
        font-size: 0.82rem; font-weight: 600;
    }
    .chip-blue   { background: #e0e7ff; color: #4f46e5; }
    .chip-green  { background: #dcfce7; color: #15803d; }
    .chip-amber  { background: #fef3c7; color: #b45309; }
    .chip-cyan   { background: #cffafe; color: #0e7490; }
    .chip-slate  { background: #e2e8f0; color: #334155; }

    .payment-header {
        display: flex; flex-wrap: wrap; align-items: center;
        justify-content: space-between; margin-bottom: 24px;
        padding: 16px 24px; background: #f8fafc;
        border-radius: 12px; border: 1px solid #e2e8f0;
    }
    .payment-stats { display: flex; gap: 12px; flex-wrap: wrap; }
    .stat-badge {
        padding: 8px 18px; border-radius: 50px;
        font-size: 0.85rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px;
        background: white; border: 1px solid #e2e8f0; color: #1e293b;
    }

    .section-card {
        background: #ffffff; border: 1px solid #e2e8f0 !important;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important; }

    .payment-card-header {
        display: flex; flex-wrap: wrap; align-items: center;
        justify-content: space-between; margin-bottom: 20px;
        padding-bottom: 16px; border-bottom: 2px solid #f1f5f9;
    }
    .header-left { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .header-right { display: flex; align-items: center; }
    .paid-amount-badge {
        padding: 6px 18px; border-radius: 50px;
        font-size: 1rem; font-weight: 600;
        background: #dcfce7; color: #15803d;
        border: 1px solid #bbf7d0;
        display: inline-flex; align-items: center; gap: 6px;
    }

    .payment-details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .detail-column { display: flex; flex-direction: column; gap: 8px; }
    .detail-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 16px; background: #f8fafc;
        border: 1px solid #e2e8f0; border-radius: 8px;
        transition: all 0.2s ease; min-height: 46px;
    }
    .detail-row:hover { background: #ffffff; border-color: #cbd5e1; }
    .detail-label {
        font-size: 0.7rem; text-transform: uppercase;
        letter-spacing: 0.5px; color: #64748b; font-weight: 600; flex-shrink: 0;
    }
    .detail-value {
        font-size: 0.9rem; color: #1e293b; font-weight: 500;
        text-align: right; word-break: break-word; max-width: 65%;
    }

    .expense-breakdown { margin-top: 16px; padding-top: 16px; border-top: 2px solid #f1f5f9; }
    .expense-header {
        display: flex; flex-wrap: wrap; align-items: center;
        justify-content: space-between; margin-bottom: 14px;
    }
    .expense-title { display: flex; align-items: center; gap: 4px; }
    .expense-total {
        display: flex; align-items: center; background: #f8fafc;
        padding: 4px 16px; border-radius: 50px; border: 1px solid #e2e8f0;
    }

    .expense-table {
        width: 100%; border-collapse: collapse;
        border-radius: 8px; overflow: hidden; font-size: 0.85rem;
    }
    .expense-table thead th {
        background: #f8fafc; font-size: 0.7rem; text-transform: uppercase;
        letter-spacing: 0.5px; font-weight: 700; color: #64748b;
        padding: 10px 14px; border-bottom: 2px solid #e2e8f0;
        text-align: left; white-space: nowrap;
    }
    .expense-table thead th.text-end { text-align: right; }
    .expense-table thead th.text-center { text-align: center; }
    .expense-table tbody td {
        padding: 10px 14px; vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .expense-table tbody tr:hover td { background: #f8fafc; }
    .expense-table tbody tr:last-child td { border-bottom: none; }
    .expense-table tfoot td {
        padding: 12px 14px; font-weight: 600;
        background: #f8fafc; border-top: 2px solid #e2e8f0;
    }

    .notes-section {
        background: #f8fafc; border-radius: 8px; margin-top: 16px;
        border: 1px solid #e2e8f0; overflow: hidden;
    }
    .notes-header {
        padding: 10px 16px; background: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
        display: flex; align-items: center; gap: 8px;
    }
    .notes-content { padding: 12px 16px; }
    .notes-content p {
        margin: 0; line-height: 1.6; color: #1e293b;
        font-size: 0.9rem; white-space: pre-wrap; word-wrap: break-word;
    }

    @media (max-width: 992px) {
        .payment-details-grid { grid-template-columns: 1fr; gap: 12px; }
        .expense-table thead th:nth-child(5),
        .expense-table tbody td:nth-child(5) { display: none; }
    }

    @media (max-width: 768px) {
        .payment-header { flex-direction: column; align-items: flex-start; gap: 12px; padding: 16px; }
        .payment-stats { width: 100%; }
        .stat-badge { width: 100%; justify-content: center; }
        .payment-card-header { flex-direction: column; align-items: flex-start; gap: 12px; }
        .header-right { width: 100%; }
        .paid-amount-badge { width: 100%; justify-content: center; }
        .detail-row { flex-direction: column; align-items: flex-start; gap: 4px; padding: 8px 14px; }
        .detail-value { max-width: 100%; text-align: left; font-size: 0.85rem; }
        .expense-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .expense-total { width: 100%; justify-content: center; }
        .expense-table { font-size: 0.75rem; }
        .expense-table thead th, .expense-table tbody td, .expense-table tfoot td { padding: 6px 10px; }
        .expense-table thead th:nth-child(5), .expense-table tbody td:nth-child(5),
        .expense-table thead th:nth-child(6), .expense-table tbody td:nth-child(6) { display: none; }
    }

    @media (max-width: 576px) {
        .card-body { padding: 16px !important; }
        .payment-header { padding: 12px; }
        .paid-amount-badge { font-size: 0.9rem; padding: 4px 14px; }
        .stat-badge { font-size: 0.8rem; padding: 6px 14px; }
        .expense-table { font-size: 0.7rem; }
        .expense-table thead th, .expense-table tbody td, .expense-table tfoot td { padding: 4px 8px; }
        .detail-row { padding: 6px 12px; }
        .detail-value { font-size: 0.8rem; }
        .notes-content p { font-size: 0.8rem; }
    }
</style>
@endif
