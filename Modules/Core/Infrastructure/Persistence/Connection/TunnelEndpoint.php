<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Connection;

final class TunnelEndpoint
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
    ) {}
}
