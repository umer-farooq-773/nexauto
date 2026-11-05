<?php

namespace App\Notifications;

use App\Models\Waybill;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WaybillStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Waybill $waybill,
        protected string  $oldStatus,
        protected string  $newStatus,
        protected string  $targetUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $waybillNumber = $this->waybill->waybill_number ?? 'N/A';
        $vehicleNo     = $this->waybill->vehicle?->truck_no ?? 'N/A';

        return [
            'title'          => "Waybill Status Updated — {$waybillNumber}",
            'message'        => "Waybill {$waybillNumber} (Vehicle: {$vehicleNo}) status changed from \"{$this->oldStatus}\" to \"{$this->newStatus}\".",
            'waybill_id'     => $this->waybill->id,
            'waybill_number' => $waybillNumber,
            'vehicle_no'     => $vehicleNo,
            'old_status'     => $this->oldStatus,
            'new_status'     => $this->newStatus,
            'target_url'     => $this->targetUrl,
        ];
    }
}
