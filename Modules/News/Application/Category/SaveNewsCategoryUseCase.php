<?php

declare(strict_types=1);

namespace Modules\News\Application\Category;

use Modules\News\Domain\Category\NewsCategory;
use Modules\News\Domain\Category\Ports\NewsCategoryRepositoryInterface;

final readonly class SaveNewsCategoryUseCase
{
    public function __construct(private NewsCategoryRepositoryInterface $categories) {}

    public function handle(string $name, string $slug, ?int $id = null): NewsCategory
    {
        $category = $id ? $this->categories->findById($id) : NewsCategory::create($name, $slug);
        abort_if($category === null, 404);

        if ($id) {
            $category->update($name, $slug);
        }

        return $this->categories->save($category);
    }
}
