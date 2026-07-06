<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\ValueObjects;

/**
 * Credenciales de una base de datos MySQL del core (auth o characters).
 * Se resuelve en caliente por RealmConnectionFactory: nunca se declara
 * en config/database.php.
 */
final class DatabaseConnectionConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
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
            'database' => $this->database,
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
            database: $data['database'],
            username: $data['username'],
            password: $data['password'],
        );
    }
}
