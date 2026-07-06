<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\RemoteConsole\Soap;

use Moon\RemoteConsole\Contracts\RemoteCommandInterface;
use Moon\RemoteConsole\Contracts\RemoteCommandResult;
use Moon\RemoteConsole\Contracts\RemoteConsoleConnection;
use Moon\RemoteConsole\Contracts\RemoteConsoleGatewayInterface;
use Moon\RemoteConsole\Exceptions\RemoteConsoleException;
use SoapClient;
use SoapFault;
use SoapParam;
use Throwable;

/**
 * Unica implementacion actual de RemoteConsoleGatewayInterface. Habla
 * SOAP sin WSDL con el worldserver, exactamente como documenta
 * TrinityCore/AzerothCore: un unico metodo remoto "executeCommand" que
 * recibe el texto plano del comando de consola y devuelve su salida
 * como string (o lanza un SoapFault si el comando falla).
 *
 * El dia que un core exponga gRPC o REST en vez de SOAP, se escribe una
 * clase hermana (ej. GrpcRemoteConsoleGateway) implementando el mismo
 * RemoteConsoleGatewayInterface y se cambia el binding en
 * CoreServiceProvider: nada mas se entera del cambio.
 */
final class SoapRemoteConsoleGateway implements RemoteConsoleGatewayInterface
{
    private const DEFAULT_NAMESPACE_URI = 'urn:TC';

    private const CONNECTION_TIMEOUT_SECONDS = 10;

    public function execute(RemoteCommandInterface $command, RemoteConsoleConnection $connection): RemoteCommandResult
    {
        try {
            $client = $this->buildClient($connection);

            $result = $client->executeCommand(new SoapParam($command->toConsoleSyntax(), 'command'));

            return RemoteCommandResult::success(is_string($result) ? $result : (string) $result);
        } catch (SoapFault $fault) {
            // El propio servicio SOAP de estos cores usa SoapFault tanto
            // para errores de negocio (comando invalido, GM sin permisos)
            // como para fallos de transporte: se modela como un resultado
            // "no exitoso", no como excepcion, para que quien llama decida
            // que hacer (reintentar, mostrar el mensaje, etc).
            return RemoteCommandResult::failure($fault->getMessage());
        } catch (Throwable $exception) {
            throw new RemoteConsoleException(
                "No se pudo ejecutar el comando \"{$command->name()}\" via SOAP: {$exception->getMessage()}",
                previous: $exception,
            );
        }
    }

    /**
     * @throws SoapFault
     */
    private function buildClient(RemoteConsoleConnection $connection): SoapClient
    {
        $namespaceUri = $connection->options['namespace_uri'] ?? self::DEFAULT_NAMESPACE_URI;

        return new SoapClient(null, [
            'location' => sprintf('http://%s:%d/', $connection->host, $connection->port),
            'uri' => $namespaceUri,
            'login' => $connection->username,
            'password' => $connection->password,
            'style' => SOAP_RPC,
            'trace' => 1,
            'exceptions' => true,
            'connection_timeout' => self::CONNECTION_TIMEOUT_SECONDS,
        ]);
    }
}
