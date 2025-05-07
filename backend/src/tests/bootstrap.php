<?php

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up session for testing
session_start();

// Define test environment constants
define('TEST_ENV', true);
define('APP_ROOT', dirname(__DIR__));

// Set up test database connection
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'qlnhansu_test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Create test database if it doesn't exist
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create test database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']}");
    
    // Use test database
    $pdo->exec("USE {$dbConfig['dbname']}");
    
    // Create test tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            password_salt VARCHAR(255) NOT NULL,
            role_id INT NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            department_id INT,
            position_id INT,
            is_active TINYINT(1) DEFAULT 1,
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS roles (
            role_id INT AUTO_INCREMENT PRIMARY KEY,
            role_name VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insert test roles
    $roles = ['admin', 'hr', 'manager'];
    foreach ($roles as $role) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO roles (role_name) VALUES (?)");
        $stmt->execute([$role]);
    }
    
    // Store PDO instance in global variable for tests
    $GLOBALS['pdo'] = $pdo;
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

// Clean up function to run after tests
function cleanupTestDatabase() {
    if (isset($GLOBALS['pdo'])) {
        try {
            $GLOBALS['pdo']->exec("DROP DATABASE IF EXISTS qlnhansu_test");
        } catch (PDOException $e) {
            error_log("Cleanup failed: " . $e->getMessage());
        }
    }
}

// Register cleanup function
register_shutdown_function('cleanupTestDatabase');

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Project;
use App\Models\Task;
use App\Models\Performance;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Skill;
use App\Models\Experience;
use App\Models\Document;
use App\Models\Training;
use App\Models\Notification;
use App\Models\User; 