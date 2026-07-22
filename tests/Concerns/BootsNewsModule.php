<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Route;
use Modules\News\Providers\NewsServiceProvider;
use Modules\Public\Providers\PublicServiceProvider;

trait BootsNewsModule
{
    protected function bootNewsModule(): void
    {
        $this->app->register(NewsServiceProvider::class);
        $this->app->register(PublicServiceProvider::class);
        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
        $this->artisan('migrate:fresh')->assertSuccessful();
        $this->artisan('moon:sync-modules')->assertSuccessful();
    }
}
