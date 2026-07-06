<?php

declare(strict_types=1);

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puerta minima para /admin/*. Deliberadamente simple (una sola bandera
 * `is_admin` en `users`, sin roles ni permisos granulares): un sistema
 * de roles/permisos completo queda fuera de este core inicial y puede
 * llegar despues como su propio modulo. El primer administrador se
 * otorga con "php artisan moon:make-admin {email}" (ver README).
 */
final class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) $request->user()?->is_admin, 403);

        return $next($request);
    }
}
