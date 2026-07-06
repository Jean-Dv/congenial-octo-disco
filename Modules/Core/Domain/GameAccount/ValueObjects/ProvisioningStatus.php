<?php

declare(strict_types=1);

namespace Modules\Core\Domain\GameAccount\ValueObjects;

enum ProvisioningStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case READY = 'ready';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En proceso',
            self::READY => 'Lista',
            self::FAILED => 'Fallida',
        };
    }
}
