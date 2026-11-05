<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClientApprovedIncidentNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $incidentLabel,
        protected string $targetUrl,
        protected string $vehicleNo = 'N/A',
        protected string $approvedBy = 'Client Director'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'          => "Client Approval — {$this->incidentLabel}",
            'message'        => "{$this->incidentLabel} incident for vehicle {$this->vehicleNo} has been approved by {$this->approvedBy}. Status change is now unlocked.",
            'incident_label' => $this->incidentLabel,
            'approved_by'    => $this->approvedBy,
            'vehicle_no'     => $this->vehicleNo,
            'target_url'     => $this->targetUrl,
        ];
    }
}
