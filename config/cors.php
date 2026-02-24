<?php

return [
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => [
    'https://tsinjoainafianara.vercel.app',
    'https://frontend-ong-nantenaina29s-projects.vercel.app', // Ampio daholo ny URL Vercel hitanao
],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true, // TENA ILAINA: Ity no mamela ny Cookies
];