<div class=" border-0 rounded-4 mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-start align-items-start mb-3">
            <h6 class="mb-0 text-dark fw-bold">{{ __('History Detail') }}</h6>
        </div>

        {{-- <div class="d-flex detail-row">
            <div class="detail-label">{{ __('Status') }}:</div>
            <div>{!! $data->status_badge !!}</div>
        </div> --}}

        @php
            $createdByLabel = $data->created_by_name;
            if (empty($createdByLabel)) {
                $createdByLabel = (isset($data->created_by) && $data->created_by)
                    ? (\App\Models\User::query()->find($data->created_by)->full_name ?? '-')
                    : '-';
            }

            $approvedByLabel = $data->client_approved_by_name;
            if (empty($approvedByLabel)) {
                $approvedByLabel = (isset($data->client_approved_by) && $data->client_approved_by)
                    ? (\App\Models\User::query()->find($data->client_approved_by)->full_name
                        ?? \App\Models\User::query()->find($data->client_approved_by)->name
                        ?? '-')
                    : '-';
            }
        @endphp

        @if(isset($payment_paid_by))
            <div class="d-flex detail-row">
            <div class="detail-label">{{ __('Incident Created By') }}:</div>
            <div>{{ $payment_paid_by ? $payment_paid_by->first_name . ' ' . $payment_paid_by->last_name : 'N/A' }}</div>
        </div>
        @endif

        @if(isset($data->impound_authority) || $data->authority)
        <div class="d-flex detail-row">
            <div class="detail-label">{{ __('Authority') }}:</div>
            <div>{{ $data->impound_authority ?? $data->authority }}</div>
        </div>
        @endif

        @if(optional($approved_by)->first_name || optional($approved_by)->last_name)
            <div class="d-flex detail-row">
                <div class="detail-label">{{ __('Paid By') }}:</div>
                <div>
                    {{ optional($approved_by)->first_name ?? '' }}
                    {{ optional($approved_by)->last_name ?? '' }}
                </div>
            </div>
        @endif

        <div class="d-flex detail-row">
            <div class="detail-label">{{ __('Created At') }}:</div>
            <div>{{ $data->created_at?->format('d M Y, h:i A') ?? '-' }}</div>
        </div>

        @if(!empty($data->client_approved))
            {{-- <hr class="my-3" /> --}}

            <div class="d-flex detail-row">
                <div class="detail-label">{{ __('Approved By') }}:</div>
                <div>
                    {{ $approvedByLabel ?? '-' }}
                </div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">{{ __('Approved At') }}:</div>
                <div>{{ $data->client_approved_at && $data->client_approved_at instanceof \Carbon\Carbon ? $data->client_approved_at->format('d M Y') : ($data->client_approved_at ? (string)$data->client_approved_at : '-') }}</div>
            </div>
            @if(isset($data->client_approved_comment))
            <div class="d-flex detail-row">
                <div class="detail-label">{{ __('Payment Approval Comment') }}:</div>
                <div>{{ $data->client_approved_comment ?? '-' }}</div>
            </div>
            @endif
        @endif
    </div>
</div>
