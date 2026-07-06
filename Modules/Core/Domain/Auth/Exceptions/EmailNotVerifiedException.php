<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\Exceptions;

use DomainException;

final class EmailNotVerifiedException extends DomainException
{
    public static function make(): self
    {
        return new self('Debes verificar tu correo electronico antes de continuar.');
    }
}
