<?php

declare(strict_types=1);

namespace Modules\News\Domain\Category\Ports;

use Modules\News\Domain\Category\NewsCategory;

interface NewsCategoryRepositoryInterface
{
    /** @return array<int, NewsCategory> */
    public function all(): array;

    public function findById(int $id): ?NewsCategory;

    public function save(NewsCategory $category): NewsCategory;

    public function delete(NewsCategory $category): void;

    public function isInUse(int $id): bool;
}
