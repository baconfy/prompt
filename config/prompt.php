<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Prompt Driver
    |--------------------------------------------------------------------------
    |
    | The driver used when calling Prompt::get() without explicitly selecting
    | one. Switching per call is always available via Prompt::driver('name').
    |
    */

    'default' => env('PROMPTS_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Each entry defines a configurable prompt source. The array key is the
    | name used with Prompt::driver('name'); the inner 'driver' key is the
    | implementation type. Multiple entries can share the same type, e.g.
    | a 'system' file source and a 'user' database source.
    |
    */

    'drivers' => [

        'file' => [
            'driver' => 'file',
            'folder' => env('PROMPTS_FOLDER', resource_path('prompts')),
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('PROMPTS_CONNECTION'),
            'table' => env('PROMPTS_TABLE', 'prompts'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    |
    | Optional admin panel for managing prompts stored in the database driver.
    | The panel ships as plain Blade views styled with Tailwind via CDN — no
    | front-end build step is required. Routes are registered when 'enabled'
    | is true. Access is gated by 'gate' (or the callback registered via
    | Baconfy\Prompt\Panel::auth()).
    |
    */

    'panel' => [
        'enabled' => env('PROMPTS_PANEL_ENABLED', true),
        'path' => env('PROMPTS_PANEL_PATH', '_prompts'),
        'gate' => 'viewPrompts',
        'middleware' => ['web'],
    ],

];
