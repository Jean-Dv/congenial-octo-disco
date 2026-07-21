<?php

declare(strict_types=1);

namespace Moon\ThemeKit\Contracts;

interface ThemeSelectionProviderInterface
{
    public function selectedThemeId(): string;
}
