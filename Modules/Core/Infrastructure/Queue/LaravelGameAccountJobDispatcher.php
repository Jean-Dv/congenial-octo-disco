<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Queue;

use Modules\Core\Application\GameAccount\Ports\GameAccountJobDispatcherInterface;
use Modules\Core\Infrastructure\Queue\Jobs\ProvisionGameAccountJob;
use Modules\Core\Infrastructure\Queue\Jobs\SyncPasswordToRealmJob;

final class LaravelGameAccountJobDispatcher implements GameAccountJobDispatcherInterface
{
    public function dispatchProvision(int $provisioningId, string $gameUsername, string $gameEmail, array $credentialColumns): void
    {
        ProvisionGameAccountJob::dispatch($provisioningId, $gameUsername, $gameEmail, $credentialColumns);
    }

    public function dispatchPasswordSync(int $provisioningId, string $gameUsername, array $credentialColumns): void
    {
        SyncPasswordToRealmJob::dispatch($provisioningId, $gameUsername, $credentialColumns);
    }
}
