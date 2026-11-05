{{-- resources/views/auth/pages/waybill/pdf/detail.blade.php --}}
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
            </table>
        </div>
    </div>

    <p class="pdf-footer-note">{{ __('Generated on') }} {{ now()->format('d M Y, h:i A') }}</p>

</body>
</html>
