<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Route;

trait BootsNewsModule
{
    protected function bootNewsModule(): void
    {
        $this->artisan('moon:install')->assertSuccessful();
        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
    }
}
