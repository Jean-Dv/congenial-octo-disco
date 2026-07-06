<?php

declare(strict_types=1);

namespace Modules\Core\Application\GameAccount;

use Moon\RemoteConsole\Commands\ServerInfoCommand;
use Moon\RemoteConsole\Contracts\RemoteConsoleConnection;
use Moon\RemoteConsole\Contracts\RemoteConsoleGatewayInterface;
use Moon\RemoteConsole\Contracts\RemoteCommandResult;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use RuntimeException;

/**
 * Consulta uptime/version/jugadores online del worldserver de un reino,
 * vía SOAP (o el protocolo que corresponda a futuro).
 */
final class GetServerStatusUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
        private readonly RemoteConsoleGatewayInterface $gateway,
    ) {
    }

    public function handle(int $realmId): RemoteCommandResult
    {
        $realm = $this->realms->findById($realmId);

        if ($realm === null) {
            throw new RuntimeException("No existe el reino #{$realmId}.");
        }

        $console = $realm->remoteConsole();

        $connection = new RemoteConsoleConnection(
            host: $console->host,
            port: $console->port,
            username: $console->username,
            password: $console->password,
            options: ['namespace_uri' => $realm->coreType()->soapNamespaceUri()],
        );

        return $this->gateway->execute(new ServerInfoCommand(), $connection);
    }
}
