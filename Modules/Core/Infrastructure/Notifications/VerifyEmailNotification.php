<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Se encola en Redis (ShouldQueue) tal como se definio con el negocio.
 */
final class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('mail');
    }

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return (new MailMessage())
            ->subject(__('core::auth.verify_email.subject'))
            ->greeting(__('core::auth.verify_email.greeting', ['name' => $notifiable->name]))
            ->line(__('core::auth.verify_email.line'))
            ->action(__('core::auth.verify_email.action'), $verificationUrl)
            ->line(__('core::auth.verify_email.footer'));
    }
}
