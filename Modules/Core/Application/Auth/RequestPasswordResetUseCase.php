<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

use Modules\Core\Application\Auth\Ports\PasswordResetNotifierInterface;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\ValueObjects\Email;

final class RequestPasswordResetUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordResetNotifierInterface $notifier,
    ) {
    }

    public function handle(string $emailAddress): string
    {
        $email = new Email($emailAddress);

        // Deliberadamente no revela si el correo existe o no (evita
        // enumeracion de cuentas). El propio broker de Laravel ya se
        // comporta asi; nos limitamos a no filtrar informacion extra.
        if (! $this->users->existsByEmail($email)) {
            return 'moon.passwords.sent';
        }

        return $this->notifier->sendResetLink($email->value());
    }
}
