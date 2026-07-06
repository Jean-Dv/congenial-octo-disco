<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\GameCore\GameAccountGateway;

use Modules\Core\Domain\GameAccount\Exceptions\PasswordHashStrategyNotImplementedException;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;

/**
 * Placeholder para cores cuyo esquema de tabla `account` no se verifico
 * en esta entrega (ver UnsupportedPasswordHashStrategy para el porque).
 */
final class UnsupportedGameAccountGateway implements GameAccountGatewayInterface
{
    public function __construct(
        private readonly CoreType $coreType,
    ) {
    }

    public function accountExists(Realm $realm, string $username): bool
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }

    public function createAccount(Realm $realm, string $username, string $email, CoreCredentialPayload $credentials): int
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }

    public function updatePassword(Realm $realm, string $username, CoreCredentialPayload $credentials): void
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }

    public function setGmLevel(Realm $realm, string $username, int $gmLevel): void
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }

    public function deleteAccount(Realm $realm, string $username): void
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }
}
