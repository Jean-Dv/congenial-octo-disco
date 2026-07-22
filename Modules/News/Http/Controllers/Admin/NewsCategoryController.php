<?php

declare(strict_types=1);

namespace Modules\News\Http\Controllers\Admin;

use DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;
use Modules\News\Application\Category\DeleteNewsCategoryUseCase;
use Modules\News\Application\Category\SaveNewsCategoryUseCase;
use Modules\News\Http\Requests\NewsCategoryRequest;
use Modules\News\Infrastructure\Persistence\Eloquent\Models\NewsCategoryModel;

final class NewsCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('News/Admin/Categories/Index', [
            'categories' => NewsCategoryModel::query()->withCount('articles')->orderBy('name')->get(),
        ]);
    }

    public function store(NewsCategoryRequest $request, SaveNewsCategoryUseCase $useCase): RedirectResponse
    {
        $useCase->handle($request->validated('name'), $request->validated('slug'));

        return back()->with('success', 'Categoria creada correctamente.');
    }

    public function update(NewsCategoryRequest $request, int $category, SaveNewsCategoryUseCase $useCase): RedirectResponse
    {
        $useCase->handle($request->validated('name'), $request->validated('slug'), $category);

        return back()->with('success', 'Categoria actualizada correctamente.');
    }

    public function destroy(int $category, DeleteNewsCategoryUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->handle($category);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Categoria eliminada correctamente.');
    }
}
