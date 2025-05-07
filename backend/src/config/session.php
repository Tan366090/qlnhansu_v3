<?php

// Session configuration
return [
    'name' => 'QLNhanSu_Session',
    'gc_maxlifetime' => 86400, // 24 hours
    'gc_probability' => 1,
    'gc_divisor' => 100,
    'use_strict_mode' => 1,
    'use_only_cookies' => 1,
    'cookie_lifetime' => 86400, // 24 hours
    'cookie_path' => '/QLNhanSu_version1/',
    'cookie_domain' => '',
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'validation' => [
        'check_ip' => true,
        'check_user_agent' => true
    ]
]; 