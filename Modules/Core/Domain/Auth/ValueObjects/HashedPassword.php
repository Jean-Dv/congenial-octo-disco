<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\ValueObjects;

use InvalidArgumentException;

/**
 * Envuelve un hash YA CALCULADO (bcrypt/argon2 del panel). Esta clase
 * nunca hashea nada por si misma: eso es responsabilidad de la
 * implementacion de PasswordHasherInterface, para mantener el dominio
 * libre de dependencias de infraestructura criptografica concreta.
 */
final class HashedPassword
{
    public function __construct(
        private readonly string $value,
    ) {
        if (trim($value) === '') {
            throw new InvalidArgumentException('El hash de la contraseña no puede estar vacio.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
