<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\ValueObjects;

/**
 * Columnas listas para escribir en la tabla `account` del core, ya
 * calculadas por una PasswordHashStrategyInterface concreta. Mantener
 * esto como un simple mapa columna => valor (en vez de forzar campos
 * fijos tipo salt/verifier) es lo que permite que el mismo
 * GameAccountGatewayInterface sirva tanto para cores SRP6 (salt/verifier)
 * como para un futuro core con sha_pass_hash u otro esquema.
 */
final class CoreCredentialPayload
{
    /**
     * @param  array<string, string>  $columns
     */
    public function __construct(
        private readonly array $columns,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function columns(): array
    {
        return $this->columns;
    }
}
