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

    'mikrotik' => [
        'host' => env('MIKROTIK_HOST'),
        'username' => env('MIKROTIK_USER'),
        'password' => env('MIKROTIK_PASS'),
        'port' => env('MIKROTIK_PORT', 8728),
    // Puerto HTTP del hotspot (NO el API). Si es null se intenta deducir (sin puerto)
    'hotspot_port' => env('MIKROTIK_HOTSPOT_PORT'),
    'minutes_correct' => env('MIKROTIK_MINUTES_CORRECT', 30),
    'minutes_incorrect' => env('MIKROTIK_MINUTES_INCORRECT', 5),
    ],

];
