<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

use Modules\Core\Application\Auth\Ports\EmailVerificationNotifierInterface;
use Modules\Core\Application\GameAccount\Ports\GameAccountJobDispatcherInterface;
use Modules\Core\Domain\Auth\Exceptions\UserAlreadyExistsException;
use Modules\Core\Domain\Auth\Ports\PasswordHasherInterface;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\User;
use Modules\Core\Domain\Auth\ValueObjects\Email;
use Modules\Core\Domain\GameAccount\Exceptions\PasswordHashStrategyNotImplementedException;
use Modules\Core\Domain\GameAccount\GameAccountProvisioning;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyResolverInterface;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;

/**
 * Registro: crea el usuario del panel y aprovisiona automaticamente una
 * cuenta de juego en TODOS los reinos habilitados. El aprovisionamiento
 * en si (escritura en la BD del reino) es asincrono/encolado y con
 * reintentos: si un reino esta caido en ese momento, el usuario igual
 * puede entrar al panel y ve el estado "pendiente" hasta que se resuelva.
 */
final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $panelHasher,
        private readonly RealmRepositoryInterface $realms,
        private readonly GameAccountProvisioningRepositoryInterface $provisionings,
        private readonly PasswordHashStrategyResolverInterface $strategies,
        private readonly GameAccountJobDispatcherInterface $jobs,
        private readonly EmailVerificationNotifierInterface $verificationNotifier,
    ) {}

    public function handle(RegisterUserInput $input): User
    {
        $email = new Email($input->email);

        if ($this->users->existsByEmail($email)) {
            throw UserAlreadyExistsException::withEmail($email->value());
        }

        $user = User::register(
            name: $input->username,
            email: $email,
            password: $this->panelHasher->hash($input->password),
            locale: $input->locale,
        );

        $user = $this->users->save($user);

        foreach ($this->realms->allEnabled() as $realm) {
            $provisioning = GameAccountProvisioning::requestFor(
                $user->id(),
                $realm->id(),
                $input->username,
            );

            try {
                $strategy = $this->strategies->resolve($realm->coreType());
                $credentials = $strategy->generateCredentials($input->username, $input->password);
            } catch (PasswordHashStrategyNotImplementedException $exception) {
                // No bloqueamos el registro por un reino con un core que
                // todavia no tiene estrategia implementada: se marca
                // fallido y queda visible en el dashboard para ese reino.
                $provisioning->markFailed($exception->getMessage());
                $this->provisionings->save($provisioning);

                continue;
            }

            $provisioning = $this->provisionings->save($provisioning);

            $this->jobs->dispatchProvision(
                provisioningId: $provisioning->id(),
                gameUsername: $input->username,
                gameEmail: $input->email,
                credentialColumns: $credentials->columns(),
            );
        }

        $this->verificationNotifier->send($user->id(), $email->value(), $user->name());

        return $user;
    }
}
