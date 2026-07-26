<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Connection;

use Modules\Core\Domain\Realm\Ports\RealmConnectivityVerifierInterface;
use Modules\Core\Domain\Realm\Realm;

final class NullRealmConnectivityVerifier implements RealmConnectivityVerifierInterface
{
    public function verify(Realm $realm): void {}
}
