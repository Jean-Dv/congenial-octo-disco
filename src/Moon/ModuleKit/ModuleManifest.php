<?php

declare(strict_types=1);

namespace Moon\ModuleKit;

use InvalidArgumentException;

/**
 * Identidad declarativa de un modulo. Se construye a partir de su
 * module.json y es la unica fuente de verdad sobre "que modulos existen"
 * para el ModuleManager y para el panel de administracion.
 */
final class ModuleManifest
{
    /**
     * @param  array<int, string>  $dependencies  Slugs de otros modulos que este modulo necesita para funcionar.
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $description,
        public readonly string $version,
        public readonly string $provider,
        public readonly bool $isCore = false,
        public readonly array $dependencies = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['slug', 'name', 'version', 'provider'] as $required) {
            if (empty($data[$required])) {
                throw new InvalidArgumentException(
                    "El module.json es invalido: falta el campo obligatorio \"{$required}\"."
                );
            }
        }

        return new self(
            slug: $data['slug'],
            name: $data['name'],
            description: $data['description'] ?? '',
            version: $data['version'],
            provider: $data['provider'],
            isCore: (bool) ($data['is_core'] ?? false),
            dependencies: $data['dependencies'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'provider' => $this->provider,
            'is_core' => $this->isCore,
            'dependencies' => $this->dependencies,
        ];
    }
}
