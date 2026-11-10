<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverCredentialsNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $driverName,
        public string $email,
        public string $plainPassword,
        public ?string $loginUrl = null
    ) {
        $this->loginUrl = $loginUrl ?: url('/account/signin');
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'NexAuto');

        return (new MailMessage)
            ->subject("[{$appName}] Your Driver ELD Portal Credentials")
            ->greeting("Hello {$this->driverName},")
            ->line("An Electronic Logging Device (ELD) driver account has been created for you on {$appName}.")
            ->line('You can use the following credentials to log in to your dedicated Driver Portal:')
            ->line("**Login Email:** {$this->email}")
            ->line("**Temporary Password:** {$this->plainPassword}")
            ->action('Log In to Driver Portal', $this->loginUrl)
            ->line('Once logged in, you can start your trip timer, view your waybill route on the live map, and access your real-time ELD driver logs.')
            ->line('For security, please keep your credentials confidential.')
            ->salutation("Best regards,\n{$appName} Fleet Management");
    }
}

