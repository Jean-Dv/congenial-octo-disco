<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\Ports;

use Modules\Core\Domain\Auth\User;
use Modules\Core\Domain\Auth\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;

    public function existsByName(string $name): bool;

    /**
     * Persiste el usuario (crea o actualiza) y devuelve la instancia con
     * su identidad ya asignada.
     */
    public function save(User $user): User;
}
