<?php

return [
    'mailgun_key' => env('MAILGUN_KEY'),
    'mailgun_domain' => env('MAILGUN_DOMAIN'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name' => env('MAIL_FROM_NAME'),
    ],
];
