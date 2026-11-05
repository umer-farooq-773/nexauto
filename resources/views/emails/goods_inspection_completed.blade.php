<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Inspection Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; color: #333;">Hello {{ $notifiableName }},</h2>
        <p style="margin: 0; color: #666;">We would like to inform you that a Goods Inspection report has been recorded and submitted.</p>
    </div>

    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 15px 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Inspection Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Fleet No:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->fleet_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Vehicle Number:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->vehicle_lorry_number ?? $inspection->vehicle?->truck_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Waybill Number:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->waybill_number ?? $inspection->waybill?->waybill_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Driver Name:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->driver_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Inspection Type:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->inspection_type ?? 'Pre-Departure' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Inspection Location:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->inspection_location ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Inspection Status:</td>
                <td style="padding: 8px 0; color: #333;">
                    <strong>{{ $inspection->goods_status === 'cleared' ? 'Cleared / Safe' : 'Issues Found' }}</strong>
                </td>
            </tr>
            @if(!empty($inspection->remarks))
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Remarks:</td>
                <td style="padding: 8px 0; color: #333;">{{ $inspection->remarks }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if(count($linkedIncidents) > 0)
    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 15px 0; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">Linked Incidents</h3>
        <table style="width: 100%; border-collapse: collapse;">
            @foreach($linkedIncidents as $inc)
            <tr>
                <td style="padding: 10px 0; font-weight: 600; color: #333; border-bottom: 1px solid #eee;">
                    {{ $inc['label'] }}
                </td>
                <td style="padding: 10px 0; text-align: right; border-bottom: 1px solid #eee;">
                    <a href="{{ $inc['show_url'] ?? $inc['edit_url'] }}" 
                       style="display: inline-block; padding: 6px 14px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 600;">
                        View Incident Details
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px; text-align: center;">
        <p style="margin: 0 0 15px 0; color: #333;">
            Please click below to review the full Goods Inspection report:
        </p>
        <a href="{{ $inspectionUrl }}" 
           style="display: inline-block; padding: 12px 30px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: 600; text-align: center;">
            View Full Inspection Report
        </a>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Kindly ensure that all required verification and updates are completed in the system.
        </p>
    </div>

    <div style="text-align: center; padding-top: 20px; border-top: 1px solid #dee2e6; color: #999; font-size: 12px;">
        <p style="margin: 0;">Thanks,<br><strong>Fleet Management System</strong></p>
    </div>

</body>
</html>
