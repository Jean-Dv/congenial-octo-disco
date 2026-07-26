<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\Ports;

use Modules\Core\Domain\Realm\Realm;

interface RealmConnectivityVerifierInterface
{
    public function verify(Realm $realm): void;
}
