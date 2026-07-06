<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\GameCore\PasswordHashStrategy;

use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyInterface;
use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyResolverInterface;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;

final class PasswordHashStrategyResolver implements PasswordHashStrategyResolverInterface
{
    public function resolve(CoreType $coreType): PasswordHashStrategyInterface
    {
        return match ($coreType) {
            // TrinityCore y AzerothCore comparten exactamente el mismo
            // SRP6 (mismos N/g, mismo k=3): una sola clase les sirve a
            // ambos, no hace falta duplicar codigo.
            CoreType::TRINITYCORE, CoreType::AZEROTHCORE => new Srp6PasswordHashStrategy(),

            CoreType::CMANGOS,
            CoreType::MANGOS_ZERO,
            CoreType::MANGOS_ONE,
            CoreType::MANGOS_TWO,
            CoreType::VMANGOS,
            CoreType::SKYFIRE => new UnsupportedPasswordHashStrategy($coreType),
        };
    }
}
