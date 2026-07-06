<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Core\Infrastructure\Notifications\ResetPasswordNotification;
use Modules\Core\Infrastructure\Notifications\VerifyEmailNotification;

/**
 * Modelo Eloquent = detalle de infraestructura. El dominio nunca ve esta
 * clase directamente: solo la usa Modules\Core\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository
 * para traducir entre Modules\Core\Domain\Auth\User y esta fila de la BD `users`.
 *
 * Implementa los contratos de Laravel (Authenticatable, MustVerifyEmail,
 * CanResetPassword) porque el propio framework (guards, middleware
 * "verified", broker de passwords) los necesita para funcionar; esto es
 * intencional y no rompe el hexagono: son adaptadores hacia Laravel, no
 * el dominio filtrandose hacia afuera.
 */
class UserModel extends Authenticatable implements MustVerifyEmailContract, CanResetPassword
{
    use Notifiable;
    use CanResetPasswordTrait;
    use MustVerifyEmailTrait;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'email_verified_at',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
