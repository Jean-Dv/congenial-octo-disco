<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\ValueObjects;

use InvalidArgumentException;

/**
 * Identidad minima leida de la tabla `account` durante la recuperacion
 * de una cuenta que todavia no existe en el CMS.
 */
final readonly class GameAccountIdentity
{
    public function __construct(
        public string $username,
        public string $email,
    ) {
        if (trim($this->username) === '') {
            throw new InvalidArgumentException('El username de la cuenta de juego no puede estar vacio.');
        }
    }
}
