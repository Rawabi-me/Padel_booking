<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    'thawani' => [
        'base_url' => env('THAWANI_BASE_URL', 'https://uatcheckout.thawani.om/api/v1'),
        'secret_key' => env('THAWANI_SECRET_KEY', 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et'),
        'publishable_key' => env('THAWANI_PUBLISHABLE_KEY', 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy'),
    ],

];
