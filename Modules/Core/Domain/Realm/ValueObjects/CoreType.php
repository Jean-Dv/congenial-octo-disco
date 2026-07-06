<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\ValueObjects;

/**
 * Cores soportados en esta version. TRINITYCORE y AZEROTHCORE tienen
 * implementacion REAL y verificada (SRP6 con los mismos N/g, confirmados
 * contra el codigo fuente de ambos proyectos). El resto queda con el
 * contrato ya definido (PasswordHashStrategyInterface, GameAccountGatewayInterface)
 * pero sin implementacion concreta todavia: ver
 * Infrastructure/GameCore/PasswordHashStrategy/UnsupportedPasswordHashStrategy.
 */
enum CoreType: string
{
    case TRINITYCORE = 'trinitycore';
    case AZEROTHCORE = 'azerothcore';
    case CMANGOS = 'cmangos';
    case MANGOS_ZERO = 'mangos_zero';
    case MANGOS_ONE = 'mangos_one';
    case MANGOS_TWO = 'mangos_two';
    case VMANGOS = 'vmangos';
    case SKYFIRE = 'skyfire';

    public function label(): string
    {
        return match ($this) {
            self::TRINITYCORE => 'TrinityCore',
            self::AZEROTHCORE => 'AzerothCore',
            self::CMANGOS => 'CMaNGOS',
            self::MANGOS_ZERO => 'MaNGOS Zero',
            self::MANGOS_ONE => 'MaNGOS One',
            self::MANGOS_TWO => 'MaNGOS Two',
            self::VMANGOS => 'VMaNGOS',
            self::SKYFIRE => 'SkyFireEMU',
        };
    }

    public function hasFullSupport(): bool
    {
        return match ($this) {
            self::TRINITYCORE, self::AZEROTHCORE => true,
            default => false,
        };
    }

    /**
     * Namespace URI que exige el SoapClient de PHP cuando no se usa WSDL.
     * Confirmado contra la documentacion oficial de TrinityCore y AzerothCore.
     */
    public function soapNamespaceUri(): string
    {
        return match ($this) {
            self::TRINITYCORE => 'urn:TC',
            self::AZEROTHCORE => 'urn:AC',
            self::CMANGOS, self::MANGOS_ZERO, self::MANGOS_ONE, self::MANGOS_TWO, self::VMANGOS => 'urn:MaNGOS',
            self::SKYFIRE => 'urn:SF',
        };
    }
}
