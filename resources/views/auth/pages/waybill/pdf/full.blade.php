{{-- resources/views/auth/pages/waybill/pdf/full.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Waybill Details') }} - {{ $data->waybill_number }}</title>
    @include('auth.pages.waybill.pdf._styles')
</head>
<body>

    <div class="pdf-header">
        <p class="title">{{ __('Waybill Details') }}</p>
        <p class="subtitle">{{ __('Waybill') }} #{{ $data->waybill_number }}</p>
    </div>

    @php
        $displayDriverPlans = $data->driverPlans->filter(fn ($plan) => $plan->driver)->values();
        if ($displayDriverPlans->isEmpty() && $data->driver) {
            $displayDriverPlans = collect([(object) ['driver' => $data->driver, 'sequence' => 1, 'planned_seconds' => null, 'status' => 'pending']]);
        }
    @endphp
    <div class="pdf-card">
        <div class="pdf-card-header"><p class="card-title">{{ __('Drivers') }}</p></div>
        <div class="pdf-card-body">
            <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="background:#f1f5f9;"><th style="padding:6px 8px;border:1px solid #e2e8f0;text-align:left;">{{ __('Order') }}</th><th style="padding:6px 8px;border:1px solid #e2e8f0;text-align:left;">{{ __('Driver') }}</th><th style="padding:6px 8px;border:1px solid #e2e8f0;text-align:left;">{{ __('Planned time') }}</th><th style="padding:6px 8px;border:1px solid #e2e8f0;text-align:left;">{{ __('Status') }}</th></tr></thead>
                <tbody>
                    @forelse ($displayDriverPlans as $plan)
                        <tr><td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ $loop->iteration }}</td><td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ $plan->driver->full_name }}</td><td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ $plan->planned_seconds ? rtrim(rtrim(number_format($plan->planned_seconds / 60, 1), '0'), '.') . ' min' : __('Until trip end') }}</td><td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ ucfirst($plan->status ?? 'pending') }}</td></tr>
                    @empty
                        <tr><td colspan="4" style="padding:6px 8px;border:1px solid #e2e8f0;">-</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pdf-card">
        <div class="pdf-card-header">
            <p class="card-title">{{ __('WayBill Information') }}</p>
            <p class="card-subtitle">{{ __('Way Bill Details') }}</p>
        </div>
        <div class="pdf-card-body">
            <table class="info-grid">
                <tr>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Waybill Number') }}</span>
                        <span class="info-value">{{ $data->waybill_number }}</span>
                    </td>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Asset') }}</span>
                        <span class="info-value">{{ $data->vehicle?->fleet_no ?? '-' }} ({{ $data->vehicle?->truck_no ?? '-' }})</span>
                    </td>
                </tr>
                <tr>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Driver') }}</span>
                        <span class="info-value">{{ $data->driver?->full_name ?? '-' }}</span>
                    </td>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Route / Trip') }}</span>
                        <span class="info-value">
                            @if($data->route_trip)
                                {{ $data->route_trip->trip_name ?? '-' }}
                            @elseif($data->origin && $data->destination)
                                {{ $data->origin }} → {{ $data->destination }}
                            @else
                                -
                            @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Dispatch Date') }}</span>
                        <span class="info-value highlight">{{ $data->dispatch_date?->format('d M Y') ?? '-' }}</span>
                    </td>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Delivery Date') }}</span>
                        <span class="info-value">{{ $data->delivery_date?->format('d M Y') ?? '-' }}</span>
                    </td>
                </tr>
                @if($data->cargos && $data->cargos->count() > 0)
                    <tr>
                        <td colspan="2" class="info-cell" style="padding: 0;">
                            <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
                                <tr style="background: #f1f5f9;">
                                    <th style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px; text-align: left;">#</th>
                                    <th style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px; text-align: left;">{{ __('Cargo Description') }}</th>
                                    <th style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px; text-align: left;">{{ __('Quantity') }}</th>
                                    <th style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px; text-align: left;">{{ __('Cargo Weight') }}</th>
                                    <th style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px; text-align: left;">{{ __('Volume') }}</th>
                                </tr>
                                @foreach($data->cargos as $idx => $cargo)
                                    <tr>
                                        <td style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px;">{{ $idx + 1 }}</td>
                                        <td style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px; font-weight: bold;">{{ $cargo->cargo_description ?? '-' }}</td>
                                        <td style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px;">{{ $cargo->quantity ?? '-' }} {{ $cargo->quantity_unit ?? '' }}</td>
                                        <td style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px;">{{ $cargo->cargo_weight ?? '-' }} {{ $cargo->weight_unit ?? '' }}</td>
                                        <td style="padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 11px;">{{ $cargo->volume_unit ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="info-cell">
                            <span class="info-label">{{ __('Cargo Description') }}</span>
                            <span class="info-value">{{ $data->cargo_description ?? '-' }}</span>
                        </td>
                        <td class="info-cell">
                            <span class="info-label">{{ __('Cargo Weight') }}</span>
                            <span class="info-value">{{ $data->cargo_weight_kg ?? '-' }} {{ $data->weight_unit ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-cell">
                            <span class="info-label">{{ __('Quantity') }}</span>
                            <span class="info-value">{{ $data->quantity ?? '-' }} {{ $data->quantity_unit ?? '' }}</span>
                        </td>
                        <td class="info-cell">
                            <span class="info-label">{{ __('Volume Unit') }}</span>
                            <span class="info-value">{{ $data->volume_unit ?? '-' }}</span>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Status') }}</span>
                        <span class="info-value">
                            @php
                                $status = $data->status->value ?? '';
                                $statusClass = match($status) {
                                    'pending' => 'pending',
                                    'despatch' => 'despatch',
                                    'return_transit' => 'return_transit',
                                    'delivered' => 'delivered',
                                    'impoundment' => 'impoundment',
                                    'accident' => 'accident',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) ?: '-' }}
                            </span>
                        </span>
                    </td>
                    <td class="info-cell">
                        <span class="info-label">{{ __('Notes') }}</span>
                        <span class="info-value">{{ $data->notes ?? '-' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pdf-card">
        <div class="pdf-card-header">
            <p class="card-title">{{ __('Trip Information') }}</p>
        </div>
        <div class="pdf-card-body">
            <table class="vehicle-info">
                <tr>
                    <td class="v-label">{{ __('Origin') }}</td>
                    <td class="v-value">{{ $data->origin ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="v-label">{{ __('Destination') }}</td>
                    <td class="v-value">{{ $data->destination ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="v-label">{{ __('Distance (KM)') }}</td>
                    <td class="v-value">{{ $data->distance ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="v-label">{{ __('Estimated Duration') }}</td>
                    <td class="v-value">{{ $data->estimated_duration ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="v-label">{{ __('Estimated Dispatch Time') }}</td>
                    <td class="v-value">{{ $data->estimated_dispatch_time ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="v-label">{{ __('Estimated Arrival Time') }}</td>
                    <td class="v-value">{{ $data->estimated_arrival_time ?? '-' }}</td>
                </tr>
                @if($data->driver_contact)
                <tr>
                    <td class="v-label">{{ __('Driver Contact') }}</td>
                    <td class="v-value">{{ $data->driver_contact }}</td>
                </tr>
                @endif
                @if($data->vehicle_contact)
                <tr>
                    <td class="v-label">{{ __('Vehicle Contact') }}</td>
                    <td class="v-value">{{ $data->vehicle_contact }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @if($incidents->isNotEmpty())
        <div class="page-break"></div>

        <div class="incident-section">
            <p class="section-title">{{ __('Related Records') }}</p>
            <p class="section-subtitle">{{ __('Incident records linked to this waybill.') }}</p>

            @php
                $incidentLabels = [
                    'accidents'            => __('Accidents'),
                    'vehicle_breakdowns'   => __('Asset Breakdowns'),
                    'vehicle_bogged_downs' => __('Asset Bogged Downs'),
                    'impoundments'         => __('Impoundments'),
                    'route_diversions'     => __('Route Diversions'),
                    'illegal_detentions'   => __('Illegal Detentions'),
                    'illegal_haulages'     => __('Illegal Haulages'),
                    'illegal_shortages'    => __('Illegal Shortages'),
                    'breathaliser_tests'   => __('Breathaliser Tests'),
                    'driver_misconducts'   => __('Driver Misconducts'),
                ];

                $hiddenColumns = [
                    'id', 'waybill_id', 'created_at', 'updated_at', 'deleted_at', 'pivot',
                    'reason', 'remarks', 'vehicle_id', 'payment_status', 'client_approved_at',
                    'client_approved_by', 'client_approved', 'client_approved_comment', 'created_by',
                    'accident_nature_detail', 'fatality_type', 'is_fatality', 'content',
                    'is_injury', 'is_damage_asset', 'driver_id', 'incident_type','status','description'
                ];
            @endphp

            @foreach($incidents as $key => $records)
                @continue($records === null || $records->isEmpty())
                @php
                    $first = $records->first();
                    $attributes = is_object($first) && method_exists($first, 'getAttributes')
                        ? array_keys($first->getAttributes())
                        : [];
                    $columns = array_values(array_diff($attributes, $hiddenColumns));

                    // Clean up column names for display
                    $displayColumns = array_map(function($col) {
                        return \Illuminate\Support\Str::headline(str_replace('_', ' ', $col));
                    }, $columns);
                @endphp

                <table class="incident-table">
                    <caption>{{ $incidentLabels[$key] ?? \Illuminate\Support\Str::headline($key) }} ({{ $records->count() }})</caption>
                    @if(count($columns))
                        <thead>
                            <tr>
                                <th>#</th>
                                @foreach($displayColumns as $displayCol)
                                    <th>{{ $displayCol }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $index => $record)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @foreach($columns as $column)
                                        @php $value = $record->{$column} ?? null; @endphp
                                        <td>
                                            @if($value instanceof \Illuminate\Support\Carbon || $value instanceof \DateTimeInterface)
                                                {{ $value->format('d M Y') }}
                                            @elseif(is_bool($value))
                                                {{ $value ? __('Yes') : __('No') }}
                                            @elseif(is_array($value) || is_object($value))
                                                {{ json_encode($value) }}
                                            @else
                                                {{ $value ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    @else
                        <tbody>
                            <tr>
                                <td colspan="10" class="text-muted text-center">
                                    {{ __('No displayable fields found for this record.') }}
                                </td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            @endforeach
        </div>
    @endif

    <p class="pdf-footer-note">{{ __('Generated on') }} {{ now()->format('d M Y, h:i A') }}</p>

</body>
</html>
