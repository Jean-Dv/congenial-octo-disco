<?php

declare(strict_types=1);

namespace Modules\Core\Application\GameAccount;

use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayResolverInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Throwable;

/**
 * Ejecuta UN intento de crear/actualizar la cuenta de juego en el reino.
 * La orquestacion de reintentos con backoff vive en el Job de Laravel
 * (Infrastructure/Queue/Jobs/ProvisionGameAccountJob): este caso de uso
 * simplemente hace el trabajo y relanza la excepcion si algo falla, para
 * que el motor de colas decida si reintentar.
 */
final class ProvisionGameAccountUseCase
{
    public function __construct(
        private readonly GameAccountProvisioningRepositoryInterface $provisionings,
        private readonly RealmRepositoryInterface $realms,
        private readonly GameAccountGatewayResolverInterface $gateways,
    ) {
    }

    /**
     * @param  array<string, string>  $credentialColumns
     */
    public function handle(int $provisioningId, string $gameUsername, string $gameEmail, array $credentialColumns): void
    {
        $provisioning = $this->provisionings->findById($provisioningId);

        if ($provisioning === null) {
            return; // Ya no existe (ej. se borro el usuario): nada que hacer.
        }

        $realm = $this->realms->findById($provisioning->realmId());

        if ($realm === null) {
            $provisioning->markFailed('El reino ya no existe.');
            $this->provisionings->save($provisioning);

            return;
        }

        $provisioning->markInProgress();
        $this->provisionings->save($provisioning);

        try {
            $gateway = $this->gateways->resolve($realm->coreType());
            $payload = new CoreCredentialPayload($credentialColumns);

            if ($gateway->accountExists($realm, $gameUsername)) {
                $gateway->updatePassword($realm, $gameUsername, $payload);
            } else {
                $gateway->createAccount($realm, $gameUsername, $gameEmail, $payload);
            }

            $provisioning->markReady();
            $this->provisionings->save($provisioning);
        } catch (Throwable $exception) {
            $provisioning->recordAttemptError($exception->getMessage());
            $this->provisionings->save($provisioning);

            throw $exception;
        }
    }
}
