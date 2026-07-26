<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Connection;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use RuntimeException;

/**
 * Resuelve conexiones MySQL de un reino EN CALIENTE, a partir de lo
 * guardado en la tabla `realms`, sin declarar nada en config/database.php.
 * Esto es lo que permite soportar multi-realm / multi-core: cada reino
 * puede vivir en un host/puerto/credenciales completamente distintos.
 */
final class RealmConnectionFactory
{
    /** @var array<string, string> */
    private array $registered = [];

    public function __construct(
        private readonly SshTunnelManager $tunnels,
    ) {}

    public function authConnectionFor(Realm $realm): ConnectionInterface
    {
        return $this->connectionFor($realm, 'auth', $realm->authDatabase());
    }

    public function charactersConnectionFor(Realm $realm): ConnectionInterface
    {
        $config = $realm->charactersDatabase();

        if ($config === null) {
            throw new RuntimeException(
                "El reino \"{$realm->name()}\" no tiene configurada una base de datos de personajes."
            );
        }

        return $this->connectionFor($realm, 'characters', $config);
    }

    private function connectionFor(Realm $realm, string $kind, DatabaseConnectionConfig $config): ConnectionInterface
    {
        $name = $this->connectionName($realm, $kind);
        $endpoint = $realm->usesSshTunnel()
            ? $this->tunnels->endpointFor($realm, $kind)
            : new TunnelEndpoint($config->host, $config->port);
        $fingerprint = hash('sha256', serialize([
            $endpoint->host,
            $endpoint->port,
            $config->database,
            $config->username,
            $config->password,
        ]));

        if (($this->registered[$name] ?? null) !== $fingerprint) {
            DB::purge($name);

            Config::set("database.connections.{$name}", [
                'driver' => 'mysql',
                'host' => $endpoint->host,
                'port' => $endpoint->port,
                'database' => $config->database,
                'username' => $config->username,
                'password' => $config->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                // Los esquemas de estos cores son antiguos (MyISAM/InnoDB
                // mixtos, columnas sin defaults estrictos): no forzamos
                // sql modes estrictos para evitar romper writes validos.
                'strict' => false,
            ]);

            $this->registered[$name] = $fingerprint;
        }

        return DB::connection($name);
    }

    public function connectionName(Realm $realm, string $kind): string
    {
        $identity = $realm->id() ?? 'new_'.spl_object_id($realm);

        return "realm_{$identity}_{$kind}";
    }
}
