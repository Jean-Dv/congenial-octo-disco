<?php

declare(strict_types=1);

namespace Modules\Core\Application\GameAccount;

use Modules\Core\Domain\GameAccount\Ports\GameAccountGatewayResolverInterface;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\CoreCredentialPayload;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Throwable;

final class SyncPasswordToRealmUseCase
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
    public function handle(int $provisioningId, string $gameUsername, array $credentialColumns): void
    {
        $provisioning = $this->provisionings->findById($provisioningId);

        if ($provisioning === null) {
            return;
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
                // Si por alguna razon la cuenta no llego a crearse antes,
                // el reseteo de password la crea (mejor esto que dejar al
                // usuario sin forma de jugar en ese reino).
                $gateway->createAccount($realm, $gameUsername, '', $payload);
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
