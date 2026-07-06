<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use RuntimeException;

final class SetRealmEnabledUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
    ) {
    }

    public function handle(int $realmId, bool $enabled): Realm
    {
        $realm = $this->realms->findById($realmId);

        if ($realm === null) {
            throw new RuntimeException("No existe el reino #{$realmId}.");
        }

        $enabled ? $realm->enable() : $realm->disable();

        return $this->realms->save($realm);
    }
}
