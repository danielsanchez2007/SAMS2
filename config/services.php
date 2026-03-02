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
    | Gemini AI Service
    |--------------------------------------------------------------------------
    |
    | Configuración para el servicio de Google Gemini AI.
    | La API Key debe estar definida en el archivo .env
    |
    */
    'gemini' => [
        // Compatibilidad con nombres alternos de variables de entorno.
        'api_key' => env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY') ?: env('GOOGLE_AI_API_KEY'),
        'api_url' => env('GEMINI_API_URL', env('GOOGLE_AI_API_URL', 'https://generativelanguage.googleapis.com/v1beta')),
        'model' => env('GEMINI_MODEL', env('GOOGLE_AI_MODEL', 'gemini-2.5-flash')),
        'enabled' => env('GEMINI_ENABLED', env('GOOGLE_AI_ENABLED', true)),
        'timeout' => env('GEMINI_TIMEOUT', env('GOOGLE_AI_TIMEOUT', 30)), // segundos
    ],

];
