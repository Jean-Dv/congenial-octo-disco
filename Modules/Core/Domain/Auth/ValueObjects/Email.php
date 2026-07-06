<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Auth\ValueObjects;

use InvalidArgumentException;
use Stringable;

final class Email implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("\"{$value}\" no es un correo electronico valido.");
        }

        $this->value = mb_strtolower($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
