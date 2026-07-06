<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth;

use DateTimeImmutable;
use LogicException;
use Modules\Core\Domain\Auth\ValueObjects\Email;
use Modules\Core\Domain\Auth\ValueObjects\HashedPassword;

/**
 * Identidad del panel (Postgres). Deliberadamente no sabe nada de
 * "account" del juego: esa relacion vive en GameAccountProvisioning,
 * uno por cada (usuario, reino). Ver README, seccion "Identidad".
 */
final class User
{
    private ?int $id;

    public function __construct(
        ?int $id,
        private string $name,
        private Email $email,
        private HashedPassword $password,
        private string $locale,
        private ?DateTimeImmutable $emailVerifiedAt,
        private readonly ?DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
    }

    public static function register(
        string $name,
        Email $email,
        HashedPassword $password,
        string $locale = 'es',
    ): self {
        return new self(
            id: null,
            name: $name,
            email: $email,
            password: $password,
            locale: $locale,
            emailVerifiedAt: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        string $name,
        Email $email,
        HashedPassword $password,
        string $locale,
        ?DateTimeImmutable $emailVerifiedAt,
        ?DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $name, $email, $password, $locale, $emailVerifiedAt, $createdAt);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * Solo debe llamarlo el repositorio, justo despues de insertar la fila.
     */
    public function assignId(int $id): void
    {
        if ($this->id !== null && $this->id !== $id) {
            throw new LogicException('Este usuario ya tiene una identidad asignada.');
        }

        $this->id = $id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): HashedPassword
    {
        return $this->password;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function emailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function markEmailAsVerified(): void
    {
        $this->emailVerifiedAt = new DateTimeImmutable();
    }

    public function changePassword(HashedPassword $password): void
    {
        $this->password = $password;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }
}
