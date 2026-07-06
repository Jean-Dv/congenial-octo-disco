<?php

declare(strict_types=1);

namespace Modules\Core\Application\Auth;

final class AuthenticateUserInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
