<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;
use Modules\News\Application\PublicNewsQueryInterface;

final class NewsDetailController extends Controller
{
    public function __invoke(string $slug, PublicNewsQueryInterface $news): Response
    {
        $article = $news->findPublishedBySlug($slug);
        abort_if($article === null, 404);

        return Inertia::render('Public/News/Show', ['article' => $article]);
    }
}
