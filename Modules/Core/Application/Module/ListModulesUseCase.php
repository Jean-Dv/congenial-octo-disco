<?php

declare(strict_types=1);

namespace Modules\Core\Application\Module;

use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleManager;

final class ListModulesUseCase
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly ModuleRepositoryInterface $modules,
    ) {
    }

    /**
     * @return array<int, array{slug: string, name: string, description: string, version: string, is_core: bool, enabled: bool}>
     */
    public function handle(): array
    {
        // Sincroniza lo detectado en disco con la tabla `modules` antes de
        // listar: asi un modulo nuevo (carpeta añadida sin tocar nada mas)
        // aparece automaticamente, habilitado por defecto.
        foreach ($this->moduleManager->discover() as $manifest) {
            $this->modules->synchronize($manifest);
        }

        return $this->modules->all();
    }
}
