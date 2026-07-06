<?php

return [

    'default' => env('QUEUE_CONNECTION', 'redis'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 300,
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    // Colas usadas por el core. Cada modulo puede añadir las suyas.
    'moon_queues' => [
        'provisioning' => 'provisioning', // creacion/edicion de cuentas de juego por reino
        'mail' => 'mail',                 // verificacion de email, reseteo de password
    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],

];
