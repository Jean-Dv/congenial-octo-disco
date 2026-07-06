<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Commands;

use Moon\RemoteConsole\Contracts\RemoteCommandInterface;

/**
 * Marca al personaje para que el cliente permita re-personalizar
 * apariencia en el siguiente login (".character customize").
 */
final class CharacterCustomizeCommand implements RemoteCommandInterface
{
    public function __construct(
        public readonly string $characterName,
    ) {
    }

    public function name(): string
    {
        return 'character.customize';
    }

    public function toConsoleSyntax(): string
    {
        return "character customize {$this->characterName}";
    }
}
