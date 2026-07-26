<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Moon\ModuleKit\Contracts\ModuleRepositoryInterface;
use Moon\ModuleKit\ModuleManifest;

final class EloquentModuleRepository implements ModuleRepositoryInterface
{
    public function synchronize(ModuleManifest $manifest, bool $enabledByDefault = true): void
    {
        $model = ModuleModel::find($manifest->slug);

        if ($model === null) {
            ModuleModel::create([
                'slug' => $manifest->slug,
                'name' => $manifest->name,
                'description' => $manifest->description,
                'version' => $manifest->version,
                'is_core' => $manifest->isCore,
                // Solo abre habilitado si su cadena de dependencias lo permite.
                'enabled' => $manifest->isCore || $enabledByDefault,
            ]);

            return;
        }

        $model->name = $manifest->name;
        $model->description = $manifest->description;
        $model->version = $manifest->version;
        $model->is_core = $manifest->isCore;

        if ($manifest->isCore) {
            $model->enabled = true;
        }

        $model->save();
    }

    public function isEnabled(string $slug): bool
    {
        return (bool) (ModuleModel::where('slug', $slug)->value('enabled') ?? false);
    }

    public function setEnabled(string $slug, bool $enabled): void
    {
        ModuleModel::where('slug', $slug)->update(['enabled' => $enabled]);
    }

    public function enabledStates(): array
    {
        return ModuleModel::query()
            ->pluck('enabled', 'slug')
            ->map(fn (mixed $enabled): bool => (bool) $enabled)
            ->all();
    }

    public function all(): array
    {
        return ModuleModel::orderByDesc('is_core')->orderBy('name')->get()
            ->map(fn (ModuleModel $model) => [
                'slug' => $model->slug,
                'name' => $model->name,
                'description' => $model->description,
                'version' => $model->version,
                'is_core' => (bool) $model->is_core,
                'enabled' => (bool) $model->enabled,
            ])
            ->all();
    }
}
