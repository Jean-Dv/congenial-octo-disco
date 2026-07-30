<?php

declare(strict_types=1);

namespace Modules\Core\Application\GameAccount;

final readonly class ServerInfo
{
    public function __construct(
        public int $onlinePlayers,
        public ?int $connectionPeak,
        public ?string $uptime,
        public ?string $version,
    ) {}
}
