<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\Ports;

use Modules\Core\Domain\Realm\ValueObjects\CoreType;

/**
 * Factoria que resuelve, para un CoreType dado, la implementacion
 * concreta de PasswordHashStrategyInterface a usar. Implementada en
 * Infrastructure/GameCore/PasswordHashStrategy/PasswordHashStrategyResolver.
 */
interface PasswordHashStrategyResolverInterface
{
    public function resolve(CoreType $coreType): PasswordHashStrategyInterface;
}
