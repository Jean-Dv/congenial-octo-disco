<?php

declare(strict_types=1);

namespace Moon\ModuleKit;

use DomainException;

final class ModuleDependencyResolver
{
    /**
     * @param  array<string, ModuleManifest>  $manifests
     * @return array<string, ModuleManifest>
     */
    public function ordered(array $manifests): array
    {
        $ordered = [];
        $visiting = [];
        $visited = [];

        foreach ($manifests as $manifest) {
            $this->visit($manifest->slug, $manifests, $visiting, $visited, $ordered);
        }

        return $ordered;
    }

    /**
     * @param  array<string, ModuleManifest>  $manifests
     * @param  array<string, bool>  $enabledStates
     * @return array<string, ModuleManifest>
     */
    public function enabled(array $manifests, array $enabledStates): array
    {
        $enabled = [];

        foreach ($this->ordered($manifests) as $slug => $manifest) {
            $dependenciesEnabled = array_filter(
                $manifest->dependencies,
                fn (string $dependency): bool => ! isset($enabled[$dependency]),
            ) === [];

            if (($enabledStates[$slug] ?? false) && $dependenciesEnabled) {
                $enabled[$slug] = $manifest;
            }
        }

        return $enabled;
    }

    /**
     * @param  array<string, ModuleManifest>  $manifests
     * @return array<int, string>
     */
    public function transitiveDependencies(string $slug, array $manifests): array
    {
        $this->ordered($manifests);

        if (! isset($manifests[$slug])) {
            throw new DomainException("El modulo \"{$slug}\" no existe.");
        }

        $dependencies = [];
        $collect = function (string $current) use (&$collect, &$dependencies, $manifests): void {
            foreach ($manifests[$current]->dependencies as $dependency) {
                $dependencies[$dependency] = true;
                $collect($dependency);
            }
        };
        $collect($slug);

        return array_keys($dependencies);
    }

    /**
     * @param  array<string, ModuleManifest>  $manifests
     * @param  array<string, bool>  $visiting
     * @param  array<string, bool>  $visited
     * @param  array<string, ModuleManifest>  $ordered
     */
    private function visit(
        string $slug,
        array $manifests,
        array &$visiting,
        array &$visited,
        array &$ordered,
    ): void {
        if (isset($visited[$slug])) {
            return;
        }

        if (isset($visiting[$slug])) {
            throw new DomainException("Se detecto una dependencia circular que incluye al modulo \"{$slug}\".");
        }

        if (! isset($manifests[$slug])) {
            throw new DomainException("El modulo requerido \"{$slug}\" no existe.");
        }

        $visiting[$slug] = true;
        $manifest = $manifests[$slug];

        foreach ($manifest->dependencies as $dependency) {
            if ($dependency === $slug) {
                throw new DomainException("El modulo \"{$slug}\" no puede depender de si mismo.");
            }

            if (! isset($manifests[$dependency])) {
                throw new DomainException(
                    "El modulo \"{$slug}\" requiere el modulo ausente \"{$dependency}\"."
                );
            }

            $this->visit($dependency, $manifests, $visiting, $visited, $ordered);
        }

        unset($visiting[$slug]);
        $visited[$slug] = true;
        $ordered[$slug] = $manifest;
    }
}
