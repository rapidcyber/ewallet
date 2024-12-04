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

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_ENT_SITE_KEY'),
        'api_key' => env('RECAPTCHA_ENT_API_KEY'),
        'project_id' => env('RECAPTCHA_ENT_PROJECT_ID'),
    ],

    'google' => [
        'geocoder_key' => env('GOOGLE_GEOCODER_KEY'),
    ],

    'alb' => [
        'webhook_secret' => env('ALB_WEBHOOK_SECRET_KEY'),
        'opc' => env('ALB_OPC'),
        'p2m' => env('ALB_P2M'),
        'api_host' => env('ALB_HOST_URL'),
        'api_id' => env('ALB_API_ID'),
        'api_secret' => env('ALB_API_SECRET')
    ],

    'bpi' => [
        'host_oauth_url' => env('BPI_OAUTH_HOST_URI'),
        'client_id' => env('BPI_CLIENT_ID'),
        'client_secret' => env('BPI_CLIENT_SECRET'),
        'callback_url' => env('BPI_CALLBACK_URL'),
        'public_key' => env('BPI_PUBLIC_KEY'),
    ],

    'unionbank' => [
        'host_api_url' => env('UB_HOST_API_URI'),
        'client_id' => env('UB_CLIENT_ID'),
        'client_secret' => env('UB_CLIENT_SECRET'),

        'redirect_url' => env('UB_REDIRECT_URL'),
        'upay_biller_uuid' => env('UPAY_BILLER_UUID'),
        'upay_api_key' => env('UPAY_AUTOPOST_API_KEY'),
        'upay_whitelist' => env('UPAY_WHITELIST'),

        'partner_username' => env('UB_PARTNER_USERNAME'),
        'partner_password' => env('UB_PARTNER_PASSWORD'),
        'partner_id' => env('UB_PARTNER_ID'),

        'partner_id_payments' => env('UB_PARTNER_ID_PAYMENTS'),
        'partner_client_id_payments' => env('UB_PARTNER_CLIENT_ID_PAYMENTS'),
        'partner_client_secret_payments' => env('UB_PARTNER_CLIENT_SECRET_PAYMENTS'),
    ],

    'ecpay' => [
        'account_id' => env('ECPAY_ACCOUNT_ID'),
        'branch_id' => env('ECPAY_BRANCH_ID'),

        'hosts' => [
            'onepay' => env('ECPAY_ONEPAY_HOST_URL'),
            'ecload' => env('ECPAY_ECLOAD_HOST_URL'),
            'eccash' => env('ECPAY_ECCASH_HOST_URL'),
        ],

        'accounts' => [
            'onepay' => [
                'account_id' => env('ECPAY_ONEPAY_ACCOUNT_ID'),
                'branch_id' => env('ECPAY_ONEPAY_BRANCH_ID'),
                'user_id' => env('ECPAY_ONEPAY_USER_ID'),
                'username' => env('ECPAY_ONEPAY_USERNAME'),
                'password' => env('ECPAY_ONEPAY_PASSWORD'),
            ],

            'ecload' => [
                'account_id' => env('ECPAY_ECLOAD_ACCOUNT_ID'),
                'branch_id' => env('ECPAY_ECLOAD_BRANCH_ID'),
                'user_id' => env('ECPAY_ECLOAD_USER_ID'),
                'username' => env('ECPAY_ECLOAD_USERNAME'),
                'password' => env('ECPAY_ECLOAD_PASSWORD'),
            ],

            'eccash' => [
                'account_id' => env('ECPAY_ECCASH_ACCOUNT_ID'),
                'branch_id' => env('ECPAY_ECCASH_BRANCH_ID'),
                'user_id' => env('ECPAY_ECCASH_USER_ID'),
                'username' => env('ECPAY_ECCASH_USERNAME'),
                'password' => env('ECPAY_ECCASH_PASSWORD'),
            ],
        ],

        'crypto' => [
            'ecload' => [
                'iv' => env('ECPAY_ECLOAD_IV_KEY'),
                'secret' => env('ECPAY_ECLOAD_SECRET_KEY'),
            ],
            'eccash' => [
                'iv' => env('ECPAY_ECCASH_IV_KEY'),
                'secret' => env('ECPAY_ECCACH_SECRET_KEY'),
            ],
        ],
    ],

    'lalamove' => [
        'api_key' => env('LALAMOVE_API_KEY'),
        'api_secret' => env('LALAMOVE_API_SECRET'),
        'url' => env('LALAMOVE_URL'),
        'region' => env('LALAMOVE_REGION'),
        'language' => env('LALAMOVE_LANGUAGE'),
    ],

    'realholmes' => [
        'url' => env('REALHOLMES_URL'),
        'api_url' => env('REALHOLMES_API_URL'),
        'client_id' => env('REALHOLMES_CLIENT_ID'),
        'callback_uri' => env('REALHOLMES_CALLBACK_URI'),
    ],

    'trusting_social' => [
        'access' => env('TS_ACCESS'),
        'secret' => env('TS_SECRET'),
        'url' => env('TS_URL'),
    ],

    'system' => [
        'whitelist' => env('SYSTEM_WHITELIST'),
    ]
];
