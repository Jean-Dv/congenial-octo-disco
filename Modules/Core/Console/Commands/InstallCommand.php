<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Modules\Core\Application\Module\SynchronizeModulesUseCase;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManager;
use Throwable;

final class InstallCommand extends Command
{
    protected $signature = 'moon:install {--force : Force migrations to run in production}';

    protected $description = 'Install or update Moon CMS, its module registry, and all enabled module migrations';

    public function handle(
        SynchronizeModulesUseCase $synchronizeModules,
        ModuleManager $moduleManager,
        ModuleDependencyResolver $dependencies,
        ModuleRepositoryInterface $modules,
    ): int {
        try {
            if ($this->runMigrations() !== self::SUCCESS) {
                return self::FAILURE;
            }

            $synchronizeModules->handle();

            $enabled = $dependencies->enabled(
                $moduleManager->discover(),
                $modules->enabledStates(),
            );

            foreach ($enabled as $manifest) {
                if (! $manifest->isCore) {
                    $this->laravel->register($manifest->provider);
                }
            }

            Route::getRoutes()->refreshNameLookups();
            Route::getRoutes()->refreshActionLookups();

            if ($this->runMigrations() !== self::SUCCESS) {
                return self::FAILURE;
            }
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Moon CMS and enabled modules installed successfully.');

        return self::SUCCESS;
    }

    private function runMigrations(): int
    {
        return $this->call('migrate', [
            '--force' => (bool) $this->option('force'),
        ]);
    }
}
