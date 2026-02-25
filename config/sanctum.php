<?php

use Laravel\Sanctum\Sanctum;

return [


'stateful' => [
    'frontend-ong-puce.vercel.app',
    'backend-ong-qarl.onrender.com',
    'localhost',
    '127.0.0.1',
],

    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];
