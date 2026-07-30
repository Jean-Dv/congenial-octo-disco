<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Consulta pública del estado
    |--------------------------------------------------------------------------
    |
    | El home no debe quedar bloqueado durante el timeout general de SOAP si
    | el worldserver está apagado. Los demás comandos conservan su timeout.
    |
    */
    'status_connection_timeout' => (int) env('REMOTE_CONSOLE_STATUS_TIMEOUT', 3),
];
