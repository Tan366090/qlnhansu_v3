<?php

return [
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'expires_in' => env('JWT_EXPIRES_IN', '24h'),
        'cookie_expires_in' => env('JWT_COOKIE_EXPIRES_IN', '7d'),
    ],
    'password' => [
        'salt_rounds' => env('PASSWORD_SALT_ROUNDS', 10),
        'min_length' => 8,
        'require_special_chars' => true,
        'require_numbers' => true,
        'require_uppercase' => true,
    ],
    'session' => [
        'driver' => env('SESSION_DRIVER', 'redis'),
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => true,
        'secure' => env('APP_ENV') === 'production',
        'http_only' => true,
        'same_site' => 'lax',
    ],
    'rate_limit' => [
        'window' => env('RATE_LIMIT_WINDOW', 15),
        'max_requests' => env('RATE_LIMIT_MAX_REQUESTS', 100),
    ],
    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ORIGIN', 'http://localhost:3000')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ],
]; 