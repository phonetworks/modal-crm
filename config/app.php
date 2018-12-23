<?php

return [
    'url' => env('APP_URL'),
    'debug' => (bool) env('APP_DEBUG', false),
    'session_storage' => env('APP_SESSION_STORAGE', 'file'),
    'log_stream' => env('APP_LOG_STREAM', 'php://stderr'),
];
