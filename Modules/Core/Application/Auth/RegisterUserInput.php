<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

/**
 * El mismo username y password que llegan aqui se usan, sin transformar,
 * tanto para el hash de panel (bcrypt) como para calcular las
 * credenciales de juego (SRP6 u otro esquema) en cada reino habilitado:
 * se capturan UNA sola vez, tal como se definio con el negocio.
 */
final class RegisterUserInput
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly string $locale = 'es',
    ) {
    }
}
