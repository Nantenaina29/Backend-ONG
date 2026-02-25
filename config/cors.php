<?php
return [
    'paths' => ['api/*', 'sanctum/*', 'sanctum/csrf-cookie', 'login', 'register'], 
     
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://frontend-ong-puce.vercel.app', 'frontend-ong-puce.vercel.app' ], 
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['*'],
    'max_age' => 0,
    'supports_credentials' => true, 
];
