<?php

declare(strict_types=1);

namespace Modules\News\Application\Article;

use Modules\News\Domain\Article\NewsArticle;
use Modules\News\Domain\Article\NewsStatus;
use Modules\News\Domain\Article\Ports\NewsRepositoryInterface;

final readonly class UpdateNewsUseCase
{
    public function __construct(private NewsRepositoryInterface $news) {}

    public function handle(int $id, NewsInput $input, int $publisherId): NewsArticle
    {
        $article = $this->news->findById($id);
        abort_if($article === null, 404);

        $article->updateContent($input->title, $input->slug, $input->excerpt, $input->bodyMarkdown, $input->coverPath, $input->categoryId);

        if (NewsStatus::from($input->status) === NewsStatus::Published) {
            $article->publish($publisherId, $input->featured);
        } else {
            $article->unpublish();
        }

        return $this->news->save($article);
    }
}
