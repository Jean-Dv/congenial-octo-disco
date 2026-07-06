<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\GameCore\PasswordHashStrategy;

use GMP;
use InvalidArgumentException;
use Modules\Core\Domain\GameAccount\Ports\PasswordHashStrategyInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;

/**
 * Implementacion REAL del SRP6 ("Grunt SRP6") usado por el cliente
 * WotLK 3.3.5a, compartida por TrinityCore Y AzerothCore.
 *
 * Verificado directamente contra el codigo fuente de ambos proyectos
 * (src/common/Cryptography/Authentication/SRP6.cpp — GruntSRP6::N/::g,
 * y src/server/game/Accounts/AccountMgr.cpp — using AccountSRP6 =
 * Trinity::Crypto::SRP::GruntSRP6, con k=3 y Utf8ToUpperOnlyLatin sobre
 * usuario y password antes de calcular x). Los mismos N/g fueron
 * confirmados de forma independiente contra el paquete npm
 * "trinitycore-srp6".
 *
 * Algoritmo:
 *   h1 = SHA1(UPPER(username) + ":" + UPPER(password))
 *   x  = SHA1(salt || h1), interpretado como entero LITTLE-ENDIAN
 *   v  = g^x mod N
 *   salt y verifier se guardan como 32 bytes little-endian (rellenados
 *   con ceros a la derecha si el numero resultante ocupa menos bytes).
 *
 * IMPORTANTE: el cliente 3.3.5a limita username y password a 16
 * caracteres ASCII imprimibles (limite historico del propio cliente, no
 * solo del servidor) — validado en RegisterUserRequest, y reforzado aqui
 * de forma defensiva.
 */
final class Srp6PasswordHashStrategy implements PasswordHashStrategyInterface
{
    private const N_HEX = '894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7';

    private const G = 7;

    private const FIELD_LENGTH_BYTES = 32;

    private const MAX_CREDENTIAL_LENGTH = 16;

    public function generateCredentials(string $username, string $plainPassword): CoreCredentialPayload
    {
        $this->guardLength($username, 'username');
        $this->guardLength($plainPassword, 'password');

        $salt = random_bytes(self::FIELD_LENGTH_BYTES);
        $verifier = $this->calculateVerifierBytes($username, $plainPassword, $salt);

        return new CoreCredentialPayload([
            'salt' => $salt,
            'verifier' => $verifier,
        ]);
    }

    public function verify(string $username, string $plainPassword, array $storedColumns): bool
    {
        if (! isset($storedColumns['salt'], $storedColumns['verifier'])) {
            return false;
        }

        $expected = $this->calculateVerifierBytes($username, $plainPassword, $storedColumns['salt']);

        return hash_equals($storedColumns['verifier'], $expected);
    }

    private function calculateVerifierBytes(string $username, string $plainPassword, string $salt): string
    {
        $identity = strtoupper($username).':'.strtoupper($plainPassword);
        $h1 = sha1($identity, true);
        $xBytes = sha1($salt.$h1, true);

        $x = gmp_import($xBytes, 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
        $n = gmp_init(self::N_HEX, 16);
        $g = gmp_init(self::G, 10);

        /** @var GMP $v */
        $v = gmp_powm($g, $x, $n);

        $verifierBytes = gmp_export($v, 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);

        return str_pad($verifierBytes, self::FIELD_LENGTH_BYTES, "\0", STR_PAD_RIGHT);
    }

    private function guardLength(string $value, string $field): void
    {
        if (strlen($value) > self::MAX_CREDENTIAL_LENGTH) {
            throw new InvalidArgumentException(
                "El {$field} de la cuenta de juego no puede superar ".self::MAX_CREDENTIAL_LENGTH.
                ' caracteres: es el limite del propio cliente 3.3.5a.'
            );
        }

        if (! mb_check_encoding($value, 'ASCII')) {
            throw new InvalidArgumentException(
                "El {$field} de la cuenta de juego debe usar solo caracteres ASCII imprimibles."
            );
        }
    }
}
