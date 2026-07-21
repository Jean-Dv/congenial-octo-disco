<?php

declare(strict_types=1);

namespace Modules\Core\Application\Module;

use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;

final class ListModulesUseCase
{
    public function __construct(
        private readonly SynchronizeModulesUseCase $synchronizeModules,
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    /**
     * @return array<int, array{slug: string, name: string, description: string, version: string, is_core: bool, enabled: bool}>
     */
    public function handle(): array
    {
        $this->synchronizeModules->handle();

        return $this->modules->all();
    }
}
