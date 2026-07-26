<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Connection;

use Symfony\Component\Process\Process;

final class SshTunnelHandle
{
    /**
     * @param  array<string, TunnelEndpoint>  $endpoints
     */
    public function __construct(
        public readonly string $fingerprint,
        public readonly Process $process,
        public readonly array $endpoints,
    ) {}
}
