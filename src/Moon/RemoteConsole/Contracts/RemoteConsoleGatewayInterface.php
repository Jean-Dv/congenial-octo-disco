<?php

declare(strict_types=1);

namespace Moon\RemoteConsole\Contracts;

/**
 * Puerto principal de comunicacion remota con el core del juego.
 *
 * Esta es la pieza que hace que cambiar de transporte sea trivial: hoy
 * existe una unica implementacion (SoapRemoteConsoleGateway, en
 * Modules/Core/Infrastructure/RemoteConsole/Soap). El dia que un core
 * exponga gRPC o una API REST en vez de SOAP, basta con escribir una
 * nueva clase que implemente esta misma interfaz (ej.
 * GrpcRemoteConsoleGateway) y cambiar un binding en el ServiceProvider:
 * ningun caso de uso ni controlador tiene que enterarse del cambio.
 */
interface RemoteConsoleGatewayInterface
{
    public function execute(RemoteCommandInterface $command, RemoteConsoleConnection $connection): RemoteCommandResult;
}
