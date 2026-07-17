<?php

declare(strict_types=1);

namespace Modules\Core\Application\Module;

use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleManager;

final class SynchronizeModulesUseCase
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    public function handle(): void
    {
        foreach ($this->moduleManager->discover() as $manifest) {
            $this->modules->synchronize($manifest);
        }
    }
}
