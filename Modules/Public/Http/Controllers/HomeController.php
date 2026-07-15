<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;

final class HomeController extends Controller
{
    public function __construct() {}

    public function __invoke(Request $request)
    {
        return Inertia::render('Public/Home/Index', [
            /**
             * Estadísticas del servidor.
             * Reemplazar con queries reales cuando estén disponibles.
             */
            'serverStats' => [
                'online'  => 2482,
                'peak'    => 4105,
                'uptime'  => '99.9%',
                'version' => '3.3.5a',
                'realm'   => 'PvP/E',
                'latency' => '12ms',
            ],

            /**
             * Estado del reino en tiempo real.
             */
            'realmStatus' => [
                'online'          => true,
                'latencyMs'       => 14,
                'latencyStable'   => true,
                'alliancePct'     => 48,
                'hordePct'        => 52,
                'latencyHistory'  => [45, 50, 48, 42, 55, 49, 44, 52, 47, 51, 46, 43],
            ],

            /**
             * Noticias recientes.
             * Reemplazar con Eloquent cuando exista el modelo News.
             */
            'latestNews' => [
                [
                    'id'       => 1,
                    'category' => 'Actualización',
                    'date'     => '12 Oct, 2023',
                    'title'    => 'Parche 1.2: El Despertar de los Ancestros',
                    'excerpt'  => 'Explora las nuevas profundidades de Azeroth con el lanzamiento de nuestra raid exclusiva para 25 jugadores. Nuevos sets de armadura, monturas y más...',
                    'author'   => 'Admin_Aetheris',
                    'authorInitials' => 'GM',
                    'image'    => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDG0Z35Ch1GEnxETu1gHmZpJCgwoPKjyDZBC6MFyV__Cxd3LB9H_ghaKoQH2nmbJKu3mtvWwwx6jP7lVZFRzL4a6AOL4SKZYLH479WXTdwov37cU2cMPT5C5MTG4ecvfuUqV6Wry8wFWk-BJeeX_zjFASwI7dtNGxz4TE00Q3JJeOw8FEqDTD43mipZlfnpGQNbsTSWn5VAXB9AVLPpmC4NYSeXVYTOlN6FoEFEgbQ4oPTKwo2VEjUJX0PBZXdyy0thqjktSqblJkM',
                    'type'     => 'primary',
                ],
                [
                    'id'       => 2,
                    'category' => 'Evento',
                    'date'     => '08 Oct, 2023',
                    'title'    => 'Torneo de Arena: La Copa Aetheris',
                    'excerpt'  => 'Inscríbete ahora para competir por premios reales en metálico y títulos únicos dentro del servidor. ¡La gloria te espera en las arenas!',
                    'author'   => 'Community_Lead',
                    'authorInitials' => 'CE',
                    'image'    => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDmYH-WJCCDwY8B6lnC1ZS9Ah1giVJCItbuFIY_8CJHqrWQC9zyt28UvpSh2vQbzBXpTxpRgyWc6T2fg9toi1bH_OL959a9QGJqKnTWt-9iLzu-SiDXi8i_ha2b7Y-5X_VhY9DVBth4zqdvnAx-L7vQvTSmmcnLZSc_jMxz4JaxH8fHgKtAx1-QteszI4ETTVUGJssq3oSyV1Xo6jD00hpWL4gqqvYxU8zFE5XCG7QAbcLFUXSak7Ynt4l6gR3AINcLbgUF5zEF1v0',
                    'type'     => 'secondary',
                ],
            ],
        ]);
    }
}