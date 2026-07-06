<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\Exceptions;

use DomainException;

final class InvalidCredentialsException extends DomainException
{
    public static function make(): self
    {
        return new self('El correo o la contraseña no son correctos.');
    }
}
