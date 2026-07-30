<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Application\GameAccount\GetServerStatusUseCase;
use Modules\Core\Application\GameAccount\ServerInfoParser;
use Modules\Core\Domain\Realm\Ports\RealmLatencyProbeInterface;
use Modules\Core\Domain\Realm\Ports\RealmRepositoryInterface;
use Modules\Core\Domain\Realm\Realm;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Infrastructure\Persistence\Connection\RealmConnectionFactory;
use Modules\News\Application\PublicNewsQueryInterface;
use Throwable;

final class HomeController extends Controller
{
    private const STATUS_CACHE_SECONDS = 30;

    private const FACTION_CACHE_SECONDS = 300;

    private const ALLIANCE_RACES = [1, 3, 4, 7, 11];

    private const HORDE_RACES = [2, 5, 6, 8, 10];

    public function __construct(
        private readonly RealmRepositoryInterface $realms,
        private readonly GetServerStatusUseCase $getServerStatus,
        private readonly ServerInfoParser $serverInfoParser,
        private readonly RealmConnectionFactory $connections,
        private readonly RealmLatencyProbeInterface $latencyProbe,
    ) {}

    public function __invoke(): Response
    {
        $newsEnabled = app()->bound(PublicNewsQueryInterface::class);
        $latestNews = $newsEnabled
            ? app(PublicNewsQueryInterface::class)->latest(2)
            : [];
        $realm = $this->realms->allEnabled()[0] ?? null;
        $realmData = $realm === null
            ? $this->unconfiguredRealmData()
            : Cache::remember(
                "public.home.realm.{$realm->id()}.status",
                self::STATUS_CACHE_SECONDS,
                fn (): array => $this->realmData($realm),
            );

        return Inertia::render('Public/Home/Index', [
            'serverStats' => $realmData['serverStats'],
            'realmStatus' => $realmData['realmStatus'],
            'latestNews' => $latestNews,
            'newsEnabled' => $newsEnabled,
        ]);
    }

    /**
     * @return array{serverStats: array<string, int|string>, realmStatus: array<string, mixed>}
     */
    private function realmData(Realm $realm): array
    {
        $factions = $this->factionBalance($realm);
        $latencyMs = $this->measureLatency($realm);
        $latencyHistory = $latencyMs === null
            ? []
            : $this->recordLatency($realm, $latencyMs);
        $serverInfo = null;

        try {
            $result = $this->getServerStatus->handle((int) $realm->id());
        } catch (Throwable $exception) {
            Log::warning('No fue posible consultar el estado público del reino.', [
                'realm_id' => $realm->id(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        if (isset($result) && ! $result->successful) {
            Log::notice('La consola remota no devolvió el estado público del reino.', [
                'realm_id' => $realm->id(),
                'message' => $result->errorMessage,
            ]);
        }

        if (isset($result) && $result->successful) {
            $serverInfo = $this->serverInfoParser->parse($result->rawOutput);
        }

        return [
            'serverStats' => [
                'online' => $serverInfo?->onlinePlayers ?? '–',
                'peak' => $serverInfo?->connectionPeak ?? '–',
                'uptime' => $serverInfo?->uptime ?? '–',
                'version' => $serverInfo?->version ?? $realm->coreType()->label(),
                'realm' => $realm->name(),
                'latency' => $latencyMs === null ? '–' : "{$latencyMs}ms",
            ],
            'realmStatus' => [
                'name' => $realm->name(),
                'configured' => true,
                'online' => $latencyMs !== null,
                'latencyMs' => $latencyMs,
                'latencyStable' => $latencyMs !== null && $this->latencyIsStable($latencyHistory),
                'alliancePct' => $factions['alliance'],
                'hordePct' => $factions['horde'],
                'latencyHistory' => $latencyHistory,
            ],
        ];
    }

    private function measureLatency(Realm $realm): ?int
    {
        try {
            return $this->latencyProbe->measure($realm);
        } catch (Throwable $exception) {
            Log::warning('No fue posible medir la latencia TCP del reino.', [
                'realm_id' => $realm->id(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{alliance: int, horde: int}
     */
    private function factionBalance(Realm $realm): array
    {
        return Cache::remember(
            "public.home.realm.{$realm->id()}.factions",
            self::FACTION_CACHE_SECONDS,
            function () use ($realm): array {
                if ($realm->charactersDatabase() === null) {
                    return ['alliance' => 0, 'horde' => 0];
                }

                try {
                    $counts = $this->connections->charactersConnectionFor($realm)
                        ->table('characters')
                        ->select('race')
                        ->selectRaw('COUNT(*) AS total')
                        ->whereIn('race', [...self::ALLIANCE_RACES, ...self::HORDE_RACES])
                        ->where(function ($query): void {
                            $query->whereNull('deleteDate')->orWhere('deleteDate', 0);
                        })
                        ->groupBy('race')
                        ->pluck('total', 'race');
                } catch (Throwable $exception) {
                    Log::warning('No fue posible calcular el balance de facciones del reino.', [
                        'realm_id' => $realm->id(),
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);

                    return ['alliance' => 0, 'horde' => 0];
                }

                $alliance = array_sum(array_map(
                    fn (int $race): int => (int) ($counts[$race] ?? 0),
                    self::ALLIANCE_RACES,
                ));
                $horde = array_sum(array_map(
                    fn (int $race): int => (int) ($counts[$race] ?? 0),
                    self::HORDE_RACES,
                ));
                $total = $alliance + $horde;

                if ($total === 0) {
                    return ['alliance' => 0, 'horde' => 0];
                }

                $alliancePct = (int) round(($alliance / $total) * 100);

                return ['alliance' => $alliancePct, 'horde' => 100 - $alliancePct];
            },
        );
    }

    /**
     * @return array<int, int>
     */
    private function recordLatency(Realm $realm, int $latencyMs): array
    {
        $key = "public.home.realm.{$realm->id()}.realmlist-latency-history";
        $history = Cache::get($key, []);
        $history = is_array($history) ? array_values(array_filter($history, 'is_int')) : [];
        $history[] = $latencyMs;
        $history = array_slice($history, -12);

        Cache::put($key, $history, now()->addDay());

        return $history;
    }

    /**
     * @param  array<int, int>  $history
     */
    private function latencyIsStable(array $history): bool
    {
        if ($history === []) {
            return false;
        }

        $average = array_sum($history) / count($history);

        return max($history) - min($history) <= max(20, $average * 0.5)
            && max($history) < 250;
    }

    /**
     * @return array{serverStats: array<string, int|string>, realmStatus: array<string, mixed>}
     */
    private function unconfiguredRealmData(): array
    {
        return [
            'serverStats' => [
                'online' => 0,
                'peak' => '–',
                'uptime' => '–',
                'version' => '–',
                'realm' => '–',
                'latency' => '–',
            ],
            'realmStatus' => [
                'name' => 'Sin reino configurado',
                'configured' => false,
                'online' => false,
                'latencyMs' => null,
                'latencyStable' => false,
                'alliancePct' => 0,
                'hordePct' => 0,
                'latencyHistory' => [],
            ],
        ];
    }
}
