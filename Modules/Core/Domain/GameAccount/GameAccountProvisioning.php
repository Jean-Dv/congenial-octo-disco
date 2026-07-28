<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount;

use DateTimeImmutable;
use LogicException;
use Modules\Core\Domain\GameAccount\ValueObjects\ProvisioningStatus;

/**
 * Estado de "crear/mantener sincronizada la cuenta de juego de este
 * usuario en este reino". Existe una fila por cada (user, realm) y
 * conserva el username real del juego; esto permite vincular cuentas
 * creadas fuera del CMS. El registro crea una por cada reino habilitado;
 * un reset de password reabre las READY para volver a sincronizar.
 */
final class GameAccountProvisioning
{
    private ?int $id;

    public function __construct(
        ?int $id,
        private readonly int $userId,
        private readonly int $realmId,
        private readonly ?string $gameUsername,
        private ProvisioningStatus $status,
        private int $attempts,
        private ?string $lastError,
        private readonly DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
    }

    public static function requestFor(int $userId, int $realmId, string $gameUsername): self
    {
        return new self(
            id: null,
            userId: $userId,
            realmId: $realmId,
            gameUsername: $gameUsername,
            status: ProvisioningStatus::PENDING,
            attempts: 0,
            lastError: null,
            createdAt: new DateTimeImmutable,
            updatedAt: null,
        );
    }

    public static function reconstitute(
        int $id,
        int $userId,
        int $realmId,
        ?string $gameUsername,
        ProvisioningStatus $status,
        int $attempts,
        ?string $lastError,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $userId, $realmId, $gameUsername, $status, $attempts, $lastError, $createdAt, $updatedAt);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null && $this->id !== $id) {
            throw new LogicException('Este aprovisionamiento ya tiene una identidad asignada.');
        }

        $this->id = $id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function realmId(): int
    {
        return $this->realmId;
    }

    public function gameUsername(): ?string
    {
        return $this->gameUsername;
    }

    public function status(): ProvisioningStatus
    {
        return $this->status;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markInProgress(): void
    {
        $this->status = ProvisioningStatus::IN_PROGRESS;
        $this->attempts++;
        $this->touch();
    }

    public function markReady(): void
    {
        $this->status = ProvisioningStatus::READY;
        $this->lastError = null;
        $this->touch();
    }

    public function markFailed(string $error): void
    {
        $this->status = ProvisioningStatus::FAILED;
        $this->lastError = $error;
        $this->touch();
    }

    /**
     * Registra el error de UN intento sin marcar el estado como
     * terminal: el Job todavia puede reintentar (segun su $tries/backoff).
     * Solo cuando el motor de colas agota los reintentos se llama a
     * markFailed() desde el metodo failed() del Job.
     */
    public function recordAttemptError(string $error): void
    {
        $this->lastError = $error;
        $this->touch();
    }

    /**
     * Vuelve a PENDING para que un Job la recoja de nuevo (usado al
     * resetear password: hay que reenviar salt/verifier nuevos a cada reino).
     */
    public function requeue(): void
    {
        $this->status = ProvisioningStatus::PENDING;
        $this->touch();
    }

    public function canRetry(int $maxAttempts): bool
    {
        return $this->attempts < $maxAttempts;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
