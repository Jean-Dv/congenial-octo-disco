<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Contracts;

/**
 * Descriptor de conexion agnostico de protocolo. El modulo Core mapea su
 * propio Realm\ValueObjects\RemoteConsoleConfig a esto antes de invocar un
 * gateway: asi el shared kernel nunca depende del dominio de un modulo.
 */
final class RemoteConsoleConnection
{
    /**
     * @param  array<string, mixed>  $options  Datos propios del protocolo
     *         concreto (ej. para SOAP: ['namespace_uri' => 'urn:TC']).
     */
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $password,
        public readonly array $options = [],
    ) {
    }
}
