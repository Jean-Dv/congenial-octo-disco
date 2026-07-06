<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Notifications;

use Modules\Core\Application\Auth\Ports\EmailVerificationNotifierInterface;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Puente hacia el mecanismo nativo de Laravel: UserModel implementa
 * MustVerifyEmail y sabe enviar su propia notificacion (ver
 * UserModel::sendEmailVerificationNotification, que despacha
 * VerifyEmailNotification en cola). El caso de uso de Application no
 * necesita saber nada de esto.
 */
final class LaravelEmailVerificationNotifier implements EmailVerificationNotifierInterface
{
    public function send(int $userId, string $email, string $name): void
    {
        UserModel::findOrFail($userId)->sendEmailVerificationNotification();
    }
}
