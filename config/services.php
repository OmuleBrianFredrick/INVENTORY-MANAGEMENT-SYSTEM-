<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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
    'flutterwave' => [
        'environment' => env('FLW_ENVIRONMENT', 'test'),
        'client_id' => env('FLW_CLIENT_ID'),
        'client_secret' => env('FLW_CLIENT_SECRET'),
        'encryption_key' => env('FLW_ENCRYPTION_KEY'),
        'secret_hash' => env('FLW_SECRET_HASH'),
        'sandbox_base_url' => env('FLW_SANDBOX_BASE_URL', 'https://developersandbox-api.flutterwave.com'),
        'production_base_url' => env('FLW_PRODUCTION_BASE_URL', 'https://f4bexperience.flutterwave.com'),
        'oauth_token_url' => env('FLW_OAUTH_TOKEN_URL', 'https://idp.flutterwave.com/realms/flutterwave/protocol/openid-connect/token'),
    ],
];
