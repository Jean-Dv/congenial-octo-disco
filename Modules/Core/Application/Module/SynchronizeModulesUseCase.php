<?php

declare(strict_types=1);

namespace Modules\Core\Application\Module;

use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManager;

final class SynchronizeModulesUseCase
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly ModuleDependencyResolver $dependencies,
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    public function handle(): void
    {
        $states = $this->modules->enabledStates();

        foreach ($this->dependencies->ordered($this->moduleManager->discover()) as $manifest) {
            $enabledByDefault = array_filter(
                $manifest->dependencies,
                fn (string $dependency): bool => ! ($states[$dependency] ?? false),
            ) === [];

            $this->modules->synchronize($manifest, $enabledByDefault);

            if (! array_key_exists($manifest->slug, $states)) {
                $states[$manifest->slug] = $manifest->isCore || $enabledByDefault;
            } elseif ($manifest->isCore) {
                $states[$manifest->slug] = true;
            }
        }
    }
}
