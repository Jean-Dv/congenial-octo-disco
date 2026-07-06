<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\Ports;

use Modules\Core\Domain\Realm\ValueObjects\CoreType;

/**
 * Factoria que resuelve, para un CoreType dado, la implementacion
 * concreta de GameAccountGatewayInterface a usar (el esquema exacto de
 * la tabla `account` puede variar de un core a otro). Implementada en
 * Infrastructure/GameCore/GameAccountGateway/GameAccountGatewayResolver.
 */
interface GameAccountGatewayResolverInterface
{
    public function resolve(CoreType $coreType): GameAccountGatewayInterface;
}
