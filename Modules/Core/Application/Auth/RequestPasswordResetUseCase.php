<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

use Modules\Core\Application\Auth\Ports\PasswordResetNotifierInterface;
use Modules\Core\Domain\Auth\Ports\PasswordHasherInterface;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\User;
use Modules\Core\Domain\Auth\ValueObjects\Email;
use Modules\Core\Domain\GameAccount\Exceptions\PasswordHashStrategyNotImplementedException;
use Modules\Core\Domain\GameAccount\GameAccountProvisioning;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayResolverInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\GameAccountIdentity;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;

final class RequestPasswordResetUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordResetNotifierInterface $notifier,
        private readonly PasswordHasherInterface $panelHasher,
        private readonly RealmRepositoryInterface $realms,
        private readonly GameAccountGatewayResolverInterface $gateways,
        private readonly GameAccountProvisioningRepositoryInterface $provisionings,
    ) {}

    public function handle(string $emailAddress): string
    {
        $email = new Email($emailAddress);

        // El CMS siempre tiene prioridad. Solo se toca una BD auth cuando
        // el correo todavia no tiene identidad local.
        if ($this->users->findByEmail($email) !== null) {
            return $this->notifier->sendResetLink($email->value());
        }

        $matches = $this->findGameAccounts($email);

        // Deliberadamente no revela si el correo existe o no (evita
        // enumeracion de cuentas).
        if ($matches === []) {
            return 'moon.passwords.sent';
        }

        $primaryUsername = $matches[0]['account']->username;
        $panelName = $this->availablePanelName($primaryUsername, $email);

        // La cuenta importada no puede iniciar sesion con este secreto
        // aleatorio. Su primera credencial real se establece al consumir
        // el enlace de recuperacion.
        $user = User::register(
            name: $panelName,
            email: $email,
            password: $this->panelHasher->hash(bin2hex(random_bytes(32))),
        );
        $user = $this->users->save($user);

        foreach ($matches as $match) {
            $provisioning = GameAccountProvisioning::requestFor(
                $user->id(),
                $match['realm']->id(),
                $match['account']->username,
            );
            $provisioning->markReady();
            $this->provisionings->save($provisioning);
        }

        return $this->notifier->sendResetLink($email->value());
    }

    /**
     * @return array<int, array{realm: Realm, account: GameAccountIdentity}>
     */
    private function findGameAccounts(Email $email): array
    {
        $matches = [];

        foreach ($this->realms->allEnabled() as $realm) {
            try {
                $gateway = $this->gateways->resolve($realm->coreType());
                $account = $gateway->findAccountByEmail($realm, $email->value());
            } catch (PasswordHashStrategyNotImplementedException) {
                continue;
            }

            if ($account !== null) {
                $matches[] = [
                    'realm' => $realm,
                    'account' => $account,
                ];
            }
        }

        return $matches;
    }

    private function availablePanelName(string $gameUsername, Email $email): string
    {
        if (strlen($gameUsername) <= 16 && ! $this->users->existsByName($gameUsername)) {
            return $gameUsername;
        }

        // Una instalacion multi-reino puede tener el mismo username
        // asignado a correos distintos. El login del panel usa email, por
        // lo que generamos un nombre local unico y conservamos el username
        // real en cada provisioning para sincronizar la cuenta correcta.
        $base = preg_replace('/[^A-Za-z0-9]/', '', $gameUsername) ?: 'Player';
        $base = substr($base, 0, 9);

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $seed = $email->value().'|'.$gameUsername.'|'.$attempt;
            $suffix = substr(hash('sha256', $seed), 0, 6);
            $candidate = "{$base}_{$suffix}";

            if (! $this->users->existsByName($candidate)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('No fue posible asignar un nombre unico al usuario importado.');
    }
}
