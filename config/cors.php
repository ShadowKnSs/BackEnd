<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [
        'Content-Disposition',   // para leer el filename
        'X-Report-URL',          // para leer la URL pública
    ],
    'max_age' => 0,
    'supports_credentials' => false,
];

