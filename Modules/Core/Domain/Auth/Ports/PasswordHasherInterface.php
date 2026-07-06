<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\Ports;

use Modules\Core\Domain\Auth\ValueObjects\HashedPassword;

/**
 * Hashing del password del PANEL (bcrypt/argon2 via Laravel Hash).
 * No confundir con Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyInterface,
 * que calcula las credenciales que entiende el core del juego (SRP6, etc).
 */
interface PasswordHasherInterface
{
    public function hash(string $plainPassword): HashedPassword;

    public function verify(string $plainPassword, HashedPassword $hashed): bool;
}
