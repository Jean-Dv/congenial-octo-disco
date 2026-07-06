<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Queue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Application\GameAccount\ProvisionGameAccountUseCase;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Throwable;

/**
 * Crea (o actualiza) la cuenta de juego en UN reino. Se despacha una vez
 * por cada reino habilitado al registrarse.
 *
 * Seguridad: implementa ShouldBeEncrypted, asi que el payload completo
 * (incluidas salt/verifier ya calculados) viaja cifrado con APP_KEY
 * mientras espera en la cola de Redis. La contraseña en texto plano
 * NUNCA llega aqui: se usa de forma sincrona en RegisterUserUseCase
 * solo para calcular las credenciales, y se descarta.
 *
 * Reintentos: 5 intentos con backoff exponencial (5s, 15s, 30s, 60s,
 * 120s). Si se agotan, failed() marca el aprovisionamiento como
 * definitivamente fallido para que se vea en el dashboard.
 */
final class ProvisionGameAccountJob implements ShouldQueue, ShouldBeEncrypted
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
        public readonly string $gameEmail,
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

    public function handle(ProvisionGameAccountUseCase $useCase): void
    {
        $useCase->handle($this->provisioningId, $this->gameUsername, $this->gameEmail, $this->credentialColumns);
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
