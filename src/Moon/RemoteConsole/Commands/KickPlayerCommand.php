<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Commands;

use Moon\RemoteConsole\Contracts\RemoteCommandInterface;

final class KickPlayerCommand implements RemoteCommandInterface
{
    public function __construct(
        public readonly string $characterName,
        public readonly ?string $reason = null,
    ) {
    }

    public function name(): string
    {
        return 'player.kick';
    }

    public function toConsoleSyntax(): string
    {
        $command = "kick {$this->characterName}";

        if (! empty($this->reason)) {
            $command .= " {$this->reason}";
        }

        return $command;
    }
}
