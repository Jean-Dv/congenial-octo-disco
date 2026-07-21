<?php

declare(strict_types=1);

namespace Moon\ThemeKit;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Log\LogManager;
use LogicException;
use Moon\ThemeKit\Contracts\ThemeSelectionProviderInterface;

final class ActiveThemeResolver
{
    private ?ThemeDefinition $resolved = null;

    public function __construct(
        private readonly ThemeRegistry $registry,
        private readonly ThemeSelectionProviderInterface $selection,
        private readonly Repository $config,
        private readonly LogManager $log,
    ) {}

    public function resolve(): ThemeDefinition
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $requestedId = $this->selection->selectedThemeId();
        $requested = $this->registry->find($requestedId);

        if ($requested !== null) {
            return $this->resolved = $requested;
        }

        $fallbackId = (string) $this->config->get('themes.fallback', 'aeris');
        $fallback = $this->registry->find($fallbackId);

        if ($fallback === null) {
            throw new LogicException("Configured theme [{$requestedId}] and fallback theme [{$fallbackId}] are unavailable.");
        }

        $this->log->warning('Configured theme is unavailable; using fallback theme.', [
            'requested_theme' => $requestedId,
            'fallback_theme' => $fallbackId,
        ]);

        return $this->resolved = $fallback;
    }
}
