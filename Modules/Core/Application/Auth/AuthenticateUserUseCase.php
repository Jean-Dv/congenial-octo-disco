<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

use Modules\Core\Domain\Auth\Exceptions\InvalidCredentialsException;
use Modules\Core\Domain\Auth\Ports\PasswordHasherInterface;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\User;
use Modules\Core\Domain\Auth\ValueObjects\Email;

/**
 * Login: autentica EXCLUSIVAMENTE contra la tabla `users` del panel.
 * Jamas consulta la tabla `account` de ningun reino (ver README).
 */
final class AuthenticateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $hasher,
    ) {
    }

    public function handle(AuthenticateUserInput $input): User
    {
        $email = new Email($input->email);
        $user = $this->users->findByEmail($email);

        if ($user === null || ! $this->hasher->verify($input->password, $user->password())) {
            throw InvalidCredentialsException::make();
        }

        return $user;
    }
}
