<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

use Modules\Core\Domain\Realm\Ports\RealmConnectivityVerifierInterface;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use Modules\Core\Domain\Realm\ValueObjects\RemoteConsoleConfig;
use Modules\Core\Domain\Realm\ValueObjects\SshTunnelConfig;
use RuntimeException;

final class UpdateRealmUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
        private readonly RealmConnectivityVerifierInterface $connectivity,
    ) {}

    public function handle(int $realmId, UpdateRealmInput $input): Realm
    {
        $realm = $this->realms->findById($realmId);

        if ($realm === null) {
            throw new RuntimeException("No existe el reino #{$realmId}.");
        }

        $authDatabase = $input->authDatabase;
        $authDatabase['password'] = $this->secretOrExisting(
            $authDatabase['password'] ?? null,
            $realm->authDatabase()->password,
        );

        $charactersDatabase = $input->charactersDatabase;
        if ($charactersDatabase !== null) {
            $charactersDatabase['password'] = $this->secretOrExisting(
                $charactersDatabase['password'] ?? null,
                $realm->charactersDatabase()?->password,
            );
        }

        $remoteConsole = $input->remoteConsole;
        $remoteConsole['password'] = $this->secretOrExisting(
            $remoteConsole['password'] ?? null,
            $realm->remoteConsole()->password,
        );

        $sshTunnel = $input->sshTunnel;
        if ($sshTunnel !== null) {
            $existingSshTunnel = $realm->sshTunnel();
            $privateKey = $sshTunnel['private_key'] ?? null;

            if ($privateKey === null || $privateKey === '') {
                if ($existingSshTunnel === null) {
                    throw new RuntimeException('Se requiere una llave privada para configurar el túnel SSH.');
                }

                $sshTunnel['private_key'] = $existingSshTunnel->privateKey;
                $sshTunnel['private_key_passphrase'] = $existingSshTunnel->privateKeyPassphrase;
            }
        }

        $realm->rename($input->name);
        $realm->updateAuthDatabase(DatabaseConnectionConfig::fromArray($authDatabase));
        $realm->updateCharactersDatabase(
            $charactersDatabase !== null ? DatabaseConnectionConfig::fromArray($charactersDatabase) : null
        );
        $realm->updateRemoteConsole(RemoteConsoleConfig::fromArray($remoteConsole));
        $realm->updateSshTunnel(
            $sshTunnel !== null ? SshTunnelConfig::fromArray($sshTunnel) : null
        );

        $input->enabled ? $realm->enable() : $realm->disable();

        $this->connectivity->verify($realm);

        return $this->realms->save($realm);
    }

    private function secretOrExisting(?string $replacement, ?string $existing): string
    {
        if ($replacement !== null && $replacement !== '') {
            return $replacement;
        }

        if ($existing === null || $existing === '') {
            throw new RuntimeException('Se requiere una contraseña para configurar una conexion nueva.');
        }

        return $existing;
    }
}
