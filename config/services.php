<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Internal Microservice URLs
    |--------------------------------------------------------------------------
    */

    'user_service' => [
        'base_url' => env('USER_SERVICE_URL', 'http://localhost:8001/api/v1'),
    ],

    'notification_service' => [
        'base_url' => env('NOTIFICATION_SERVICE_URL', 'http://localhost:8002/api/v1'),
    ],

    'messaging_service' => [
        'base_url' => env('MESSAGING_SERVICE_URL', 'http://localhost:8003/api/v1'),
    ],

    'template_service' => [
        'base_url' => env('TEMPLATE_SERVICE_URL', 'http://localhost:8004/api/v1'),
    ],

];
