<?php

declare(strict_types=1);

namespace Moon\ThemeKit;

use Illuminate\Contracts\Config\Repository;
use Moon\ThemeKit\Contracts\ThemeSelectionProviderInterface;

final readonly class ConfigThemeSelectionProvider implements ThemeSelectionProviderInterface
{
    public function __construct(private Repository $config) {}

    public function selectedThemeId(): string
    {
        $id = $this->config->get('themes.active', 'aeris');

        return is_string($id) && $id !== '' ? $id : 'aeris';
    }
}
