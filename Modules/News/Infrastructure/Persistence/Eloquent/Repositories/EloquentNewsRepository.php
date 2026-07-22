<?php

declare(strict_types=1);

namespace Modules\News\Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Modules\News\Domain\Article\NewsArticle;
use Modules\News\Domain\Article\NewsStatus;
use Modules\News\Domain\Article\Ports\NewsRepositoryInterface;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsModel;

final class EloquentNewsRepository implements NewsRepositoryInterface
{
    public function all(): array
    {
        return NewsModel::query()->orderByDesc('created_at')->get()
            ->map(fn (NewsModel $model) => $this->toDomain($model))->all();
    }

    public function findById(int $id): ?NewsArticle
    {
        $model = NewsModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function save(NewsArticle $article): NewsArticle
    {
        DB::transaction(function () use ($article): void {
            if ($article->status() === NewsStatus::Published && $article->isFeatured()) {
                NewsModel::query()
                    ->where('is_featured', true)
                    ->when($article->id(), fn ($query, int $id) => $query->whereKeyNot($id))
                    ->update(['is_featured' => false]);
            }

            $model = $article->id() ? NewsModel::findOrFail($article->id()) : new NewsModel;
            $model->fill([
                'title' => $article->title(),
                'slug' => $article->slug(),
                'excerpt' => $article->excerpt(),
                'body_markdown' => $article->bodyMarkdown(),
                'cover_path' => $article->coverPath(),
                'category_id' => $article->categoryId(),
                'status' => $article->status()->value,
                'published_by' => $article->publishedBy(),
                'published_at' => $article->publishedAt(),
                'is_featured' => $article->isFeatured(),
            ]);
            $model->save();
            $article->assignId($model->id);
        });

        return $article;
    }

    public function delete(NewsArticle $article): void
    {
        if ($article->id()) {
            NewsModel::destroy($article->id());
        }
    }

    private function toDomain(NewsModel $model): NewsArticle
    {
        return new NewsArticle(
            id: $model->id,
            title: $model->title,
            slug: $model->slug,
            excerpt: $model->excerpt,
            bodyMarkdown: $model->body_markdown,
            coverPath: $model->cover_path,
            categoryId: (int) $model->category_id,
            status: NewsStatus::from($model->status),
            publishedBy: $model->published_by ? (int) $model->published_by : null,
            publishedAt: $model->published_at ? DateTimeImmutable::createFromInterface($model->published_at) : null,
            featured: (bool) $model->is_featured,
        );
    }
}
