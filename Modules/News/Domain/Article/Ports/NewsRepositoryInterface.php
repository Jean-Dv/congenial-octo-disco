<?php

declare(strict_types=1);

namespace Modules\News\Domain\Article\Ports;

use Modules\News\Domain\Article\NewsArticle;

interface NewsRepositoryInterface
{
    /** @return array<int, NewsArticle> */
    public function all(): array;

    public function findById(int $id): ?NewsArticle;

    public function save(NewsArticle $article): NewsArticle;

    public function delete(NewsArticle $article): void;
}
