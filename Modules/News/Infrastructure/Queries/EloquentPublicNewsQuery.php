<?php

declare(strict_types=1);

namespace Modules\News\Infrastructure\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\News\Application\MarkdownRenderer;
use Modules\News\Application\PublicNewsQueryInterface;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsCategoryModel;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsModel;

final readonly class EloquentPublicNewsQuery implements PublicNewsQueryInterface
{
    public function __construct(private MarkdownRenderer $markdown) {}

    public function latest(int $limit): array
    {
        return $this->publishedQuery()->limit($limit)->get()
            ->map(fn (NewsModel $article) => $this->present($article))->all();
    }

    public function featured(): ?array
    {
        $article = $this->publishedQuery()->where('is_featured', true)->first();

        return $article ? $this->present($article) : null;
    }

    public function archive(int $perPage, ?string $categorySlug): array
    {
        $query = $this->publishedQuery()->where('is_featured', false);

        if ($categorySlug) {
            $query->whereHas('category', fn (Builder $category) => $category->where('slug', $categorySlug));
        }

        $paginator = $query->paginate($perPage)->withQueryString();
        $paginator->through(fn (NewsModel $article) => $this->present($article));

        return $paginator->toArray();
    }

    public function categories(): array
    {
        return NewsCategoryModel::query()
            ->withCount(['articles' => fn (Builder $query) => $query->where('status', 'published')])
            ->whereHas('articles', fn (Builder $query) => $query->where('status', 'published'))
            ->orderBy('name')
            ->get()
            ->map(fn (NewsCategoryModel $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'articles_count' => $category->articles_count,
            ])->all();
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $article = $this->publishedQuery()->where('slug', $slug)->first();

        return $article ? [
            ...$this->present($article),
            'bodyHtml' => $this->markdown->render($article->body_markdown),
        ] : null;
    }

    private function publishedQuery(): Builder
    {
        return NewsModel::query()
            ->with(['category', 'publisher'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at');
    }

    private function present(NewsModel $article): array
    {
        $publisher = $article->publisher?->name ?? 'Usuario eliminado';
        $initials = Str::of($publisher)->explode(' ')->filter()->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');

        return [
            'id' => $article->id,
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'coverUrl' => Storage::disk('public')->url($article->cover_path),
            'category' => [
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ],
            'publishedAt' => $article->published_at?->toIso8601String(),
            'publishedAtLabel' => $article->published_at?->locale('es')->translatedFormat('d M, Y'),
            'author' => $publisher,
            'authorInitials' => $initials ?: '–',
            'isFeatured' => (bool) $article->is_featured,
        ];
    }
}
