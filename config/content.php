<?php

return [
    'canned_responses' => (function () {
        if ($responses = env('CONTENT_CANNED_RESPONSES')) {
            return explode('|', $responses);
        }
        return [];
    })(),
];
