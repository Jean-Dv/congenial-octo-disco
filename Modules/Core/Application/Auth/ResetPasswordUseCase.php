<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

use Modules\Core\Application\GameAccount\Ports\GameAccountJobDispatcherInterface;
use Modules\Core\Domain\Auth\Ports\PasswordHasherInterface;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\User;
use Modules\Core\Domain\GameAccount\Exceptions\PasswordHashStrategyNotImplementedException;
use Modules\Core\Domain\GameAccount\GameAccountProvisioning;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyResolverInterface;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use RuntimeException;

/**
 * Reset de password: actualiza el panel Y empuja la nueva contraseña a
 * cada reino habilitado (confirmado con el negocio: "tambien debe
 * empujar los cambios a los reinos"). El password en texto plano solo
 * se usa aqui mismo, de forma sincrona, para calcular las credenciales
 * de cada core; nunca se encola en texto plano.
 */
final class ResetPasswordUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $panelHasher,
        private readonly RealmRepositoryInterface $realms,
        private readonly GameAccountProvisioningRepositoryInterface $provisionings,
        private readonly PasswordHashStrategyResolverInterface $strategies,
        private readonly GameAccountJobDispatcherInterface $jobs,
    ) {
    }

    public function handle(int $userId, string $newPlainPassword): User
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new RuntimeException("No existe el usuario #{$userId}.");
        }

        $user->changePassword($this->panelHasher->hash($newPlainPassword));
        $user = $this->users->save($user);

        foreach ($this->realms->allEnabled() as $realm) {
            try {
                $strategy = $this->strategies->resolve($realm->coreType());
                $credentials = $strategy->generateCredentials($user->name(), $newPlainPassword);
            } catch (PasswordHashStrategyNotImplementedException) {
                // Este core no tiene estrategia implementada todavia: no
                // hay forma de sincronizar la contraseña ahi, se omite.
                continue;
            }

            $provisioning = $this->provisionings->findByUserAndRealm($userId, $realm->id());

            if ($provisioning === null) {
                $provisioning = GameAccountProvisioning::requestFor($userId, $realm->id());
            }

            $provisioning->requeue();
            $provisioning = $this->provisionings->save($provisioning);

            $this->jobs->dispatchPasswordSync(
                provisioningId: $provisioning->id(),
                gameUsername: $user->name(),
                credentialColumns: $credentials->columns(),
            );
        }

        return $user;
    }
}
