<?php

declare(strict_types=1);

namespace Modules\News\Application;

use Illuminate\Support\Str;

final class MarkdownRenderer
{
    public function render(string $markdown): string
    {
        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
