<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$dbname = 'qlnhansu';
$username = 'root';
$password = '';

try {
    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Function to test database connection
    function testDatabaseConnection() {
        global $conn;
        try {
            $conn->query("SELECT 1");
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    // Function to get all tables
    function getAllTables() {
        global $conn;
        try {
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return ['success' => true, 'tables' => $tables];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error getting tables: ' . $e->getMessage()];
        }
    }

    // Function to get table structure
    function getTableStructure($tableName) {
        global $conn;
        try {
            $stmt = $conn->query("DESCRIBE $tableName");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'structure' => $structure];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Error getting structure for table $tableName: " . $e->getMessage()];
        }
    }

    // Function to get table data
    function getTableData($tableName, $limit = 5) {
        global $conn;
        try {
            $stmt = $conn->query("SELECT * FROM $tableName LIMIT $limit");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Error getting data from table $tableName: " . $e->getMessage()];
        }
    }

    // Main test function
    function runAllTests() {
        $results = [];
        
        // Test 1: Database Connection
        $results['connection'] = testDatabaseConnection();
        
        if ($results['connection']['success']) {
            // Test 2: Get All Tables
            $results['tables'] = getAllTables();
            
            if ($results['tables']['success']) {
                // Test 3: Get Structure and Sample Data for Each Table
                foreach ($results['tables']['tables'] as $table) {
                    $results['table_details'][$table] = [
                        'structure' => getTableStructure($table),
                        'sample_data' => getTableData($table)
                    ];
                }
            }
        }
        
        return $results;
    }

    // Run tests and output results
    $testResults = runAllTests();
    echo json_encode($testResults, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?> 