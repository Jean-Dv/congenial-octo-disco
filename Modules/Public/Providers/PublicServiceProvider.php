<?php

declare(strict_types=1);

namespace Modules\Public\Providers;

use Modules\Core\Application\Module\ToggleModuleUseCase;
use Modules\Core\Infrastructure\Persistence\Eloquent\Repositories\EloquentModuleRepository;
use Moon\ModuleKit\AbstractModule;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleManifest;

final class PublicServiceProvider extends AbstractModule
{
    public function manifest(): ModuleManifest
    {
        $raw = json_decode(
            file_get_contents($this->moduleBasePath().'/module.json') ?: '{}',
            true,
        );

        return ModuleManifest::fromArray($raw);
    }

    public function register(): void
    {
        $this->app->singleton(ModuleRepositoryInterface::class, EloquentModuleRepository::class);

        $this->app->when(ToggleModuleUseCase::class)
            ->needs('$protectedSlugs')
            ->give(fn() => config('modules.protected', []));
    }

    public function boot(): void
    {
        parent::boot();
    }
}