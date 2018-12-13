<?php

return [
    'canned_responses' => (function () {
        if ($responses = env('CRM_CANNED_RESPONSES')) {
            return explode('|', $responses);
        }
        return [];
    })(),
];
