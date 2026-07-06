<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Notifications;

use Illuminate\Support\Facades\Password;
use Modules\Core\Application\Auth\Ports\PasswordResetNotifierInterface;

final class LaravelPasswordResetNotifier implements PasswordResetNotifierInterface
{
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }
}
