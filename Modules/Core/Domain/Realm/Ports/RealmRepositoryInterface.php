<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\Ports;

use Modules\Core\Domain\Realm\Realm;

interface RealmRepositoryInterface
{
    public function findById(int $id): ?Realm;

    public function findBySlug(string $slug): ?Realm;

    /**
     * @return array<int, Realm>
     */
    public function all(): array;

    /**
     * @return array<int, Realm> Solo los reinos habilitados: en estos se
     *         aprovisiona una cuenta de juego automaticamente al registrarse.
     */
    public function allEnabled(): array;

    public function save(Realm $realm): Realm;

    public function delete(Realm $realm): void;
}
