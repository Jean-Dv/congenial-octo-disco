<?php

declare(strict_types=1);

namespace Modules\News\Providers;

use Inertia\Inertia;
use Modules\News\Application\PublicNewsQueryInterface;
use Modules\News\Domain\Article\Ports\NewsRepositoryInterface;
use Modules\News\Domain\Category\Ports\NewsCategoryRepositoryInterface;
use Modules\News\Infrastructure\Persistence\Eloquent\Repositories\EloquentNewsCategoryRepository;
use Modules\News\Infrastructure\Persistence\Eloquent\Repositories\EloquentNewsRepository;
use Modules\News\Infrastructure\Queries\EloquentPublicNewsQuery;
use Moon\ModuleKit\AbstractModule;
use Moon\ModuleKit\ModuleManifest;

final class NewsServiceProvider extends AbstractModule
{
    public function manifest(): ModuleManifest
    {
        $raw = json_decode(file_get_contents($this->moduleBasePath().'/module.json') ?: '{}', true);

        return ModuleManifest::fromArray($raw);
    }

    public function register(): void
    {
        $this->app->bind(NewsRepositoryInterface::class, EloquentNewsRepository::class);
        $this->app->bind(NewsCategoryRepositoryInterface::class, EloquentNewsCategoryRepository::class);
        $this->app->bind(PublicNewsQueryInterface::class, EloquentPublicNewsQuery::class);
    }

    public function boot(): void
    {
        parent::boot();

        Inertia::share('enabledModules', fn () => ['news' => true]);
    }
}
