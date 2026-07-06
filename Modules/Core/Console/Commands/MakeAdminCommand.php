<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * php artisan moon:make-admin correo@ejemplo.com
 *
 * Unica forma de otorgar acceso a /admin (realms, modulos) en esta
 * version: no hay UI para promover administradores todavia, a proposito
 * (evita que un admin mal configurado se auto-otorgue permisos desde el
 * propio panel).
 */
final class MakeAdminCommand extends Command
{
    protected $signature = 'moon:make-admin {email : Correo del usuario a promover}';

    protected $description = 'Otorga acceso de administrador del panel (/admin) a un usuario existente.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = UserModel::where('email', $email)->first();

        if ($user === null) {
            $this->error("No existe ningun usuario del panel con el correo \"{$email}\".");

            return self::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("\"{$email}\" ahora es administrador del panel.");

        return self::SUCCESS;
    }
}
