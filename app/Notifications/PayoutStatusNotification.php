<?php
// © Atia Hegazy — atiaeno.com

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private Payout $payout, private ?string $adminNote = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount  = number_format((float) $this->payout->amount, 2);
        $status  = ucfirst($this->payout->status);

        $mail = (new MailMessage)
            ->subject("Payout Request {$status} — \${$amount}");

        match ($this->payout->status) {
            Payout::STATUS_APPROVED => $mail
                ->line("Your payout request of \${$amount} has been **approved**.")
                ->line('It will be transferred to your PayPal account shortly.'),

            Payout::STATUS_REJECTED => $mail
                ->line("Your payout request of \${$amount} has been **rejected**.")
                ->when($this->adminNote, fn ($m) => $m->line('Reason: ' . $this->adminNote))
                ->line('You may submit a new request once the issue is resolved.'),

            Payout::STATUS_PAID => $mail
                ->line("Your payout of \${$amount} has been **sent** to your PayPal account.")
                ->line('Please allow 1–3 business days for the funds to arrive.'),

            default => $mail->line("Your payout request status has been updated to: {$status}."),
        };

        $mail->line('If you have any questions, please contact support.');

        return $mail;
    }
}
