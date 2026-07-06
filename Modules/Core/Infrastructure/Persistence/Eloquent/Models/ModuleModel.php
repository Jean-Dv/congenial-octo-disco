<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleModel extends Model
{
    protected $table = 'modules';

    public $incrementing = false;

    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'version',
        'is_core',
        'enabled',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'enabled' => 'boolean',
    ];
}
