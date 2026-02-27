<?php

use Laravel\Sanctum\Sanctum;

return [


'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'frontend-ong-puce.vercel.app')),

    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];
