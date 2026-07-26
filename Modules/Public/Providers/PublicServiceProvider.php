<?php

declare(strict_types=1);

namespace Modules\Public\Providers;

use Moon\ModuleKit\AbstractModule;
use Moon\ModuleKit\ModuleManifest;

final class PublicServiceProvider extends AbstractModule
{
    public function manifest(): ModuleManifest
    {
        $raw = json_decode(
            file_get_contents($this->moduleBasePath().'/module.json') ?: '{}',
            true,
        );

        return ModuleManifest::fromArray($raw);
    }
}
