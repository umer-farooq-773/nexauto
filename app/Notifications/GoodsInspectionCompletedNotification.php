<?php

namespace App\Notifications;

use App\Models\GoodsInspection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoodsInspectionCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected GoodsInspection $inspection,
        protected array $linkedIncidents = [],
        protected ?string $inspectionUrl = null
    ) {
        $this->inspectionUrl = $inspectionUrl ?: route('goods_inspection.show', $inspection->id);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fleet = $this->inspection->fleet_no ?? 'Asset';
        $status = $this->inspection->goods_status === 'cleared' ? 'Cleared' : 'Issues Found';
        $incidentsCount = count($this->linkedIncidents);

        $subject = "Goods Inspection Completed - Fleet {$fleet} ({$status})" . ($incidentsCount > 0 ? " - 🚨 {$incidentsCount} Incidents Logged" : "");

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.goods_inspection_completed', [
                'notifiableName' => $notifiable->name ?? $notifiable->full_name ?? 'Team',
                'inspection' => $this->inspection,
                'linkedIncidents' => $this->linkedIncidents,
                'inspectionUrl' => $this->inspectionUrl,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $fleet = $this->inspection->fleet_no ?? 'Asset';
        $waybill = $this->inspection->waybill_number ?? 'N/A';
        $count = count($this->linkedIncidents);

        return [
            'title' => 'Goods Inspection Completed',
            'message' => "Goods Inspection for Fleet {$fleet} (Waybill {$waybill}) recorded. Status: " . ucfirst($this->inspection->goods_status) . ($count > 0 ? " with {$count} linked incident(s)." : "."),
            'target_url' => $this->inspectionUrl,
            'inspection_id' => $this->inspection->id,
            'goods_status' => $this->inspection->goods_status,
            'fleet_no' => $fleet,
            'waybill_number' => $waybill,
            'incidents_count' => $count,
            'incidents' => $this->linkedIncidents,
        ];
    }
}
