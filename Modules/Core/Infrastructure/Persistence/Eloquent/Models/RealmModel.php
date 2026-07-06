<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class RealmModel extends Model
{
    protected $table = 'realms';

    protected $fillable = [
        'name',
        'slug',
        'core_type',
        'auth_database',
        'characters_database',
        'remote_console',
        'gm_realm_id',
        'enabled',
    ];

    protected $casts = [
        // Cifrado en reposo con APP_KEY: aqui viven contraseñas de BD y
        // de SOAP de cada reino. Ver README, seccion "Seguridad".
        'auth_database' => 'encrypted:array',
        'characters_database' => 'encrypted:array',
        'remote_console' => 'encrypted:array',
        'enabled' => 'boolean',
        'gm_realm_id' => 'integer',
    ];
}
