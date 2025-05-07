<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'qlnhansu');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/database_error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Function to handle errors and return JSON response
function handleError($message, $code = 500) {
    error_log("Database Error: " . $message);
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

class Database {
    private static $host = DB_HOST;
    private static $db_name = DB_NAME;
    private static $username = DB_USER;
    private static $password = DB_PASS;
    private static $charset = DB_CHARSET;
    private static $conn;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                error_log("Attempting to connect to database: " . self::$db_name);
                
                // First connect without database name to check if database exists
                $pdo = new PDO(
                    "mysql:host=" . self::$host . ";charset=" . self::$charset,
                    self::$username,
                    self::$password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                // Check if database exists
                $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . self::$db_name . "'");
                if ($stmt->rowCount() == 0) {
                    // Create database if it doesn't exist
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . self::$db_name . " CHARACTER SET " . self::$charset);
                    error_log("Database created: " . self::$db_name);
                }

                // Now connect to the database
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=" . self::$charset;
                error_log("DSN: " . $dsn);
                
                self::$conn = new PDO(
                    $dsn,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                error_log("Database connection successful");
            } catch(PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                handleError("Database connection failed: " . $e->getMessage(), 500);
            }
        }
        return self::$conn;
    }
}

// Initialize database connection
try {
    $db = Database::getConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    handleError("Database connection failed: " . $e->getMessage(), 500);
}

// Return configuration array for DataStore
return [
    'host' => DB_HOST,
    'database' => DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASS,
    'charset' => DB_CHARSET
]; 