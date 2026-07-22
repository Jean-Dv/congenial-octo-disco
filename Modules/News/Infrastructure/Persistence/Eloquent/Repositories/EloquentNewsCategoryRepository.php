<?php

declare(strict_types=1);

namespace Modules\News\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\News\Domain\Category\NewsCategory;
use Modules\News\Domain\Category\Ports\NewsCategoryRepositoryInterface;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsCategoryModel;

final class EloquentNewsCategoryRepository implements NewsCategoryRepositoryInterface
{
    public function all(): array
    {
        return NewsCategoryModel::query()->orderBy('name')->get()
            ->map(fn (NewsCategoryModel $model) => $this->toDomain($model))->all();
    }

    public function findById(int $id): ?NewsCategory
    {
        $model = NewsCategoryModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function save(NewsCategory $category): NewsCategory
    {
        $model = $category->id() ? NewsCategoryModel::findOrFail($category->id()) : new NewsCategoryModel;
        $model->fill(['name' => $category->name(), 'slug' => $category->slug()]);
        $model->save();
        $category->assignId($model->id);

        return $category;
    }

    public function delete(NewsCategory $category): void
    {
        if ($category->id()) {
            NewsCategoryModel::destroy($category->id());
        }
    }

    public function isInUse(int $id): bool
    {
        return NewsCategoryModel::find($id)?->articles()->exists() ?? false;
    }

    private function toDomain(NewsCategoryModel $model): NewsCategory
    {
        return new NewsCategory($model->id, $model->name, $model->slug);
    }
}
