<?php

declare(strict_types=1);

namespace Moon\ModuleKit\Contracts;

use Moon\ModuleKit\ModuleManifest;

/**
 * Puerto de persistencia para el registro de modulos. La implementacion
 * concreta (Eloquent contra Postgres) vive en el modulo Core, ya que es
 * el modulo Core quien "posee" la tabla `modules` y su panel de admin.
 */
interface ModuleRepositoryInterface
{
    /**
     * Da de alta (o actualiza los metadatos de) un modulo detectado en
     * disco. Si el modulo es nuevo, queda habilitado segun
     * $enabledByDefault (normalmente true si sus dependencias lo permiten), salvo
     * que sea explicitamente marcado is_core, en cuyo caso siempre esta
     * habilitado y no es alternable desde el panel.
     */
    public function synchronize(ModuleManifest $manifest, bool $enabledByDefault = true): void;

    public function isEnabled(string $slug): bool;

    public function setEnabled(string $slug, bool $enabled): void;

    /**
     * @return array<string, bool>
     */
    public function enabledStates(): array;

    /**
     * @return array<int, array{slug: string, name: string, description: string, version: string, is_core: bool, enabled: bool}>
     */
    public function all(): array;
}
