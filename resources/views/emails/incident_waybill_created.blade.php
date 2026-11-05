<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0; color: #333;">Hello {{ $notifiableName }},</h2>
        <p style="margin: 0; color: #666;">We would like to inform you that a new waybill has been created and linked with an existing incident record.</p>
    </div>

    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 15px 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Incident Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Waybill Number:</td>
                <td style="padding: 8px 0; color: #333;">{{ $waybillNumber }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Vehicle Number:</td>
                <td style="padding: 8px 0; color: #333;">{{ $vehicle }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600; color: #555;">Incident Type:</td>
                <td style="padding: 8px 0; color: #333;">{{ $incidentLabel }}</td>
            </tr>
        </table>
    </div>

    @if($isPositiveBreathaliser)
    <div style="background-color: #f8d7da; border: 3px solid #dc3545; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; align-items: flex-start;">
            <div style="font-size: 32px; color: #dc3545; margin-right: 15px;">⚠️</div>
            <div style="flex: 1;">
                <h3 style="color: #721c24; margin: 0 0 12px 0; font-size: 18px; font-weight: 700;">
                    🚫 CRITICAL: Driver Stand-Down Required
                </h3>
                <p style="color: #721c24; margin: 0 0 10px 0; font-size: 15px; line-height: 1.6; font-weight: 600;">
                    This failure means driver must not be allowed to drive the vehicle until otherwise instructed.
                </p>
                <p style="color: #721c24; margin: 0; font-size: 14px; font-weight: 600; background-color: #f1aeb5; padding: 8px; border-radius: 4px;">
                    ⚠️ Immediate action is required. The driver must be removed from duty immediately.
                </p>
            </div>
        </div>
    </div>
    @endif

    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <p style="margin: 0 0 20px 0; color: #333;">
            This vehicle is already associated with the <strong>{{ $incidentLabel }}</strong> incident category. Please review it for further action.
        </p>
        
        <a href="{{ $targetUrl }}" 
           style="display: inline-block; padding: 12px 30px; background-color: {{ $isPositiveBreathaliser ? '#dc3545' : '#007bff' }}; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: 600; text-align: center;">
            View Incident Details
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
