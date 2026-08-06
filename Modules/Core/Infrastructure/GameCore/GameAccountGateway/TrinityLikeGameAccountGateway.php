<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\GameCore\GameAccountGateway;

use Illuminate\Support\Facades\Date;
use Modules\Core\Domain\GameAccount\Exceptions\GameAccountGatewayException;
use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\GameAccount\ValueObjects\GameAccountIdentity;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Infrastructure\Persistence\Connection\RealmConnectionFactory;

/**
 * Implementacion REAL para TrinityCore y AzerothCore (comparten el mismo
 * layout de `account` / `account_access`). Toda operacion pasa por
 * conexion SQL directa via RealmConnectionFactory, nunca por SOAP: eso
 * fue una decision explicita del negocio (SOAP se reserva para
 * "server info" y comandos en caliente sobre personajes conectados).
 */
final class TrinityLikeGameAccountGateway implements GameAccountGatewayInterface
{
    public function __construct(
        private readonly RealmConnectionFactory $connections,
    ) {}

    public function accountExists(Realm $realm, string $username): bool
    {
        return $this->connections->authConnectionFor($realm)
            ->table('account')
            ->where('username', $this->normalize($username))
            ->exists();
    }

    public function findAccountByEmail(Realm $realm, string $email): ?GameAccountIdentity
    {
        $normalizedEmail = mb_strtolower(trim($email));

        $account = $this->connections->authConnectionFor($realm)
            ->table('account')
            ->select(['username', 'email'])
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->orderBy('id')
            ->first();

        if ($account === null) {
            return null;
        }

        return new GameAccountIdentity(
            username: (string) $account->username,
            email: (string) $account->email,
        );
    }

    public function createAccount(Realm $realm, string $username, string $email, CoreCredentialPayload $credentials): int
    {
        $connection = $this->connections->authConnectionFor($realm);
        $normalized = $this->normalize($username);

        if ($this->accountExists($realm, $normalized)) {
            throw new GameAccountGatewayException(
                "Ya existe una cuenta de juego \"{$normalized}\" en el reino \"{$realm->name()}\"."
            );
        }

        return $connection->table('account')->insertGetId(array_merge([
            'username' => $normalized,
            'email' => $email,
            'reg_mail' => $email,
            'joindate' => Date::now(),
            'expansion' => 3,
        ], $credentials->columns()));
    }

    public function updatePassword(Realm $realm, string $username, CoreCredentialPayload $credentials): void
    {
        $connection = $this->connections->authConnectionFor($realm);
        $normalized = $this->normalize($username);

        $updated = $connection->table('account')
            ->where('username', $normalized)
            ->update($credentials->columns());

        if ($updated === 0) {
            throw new GameAccountGatewayException(
                "No se encontro la cuenta de juego \"{$normalized}\" en el reino \"{$realm->name()}\" para actualizar el password."
            );
        }
    }

    public function setGmLevel(Realm $realm, string $username, int $gmLevel): void
    {
        $connection = $this->connections->authConnectionFor($realm);
        $normalized = $this->normalize($username);

        $accountId = $connection->table('account')->where('username', $normalized)->value('id');

        if ($accountId === null) {
            throw new GameAccountGatewayException(
                "No se encontro la cuenta de juego \"{$normalized}\" en el reino \"{$realm->name()}\"."
            );
        }

        $connection->table('account_access')->upsert(
            [[
                'id' => $accountId,
                'gmlevel' => $gmLevel,
                'RealmID' => $realm->gmRealmId(),
            ]],
            ['id', 'RealmID'],
            ['gmlevel'],
        );
    }

    public function deleteAccount(Realm $realm, string $username): void
    {
        $connection = $this->connections->authConnectionFor($realm);
        $normalized = $this->normalize($username);

        $accountId = $connection->table('account')->where('username', $normalized)->value('id');

        if ($accountId === null) {
            return;
        }

        $connection->table('account_access')->where('id', $accountId)->delete();
        $connection->table('account_banned')->where('id', $accountId)->delete();
        $connection->table('account')->where('id', $accountId)->delete();
    }

    /**
     * TrinityCore/AzerothCore normalizan el username a mayusculas antes
     * de guardarlo (Utf8ToUpperOnlyLatin en AccountMgr::CreateAccount):
     * replicamos esa misma normalizacion para que las busquedas siempre calcen.
     */
    private function normalize(string $username): string
    {
        return strtoupper($username);
    }
}
