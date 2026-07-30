<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sonda TCP del reino
    |--------------------------------------------------------------------------
    |
    | Tiempo máximo para establecer la conexión TCP con address:port de la
    | tabla realmlist. La sonda no autentica ni intercambia datos del juego.
    |
    */
    'latency_timeout' => (float) env('REALMLIST_LATENCY_TIMEOUT', 1),
];
