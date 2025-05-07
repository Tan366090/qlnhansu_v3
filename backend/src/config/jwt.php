<?php
// Kiểm tra và tải autoload.php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Error: Please run "composer install" to install dependencies');
}
require_once $autoloadPath;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// JWT Secret key from environment
define('JWT_SECRET', $_ENV['JWT_SECRET']);
define('JWT_EXPIRATION', $_ENV['JWT_EXPIRATION']);

function generateJWT($data) {
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRATION;
    
    $payload = array(
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => $data
    );
    
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function verifyJWT($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return $decoded->data;
    } catch (Exception $e) {
        return false;
    }
} 