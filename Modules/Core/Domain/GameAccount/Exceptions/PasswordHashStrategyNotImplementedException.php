<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\Exceptions;

use DomainException;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;

/**
 * Se lanza cuando un reino usa un CoreType para el que todavia no existe
 * una PasswordHashStrategyInterface concreta (ver
 * Infrastructure/GameCore/PasswordHashStrategy/UnsupportedPasswordHashStrategy).
 * Ahi mismo se explica que hay que verificar/implementar antes de habilitarlo.
 */
final class PasswordHashStrategyNotImplementedException extends DomainException
{
    public static function forCore(CoreType $coreType): self
    {
        return new self(
            "No hay una estrategia de hash de password implementada todavia para \"{$coreType->label()}\". ".
            'Revisa Infrastructure/GameCore/PasswordHashStrategy/UnsupportedPasswordHashStrategy.php para completarla.'
        );
    }
}
