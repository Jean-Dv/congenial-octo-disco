<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
    ) {
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
        // The reset token is hashed at rest and expires through the password broker.
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject(__('core::auth.reset_password.subject'))
            ->greeting(__('core::auth.reset_password.greeting', ['name' => $notifiable->name]))
            ->line(__('core::auth.reset_password.line'))
            ->action(__('core::auth.reset_password.action'), $resetUrl)
            ->line(__('core::auth.reset_password.expires'))
            ->line(__('core::auth.reset_password.footer'));
    }
}
