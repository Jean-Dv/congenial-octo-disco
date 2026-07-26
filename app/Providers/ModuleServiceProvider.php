<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManager;
use Moon\ModuleKit\ModuleManifest;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager(config('modules.path'));
        });
        $this->app->singleton(ModuleDependencyResolver::class);

        $this->app->booted(function (): void {
            foreach ($this->enabledModules() as $manifest) {
                if (! $manifest->isCore) {
                    $this->app->register($manifest->provider);
                }
            }

            Inertia::share('enabledModules', fn (): array => array_fill_keys(
                array_keys($this->enabledModules()),
                true,
            ));
        });
    }

    /**
     * @return array<string, ModuleManifest>
     */
    private function enabledModules(): array
    {
        try {
            /** @var ModuleManager $manager */
            $manager = $this->app->make(ModuleManager::class);
            /** @var ModuleDependencyResolver $dependencies */
            $dependencies = $this->app->make(ModuleDependencyResolver::class);
            /** @var ModuleRepositoryInterface $repository */
            $repository = $this->app->make(ModuleRepositoryInterface::class);

            return $dependencies->enabled($manager->discover(), $repository->enabledStates());
        } catch (\Throwable) {
            return [];
        }
    }
}
