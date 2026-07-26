<?php

declare(strict_types=1);

namespace Tests\Feature;

use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Application\Module\ToggleModuleUseCase;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManager;
use Tests\TestCase;

final class ModuleDependencyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_module_cannot_be_enabled_while_a_dependency_is_disabled(): void
    {
        $this->artisan('moon:sync-modules')->assertSuccessful();
        ModuleModel::query()->where('slug', 'core')->update(['enabled' => false]);
        ModuleModel::query()->where('slug', 'news')->update(['enabled' => false]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Habilita primero: core');

        $this->toggleUseCase()->handle('news', true);
    }

    public function test_a_module_cannot_be_disabled_while_enabled_modules_depend_on_it(): void
    {
        $this->artisan('moon:sync-modules')->assertSuccessful();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('lo requieren:');

        $this->toggleUseCase()->handle('core', false);
    }

    private function toggleUseCase(): ToggleModuleUseCase
    {
        return new ToggleModuleUseCase(
            $this->app->make(ModuleRepositoryInterface::class),
            $this->app->make(ModuleManager::class),
            $this->app->make(ModuleDependencyResolver::class),
        );
    }
}
