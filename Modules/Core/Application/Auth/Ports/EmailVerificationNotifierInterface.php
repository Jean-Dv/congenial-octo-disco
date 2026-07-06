<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth\Ports;

/**
 * Envia el correo de verificacion de cuenta. La implementacion (en
 * Infrastructure/Notifications) construye la URL firmada de Laravel y
 * despacha una Notification en cola (Redis) — el caso de uso no sabe
 * nada de URLs firmadas ni de colas.
 */
interface EmailVerificationNotifierInterface
{
    public function send(int $userId, string $email, string $name): void;
}
