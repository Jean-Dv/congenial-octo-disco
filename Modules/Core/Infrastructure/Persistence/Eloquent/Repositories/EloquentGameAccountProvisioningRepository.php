<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Modules\Core\Domain\GameAccount\GameAccountProvisioning;
use Modules\Core\Domain\GameAccount\Ports\GameAccountProvisioningRepositoryInterface;
use Modules\Core\Domain\GameAccount\ValueObjects\ProvisioningStatus;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\GameAccountProvisioningModel;

final class EloquentGameAccountProvisioningRepository implements GameAccountProvisioningRepositoryInterface
{
    public function findById(int $id): ?GameAccountProvisioning
    {
        $model = GameAccountProvisioningModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserAndRealm(int $userId, int $realmId): ?GameAccountProvisioning
    {
        $model = GameAccountProvisioningModel::where('user_id', $userId)
            ->where('realm_id', $realmId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUser(int $userId): array
    {
        return GameAccountProvisioningModel::where('user_id', $userId)->get()
            ->map(fn (GameAccountProvisioningModel $model) => $this->toDomain($model))
            ->all();
    }

    public function save(GameAccountProvisioning $provisioning): GameAccountProvisioning
    {
        $model = $provisioning->id() !== null
            ? GameAccountProvisioningModel::findOrFail($provisioning->id())
            : new GameAccountProvisioningModel;

        $model->user_id = $provisioning->userId();
        $model->realm_id = $provisioning->realmId();
        $model->game_username = $provisioning->gameUsername();
        $model->status = $provisioning->status()->value;
        $model->attempts = $provisioning->attempts();
        $model->last_error = $provisioning->lastError();

        $model->save();

        $provisioning->assignId($model->id);

        return $provisioning;
    }

    private function toDomain(GameAccountProvisioningModel $model): GameAccountProvisioning
    {
        return GameAccountProvisioning::reconstitute(
            id: $model->id,
            userId: $model->user_id,
            realmId: $model->realm_id,
            gameUsername: $model->game_username,
            status: ProvisioningStatus::from($model->status),
            attempts: $model->attempts,
            lastError: $model->last_error,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: $model->updated_at
                ? DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
