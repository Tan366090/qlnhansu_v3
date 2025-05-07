<?php

namespace App\Config;

class Auth {
    // Default admin credentials
    public static $defaultAdmin = [
        'username' => 'admin',
        'password' => 'Admin@123',
        'email' => 'admin@qlnhansu.com',
        'role' => 'admin',
        'full_name' => 'Administrator'
    ];

    // Default user credentials
    public static $defaultUser = [
        'username' => 'user',
        'password' => 'User@123',
        'email' => 'user@qlnhansu.com',
        'role' => 'user',
        'full_name' => 'Regular User'
    ];

    // Password requirements
    public static $passwordRequirements = [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true
    ];

    // Session configuration
    public static $session = [
        'name' => 'QLNhanSu_session',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    // Remember me configuration
    public static $rememberMe = [
        'enabled' => true,
        'cookie_name' => 'QLNhanSu_remember',
        'expiry' => 2592000, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    // Login attempts configuration
    public static $loginAttempts = [
        'max_attempts' => 5,
        'time_window' => 300, // 5 minutes
        'ban_duration' => 1800 // 30 minutes
    ];
} 