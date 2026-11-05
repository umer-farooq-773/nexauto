<div class="card shadow-sm border-0 rounded-4 mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-dark fw-bold">{{ __('Payment Status') }}</h6>
        </div>

        <div class="d-flex detail-row">
            <div class="detail-label">{{ __('Payment Status') }}:</div>
            <div>{!! $data->payment_status_badge !!}</div>
        </div>



        @if (method_exists($data, 'paymentHistories') && $data->paymentHistories()->exists())
            <hr class="my-3" />
            <h6 class="mb-3 text-dark fw-bold">{{ __('Payment History') }}</h6>

            @foreach ($data->paymentHistories as $payment)
                <div class="border rounded-3 p-3 mb-2">
                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Amount') }}:</div>
                        <div>
                            {{ $payment->paid_amount !== null ? number_format((float) $payment->paid_amount, 2) : '-' }}
                        </div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Original Cost') }}:</div>
                        <div>
                            {{ $payment->original_cost !== null ? number_format((float) $payment->original_cost, 2) : '-' }}
                        </div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Payment Method') }}:</div>
                        <div>{{ $payment->payment_method_label }}</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Reference Number') }}:</div>
                        <div>{{ $payment->reference_number ?? '-' }}</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Processed By') }}:</div>
                        <div>{{ $payment->processed_by_name ?? '-' }}</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Processed At') }}:</div>
                        <div>{{ $payment->created_at?->format('d M Y, h:i A') ?? '-' }}</div>
                    </div>
                    @if (!empty($payment->notes))
                        <div class="d-flex detail-row">
                            <div class="detail-label">{{ __('Notes') }}:</div>
                            <div>{{ $payment->notes }}</div>
                        </div>
                    @endif
                    @php
                        $createdByLabel = $data->created_by_name;
                        if (empty($createdByLabel)) {
                            $createdByLabel =
                                isset($data->created_by) && $data->created_by
                                    ? \App\Models\User::query()->find($data->created_by)->full_name ?? '-'
                                    : '-';
                        }

                        $approvedByLabel = $data->client_approved_by_name;
                        if (empty($approvedByLabel)) {
                            $approvedByLabel =
                                isset($data->client_approved_by) && $data->client_approved_by
                                    ? \App\Models\User::query()->find($data->client_approved_by)->full_name ??
                                        (\App\Models\User::query()->find($data->client_approved_by)->name ?? '-')
                                    : '-';
                        }
                    @endphp


                    @if (!empty($data->client_approved))
                        <div class="d-flex detail-row">
                            <div class="detail-label">{{ __('Who Paid') }}:</div>
                            <div>
                                {{ $approvedByLabel ?? '-' }}
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

        @endif
    </div>
</div>
