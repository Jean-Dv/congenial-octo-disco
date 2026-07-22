<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;
use Modules\News\Application\PublicNewsQueryInterface;

final class NewsController extends Controller
{
    public function __invoke(Request $request, PublicNewsQueryInterface $news): Response
    {
        $category = $request->string('category')->trim()->value() ?: null;

        return Inertia::render('Public/News/Index', [
            'featuredArticle' => $news->featured(),
            'articles' => $news->archive(8, $category),
            'categories' => $news->categories(),
            'activeCategory' => $category,
        ]);
    }
}
