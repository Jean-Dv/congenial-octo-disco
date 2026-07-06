<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Contracts;

/**
 * Un comando a ejecutar en el core remoto, independiente del transporte
 * (SOAP hoy, gRPC/REST mañana). Cada core WoW actual (TrinityCore,
 * AzerothCore y la familia MaNGOS) comparte la misma sintaxis de texto
 * para su consola de comandos, asi que toConsoleSyntax() es el formato
 * "de facto" que casi cualquier gateway de texto va a necesitar. Un
 * gateway de un protocolo estructurado (gRPC/REST a medida) puede optar
 * por ignorar toConsoleSyntax() y leer las propiedades publicas de cada
 * comando concreto en su lugar.
 */
interface RemoteCommandInterface
{
    /**
     * Nombre corto y estable (para logging/depuracion), no depende del protocolo.
     */
    public function name(): string;

    /**
     * Sintaxis de texto plano que entienden las consolas de TrinityCore,
     * AzerothCore y la familia MaNGOS.
     */
    public function toConsoleSyntax(): string;
}
