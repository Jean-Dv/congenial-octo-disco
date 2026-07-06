<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    // El guard "web" autentica exclusivamente contra la tabla `users` del
    // panel (Postgres). Nunca contra la tabla `account` de un reino: esa
    // separacion es deliberada (ver README, seccion "Identidad").
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => Modules\Core\Infrastructure\Persistence\Eloquent\Models\UserModel::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
