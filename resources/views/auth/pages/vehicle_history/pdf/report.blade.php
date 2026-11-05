<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Vehicle Track & History Report') }} - {{ $trackData['vehicle']['fleet_no'] ?? 'Unit' }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .pdf-header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .pdf-header table {
            width: 100%;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subtitle {
            font-size: 9.5px;
            color: #64748b;
            margin: 0;
        }

        .gen-date {
            text-align: right;
            font-size: 8.5px;
            color: #64748b;
        }

        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 10px;
            font-size: 10.5px;
            font-weight: bold;
            color: #1e293b;
        }

        .section-body {
            padding: 8px 10px;
        }

        /* Grid / Table Layouts */
        table.grid-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 1px;
        }

        .value {
            font-size: 10px;
            font-weight: 600;
            color: #0f172a;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 6px;
        }

        .kpi-cell {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 10px;
            width: 25%;
            text-align: center;
        }

        .kpi-val {
            font-size: 13px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 2px;
        }

        .kpi-lbl {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 4px;
        }

        table.data-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8.5px;
            font-weight: bold;
            color: #334155;
            text-align: left;
            text-transform: uppercase;
        }

        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            color: #1e293b;
        }

        table.data-table tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge-trip {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
        }

        .badge-stop {
            background: #fef2f2;
            color: #dc2626;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
        }

        .page-break {
            page-break-after: always;
        }

        .footer {
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="pdf-header">
        <table>
            <tr>
                <td style="width: 65%;">
                    <div class="title">{{ __('Vehicle GPS Track & Activity Report') }}</div>
                    <div class="subtitle">{{ __('Automated Fleet Telemetry, Trip Analysis & Stop Monitoring') }}</div>
                </td>
                <td class="gen-date" style="width: 35%;">
                    <strong>{{ __('Generated:') }}</strong> {{ date('d M Y, H:i') }}<br>
                    <strong>{{ __('System:') }}</strong> {{ config('app.name', 'Fleet Management') }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Vehicle & Period Information -->
    <div class="section-box">
        <div class="section-header">{{ __('Vehicle & Query Parameters') }}</div>
        <div class="section-body">
            <table class="grid-table">
                <tr>
                    <td style="width: 25%;">
                        <span class="label">{{ __('Fleet Number') }}</span>
                        <span class="value">{{ $trackData['vehicle']['fleet_no'] ?? '—' }}</span>
                    </td>
                    <td style="width: 25%;">
                        <span class="label">{{ __('Truck / Plate') }}</span>
                        <span class="value">{{ $trackData['vehicle']['truck_no'] ?? '—' }}</span>
                    </td>
                    <td style="width: 25%;">
                        <span class="label">{{ __('Assigned Driver') }}</span>
                        <span class="value">{{ $trackData['vehicle']['driver_name'] ?? '—' }}</span>
                    </td>
                    <td style="width: 25%;">
                        <span class="label">{{ __('Make / Model') }}</span>
                        <span class="value">{{ $trackData['vehicle']['make_model'] ?? '—' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="label">{{ __('GPS Unit ID') }}</span>
                        <span class="value">{{ $trackData['unit_id'] ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="label">{{ __('Period From') }}</span>
                        <span class="value">{{ $trackData['period']['from_formatted'] ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="label">{{ __('Period To') }}</span>
                        <span class="value">{{ $trackData['period']['to_formatted'] ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="label">{{ __('Total Time Range') }}</span>
                        <span class="value">{{ $trackData['period']['duration_formatted'] ?? '—' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Summary Statistics KPIs -->
    <div class="section-box">
        <div class="section-header">{{ __('Performance & Telemetry Summary') }}</div>
        <div class="section-body" style="padding: 6px;">
            <table class="kpi-table">
                <tr>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Total Distance') }}</div>
                        <div class="kpi-val">{{ number_format($trackData['summary']['total_mileage_km'] ?? 0, 2) }} km</div>
                    </td>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Total Track Time') }}</div>
                        <div class="kpi-val" style="color: #0f172a;">{{ $trackData['summary']['total_duration_fmt'] ?? '0h 0m' }}</div>
                    </td>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Moving Duration') }}</div>
                        <div class="kpi-val" style="color: #16a34a;">{{ $trackData['summary']['moving_duration_fmt'] ?? '0h 0m' }}</div>
                    </td>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Stopped Duration') }}</div>
                        <div class="kpi-val" style="color: #dc2626;">{{ $trackData['summary']['stop_duration_fmt'] ?? '0h 0m' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Max Speed') }}</div>
                        <div class="kpi-val" style="color: #ea580c;">{{ $trackData['summary']['max_speed'] ?? 0 }} km/h</div>
                    </td>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Average Speed') }}</div>
                        <div class="kpi-val" style="color: #0284c7;">{{ $trackData['summary']['avg_speed'] ?? 0 }} km/h</div>
                    </td>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Trips Segmented') }}</div>
                        <div class="kpi-val">{{ $trackData['summary']['trips_count'] ?? 0 }}</div>
                    </td>
                    <td class="kpi-cell">
                        <div class="kpi-lbl">{{ __('Stops / Parkings') }}</div>
                        <div class="kpi-val">{{ $trackData['summary']['stops_count'] ?? 0 }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Trips Breakdown Table -->
    <div class="section-box">
        <div class="section-header">
            {{ __('Trips & Driving Segments') }} ({{ count($trackData['trips'] ?? []) }})
        </div>
        <div class="section-body">
            @if(!empty($trackData['trips']) && count($trackData['trips']) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 6%;">#</th>
                            <th style="width: 18%;">{{ __('Start Time') }}</th>
                            <th style="width: 18%;">{{ __('End Time') }}</th>
                            <th style="width: 12%;">{{ __('Duration') }}</th>
                            <th style="width: 12%;">{{ __('Distance') }}</th>
                            <th style="width: 10%;">{{ __('Avg Speed') }}</th>
                            <th style="width: 10%;">{{ __('Max Speed') }}</th>
                            <th style="width: 14%;">{{ __('Coordinates') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackData['trips'] as $idx => $trip)
                            @php
                                $startPt = $trip['start_point'] ?? [];
                                $endPt   = $trip['end_point'] ?? [];
                                $startStr = (!empty($startPt) && count($startPt) >= 2) ? sprintf('%.4f, %.4f', $startPt[1], $startPt[0]) : '—';
                                $endStr   = (!empty($endPt) && count($endPt) >= 2) ? sprintf('%.4f, %.4f', $endPt[1], $endPt[0]) : '—';
                            @endphp
                            <tr>
                                <td><span class="badge-trip">{{ $trip['id'] ?? ($idx + 1) }}</span></td>
                                <td>{{ $trip['start_time_fmt'] ?? ($trip['start_formatted'] ?? '—') }}</td>
                                <td>{{ $trip['end_time_fmt'] ?? ($trip['end_formatted'] ?? '—') }}</td>
                                <td><strong>{{ $trip['duration_fmt'] ?? '—' }}</strong></td>
                                <td><strong>{{ number_format($trip['distance_km'] ?? 0, 2) }} km</strong></td>
                                <td>{{ $trip['avg_speed'] ?? 0 }} km/h</td>
                                <td>{{ $trip['max_speed'] ?? 0 }} km/h</td>
                                <td style="font-size: 8px; color: #64748b;">
                                    {{ $startStr }}<br>↳ {{ $endStr }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-style: italic; margin: 4px 0;">{{ __('No driving trips recorded for this time range.') }}</p>
            @endif
        </div>
    </div>

    <!-- Stops / Parking Table -->
    <div class="section-box">
        <div class="section-header">
            {{ __('Stops & Stationary Events') }} ({{ count($trackData['stops'] ?? []) }})
        </div>
        <div class="section-body">
            @if(!empty($trackData['stops']) && count($trackData['stops']) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 6%;">#</th>
                            <th style="width: 22%;">{{ __('Arrival / Start') }}</th>
                            <th style="width: 22%;">{{ __('Departure / End') }}</th>
                            <th style="width: 16%;">{{ __('Parked Duration') }}</th>
                            <th style="width: 34%;">{{ __('Location / Coordinates') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackData['stops'] as $idx => $stop)
                            @php
                                $lat = $stop['lat'] ?? null;
                                $lng = $stop['lng'] ?? null;
                                $coordsStr = ($lat !== null && $lng !== null) ? sprintf('%.5f, %.5f', $lat, $lng) : '—';
                            @endphp
                            <tr>
                                <td><span class="badge-stop">{{ $stop['id'] ?? ($idx + 1) }}</span></td>
                                <td>{{ $stop['start_fmt'] ?? '—' }}</td>
                                <td>{{ $stop['end_fmt'] ?? '—' }}</td>
                                <td><strong>{{ $stop['duration_fmt'] ?? '—' }}</strong></td>
                                <td>{{ $coordsStr }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-style: italic; margin: 4px 0;">{{ __('No stationary stops detected in this time interval.') }}</p>
            @endif
        </div>
    </div>

    <div class="footer">
        {{ config('app.name', 'Fleet Management') }} &bull; {{ __('Confidential Vehicle Activity Report') }} &bull; {{ __('Page') }} 1
    </div>

</body>
</html>
