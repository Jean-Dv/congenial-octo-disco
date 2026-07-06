<?php

return [
    App\Providers\AppServiceProvider::class,
    // El modulo Core se registra siempre, de forma incondicional: es el
    // unico modulo que no puede deshabilitarse (module.json: is_core=true).
    Modules\Core\Providers\CoreServiceProvider::class,
    // Este provider escanea Modules/*/module.json y registra el resto de
    // modulos (is_core=false) solo si estan marcados como "enabled" en BD.
    App\Providers\ModuleServiceProvider::class,
];
