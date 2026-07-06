<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Commands;

use Moon\RemoteConsole\Contracts\RemoteCommandInterface;

/**
 * Marca al personaje para que el cliente le pida un nuevo nombre en el
 * siguiente login (comportamiento estandar de ".character rename").
 */
final class CharacterRenameCommand implements RemoteCommandInterface
{
    public function __construct(
        public readonly string $characterName,
    ) {
    }

    public function name(): string
    {
        return 'character.rename';
    }

    public function toConsoleSyntax(): string
    {
        return "character rename {$this->characterName}";
    }
}
