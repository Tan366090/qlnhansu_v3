<?php

namespace App\Config;

// Security configurations for the QLNhanSu system
class Security {
    // Session Configuration
    public static $session = [
        'name' => 'QLNhanSu_session',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ];

    // Password Configuration
    public static $password = [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'hash_algo' => PASSWORD_BCRYPT,
        'hash_options' => ['cost' => 12]
    ];

    // Rate Limiting
    public static $rate_limit = [
        'enabled' => true,
        'max_attempts' => 5,
        'time_window' => 300, // 5 minutes
        'ban_duration' => 1800 // 30 minutes
    ];

    // XSS Protection
    public static $xss = [
        'enabled' => true,
        'strip_tags' => true,
        'htmlspecialchars' => true,
        'allowed_tags' => '<p><br><strong><em><ul><ol><li><a>'
    ];

    // CORS Configuration
    public static $cors = [
        'enabled' => true,
        'allowed_origins' => ['http://localhost'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
        'max_age' => 86400 // 24 hours
    ];

    // File Upload Security
    public static $upload = [
        'max_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'sanitize_filename' => true,
        'randomize_filename' => true
    ];

    // Security Headers
    public static $headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net http://localhost:3000; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self' http://localhost:3000;"
    ];
}