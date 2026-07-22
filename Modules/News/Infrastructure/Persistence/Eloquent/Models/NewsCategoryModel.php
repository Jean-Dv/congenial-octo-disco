<?php

declare(strict_types=1);

namespace Modules\News\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NewsCategoryModel extends Model
{
    protected $table = 'news_categories';

    protected $fillable = ['name', 'slug'];

    public function articles(): HasMany
    {
        return $this->hasMany(NewsModel::class, 'category_id');
    }
}
