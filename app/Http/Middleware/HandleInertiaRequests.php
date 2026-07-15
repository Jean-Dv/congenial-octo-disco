<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Moon\ThemeKit\ActiveThemeResolver;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly ActiveThemeResolver $themeResolver) {}

    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props compartidas con TODAS las paginas Inertia, de cualquier modulo.
     * Cada modulo puede añadir sus propios "shared props" adicionales desde
     * su propio ServiceProvider si lo necesita (ver CoreServiceProvider).
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'locale', 'is_admin']),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => app()->getLocale(),
            'theme' => $this->themeResolver->resolve()->metadata(),
        ];
    }
}
