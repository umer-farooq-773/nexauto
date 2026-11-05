<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecordCreatedNotification extends Notification
{
    public function __construct(
        protected string $moduleLabel,
        protected string $targetUrl,
        protected string $vehicleNo = 'N/A'
    ) {}

    public function via(object $notifiable): array
    {
        // Send both DB (bell icon) and mail on every channel
        $channels = ['database'];

        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->full_name ?? $notifiable->name ?? 'Team';

        return (new MailMessage)
            ->subject("New {$this->moduleLabel} Record — Vehicle {$this->vehicleNo}")
            ->greeting("Hello {$name},")
            ->line("A new **{$this->moduleLabel}** record has been created for vehicle **{$this->vehicleNo}**.")
            ->line('Please review the record at your earliest convenience and take any required action.')
            ->action('View Record', $this->targetUrl)
            ->line('Thank you for your attention.')
            ->salutation('Regards, Fleet Management System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'        => "New {$this->moduleLabel} Record",
            'message'      => "A new {$this->moduleLabel} record has been created for vehicle {$this->vehicleNo}.",
            'module_label' => $this->moduleLabel,
            'vehicle_no'   => $this->vehicleNo,
            'target_url'   => $this->targetUrl,
        ];
    }
}
