<x-layouts.auth page-title="{{ __('Trip & Route Details') }}" sub-title="">
    <div class="p-4 rounded-4 border">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-xl-8">

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Trip Name') }}:</div>
                        <div>{{ $data->trip_name }}</div>
                    </div>

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Origin') }}:</div>
                        <div>{{ $data->origin }}</div>
                    </div>

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Destination') }}:</div>
                        <div>{{ $data->destination }}</div>
                    </div>

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Distance (KM)') }}:</div>
                        <div>{{ $data->distance_km ?? '-' }}</div>
                    </div>

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Estimated Duration') }}:</div>
                        <div>{{ $data->estimated_duration ?? '-' }}</div>
                    </div>

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Description') }}:</div>
                        <div>{{ $data->description ?? '-' }}</div>
                    </div>

                    <div class="d-flex detail-row">
                        <div class="detail-label">{{ __('Status') }}:</div>
                        <div>{!! $data->status_badge !!}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.auth>
