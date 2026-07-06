<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Contracts;

/**
 * Resultado uniforme de ejecutar un RemoteCommandInterface, sin importar
 * que gateway lo haya ejecutado.
 */
final class RemoteCommandResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly string $rawOutput,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public static function success(string $rawOutput): self
    {
        return new self(successful: true, rawOutput: $rawOutput);
    }

    public static function failure(string $errorMessage, string $rawOutput = ''): self
    {
        return new self(successful: false, rawOutput: $rawOutput, errorMessage: $errorMessage);
    }
}
