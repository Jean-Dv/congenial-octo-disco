<?php

return [

    // Carpeta donde vive cada modulo. Cualquier subcarpeta con un
    // module.json valido es detectada automaticamente por el
    // Moon\ModuleKit\ModuleManager, sin tocar ningun archivo de config.
    'path' => base_path('Modules'),

    // Slugs que jamas pueden deshabilitarse desde el panel, sin importar
    // lo que diga la tabla `modules`. "core" siempre debe estar activo:
    // sin el no hay identidad, ni reinos, ni sistema de modulos.
    'protected' => ['core'],

];
