<?php

declare(strict_types=1);

namespace Modules\News\Domain\Article;

enum NewsStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
