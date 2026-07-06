<?php

declare(strict_types=1);

namespace Modules\Core\Application\GameAccount\Ports;

/**
 * Encola el trabajo real de escribir en la BD del reino. Notese que
 * SOLO viajan credenciales YA CALCULADAS (salt/verifier u otro esquema),
 * nunca la contraseña en texto plano: esta se usa de forma sincrona y
 * local en el propio request (ver RegisterUserUseCase/ResetPasswordUseCase)
 * y nunca se serializa a la cola. Ademas, los Jobs concretos implementan
 * ShouldBeEncrypted, asi que aunque solo viajen credenciales derivadas,
 * el payload tambien va cifrado en Redis con APP_KEY.
 */
interface GameAccountJobDispatcherInterface
{
    /**
     * @param  array<string, string>  $credentialColumns
     */
    public function dispatchProvision(
        int $provisioningId,
        string $gameUsername,
        string $gameEmail,
        array $credentialColumns,
    ): void;

    /**
     * @param  array<string, string>  $credentialColumns
     */
    public function dispatchPasswordSync(
        int $provisioningId,
        string $gameUsername,
        array $credentialColumns,
    ): void;
}
