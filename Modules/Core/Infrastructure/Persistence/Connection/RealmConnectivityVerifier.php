<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Connection;

use Modules\Core\Domain\Realm\Exceptions\RealmConnectivityException;
use Modules\Core\Domain\Realm\Ports\RealmConnectivityVerifierInterface;
use Modules\Core\Domain\Realm\Realm;
use Throwable;

final class RealmConnectivityVerifier implements RealmConnectivityVerifierInterface
{
    public function __construct(
        private readonly RealmConnectionFactory $connections,
    ) {}

    public function verify(Realm $realm): void
    {
        try {
            $this->connections->authConnectionFor($realm)->selectOne('SELECT 1');
        } catch (RealmConnectivityException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RealmConnectivityException(
                'auth_database',
                'No fue posible conectar con la base de datos auth: '.$this->safeReason($exception),
                $exception,
            );
        }

        if ($realm->charactersDatabase() !== null) {
            try {
                $this->connections->charactersConnectionFor($realm)->selectOne('SELECT 1');
            } catch (RealmConnectivityException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                throw new RealmConnectivityException(
                    'characters_database',
                    'No fue posible conectar con la base de datos characters: '.$this->safeReason($exception),
                    $exception,
                );
            }
        }
    }

    private function safeReason(Throwable $exception): string
    {
        $message = preg_replace('/password\\s*=\\s*[^\\s;]+/i', 'password=[oculta]', $exception->getMessage());
        $message = preg_replace('/[\r\n]+/', ' ', $message ?? '') ?? '';

        return mb_substr($message, 0, 500);
    }
}
