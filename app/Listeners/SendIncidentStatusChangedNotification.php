<?php

namespace App\Listeners;

use App\Constants\Constants;
use App\Events\IncidentStatusChanged;
use App\Models\User;
use App\Notifications\IncidentStatusChangedNotification;
use Illuminate\Support\Facades\Log;

class SendIncidentStatusChangedNotification
{
    public function handle(IncidentStatusChanged $event): void
    {
        // Roles that should receive the notification
        $recipients = User::role([
            Constants::ADMIN,
            Constants::SUPERADMIN,
            Constants::CLIENT_MANAGEMENT,
            Constants::CHAMPS_MANAGEMENT,
        ])
        ->whereNotNull('email')
        ->get()
        ->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new IncidentStatusChangedNotification($event);

        foreach ($recipients as $user) {
            try {
                // Database notification
                $user->notifyNow($notification, ['database']);
            } catch (\Throwable $e) {
                Log::warning('IncidentStatusChanged: DB notification failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            try {
                // Mail notification
                $user->notifyNow($notification, ['mail']);
            } catch (\Throwable $e) {
                Log::warning('IncidentStatusChanged: Mail notification failed', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
