<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\Ports;

use Modules\Core\Domain\Realm\Realm;

interface RealmLatencyProbeInterface
{
    /**
     * Tiempo en milisegundos para establecer una conexión TCP con el endpoint
     * público publicado en realmlist. Null indica que no fue alcanzable.
     */
    public function measure(Realm $realm): ?int;
}
