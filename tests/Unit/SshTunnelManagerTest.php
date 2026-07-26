<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Domain\Realm\ValueObjects\CoreType;
use Modules\Core\Domain\Realm\ValueObjects\DatabaseConnectionConfig;
use Modules\Core\Domain\Realm\ValueObjects\RemoteConsoleConfig;
use Modules\Core\Domain\Realm\ValueObjects\SshTunnelConfig;
use Modules\Core\Infrastructure\Persistence\Connection\SshTunnelManager;
use Tests\TestCase;

final class SshTunnelManagerTest extends TestCase
{
    private string $temporaryDirectory;

    private string $fakeSsh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir().'/moon-ssh-manager-test-'.bin2hex(random_bytes(8));
        mkdir($this->temporaryDirectory.'/secrets', 0700, true);
        $this->fakeSsh = $this->temporaryDirectory.'/fake-ssh';

        file_put_contents($this->fakeSsh, <<<'PHP'
#!/usr/bin/env php
<?php
$sockets = [];

for ($index = 1; $index < count($argv); $index++) {
    if ($argv[$index] !== '-L') {
        continue;
    }

    $forward = $argv[++$index];
    preg_match('/^127\.0\.0\.1:(\d+):/', $forward, $matches);
    $socket = stream_socket_server('tcp://127.0.0.1:'.$matches[1], $errorCode, $errorMessage);

    if ($socket === false) {
        fwrite(STDERR, $errorMessage);
        exit(1);
    }

    $sockets[] = $socket;
}

while (true) {
    usleep(100000);
}
PHP);
        chmod($this->fakeSsh, 0700);

        config()->set('realm-ssh.binary', $this->fakeSsh);
        config()->set('realm-ssh.temporary_directory', $this->temporaryDirectory.'/secrets');
        config()->set('realm-ssh.startup_timeout', 2);
    }

    protected function tearDown(): void
    {
        if (is_file($this->fakeSsh)) {
            unlink($this->fakeSsh);
        }

        if (is_dir($this->temporaryDirectory.'/secrets')) {
            rmdir($this->temporaryDirectory.'/secrets');
        }

        if (is_dir($this->temporaryDirectory)) {
            rmdir($this->temporaryDirectory);
        }

        parent::tearDown();
    }

    public function test_it_reuses_one_tunnel_for_both_databases_and_removes_temporary_secrets(): void
    {
        $realm = Realm::create(
            name: 'Moon Realm',
            slug: 'moon-realm',
            coreType: CoreType::TRINITYCORE,
            authDatabase: new DatabaseConnectionConfig('auth-db', 3306, 'auth', 'trinity', 'secret'),
            charactersDatabase: new DatabaseConnectionConfig('characters-db', 3306, 'characters', 'trinity', 'secret'),
            remoteConsole: new RemoteConsoleConfig('worldserver', 7878, 'admin', 'secret'),
            sshTunnel: new SshTunnelConfig(
                host: 'bastion.example.test',
                port: 22,
                username: 'moon-cms',
                privateKey: 'private-key-material',
                privateKeyPassphrase: 'key-passphrase',
            ),
        );
        $manager = new SshTunnelManager;

        $authEndpoint = $manager->endpointFor($realm, 'auth');
        $charactersEndpoint = $manager->endpointFor($realm, 'characters');

        $this->assertSame('127.0.0.1', $authEndpoint->host);
        $this->assertSame('127.0.0.1', $charactersEndpoint->host);
        $this->assertNotSame($authEndpoint->port, $charactersEndpoint->port);
        $this->assertSame([], glob($this->temporaryDirectory.'/secrets/moon-realm-ssh-*'));

        $manager->closeFor($realm);
    }
}
