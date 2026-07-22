<?php

declare(strict_types=1);

namespace Modules\News\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;
use Modules\News\Application\Article\CreateNewsUseCase;
use Modules\News\Application\Article\DeleteNewsUseCase;
use Modules\News\Application\Article\NewsInput;
use Modules\News\Application\Article\UpdateNewsUseCase;
use Modules\News\Http\Requests\NewsRequest;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsCategoryModel;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use Throwable;

final class NewsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('News/Admin/News/Index', [
            'articles' => NewsModel::query()->with(['category', 'publisher'])->orderByDesc('created_at')->get()->map(fn (NewsModel $article) => $this->present($article)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('News/Admin/News/Create', ['categories' => $this->categories()]);
    }

    public function store(NewsRequest $request, CreateNewsUseCase $useCase): RedirectResponse
    {
        $data = $request->validated();
        $coverPath = $request->file('cover')->store('news', 'public');

        try {
            $useCase->handle($this->input($data, $coverPath), (int) $request->user()->id);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($coverPath);
            throw $exception;
        }

        return redirect()->route('admin.news.index')->with('success', 'Noticia creada correctamente.');
    }

    public function edit(int $news): Response
    {
        $article = NewsModel::query()->with(['category', 'publisher'])->findOrFail($news);

        return Inertia::render('News/Admin/News/Edit', [
            'article' => $this->present($article, true),
            'categories' => $this->categories(),
        ]);
    }

    public function update(NewsRequest $request, int $news, UpdateNewsUseCase $useCase): RedirectResponse
    {
        $existing = NewsModel::findOrFail($news);
        $oldCover = $existing->cover_path;
        $newCover = $request->hasFile('cover') ? $request->file('cover')->store('news', 'public') : null;

        try {
            $useCase->handle($news, $this->input($request->validated(), $newCover ?? $oldCover), (int) $request->user()->id);
        } catch (Throwable $exception) {
            if ($newCover) {
                Storage::disk('public')->delete($newCover);
            }
            throw $exception;
        }

        if ($newCover) {
            Storage::disk('public')->delete($oldCover);
        }

        return redirect()->route('admin.news.index')->with('success', 'Noticia actualizada correctamente.');
    }

    public function destroy(int $news, DeleteNewsUseCase $useCase): RedirectResponse
    {
        Storage::disk('public')->delete($useCase->handle($news));

        return redirect()->route('admin.news.index')->with('success', 'Noticia eliminada correctamente.');
    }

    private function input(array $data, string $coverPath): NewsInput
    {
        return new NewsInput(
            title: $data['title'], slug: $data['slug'], excerpt: $data['excerpt'],
            bodyMarkdown: $data['body_markdown'], coverPath: $coverPath,
            categoryId: (int) $data['category_id'], status: $data['status'],
            featured: (bool) ($data['is_featured'] ?? false),
        );
    }

    private function categories(): array
    {
        return NewsCategoryModel::query()->orderBy('name')->get(['id', 'name', 'slug'])->toArray();
    }

    private function present(NewsModel $article, bool $full = false): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'body_markdown' => $full ? $article->body_markdown : null,
            'cover_url' => Storage::disk('public')->url($article->cover_path),
            'category_id' => $article->category_id,
            'category' => $article->category?->name,
            'status' => $article->status,
            'is_featured' => (bool) $article->is_featured,
            'author' => $article->publisher?->name,
            'published_at' => $article->published_at?->toIso8601String(),
        ];
    }
}
