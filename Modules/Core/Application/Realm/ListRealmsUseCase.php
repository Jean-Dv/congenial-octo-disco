<?php

declare(strict_types=1);

namespace Modules\Core\Application\Realm;

use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;

final class ListRealmsUseCase
{
    public function __construct(
        private readonly RealmRepositoryInterface $realms,
    ) {
    }

    /**
     * @return array<int, \Modules\Core\Domain\Realm\Realm>
     */
    public function handle(): array
    {
        return $this->realms->all();
    }
}
