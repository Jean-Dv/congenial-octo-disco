<?php

declare(strict_types=1);

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Modules\Core\Domain\Realm\Ports\RealmLatencyProbeInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\RealmModel;
use Moon\RemoteConsole\Contracts\RemoteCommandInterface;
use Moon\RemoteConsole\Contracts\RemoteCommandResult;
use Moon\RemoteConsole\Contracts\RemoteConsoleConnection;
use Moon\RemoteConsole\Contracts\RemoteConsoleGatewayInterface;
use Tests\Concerns\BootsNewsModule;
use Tests\TestCase;

final class PublicHomeStatusTest extends TestCase
{
    use BootsNewsModule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootNewsModule();
    }

    public function test_it_renders_real_server_info_from_the_remote_console(): void
    {
        $this->realm();
        $this->app->instance(
            RemoteConsoleGatewayInterface::class,
            new HomeStatusRemoteConsoleGateway(RemoteCommandResult::success(<<<'OUTPUT'
                TrinityCore rev. abc1234 (Unix, Release)
                Connected players: 23. Characters in world: 20.
                Connection peak: 57.
                Server uptime: 1 Day 2 Hours.
                OUTPUT)),
        );
        $this->app->instance(RealmLatencyProbeInterface::class, new FixedRealmLatencyProbe(42));

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home/Index', shouldExist: false)
            ->where('serverStats.online', 23)
            ->where('serverStats.peak', 57)
            ->where('serverStats.uptime', '1d 2h')
            ->where('serverStats.version', 'TrinityCore abc1234')
            ->where('serverStats.realm', 'Moonshard')
            ->where('realmStatus.name', 'Moonshard')
            ->where('realmStatus.online', true)
            ->where('realmStatus.latencyMs', 42));
    }

    public function test_soap_failure_does_not_mark_an_reachable_realm_as_offline(): void
    {
        $this->realm();
        $this->app->instance(
            RemoteConsoleGatewayInterface::class,
            new HomeStatusRemoteConsoleGateway(RemoteCommandResult::failure('Could not connect to host')),
        );
        $this->app->instance(RealmLatencyProbeInterface::class, new FixedRealmLatencyProbe(96));

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('serverStats.online', '–')
            ->where('serverStats.peak', '–')
            ->where('serverStats.realm', 'Moonshard')
            ->where('serverStats.latency', '96ms')
            ->where('realmStatus.online', true)
            ->where('realmStatus.latencyMs', 96));
    }

    private function realm(): RealmModel
    {
        return RealmModel::create([
            'name' => 'Moonshard',
            'slug' => 'moonshard',
            'core_type' => 'trinitycore',
            'auth_database' => [
                'host' => 'db',
                'port' => 3306,
                'database' => 'auth',
                'username' => 'moon',
                'password' => 'secret',
            ],
            'characters_database' => null,
            'remote_console' => [
                'host' => 'worldserver',
                'port' => 7878,
                'username' => 'soap',
                'password' => 'secret',
            ],
            'ssh_tunnel' => null,
            'gm_realm_id' => -1,
            'enabled' => true,
        ]);
    }
}

final readonly class HomeStatusRemoteConsoleGateway implements RemoteConsoleGatewayInterface
{
    public function __construct(private RemoteCommandResult $result) {}

    public function execute(
        RemoteCommandInterface $command,
        RemoteConsoleConnection $connection,
    ): RemoteCommandResult {
        return $this->result;
    }
}

final readonly class FixedRealmLatencyProbe implements RealmLatencyProbeInterface
{
    public function __construct(private ?int $latencyMs) {}

    public function measure(Realm $realm): ?int
    {
        return $this->latencyMs;
    }
}
