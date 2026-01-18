<?php
// config/api.php

return [
    'version' => 'v1',
    'name' => 'LRC Group API',
    
    'rate_limit' => [
        'max_attempts' => 60,
        'decay_minutes' => 1
    ],
    
    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100
    ],
    
    'cors' => [
        'allowed_origins' => explode(',', env('API_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:5173')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => false,
    ],
    
    'response_format' => [
        'success' => 'success',
        'error' => 'error',
        'data' => 'data',
        'message' => 'message',
        'meta' => 'meta'
    ],
];