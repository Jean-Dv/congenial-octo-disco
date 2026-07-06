<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm;

use DateTimeImmutable;
use LogicException;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use Modules\Core\Domain\Realm\ValueObjects\RemoteConsoleConfig;

/**
 * Un reino configurado en el CMS. El CMS soporta multiples reinos y
 * multiples cores simultaneamente: cada Realm trae su propio CoreType,
 * su propia conexion a base de datos y sus propias credenciales SOAP.
 */
final class Realm
{
    private ?int $id;

    public function __construct(
        ?int $id,
        private string $name,
        private string $slug,
        private CoreType $coreType,
        private DatabaseConnectionConfig $authDatabase,
        private ?DatabaseConnectionConfig $charactersDatabase,
        private RemoteConsoleConfig $remoteConsole,
        private bool $enabled,
        /** RealmID usado en account_access. -1 = acceso GM en todos los reinos del cluster. */
        private int $gmRealmId,
        private readonly ?DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
    }

    public static function create(
        string $name,
        string $slug,
        CoreType $coreType,
        DatabaseConnectionConfig $authDatabase,
        ?DatabaseConnectionConfig $charactersDatabase,
        RemoteConsoleConfig $remoteConsole,
        int $gmRealmId = -1,
        bool $enabled = true,
    ): self {
        return new self(
            id: null,
            name: $name,
            slug: $slug,
            coreType: $coreType,
            authDatabase: $authDatabase,
            charactersDatabase: $charactersDatabase,
            remoteConsole: $remoteConsole,
            enabled: $enabled,
            gmRealmId: $gmRealmId,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        string $name,
        string $slug,
        CoreType $coreType,
        DatabaseConnectionConfig $authDatabase,
        ?DatabaseConnectionConfig $charactersDatabase,
        RemoteConsoleConfig $remoteConsole,
        bool $enabled,
        int $gmRealmId,
        ?DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $name, $slug, $coreType, $authDatabase, $charactersDatabase, $remoteConsole, $enabled, $gmRealmId, $createdAt);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null && $this->id !== $id) {
            throw new LogicException('Este reino ya tiene una identidad asignada.');
        }

        $this->id = $id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function coreType(): CoreType
    {
        return $this->coreType;
    }

    public function authDatabase(): DatabaseConnectionConfig
    {
        return $this->authDatabase;
    }

    public function charactersDatabase(): ?DatabaseConnectionConfig
    {
        return $this->charactersDatabase;
    }

    public function remoteConsole(): RemoteConsoleConfig
    {
        return $this->remoteConsole;
    }

    public function gmRealmId(): int
    {
        return $this->gmRealmId;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function updateAuthDatabase(DatabaseConnectionConfig $config): void
    {
        $this->authDatabase = $config;
    }

    public function updateCharactersDatabase(?DatabaseConnectionConfig $config): void
    {
        $this->charactersDatabase = $config;
    }

    public function updateRemoteConsole(RemoteConsoleConfig $config): void
    {
        $this->remoteConsole = $config;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
