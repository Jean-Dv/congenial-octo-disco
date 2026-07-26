<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\ValueObjects;

final class SshTunnelConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $privateKey,
        public readonly ?string $privateKeyPassphrase = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'private_key' => $this->privateKey,
            'private_key_passphrase' => $this->privateKeyPassphrase,
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
            privateKey: $data['private_key'],
            privateKeyPassphrase: self::nullableSecret($data['private_key_passphrase'] ?? null),
        );
    }

    private static function nullableSecret(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
