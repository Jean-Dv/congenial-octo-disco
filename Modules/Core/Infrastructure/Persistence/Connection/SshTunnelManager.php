<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Connection;

use Modules\Core\Domain\Realm\Exceptions\RealmConnectivityException;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class SshTunnelManager
{
    /** @var array<string, SshTunnelHandle> */
    private array $tunnels = [];

    public function endpointFor(Realm $realm, string $kind): TunnelEndpoint
    {
        if (! $realm->usesSshTunnel()) {
            throw new RuntimeException('El realm no tiene configurado un túnel SSH.');
        }

        $key = $this->realmKey($realm);
        $fingerprint = $this->fingerprint($realm);
        $handle = $this->tunnels[$key] ?? null;

        if ($handle === null || $handle->fingerprint !== $fingerprint || ! $handle->process->isRunning()) {
            if ($handle !== null) {
                $this->stop($handle);
            }

            $handle = $this->start($realm, $fingerprint);
            $this->tunnels[$key] = $handle;
        }

        if (! isset($handle->endpoints[$kind])) {
            throw new RuntimeException("No existe un forward SSH para la base {$kind}.");
        }

        return $handle->endpoints[$kind];
    }

    public function closeFor(Realm $realm): void
    {
        $key = $this->realmKey($realm);

        if (isset($this->tunnels[$key])) {
            $this->stop($this->tunnels[$key]);
            unset($this->tunnels[$key]);
        }
    }

    public function __destruct()
    {
        foreach ($this->tunnels as $handle) {
            $this->stop($handle);
        }
    }

    private function start(Realm $realm, string $fingerprint): SshTunnelHandle
    {
        $ssh = $realm->sshTunnel();

        if ($ssh === null) {
            throw new RuntimeException('El realm no tiene configurado un túnel SSH.');
        }

        $temporaryDirectory = null;
        $process = null;

        try {
            $temporaryDirectory = $this->createTemporaryDirectory();
            $keyPath = $temporaryDirectory.'/identity';
            $passphrasePath = $temporaryDirectory.'/passphrase';
            $askPassPath = $temporaryDirectory.'/askpass';
            $this->writePrivateFile($keyPath, rtrim($ssh->privateKey).PHP_EOL);

            $environment = [];
            $batchMode = 'yes';

            if ($ssh->privateKeyPassphrase !== null) {
                $this->writePrivateFile($passphrasePath, $ssh->privateKeyPassphrase);
                $this->writeAskPassHelper($askPassPath);
                $environment = [
                    'DISPLAY' => 'moon-cms-ssh',
                    'SSH_ASKPASS' => $askPassPath,
                    'SSH_ASKPASS_REQUIRE' => 'force',
                    'MOON_SSH_PASSPHRASE_FILE' => $passphrasePath,
                ];
                $batchMode = 'no';
            }

            $endpoints = $this->allocateEndpoints($realm);
            $process = new Process(
                $this->command($realm, $keyPath, $batchMode, $endpoints),
                env: $environment,
            );
            $process->setTimeout(null);
            $process->start();

            $this->waitUntilReady($process, $endpoints, $temporaryDirectory);

            return new SshTunnelHandle($fingerprint, $process, $endpoints);
        } catch (RealmConnectivityException $exception) {
            if ($process?->isRunning()) {
                $process->stop(1);
            }

            throw $exception;
        } catch (Throwable $exception) {
            if ($process?->isRunning()) {
                $process->stop(1);
            }

            throw new RealmConnectivityException(
                'ssh_tunnel',
                'No fue posible iniciar el túnel SSH. Verifica el host, la llave privada y su passphrase.',
                $exception,
            );
        } finally {
            if ($temporaryDirectory !== null) {
                $this->removeTemporarySecrets($temporaryDirectory);
            }
        }
    }

    /**
     * @param  array<string, TunnelEndpoint>  $endpoints
     * @return array<int, string>
     */
    private function command(Realm $realm, string $keyPath, string $batchMode, array $endpoints): array
    {
        $ssh = $realm->sshTunnel();

        if ($ssh === null) {
            throw new RuntimeException('El realm no tiene configurado un túnel SSH.');
        }

        $command = [
            (string) config('realm-ssh.binary', '/usr/bin/ssh'),
            '-F', '/dev/null',
            '-N',
            '-T',
            '-o', "BatchMode={$batchMode}",
            '-o', 'ExitOnForwardFailure=yes',
            '-o', 'StrictHostKeyChecking=yes',
            '-o', 'IdentitiesOnly=yes',
            '-o', 'PreferredAuthentications=publickey',
            '-o', 'PasswordAuthentication=no',
            '-o', 'KbdInteractiveAuthentication=no',
            '-o', 'ForwardAgent=no',
            '-o', 'ForwardX11=no',
            '-o', 'GatewayPorts=no',
            '-o', 'PermitLocalCommand=no',
            '-o', 'ServerAliveInterval='.(int) config('realm-ssh.server_alive_interval', 30),
            '-o', 'ServerAliveCountMax='.(int) config('realm-ssh.server_alive_count_max', 3),
            '-o', 'ConnectTimeout='.(int) config('realm-ssh.connect_timeout', 8),
            '-o', 'UserKnownHostsFile='.(string) config('realm-ssh.known_hosts_file', '/etc/ssh/ssh_known_hosts'),
            '-i', $keyPath,
            '-p', (string) $ssh->port,
        ];

        foreach ($endpoints as $kind => $endpoint) {
            $database = $kind === 'auth'
                ? $realm->authDatabase()
                : $realm->charactersDatabase();

            if ($database !== null) {
                $command[] = '-L';
                $command[] = sprintf(
                    '127.0.0.1:%d:%s:%d',
                    $endpoint->port,
                    $this->forwardHost($database),
                    $database->port,
                );
            }
        }

        $sshHost = str_contains($ssh->host, ':') ? "[{$ssh->host}]" : $ssh->host;
        $command[] = "{$ssh->username}@{$sshHost}";

        return $command;
    }

    private function forwardHost(DatabaseConnectionConfig $database): string
    {
        return str_contains($database->host, ':')
            ? "[{$database->host}]"
            : $database->host;
    }

    /**
     * @return array<string, TunnelEndpoint>
     */
    private function allocateEndpoints(Realm $realm): array
    {
        $endpoints = [
            'auth' => new TunnelEndpoint('127.0.0.1', $this->availablePort()),
        ];

        if ($realm->charactersDatabase() !== null) {
            do {
                $charactersPort = $this->availablePort();
            } while ($charactersPort === $endpoints['auth']->port);

            $endpoints['characters'] = new TunnelEndpoint('127.0.0.1', $charactersPort);
        }

        return $endpoints;
    }

    private function availablePort(): int
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);

        if ($socket === false) {
            throw new RuntimeException("No se pudo reservar un puerto local: {$errorMessage} ({$errorCode}).");
        }

        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        if ($address === false || ! str_contains($address, ':')) {
            throw new RuntimeException('No se pudo determinar el puerto local reservado.');
        }

        return (int) substr($address, strrpos($address, ':') + 1);
    }

    /**
     * @param  array<string, TunnelEndpoint>  $endpoints
     */
    private function waitUntilReady(Process $process, array $endpoints, string $temporaryDirectory): void
    {
        $deadline = microtime(true) + (float) config('realm-ssh.startup_timeout', 10);

        do {
            if (! $process->isRunning()) {
                $reason = $this->safeProcessError($process, $temporaryDirectory);

                throw new RealmConnectivityException(
                    'ssh_tunnel',
                    "El túnel SSH terminó durante el arranque{$reason}",
                );
            }

            $ready = true;
            foreach ($endpoints as $endpoint) {
                $socket = @fsockopen($endpoint->host, $endpoint->port, $errorCode, $errorMessage, 0.2);
                if ($socket === false) {
                    $ready = false;
                    break;
                }

                fclose($socket);
            }

            if ($ready) {
                return;
            }

            usleep(100_000);
        } while (microtime(true) < $deadline);

        throw new RealmConnectivityException(
            'ssh_tunnel',
            'El túnel SSH no estuvo disponible dentro del tiempo esperado.',
        );
    }

    private function safeProcessError(Process $process, string $temporaryDirectory): string
    {
        $error = trim($process->getErrorOutput());

        if ($error === '') {
            return '.';
        }

        $error = str_replace($temporaryDirectory, '[archivo temporal]', $error);
        $error = preg_replace('/[\r\n]+/', ' ', $error) ?? '';
        $error = mb_substr($error, 0, 500);

        return ": {$error}";
    }

    private function createTemporaryDirectory(): string
    {
        $base = rtrim((string) config('realm-ssh.temporary_directory', sys_get_temp_dir()), '/');
        $this->cleanupStaleTemporaryDirectories($base);
        $directory = $base.'/moon-realm-ssh-'.bin2hex(random_bytes(16));

        if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio temporal para SSH.');
        }

        chmod($directory, 0700);

        return $directory;
    }

    private function cleanupStaleTemporaryDirectories(string $base): void
    {
        $staleBefore = time() - 300;
        $processUserId = function_exists('posix_geteuid') ? posix_geteuid() : getmyuid();

        foreach (glob($base.'/moon-realm-ssh-*', GLOB_ONLYDIR) ?: [] as $directory) {
            if (
                is_link($directory)
                || fileowner($directory) !== $processUserId
                || (filemtime($directory) ?: PHP_INT_MAX) >= $staleBefore
            ) {
                continue;
            }

            $this->removeTemporarySecrets($directory);
        }
    }

    private function writePrivateFile(string $path, string $contents): void
    {
        $previousUmask = umask(0077);

        try {
            if (file_put_contents($path, $contents, LOCK_EX) === false) {
                throw new RuntimeException('No se pudo escribir un secreto temporal para SSH.');
            }
        } finally {
            umask($previousUmask);
        }

        chmod($path, 0600);
    }

    private function writeAskPassHelper(string $path): void
    {
        $helper = <<<'SH'
#!/bin/sh
exec /bin/cat "$MOON_SSH_PASSPHRASE_FILE"
SH;

        $previousUmask = umask(0077);

        try {
            if (file_put_contents($path, $helper, LOCK_EX) === false) {
                throw new RuntimeException('No se pudo preparar SSH_ASKPASS.');
            }
        } finally {
            umask($previousUmask);
        }

        chmod($path, 0700);
    }

    private function removeTemporarySecrets(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (['identity', 'passphrase', 'askpass'] as $file) {
            $path = $directory.'/'.$file;

            if (is_file($path)) {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }

    private function fingerprint(Realm $realm): string
    {
        return hash('sha256', serialize([
            $realm->sshTunnel()?->toArray(),
            $realm->authDatabase()->toArray(),
            $realm->charactersDatabase()?->toArray(),
        ]));
    }

    private function realmKey(Realm $realm): string
    {
        return $realm->id() !== null
            ? 'realm-'.$realm->id()
            : 'new-'.spl_object_id($realm);
    }

    private function stop(SshTunnelHandle $handle): void
    {
        if ($handle->process->isRunning()) {
            $handle->process->stop(1);
        }
    }
}
