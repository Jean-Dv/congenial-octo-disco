<?php

declare(strict_types=1);

namespace Modules\News\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class NewsModel extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body_markdown', 'cover_path', 'category_id',
        'status', 'published_by', 'published_at', 'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategoryModel::class, 'category_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'published_by');
    }
}
