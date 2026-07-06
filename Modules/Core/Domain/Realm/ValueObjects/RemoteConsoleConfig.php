<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\ValueObjects;

/**
 * Credenciales SOAP (o del protocolo que corresponda a futuro) del
 * worldserver de este reino especifico. Cada reino tiene las suyas,
 * porque cada worldserver corre su propio servicio SOAP.
 */
final class RemoteConsoleConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $password,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            host: $data['host'],
            port: (int) $data['port'],
            username: $data['username'],
            password: $data['password'],
        );
    }
}
