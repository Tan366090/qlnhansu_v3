<?php
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Set default timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// JWT Configuration
define('JWT_SECRET', 'qlnhansu_secret_key_2024');
define('JWT_EXPIRES_IN', '24h');
define('JWT_REFRESH_EXPIRES_IN', '7d');

// Set default charset
ini_set('default_charset', 'UTF-8');

// Enable output buffering
ob_start();
?> 