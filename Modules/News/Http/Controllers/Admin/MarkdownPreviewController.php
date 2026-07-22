<?php

declare(strict_types=1);

namespace Modules\News\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\News\Application\MarkdownRenderer;

final class MarkdownPreviewController extends Controller
{
    public function __invoke(Request $request, MarkdownRenderer $renderer): JsonResponse
    {
        $data = $request->validate(['body_markdown' => ['nullable', 'string', 'max:1000000']]);

        return response()->json(['html' => $renderer->render($data['body_markdown'] ?? '')]);
    }
}
