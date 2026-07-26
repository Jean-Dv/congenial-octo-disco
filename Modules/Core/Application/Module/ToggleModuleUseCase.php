<?php

declare(strict_types=1);

namespace Modules\Core\Application\Module;

use DomainException;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleDependencyResolver;
use Moon\ModuleKit\ModuleManager;

final class ToggleModuleUseCase
{
    /**
     * @param  array<int, string>  $protectedSlugs  Slugs que jamas pueden deshabilitarse (ej. "core").
     */
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
        private readonly ModuleManager $moduleManager,
        private readonly ModuleDependencyResolver $dependencies,
        private readonly array $protectedSlugs = [],
    ) {}

    public function handle(string $slug, bool $enabled): void
    {
        $manifests = $this->moduleManager->discover();
        $manifest = $manifests[$slug] ?? null;

        if ($manifest === null) {
            throw new DomainException("El modulo \"{$slug}\" no existe.");
        }

        if (! $enabled && in_array($slug, $this->protectedSlugs, true)) {
            throw new DomainException("El modulo \"{$slug}\" es obligatorio y no puede deshabilitarse.");
        }

        $states = $this->modules->enabledStates();

        if ($enabled) {
            $disabledDependencies = array_values(array_filter(
                $this->dependencies->transitiveDependencies($slug, $manifests),
                fn (string $dependency): bool => ! ($states[$dependency] ?? false),
            ));

            if ($disabledDependencies !== []) {
                throw new DomainException(
                    "No se puede habilitar \"{$slug}\". Habilita primero: ".implode(', ', $disabledDependencies).'.'
                );
            }
        } else {
            $enabledDependants = [];

            foreach ($manifests as $candidate) {
                if (
                    $candidate->slug !== $slug
                    && ($states[$candidate->slug] ?? false)
                    && in_array($slug, $this->dependencies->transitiveDependencies($candidate->slug, $manifests), true)
                ) {
                    $enabledDependants[] = $candidate->slug;
                }
            }

            if ($enabledDependants !== []) {
                throw new DomainException(
                    "No se puede deshabilitar \"{$slug}\" porque lo requieren: ".implode(', ', $enabledDependants).'.'
                );
            }
        }

        $this->modules->setEnabled($slug, $enabled);
    }
}
