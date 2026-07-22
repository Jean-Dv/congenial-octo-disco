<?php

declare(strict_types=1);

namespace Modules\News\Application\Article;

final readonly class NewsInput
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $excerpt,
        public string $bodyMarkdown,
        public string $coverPath,
        public int $categoryId,
        public string $status,
        public bool $featured,
    ) {}
}
