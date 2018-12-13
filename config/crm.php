<?php

return [
    'canned_responses' => (function () {
        if ($responses = env('CRM_CANNED_RESPONSES')) {
            return explode('|', $responses);
        }
        return [];
    })(),
    'default_assignee_email' => env('CRM_DEFAULT_ASSIGNEE_EMAIL'),
];
