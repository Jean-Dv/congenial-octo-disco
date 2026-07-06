<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Commands;

use Moon\RemoteConsole\Contracts\RemoteCommandInterface;

/**
 * Pide version, uptime y jugadores conectados del worldserver.
 */
final class ServerInfoCommand implements RemoteCommandInterface
{
    public function name(): string
    {
        return 'server.info';
    }

    public function toConsoleSyntax(): string
    {
        return 'server info';
    }
}
