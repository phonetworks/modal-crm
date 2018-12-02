<?php

return [
    'driver' => env('DB_DRIVER', 'mysql'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '3306'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'database' => env('DB_DATABASE'),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => env('DB_PREFIX', ''),
];
