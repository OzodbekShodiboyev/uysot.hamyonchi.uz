<?php

use BeyondCode\LaravelWebSockets\Dashboard\Http\Middleware\Authorize;

return [

    'dashboard' => [
        'port'       => env('LARAVEL_WEBSOCKETS_PORT', 6001),
        'path'       => 'laravel-websockets',
        'middleware' => [
            'web',
            Authorize::class,
        ],
    ],

    'apps' => [
        [
            'id'                    => env('PUSHER_APP_ID'),
            'name'                  => env('APP_NAME'),
            'key'                   => env('PUSHER_APP_KEY'),
            'secret'                => env('PUSHER_APP_SECRET'),
            'path'                  => env('PUSHER_APP_PATH'),
            'capacity'              => null,
            'enable_client_messages' => false,
            'enable_statistics'     => true,
        ],
    ],

    'app_provider' => BeyondCode\LaravelWebSockets\Apps\ConfigAppProvider::class,

    'allowed_origins' => [],

    'max_request_size_in_kilobytes' => 250,

    'path' => env('LARAVEL_WEBSOCKETS_PATH', 'laravel-websockets'),

    'middleware' => [
        'web',
        Authorize::class,
    ],

    'statistics' => [
        'model'                    => \BeyondCode\LaravelWebSockets\Statistics\Models\WebSocketsStatisticsEntry::class,
        'interval_in_seconds'      => 60,
        'delete_statistics_older_than_days' => 60,
        'perform_dns_lookup'       => false,
    ],

    'ssl' => [
        'local_cert'        => null,
        'capath'            => null,
        'local_pk'          => null,
        'passphrase'        => null,
        'verify_peer'       => true,
        'allow_self_signed' => false,
    ],

    'route_attributes' => [
        'prefix'     => env('LARAVEL_WEBSOCKETS_PATH', 'laravel-websockets'),
        'middleware' => [],
    ],

    'channel_manager' => \BeyondCode\LaravelWebSockets\ChannelManagers\ArrayChannelManager::class,

];
