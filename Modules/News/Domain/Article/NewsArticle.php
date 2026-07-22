<?php

declare(strict_types=1);

namespace Modules\News\Domain\Article;

use DateTimeImmutable;

final class NewsArticle
{
    public function __construct(
        private ?int $id,
        private string $title,
        private string $slug,
        private string $excerpt,
        private string $bodyMarkdown,
        private string $coverPath,
        private int $categoryId,
        private NewsStatus $status,
        private ?int $publishedBy,
        private ?DateTimeImmutable $publishedAt,
        private bool $featured,
    ) {}

    public static function draft(
        string $title,
        string $slug,
        string $excerpt,
        string $bodyMarkdown,
        string $coverPath,
        int $categoryId,
    ): self {
        return new self(null, $title, $slug, $excerpt, $bodyMarkdown, $coverPath, $categoryId, NewsStatus::Draft, null, null, false);
    }

    public function updateContent(string $title, string $slug, string $excerpt, string $bodyMarkdown, string $coverPath, int $categoryId): void
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->excerpt = $excerpt;
        $this->bodyMarkdown = $bodyMarkdown;
        $this->coverPath = $coverPath;
        $this->categoryId = $categoryId;
    }

    public function publish(int $publisherId, bool $featured): void
    {
        if ($this->status === NewsStatus::Draft) {
            $this->publishedBy = $publisherId;
            $this->publishedAt = new DateTimeImmutable;
        }

        $this->status = NewsStatus::Published;
        $this->featured = $featured;
    }

    public function unpublish(): void
    {
        $this->status = NewsStatus::Draft;
        $this->publishedBy = null;
        $this->publishedAt = null;
        $this->featured = false;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function excerpt(): string
    {
        return $this->excerpt;
    }

    public function bodyMarkdown(): string
    {
        return $this->bodyMarkdown;
    }

    public function coverPath(): string
    {
        return $this->coverPath;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function status(): NewsStatus
    {
        return $this->status;
    }

    public function publishedBy(): ?int
    {
        return $this->publishedBy;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }
}
