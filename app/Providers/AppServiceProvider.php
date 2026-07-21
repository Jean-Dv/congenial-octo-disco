<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Moon\ThemeKit\ActiveThemeResolver;
use Moon\ThemeKit\ConfigThemeSelectionProvider;
use Moon\ThemeKit\Contracts\ThemeSelectionProviderInterface;
use Moon\ThemeKit\ThemeRegistry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ThemeSelectionProviderInterface::class, ConfigThemeSelectionProvider::class);

        $this->app->singleton(ThemeRegistry::class, fn ($app) => new ThemeRegistry(
            $app['files'],
            $app['log'],
            (string) $app['config']->get('themes.path', resource_path('themes')),
        ));

        $this->app->scoped(ActiveThemeResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
