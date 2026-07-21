<?php

declare(strict_types=1);

namespace Modules\Public\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;

final class DownloadsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/Downloads/Index');
    }
}
