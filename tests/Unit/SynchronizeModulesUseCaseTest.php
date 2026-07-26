<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\Core\Application\Module\SynchronizeModulesUseCase;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManager;
use Moon\ModuleKit\ModuleManifest;
use PHPUnit\Framework\TestCase;

final class SynchronizeModulesUseCaseTest extends TestCase
{
    public function test_it_synchronizes_every_discovered_module(): void
    {
        $directory = sys_get_temp_dir().'/moon-modules-'.bin2hex(random_bytes(8));
        $moduleDirectory = $directory.'/Public';
        mkdir($moduleDirectory, 0777, true);

        file_put_contents($moduleDirectory.'/module.json', json_encode([
            'slug' => 'public',
            'name' => 'Public',
            'description' => 'Public website',
            'version' => '1.0.0',
            'is_core' => false,
            'provider' => 'Modules\\Public\\Providers\\PublicServiceProvider',
            'dependencies' => [],
        ], JSON_THROW_ON_ERROR));

        try {
            $repository = $this->createMock(ModuleRepositoryInterface::class);
            $repository->method('enabledStates')->willReturn([]);
            $repository->expects($this->once())
                ->method('synchronize')
                ->with($this->callback(
                    fn (ModuleManifest $manifest): bool => $manifest->slug === 'public',
                ), true);

            $useCase = new SynchronizeModulesUseCase(
                new ModuleManager($directory),
                new ModuleDependencyResolver,
                $repository,
            );

            $useCase->handle();
        } finally {
            unlink($moduleDirectory.'/module.json');
            rmdir($moduleDirectory);
            rmdir($directory);
        }
    }
}
