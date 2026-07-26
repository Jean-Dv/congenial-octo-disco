<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Realm\Exceptions;

use RuntimeException;
use Throwable;

final class RealmConnectivityException extends RuntimeException
{
    public function __construct(
        public readonly string $field,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }
}
