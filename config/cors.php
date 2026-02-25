<?php

return [
    'paths' => [ 'sanctum/csrf-cookie', 'login', 'register'], // Ampio ny register/login eto
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://frontend-ong-puce.vercel.app'], // Hamarino tsara ny tsipelina
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Tsy maintsy true
];