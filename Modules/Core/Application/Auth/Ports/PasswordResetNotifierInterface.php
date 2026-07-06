<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth\Ports;

interface PasswordResetNotifierInterface
{
    /**
     * @return string Estado devuelto por el broker (ej. Password::RESET_LINK_SENT).
     */
    public function sendResetLink(string $email): string;
}
