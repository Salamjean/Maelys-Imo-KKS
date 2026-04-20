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

    'cinetpay' => [
        'api_key' => env('CINETPAY_API_KEY'),
        'site_id' => env('CINETPAY_SITE_ID'),
        'mode' => env('CINETPAY_MODE', 'TEST'),
    ],

    'wave' => [
        'api_key'        => env('WAVE_API_KEY'),
        'payout_api_key' => env('WAVE_PAYOUT_API_KEY', env('WAVE_API_KEY')), // clé avec payouts_api scope
        'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
    ],

    // Réservé - non encore configuré
    'orange_money' => [
        'api_key'      => env('ORANGE_MONEY_API_KEY'),
        'client_id'   => env('ORANGE_MONEY_CLIENT_ID'),
        'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY'),
    ],

    'moov_money' => [
        'api_key'  => env('MOOV_MONEY_API_KEY'),
        'username' => env('MOOV_MONEY_USERNAME'),
        'password' => env('MOOV_MONEY_PASSWORD'),
    ],

    'mtn_money' => [
        'api_key'          => env('MTN_MONEY_API_KEY'),
        'subscription_key' => env('MTN_MONEY_SUBSCRIPTION_KEY'),
        'callback_url'     => env('MTN_MONEY_CALLBACK_URL'),
    ],

    'yellika' => [
        'api_url'   => env('YELLIKA_API_URL', 'http://app.1smsafrica.com/api/v3/'),
        'api_key'   => env('YELLIKA_API_KEY'),
        'sender_id' => env('YELLIKA_SENDER_ID', 'Maelys imo'),
    ],

];
