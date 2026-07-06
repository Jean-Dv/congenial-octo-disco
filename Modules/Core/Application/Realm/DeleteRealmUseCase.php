<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use RuntimeException;

final class DeleteRealmUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
    ) {
    }

    public function handle(int $realmId): void
    {
        $realm = $this->realms->findById($realmId);

        if ($realm === null) {
            throw new RuntimeException("No existe el reino #{$realmId}.");
        }

        $this->realms->delete($realm);
    }
}
