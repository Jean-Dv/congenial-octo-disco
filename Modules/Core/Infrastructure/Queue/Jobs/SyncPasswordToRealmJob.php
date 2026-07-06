<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Queue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Application\GameAccount\SyncPasswordToRealmUseCase;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Throwable;

final class SyncPasswordToRealmJob implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    /**
     * @param  array<string, string>  $credentialColumns
     */
    public function __construct(
        public readonly int $provisioningId,
        public readonly string $gameUsername,
        public readonly array $credentialColumns,
    ) {
        $this->onQueue('provisioning');
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30, 60, 120];
    }

    public function handle(SyncPasswordToRealmUseCase $useCase): void
    {
        $useCase->handle($this->provisioningId, $this->gameUsername, $this->credentialColumns);
    }

    public function failed(Throwable $exception): void
    {
        $repository = app(GameAccountProvisioningRepositoryInterface::class);
        $provisioning = $repository->findById($this->provisioningId);

        if ($provisioning !== null) {
            $provisioning->markFailed($exception->getMessage());
            $repository->save($provisioning);
        }
    }
}
