<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\Ports;

use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;

/**
 * Strategy Pattern: como cada core calcula las credenciales de forma
 * distinta (TrinityCore/AzerothCore usan SRP6 con salt+verifier; otros
 * forks usan SHA1 sobre usuario+password), cada CoreType resuelve a una
 * implementacion concreta de esta interfaz via
 * Infrastructure/GameCore/PasswordHashStrategy/PasswordHashStrategyResolver.
 */
interface PasswordHashStrategyInterface
{
    public function generateCredentials(string $username, string $plainPassword): CoreCredentialPayload;

    /**
     * @param  array<string, string>  $storedColumns  Tal cual vienen de la fila `account`.
     */
    public function verify(string $username, string $plainPassword, array $storedColumns): bool;
}
