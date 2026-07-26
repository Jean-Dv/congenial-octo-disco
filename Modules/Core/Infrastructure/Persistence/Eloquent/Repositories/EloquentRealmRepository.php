<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use Modules\Core\Domain\Realm\ValueObjects\RemoteConsoleConfig;
use Modules\Core\Domain\Realm\ValueObjects\SshTunnelConfig;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\RealmModel;

final class EloquentRealmRepository implements RealmRepositoryInterface
{
    public function findById(int $id): ?Realm
    {
        $model = RealmModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(string $slug): ?Realm
    {
        $model = RealmModel::where('slug', $slug)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function all(): array
    {
        return RealmModel::orderBy('name')->get()
            ->map(fn (RealmModel $model) => $this->toDomain($model))
            ->all();
    }

    public function allEnabled(): array
    {
        return RealmModel::where('enabled', true)->orderBy('name')->get()
            ->map(fn (RealmModel $model) => $this->toDomain($model))
            ->all();
    }

    public function save(Realm $realm): Realm
    {
        $model = $realm->id() !== null
            ? RealmModel::findOrFail($realm->id())
            : new RealmModel;

        $model->name = $realm->name();
        $model->slug = $realm->slug();
        $model->core_type = $realm->coreType()->value;
        $model->auth_database = $realm->authDatabase()->toArray();
        $model->characters_database = $realm->charactersDatabase()?->toArray();
        $model->remote_console = $realm->remoteConsole()->toArray();
        $model->ssh_tunnel = $realm->sshTunnel()?->toArray();
        $model->gm_realm_id = $realm->gmRealmId();
        $model->enabled = $realm->isEnabled();

        $model->save();

        $realm->assignId($model->id);

        return $realm;
    }

    public function delete(Realm $realm): void
    {
        if ($realm->id() === null) {
            return;
        }

        RealmModel::destroy($realm->id());
    }

    private function toDomain(RealmModel $model): Realm
    {
        return Realm::reconstitute(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            coreType: CoreType::from($model->core_type),
            authDatabase: DatabaseConnectionConfig::fromArray($model->auth_database),
            charactersDatabase: $model->characters_database !== null
                ? DatabaseConnectionConfig::fromArray($model->characters_database)
                : null,
            remoteConsole: RemoteConsoleConfig::fromArray($model->remote_console),
            sshTunnel: $model->ssh_tunnel !== null
                ? SshTunnelConfig::fromArray($model->ssh_tunnel)
                : null,
            enabled: (bool) $model->enabled,
            gmRealmId: (int) $model->gm_realm_id,
            createdAt: $model->created_at
                ? DateTimeImmutable::createFromInterface($model->created_at)
                : null,
        );
    }
}
