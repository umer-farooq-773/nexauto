<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverOvertimeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $driverName,
        public string $waybillNumber,
        public string $vehicleFleet,
        public int $allocatedSeconds,
        public int $actualDrivenSeconds,
        public string $targetUrl
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'NexAuto');
        $allocatedHours = round($this->allocatedSeconds / 3600, 2);
        $drivenHours = round($this->actualDrivenSeconds / 3600, 2);

        return (new MailMessage)
            ->error()
            ->subject("[{$appName} ALERT] Driver Overtime Violation — {$this->driverName}")
            ->greeting("Attention Required,")
            ->line("Driver **{$this->driverName}** has exceeded their scheduled driving time limit on Waybill **#{$this->waybillNumber}** (Vehicle: **{$this->vehicleFleet}**).")
            ->line("**Allocated Time:** {$allocatedHours} hours")
            ->line("**Actual Driving Time:** {$drivenHours} hours")
            ->line("ELD compliance requires immediate driver rest or handover to relief driver.")
            ->action('Review Trip & Driver Log', $this->targetUrl)
            ->salutation("Fleet Safety Monitoring System");
    }

    public function toArray(object $notifiable): array
    {
        $allocatedHours = round($this->allocatedSeconds / 3600, 2);
        $drivenHours = round($this->actualDrivenSeconds / 3600, 2);

        return [
            'title'            => '🚨 Driver Overtime Violation',
            'message'          => "Driver {$this->driverName} has exceeded allocated driving time ({$allocatedHours}h) on Waybill #{$this->waybillNumber}.",
            'driver_name'      => $this->driverName,
            'waybill_number'   => $this->waybillNumber,
            'vehicle_fleet'    => $this->vehicleFleet,
            'allocated_hours'  => $allocatedHours,
            'driven_hours'     => $drivenHours,
            'target_url'       => $this->targetUrl,
        ];
    }
}

