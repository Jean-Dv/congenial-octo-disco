<?php

declare(strict_types=1);

namespace Modules\News\Application\Category;

use DomainException;
use Modules\News\Domain\Category\Ports\NewsCategoryRepositoryInterface;

final readonly class DeleteNewsCategoryUseCase
{
    public function __construct(private NewsCategoryRepositoryInterface $categories) {}

    public function handle(int $id): void
    {
        $category = $this->categories->findById($id);
        abort_if($category === null, 404);

        if ($this->categories->isInUse($id)) {
            throw new DomainException('No puedes eliminar una categoria que tiene noticias asociadas.');
        }

        $this->categories->delete($category);
    }
}
