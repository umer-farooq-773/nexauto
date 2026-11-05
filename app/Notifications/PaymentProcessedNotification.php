<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $moduleLabel,
        protected string $targetUrl,
        protected string $vehicleNo = 'N/A'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Payment Processed - {$this->moduleLabel}",
            'message' => "{$this->moduleLabel} payment for vehicle {$this->vehicleNo} has been processed.",
            'module_label' => $this->moduleLabel,
            'vehicle_no' => $this->vehicleNo,
            'target_url' => $this->targetUrl,
        ];
    }
}
