<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'pusher' => [
        'key'     => env('PUSHER_APP_KEY'),
        'secret'  => env('PUSHER_APP_SECRET'),
        'app_id'  => env('PUSHER_APP_ID'),
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
    ],

];
