<?php

declare(strict_types=1);

namespace Moon\ModuleKit;

/**
 * Descubre los modulos instalados escaneando Modules/{Nombre}/module.json.
 * Deliberadamente no toca el contenedor de Laravel ni la base de datos:
 * eso es responsabilidad de App\Providers\ModuleServiceProvider, que es
 * quien decide, con esta informacion, que ServiceProviders registrar.
 * Mantenerlo asi permite testear el descubrimiento de modulos con un
 * simple directorio temporal, sin arrancar la aplicacion completa.
 */
final class ModuleManager
{
    /** @var array<string, ModuleManifest>|null */
    private ?array $manifests = null;

    public function __construct(
        private readonly string $modulesPath,
    ) {
    }

    /**
     * @return array<string, ModuleManifest> Manifiestos indexados por slug.
     */
    public function discover(): array
    {
        if ($this->manifests !== null) {
            return $this->manifests;
        }

        $manifests = [];

        foreach (glob(rtrim($this->modulesPath, '/').'/*/module.json') ?: [] as $manifestPath) {
            $raw = json_decode(file_get_contents($manifestPath) ?: '', true);

            if (! is_array($raw)) {
                continue;
            }

            $manifest = ModuleManifest::fromArray($raw);
            $manifests[$manifest->slug] = $manifest;
        }

        return $this->manifests = $manifests;
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function core(): array
    {
        return array_filter($this->discover(), fn (ModuleManifest $m) => $m->isCore);
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function nonCore(): array
    {
        return array_filter($this->discover(), fn (ModuleManifest $m) => ! $m->isCore);
    }

    public function find(string $slug): ?ModuleManifest
    {
        return $this->discover()[$slug] ?? null;
    }
}
