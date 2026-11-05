<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentChatNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $incidentType,
        protected string $incidentId,
        protected string $incidentLabel,
        protected string $senderName,
        protected string $message,
        protected string $targetUrl
    ) {
        // Run on sync connection for immediate delivery
        $this->onConnection('sync');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New Chat Message - {$this->incidentLabel}",
            'message' => "{$this->senderName}: " . substr($this->message, 0, 100) . (strlen($this->message) > 100 ? '...' : ''),
            'incident_type' => $this->incidentType,
            'incident_id' => $this->incidentId,
            'incident_label' => $this->incidentLabel,
            'sender_name' => $this->senderName,
            'target_url' => $this->targetUrl,
            'type' => 'incident_chat',
        ];
    }
}
