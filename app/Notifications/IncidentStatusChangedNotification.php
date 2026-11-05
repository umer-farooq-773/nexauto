<?php

namespace App\Notifications;

use App\Events\IncidentStatusChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(protected IncidentStatusChanged $event) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event    = $this->event;
        $incident = $event->incident;
        $waybill  = $event->waybill;

        // Vehicle details
        $vehicleNo = $incident->vehicle?->truck_no
            ?? $waybill?->vehicle?->truck_no
            ?? 'N/A';

        $fleetNo = $incident->vehicle?->fleet_no
            ?? $waybill?->vehicle?->fleet_no
            ?? 'N/A';

        // Route details from waybill's route_trip
        $routeFrom = $waybill?->route_trip?->origin      ?? 'N/A';
        $routeTo   = $waybill?->route_trip?->destination ?? 'N/A';

        // Incident date — try common date fields
        $incidentDate = $incident->accident_date
            ?? $incident->impound_date
            ?? $incident->breakdown_date
            ?? $incident->bogged_date
            ?? $incident->detention_date
            ?? $incident->haulage_date
            ?? $incident->misconduct_date
            ?? $incident->test_date
            ?? $incident->diversion_date
            ?? $incident->created_at;

        $formattedDate = $incidentDate
            ? \Carbon\Carbon::parse($incidentDate)->format('d M Y')
            : 'N/A';

        return (new MailMessage)
            ->subject("Incident Status Update — {$event->incidentLabel} | {$vehicleNo}")
            ->greeting('Dear Team,')
            ->line('This is to confirm that the reported incident details below have now been reviewed:')
            ->line('')
            ->line("**Incident Type:** {$event->incidentLabel}")
            ->line("**Vehicle No:** {$vehicleNo}")
            ->line("**Fleet No:** {$fleetNo}")
            ->line("**Route:** {$routeFrom} to {$routeTo}")
            ->line("**Date of Incident:** {$formattedDate}")
            ->line('**Time:** 8:00 PM Local Time')
            ->line('')
            ->line('The next course of action has been authorised by Management. Please proceed accordingly and ensure all necessary steps are taken without delay.')
            ->line('')
            ->line('Kindly use the link below to view the details and action accordingly:')
            ->action('View Incident Details', $event->showUrl)
            ->line('')
            ->line('Thank you for your prompt attention to this matter.')
            ->salutation('Regards, Fleet Management System');
    }

    public function toArray(object $notifiable): array
    {
        $event = $this->event;

        return [
            'title'          => "Incident Status Updated — {$event->incidentLabel}",
            'message'        => "The status of a {$event->incidentLabel} incident has been updated by the Client Director.",
            'incident_type'  => $event->incidentType,
            'incident_label' => $event->incidentLabel,
            'old_status'     => $event->oldStatus,
            'new_status'     => $event->newStatus,
            'target_url'     => $event->showUrl,
            'waybill_id'     => $event->waybill?->id,
        ];
    }
}
