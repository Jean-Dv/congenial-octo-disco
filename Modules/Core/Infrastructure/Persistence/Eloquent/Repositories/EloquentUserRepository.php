<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Modules\Core\Domain\Auth\Ports\UserRepositoryInterface;
use Modules\Core\Domain\Auth\User;
use Modules\Core\Domain\Auth\ValueObjects\Email;
use Modules\Core\Domain\Auth\ValueObjects\HashedPassword;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $model = UserModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    public function existsByName(string $name): bool
    {
        return UserModel::where('name', $name)->exists();
    }

    public function save(User $user): User
    {
        $model = $user->id() !== null
            ? UserModel::findOrFail($user->id())
            : new UserModel;

        $model->name = $user->name();
        $model->email = $user->email()->value();
        $model->password = $user->password()->value();
        $model->locale = $user->locale();
        $model->email_verified_at = $user->emailVerifiedAt();

        $model->save();

        $user->assignId($model->id);

        return $user;
    }

    private function toDomain(UserModel $model): User
    {
        return User::reconstitute(
            id: $model->id,
            name: $model->name,
            email: new Email($model->email),
            password: new HashedPassword($model->password),
            locale: $model->locale ?? 'es',
            emailVerifiedAt: $model->email_verified_at
                ? DateTimeImmutable::createFromInterface($model->email_verified_at)
                : null,
            createdAt: $model->created_at
                ? DateTimeImmutable::createFromInterface($model->created_at)
                : null,
        );
    }
}
