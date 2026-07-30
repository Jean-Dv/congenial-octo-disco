<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Network;

use Modules\Core\Domain\Realm\Ports\RealmLatencyProbeInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Infrastructure\Persistence\Connection\RealmConnectionFactory;

final readonly class RealmlistTcpLatencyProbe implements RealmLatencyProbeInterface
{
    public function __construct(
        private RealmConnectionFactory $connections,
    ) {}

    public function measure(Realm $realm): ?int
    {
        $query = $this->connections->authConnectionFor($realm)
            ->table('realmlist');

        if ($realm->gmRealmId() > 0) {
            $query->where('id', $realm->gmRealmId());
        } else {
            $query->where('name', $realm->name());
        }

        $endpoint = $query->first(['address', 'port'])
            ?? $this->connections->authConnectionFor($realm)
                ->table('realmlist')
                ->orderBy('id')
                ->first(['address', 'port']);

        if ($endpoint === null) {
            return null;
        }

        $host = trim((string) $endpoint->address);
        $port = (int) $endpoint->port;

        if ($host === '' || $port < 1 || $port > 65535) {
            return null;
        }

        $socketHost = str_contains($host, ':') ? "[{$host}]" : $host;
        $startedAt = hrtime(true);
        $socket = @stream_socket_client(
            "tcp://{$socketHost}:{$port}",
            $errorCode,
            $errorMessage,
            (float) config('realmlist.latency_timeout', 1),
            STREAM_CLIENT_CONNECT,
        );

        if ($socket === false) {
            return null;
        }

        fclose($socket);

        return max(1, (int) round((hrtime(true) - $startedAt) / 1_000_000));
    }
}
