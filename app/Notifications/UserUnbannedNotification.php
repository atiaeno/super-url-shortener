<?php
// © Atia Hegazy — atiaeno.com

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserUnbannedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Reinstated')
            ->line('Good news! Your account suspension has been lifted.')
            ->line('You can now log in and use the platform normally.')
            ->action('Log In', url('/login'))
            ->line('Thank you for your patience.');
    }
}
