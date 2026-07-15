<?php

declare(strict_types=1);

namespace Moon\ThemeKit;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use JsonException;
use Throwable;

final class ThemeRegistry
{
    /** @var array<string, ThemeDefinition>|null */
    private ?array $themes = null;

    public function __construct(
        private readonly Filesystem $files,
        private readonly LogManager $log,
        private readonly string $themesPath,
    ) {}

    /**
     * @return array<string, ThemeDefinition>
     */
    public function all(): array
    {
        if ($this->themes !== null) {
            return $this->themes;
        }

        $this->themes = [];

        if (! $this->files->isDirectory($this->themesPath)) {
            $this->log->warning('Theme directory does not exist.', ['path' => $this->themesPath]);

            return $this->themes;
        }

        foreach ($this->files->directories($this->themesPath) as $directory) {
            $manifestPath = $directory.'/theme.json';

            if (! $this->files->isFile($manifestPath)) {
                continue;
            }

            try {
                $manifest = json_decode(
                    $this->files->get($manifestPath),
                    true,
                    flags: JSON_THROW_ON_ERROR,
                );

                if (! is_array($manifest)) {
                    throw new JsonException('Theme manifest must decode to an object.');
                }

                $theme = ThemeDefinition::fromManifest($manifest, $directory);

                foreach (['index.js', 'theme.css'] as $requiredFile) {
                    if (! $this->files->isFile($directory.'/'.$requiredFile)) {
                        throw new JsonException("Theme [{$theme->id}] is missing {$requiredFile}.");
                    }
                }

                $this->themes[$theme->id] = $theme;
            } catch (Throwable $exception) {
                $this->log->warning('Ignoring invalid theme manifest.', [
                    'manifest' => $manifestPath,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        ksort($this->themes);

        return $this->themes;
    }

    public function find(string $id): ?ThemeDefinition
    {
        return $this->all()[$id] ?? null;
    }
}
