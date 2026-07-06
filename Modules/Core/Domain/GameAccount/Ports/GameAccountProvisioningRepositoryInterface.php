<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\Ports;

use Modules\Core\Domain\GameAccount\GameAccountProvisioning;

interface GameAccountProvisioningRepositoryInterface
{
    public function findById(int $id): ?GameAccountProvisioning;

    public function findByUserAndRealm(int $userId, int $realmId): ?GameAccountProvisioning;

    /**
     * @return array<int, GameAccountProvisioning>
     */
    public function findByUser(int $userId): array;

    public function save(GameAccountProvisioning $provisioning): GameAccountProvisioning;
}
