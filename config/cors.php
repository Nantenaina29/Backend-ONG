<?php
return [
    'paths' => ['api/*', 'login', 'register', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // Production: ['https://frontend-ong-puce.vercel.app']
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false  // ← CRITICAL!
];
