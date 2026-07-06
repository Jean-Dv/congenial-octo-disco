<?php

declare(strict_types=1);

namespace Modules\Core\Application\Module;

use DomainException;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;

final class ToggleModuleUseCase
{
    /**
     * @param  array<int, string>  $protectedSlugs  Slugs que jamas pueden deshabilitarse (ej. "core").
     */
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
        private readonly array $protectedSlugs = [],
    ) {
    }

    public function handle(string $slug, bool $enabled): void
    {
        if (! $enabled && in_array($slug, $this->protectedSlugs, true)) {
            throw new DomainException("El modulo \"{$slug}\" es obligatorio y no puede deshabilitarse.");
        }

        $this->modules->setEnabled($slug, $enabled);
    }
}
