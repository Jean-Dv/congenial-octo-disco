<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\GameCore\PasswordHashStrategy;

use Modules\Core\Domain\GameAccount\Exceptions\PasswordHashStrategyNotImplementedException;
use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;

/**
 * Placeholder deliberado. El esquema de hash de CMaNGOS/MaNGOS/VMaNGOS/
 * SkyFireEMU varia segun el fork y la revision exacta (algunos siguen en
 * SHA1 `sha_pass_hash`, otros ya migraron a salt/verifier tipo SRP6) y
 * no se pudo verificar contra una fuente unica y autoritativa para
 * TODOS ellos en esta entrega. En vez de adivinar y arriesgar cuentas
 * de juego rotas, esta clase existe para dejar el contrato ya
 * conectado (el reino puede crearse y configurarse igual) y avisar
 * claramente que falta implementar el calculo real.
 *
 * Para completarla:
 *   1. Confirma el esquema exacto de tu build (columna(s) de la tabla
 *      `account` que guardan la credencial: sha_pass_hash, o salt+verifier).
 *   2. Escribe una clase hermana a esta implementando
 *      PasswordHashStrategyInterface (puedes copiar la estructura de
 *      Srp6PasswordHashStrategy si tu fork ya usa SRP6).
 *   3. Registra el mapeo en PasswordHashStrategyResolver::resolve().
 */
final class UnsupportedPasswordHashStrategy implements PasswordHashStrategyInterface
{
    public function __construct(
        private readonly CoreType $coreType,
    ) {
    }

    public function generateCredentials(string $username, string $plainPassword): CoreCredentialPayload
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }

    public function verify(string $username, string $plainPassword, array $storedColumns): bool
    {
        throw PasswordHashStrategyNotImplementedException::forCore($this->coreType);
    }
}
