<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\User;
use RuntimeException;

/**
 * La validez de la URL firmada (hash, expiracion) la revisa el
 * controlador con las herramientas nativas de Laravel: este caso de uso
 * solo aplica el cambio de estado del dominio una vez esa validacion ya
 * paso, para mantenerlo libre de detalles de infraestructura HTTP.
 */
final class VerifyEmailUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function handle(int $userId): User
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new RuntimeException("No existe el usuario #{$userId}.");
        }

        if (! $user->isEmailVerified()) {
            $user->markEmailAsVerified();
            $user = $this->users->save($user);
        }

        return $user;
    }
}
