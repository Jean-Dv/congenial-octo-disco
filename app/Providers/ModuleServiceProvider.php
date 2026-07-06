<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Moon\ModuleKit\ModuleManager;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager(config('modules.path'));
        });

        /** @var ModuleManager $manager */
        $manager = $this->app->make(ModuleManager::class);

        foreach ($manager->nonCore() as $manifest) {
            if ($this->isEnabled($manifest->slug)) {
                $this->app->register($manifest->provider);
            }
        }
    }

    /**
     * Consulta si un modulo no-core esta habilitado. Si la tabla `modules`
     * todavia no existe (instalacion nueva, antes de migrar), el modulo
     * simplemente no se carga todavia: el Core no depende de esto para
     * funcionar, asi que es seguro fallar "cerrado" en ese instante.
     */
    private function isEnabled(string $slug): bool
    {
        try {
            /** @var ModuleRepositoryInterface $repository */
            $repository = $this->app->make(ModuleRepositoryInterface::class);

            return $repository->isEnabled($slug);
        } catch (\Throwable) {
            return false;
        }
    }
}
