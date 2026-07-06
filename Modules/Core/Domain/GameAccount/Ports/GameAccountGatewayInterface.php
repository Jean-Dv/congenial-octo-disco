<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\Ports;

use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\Realm\Realm;

/**
 * Operaciones directas contra la base de datos "auth" de un reino
 * (creacion de cuenta, cambio de password, nivel de GM, borrado). Todo
 * lo que NO sea "server info" / comandos en caliente al worldserver pasa
 * por aqui, via conexion SQL directa (RealmConnectionFactory), no por SOAP.
 */
interface GameAccountGatewayInterface
{
    public function accountExists(Realm $realm, string $username): bool;

    /**
     * @return int El id de la cuenta creada en la tabla `account` del reino.
     */
    public function createAccount(Realm $realm, string $username, string $email, CoreCredentialPayload $credentials): int;

    public function updatePassword(Realm $realm, string $username, CoreCredentialPayload $credentials): void;

    public function setGmLevel(Realm $realm, string $username, int $gmLevel): void;

    public function deleteAccount(Realm $realm, string $username): void;
}
