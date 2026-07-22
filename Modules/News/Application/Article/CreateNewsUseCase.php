<?php

declare(strict_types=1);

namespace Modules\News\Application\Article;

use Modules\News\Domain\Article\NewsArticle;
use Modules\News\Domain\Article\NewsStatus;
use Modules\News\Domain\Article\Ports\NewsRepositoryInterface;

final readonly class CreateNewsUseCase
{
    public function __construct(private NewsRepositoryInterface $news) {}

    public function handle(NewsInput $input, int $publisherId): NewsArticle
    {
        $article = NewsArticle::draft(
            $input->title,
            $input->slug,
            $input->excerpt,
            $input->bodyMarkdown,
            $input->coverPath,
            $input->categoryId,
        );

        if (NewsStatus::from($input->status) === NewsStatus::Published) {
            $article->publish($publisherId, $input->featured);
        }

        return $this->news->save($article);
    }
}
