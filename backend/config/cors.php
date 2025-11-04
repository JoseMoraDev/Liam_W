<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://192.168.0.105:3000',
        'http://192.168.8.250:3000',
        // Añade aquí otras IPs LAN del dev server de Nuxt o la URL pública de Live Share si haces port forwarding del 3000
    ],
    'allowed_origins_patterns' => [
        '#^http://192\\.168\\.[0-9]+\\.[0-9]+:3000$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
