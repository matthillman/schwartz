<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'token' => env('DISCORD_BOT_TOKEN'),
        'redirect' => '/login/discord/callback',
    ],

    'swgoh_help' => [
        'user' => env('SWGOH_HELP_USER'),
        'password' => env('SWGOH_HELP_PASSWORD'),
    ],

    'swgoh_stats' => [
        'url' => env('SWGOH_HELP_STATS_URL'),
    ],

    'shitty_bot' => [
        'token' => env('SHITTY_BOT_TOKEN'),
        'active' => env('SHITTY_BOT_ACTIVE', false),
    ]

];
