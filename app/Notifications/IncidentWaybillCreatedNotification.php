<?php

namespace App\Notifications;

use App\Models\Waybill;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentWaybillCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Waybill $waybill,
        protected string $incidentType,
        protected string $incidentLabel,
        protected string $targetUrl,
        protected ?int $incidentId = null,
        protected array $additionalData = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->waybill->vehicle?->truck_no ?? 'No Vehicle Assigned';
        $waybillNumber = $this->waybill->waybill_number ?? 'N/A';
        
        $isPositiveBreathaliser = ($this->incidentType === 'breathaliser_test' 
            && isset($this->additionalData['test_result']) 
            && $this->additionalData['test_result'] === 'positive');

        return (new MailMessage)
            ->subject("New Incident Alert - Waybill " . $waybillNumber . " Linked" . ($isPositiveBreathaliser ? " - ⚠️ CRITICAL" : ""))
            ->view('emails.incident_waybill_created', [
                'notifiableName' => $notifiable->name ?? 'Team',
                'waybillNumber' => $waybillNumber,
                'vehicle' => $vehicle,
                'incidentLabel' => $this->incidentLabel,
                'isPositiveBreathaliser' => $isPositiveBreathaliser,
                'targetUrl' => $this->targetUrl,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $vehicle = $this->waybill->vehicle?->truck_no ?? 'No Vehicle Assigned';
        $waybillNumber = $this->waybill->waybill_number ?? 'N/A';

        return [
            'title' => 'New Incident Waybill Alert',
            'message' => "Waybill " . $waybillNumber . " has been linked to " . $this->incidentLabel . " for vehicle " . $vehicle . ". Immediate review may be required.",
            'target_url' => $this->targetUrl,
            'incident_id' => $this->incidentId,
            'incident_type' => $this->incidentType,
            'incident_label' => $this->incidentLabel,
            'waybill_id' => $this->waybill->id,
            'waybill_number' => $waybillNumber,
            'vehicle_id' => $this->waybill->vehicle_id,
        ];
    }
}
