<?php

declare(strict_types=1);

namespace Modules\News\Application\Article;

use Modules\News\Domain\Article\Ports\NewsRepositoryInterface;

final readonly class DeleteNewsUseCase
{
    public function __construct(private NewsRepositoryInterface $news) {}

    public function handle(int $id): string
    {
        $article = $this->news->findById($id);
        abort_if($article === null, 404);
        $coverPath = $article->coverPath();
        $this->news->delete($article);

        return $coverPath;
    }
}
