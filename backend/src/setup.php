<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/database/MigrationManager.php';

echo "Starting database setup...\n";


// filepath: c:\xampp\htdocs\QLNhanSu_version1\setup.php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Debug: Print environment variables
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_PASSWORD: " . getenv('DB_PASSWORD') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";

try {
    // Create database if not exists
    $pdo = new PDO(
        "mysql:host=localhost",
        "root",
        ""
    );
    
    $dbName = 'qlnhansu';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$dbName}' created or already exists.\n";

    // Run migrations 
    echo "\nRunning migrations...\n";
    $manager = new MigrationManager();
    $manager->migrate();

    echo "\nSetup completed successfully!\n";
    echo "\nDefault admin credentials:\n";
    echo "Username: admin\n";
    echo "Password: Admin@123\n";

} catch (Exception $e) {
    echo "Error during setup: " . $e->getMessage() . "\n";
    exit(1);
} 