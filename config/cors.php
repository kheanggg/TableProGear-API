<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',                 // your frontend dev server
        'https://table-pro-gear-frontend-mengkheangs-projects.vercel.app',
        'https://table-pro-gear-frontend.vercel.app',
        'https://ce9ec893957f.ngrok-free.app',  // ngrok tunnel
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];