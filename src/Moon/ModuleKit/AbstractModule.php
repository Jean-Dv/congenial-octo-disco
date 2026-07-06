<?php

declare(strict_types=1);

namespace Moon\ModuleKit;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Moon\ModuleKit\Contracts\ModuleInterface;
use ReflectionClass;

/**
 * Clase base para el ServiceProvider de un modulo.
 *
 * Convencion de carpetas esperada (todas opcionales, se cargan solo si
 * existen):
 *
 *   Modules/{Nombre}/
 *     ├── Providers/{Nombre}ServiceProvider.php   <- extiende esta clase
 *     ├── module.json
 *     ├── database/migrations/*.php
 *     ├── routes/web.php
 *     ├── resources/lang/{locale}/{slug}.php
 *     └── resources/js/Pages/**.vue               <- lo resuelve Vite, no PHP
 *
 * Un modulo nuevo, en el 90% de los casos, solo necesita:
 *   1. Escribir su module.json
 *   2. Extender esta clase e implementar manifest()
 *   3. Poner sus rutas/migraciones/vistas en las carpetas de arriba
 */
abstract class AbstractModule extends ServiceProvider implements ModuleInterface
{
    /**
     * Ruta absoluta a la raiz del modulo (donde vive module.json).
     * Por convencion, el ServiceProvider vive en "{raiz}/Providers/", por
     * lo que basta con subir un nivel. Sobrescribe este metodo si tu
     * modulo usa una estructura distinta.
     */
    protected function moduleBasePath(): string
    {
        $providerFile = (new ReflectionClass($this))->getFileName();

        return dirname($providerFile, 2);
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $base = $this->moduleBasePath();
        $slug = $this->manifest()->slug;

        if (is_dir("{$base}/database/migrations")) {
            $this->loadMigrationsFrom("{$base}/database/migrations");
        }

        if (is_file("{$base}/routes/web.php")) {
            Route::middleware('web')->group("{$base}/routes/web.php");
        }

        if (is_dir("{$base}/resources/lang")) {
            $this->loadTranslationsFrom("{$base}/resources/lang", $slug);
        }
    }
}
