<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\GameCore\GameAccountGateway;

use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayResolverInterface;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Infrastructure\Persistence\Connection\RealmConnectionFactory;

final class GameAccountGatewayResolver implements GameAccountGatewayResolverInterface
{
    public function __construct(
        private readonly RealmConnectionFactory $connections,
    ) {
    }

    public function resolve(CoreType $coreType): GameAccountGatewayInterface
    {
        return match ($coreType) {
            CoreType::TRINITYCORE, CoreType::AZEROTHCORE => new TrinityLikeGameAccountGateway($this->connections),

            CoreType::CMANGOS,
            CoreType::MANGOS_ZERO,
            CoreType::MANGOS_ONE,
            CoreType::MANGOS_TWO,
            CoreType::VMANGOS,
            CoreType::SKYFIRE => new UnsupportedGameAccountGateway($coreType),
        };
    }
}
