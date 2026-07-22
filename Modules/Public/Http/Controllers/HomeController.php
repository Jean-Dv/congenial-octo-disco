<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\News\Application\PublicNewsQueryInterface;

final class HomeController extends Controller
{
    public function __construct() {}

    public function __invoke(Request $request)
    {
        $newsEnabled = app()->bound(PublicNewsQueryInterface::class);
        $latestNews = $newsEnabled
            ? app(PublicNewsQueryInterface::class)->latest(2)
            : [];

        return Inertia::render('Public/Home/Index', [
            /**
             * Estadísticas del servidor.
             * Reemplazar con queries reales cuando estén disponibles.
             */
            'serverStats' => [
                'online' => 2482,
                'peak' => 4105,
                'uptime' => '99.9%',
                'version' => '3.3.5a',
                'realm' => 'PvP/E',
                'latency' => '12ms',
            ],

            /**
             * Estado del reino en tiempo real.
             */
            'realmStatus' => [
                'online' => true,
                'latencyMs' => 14,
                'latencyStable' => true,
                'alliancePct' => 48,
                'hordePct' => 52,
                'latencyHistory' => [45, 50, 48, 42, 55, 49, 44, 52, 47, 51, 46, 43],
            ],

            'latestNews' => $latestNews,
            'newsEnabled' => $newsEnabled,
        ]);
    }
}
