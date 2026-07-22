<?php

declare(strict_types=1);

namespace Modules\News\Application;

interface PublicNewsQueryInterface
{
    /** @return array<int, array<string, mixed>> */
    public function latest(int $limit): array;

    /** @return array<string, mixed>|null */
    public function featured(): ?array;

    /** @return array<string, mixed> */
    public function archive(int $perPage, ?string $categorySlug): array;

    /** @return array<int, array<string, mixed>> */
    public function categories(): array;

    /** @return array<string, mixed>|null */
    public function findPublishedBySlug(string $slug): ?array;
}
