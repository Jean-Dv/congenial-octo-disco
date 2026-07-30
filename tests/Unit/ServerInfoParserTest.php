<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\Core\Application\GameAccount\ServerInfoParser;
use PHPUnit\Framework\TestCase;

final class ServerInfoParserTest extends TestCase
{
    public function test_it_parses_trinitycore_server_info(): void
    {
        $info = (new ServerInfoParser)->parse(<<<'OUTPUT'
            TrinityCore rev. a1b2c3d 2026-07-29 (Unix, Release) (worldserver-daemon)
            Connected players: 42. Characters in world: 39.
            Connection peak: 128.
            Server uptime: 2 Days 4 Hours 12 Minutes.
            OUTPUT);

        $this->assertSame(42, $info->onlinePlayers);
        $this->assertSame(128, $info->connectionPeak);
        $this->assertSame('2d 4h', $info->uptime);
        $this->assertSame('TrinityCore a1b2c3d', $info->version);
    }

    public function test_it_parses_alternate_server_info_labels(): void
    {
        $info = (new ServerInfoParser)->parse(<<<'OUTPUT'
            AzerothCore rev. 7f8e9d
            Players online: 7 (Max: 31) (Queued: 0)
            Uptime: 3 Hours 5 Minutes
            OUTPUT);

        $this->assertSame(7, $info->onlinePlayers);
        $this->assertSame(31, $info->connectionPeak);
        $this->assertSame('3h 5m', $info->uptime);
        $this->assertSame('AzerothCore 7f8e9d', $info->version);
    }

    public function test_it_compacts_parenthesized_uptime_units(): void
    {
        $info = (new ServerInfoParser)->parse(
            'Server uptime: 0 Day(s) 0 Hour(s) 11 Minute(s) 1 Second(s).',
        );

        $this->assertSame('11m 1s', $info->uptime);
    }

    public function test_it_returns_safe_defaults_for_an_unknown_output(): void
    {
        $info = (new ServerInfoParser)->parse('Comando ejecutado.');

        $this->assertSame(0, $info->onlinePlayers);
        $this->assertNull($info->connectionPeak);
        $this->assertNull($info->uptime);
        $this->assertNull($info->version);
    }
}
