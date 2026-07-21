<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active theme
    |--------------------------------------------------------------------------
    |
    | The environment value is the deployment default. A future admin-backed
    | selection provider can take precedence without changing theme packages.
    |
    */
    'active' => env('APP_THEME', 'aeris'),

    // A safe, bundled theme used whenever the requested theme is unavailable.
    'fallback' => 'aeris',

    // Every direct child containing theme.json is treated as a theme bundle.
    'path' => resource_path('themes'),

];
