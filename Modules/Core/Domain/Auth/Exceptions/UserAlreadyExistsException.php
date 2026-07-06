<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\Exceptions;

use DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public static function withEmail(string $email): self
    {
        return new self("Ya existe una cuenta de panel registrada con el correo \"{$email}\".");
    }
}
