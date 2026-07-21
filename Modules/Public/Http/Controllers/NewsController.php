<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;

final class NewsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/News/Index');
    }
}
