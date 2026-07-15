<?php

declare(strict_types=1);

namespace Moon\ThemeKit;

use InvalidArgumentException;

final readonly class ThemeDefinition
{
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public string $progressColor,
        public string $path,
    ) {}

    /**
     * @param  array<string, mixed>  $manifest
     */
    public static function fromManifest(array $manifest, string $directory): self
    {
        $id = $manifest['id'] ?? null;
        $name = $manifest['name'] ?? null;
        $version = $manifest['version'] ?? null;
        $progressColor = $manifest['progress_color'] ?? null;
        $directoryId = basename($directory);

        if (! is_string($id) || ! preg_match('/^[a-z][a-z0-9-]*$/', $id)) {
            throw new InvalidArgumentException("Theme at [{$directory}] has an invalid id.");
        }

        if ($id !== $directoryId) {
            throw new InvalidArgumentException("Theme id [{$id}] must match directory [{$directoryId}].");
        }

        if (! is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException("Theme [{$id}] must define a name.");
        }

        if (! is_string($version) || trim($version) === '') {
            throw new InvalidArgumentException("Theme [{$id}] must define a version.");
        }

        if (! is_string($progressColor) || ! preg_match('/^#[0-9a-fA-F]{6}$/', $progressColor)) {
            throw new InvalidArgumentException("Theme [{$id}] must define progress_color as a six-digit hex color.");
        }

        return new self($id, $name, $version, strtolower($progressColor), $directory);
    }

    /**
     * @return array{id: string, name: string, version: string, progress_color: string}
     */
    public function metadata(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'version' => $this->version,
            'progress_color' => $this->progressColor,
        ];
    }
}
