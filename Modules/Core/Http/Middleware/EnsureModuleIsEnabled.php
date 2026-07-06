<?php

declare(strict_types=1);

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware reutilizable por CUALQUIER modulo (no solo Core) para
 * proteger sus propias rutas: `->middleware('module:blog')`. Si el
 * modulo se deshabilita desde el panel, sus rutas devuelven 404 en vez
 * de seguir respondiendo.
 */
final class EnsureModuleIsEnabled
{
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
    ) {
    }

    public function handle(Request $request, Closure $next, string $slug): Response
    {
        if (! $this->modules->isEnabled($slug)) {
            abort(404);
        }

        return $next($request);
    }
}
