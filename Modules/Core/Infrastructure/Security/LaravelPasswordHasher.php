<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Security;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Domain\Auth\Ports\PasswordHasherInterface;
use Modules\Core\Domain\Auth\ValueObjects\HashedPassword;

/**
 * Hashing del PANEL (bcrypt via Laravel). No confundir con el hashing de
 * la cuenta de juego (SRP6 u otro), que vive en GameCore/PasswordHashStrategy.
 */
final class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): HashedPassword
    {
        return new HashedPassword(Hash::make($plainPassword));
    }

    public function verify(string $plainPassword, HashedPassword $hashed): bool
    {
        return Hash::check($plainPassword, $hashed->value());
    }
}
