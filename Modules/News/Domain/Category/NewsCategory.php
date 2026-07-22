<?php

declare(strict_types=1);

namespace Modules\News\Domain\Category;

final class NewsCategory
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $slug,
    ) {}

    public static function create(string $name, string $slug): self
    {
        return new self(null, $name, $slug);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function update(string $name, string $slug): void
    {
        $this->name = $name;
        $this->slug = $slug;
    }
}
