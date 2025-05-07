<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Log file path
$logFile = __DIR__ . '/error.log';

// Function to log errors
function logError($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    // Include database configuration
    if (!file_exists(__DIR__ . '/config/Database.php')) {
        throw new Exception('Database configuration file not found');
    }
    require_once __DIR__ . '/config/Database.php';

    $database = new Database();
    $db = $database->connect();
    
    // Fetch department data
    $query = "SELECT department_name, COUNT(*) as count 
              FROM employees 
              WHERE status = 'active'
              GROUP BY department_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($departments === false) {
        throw new Exception('Failed to fetch department data');
    }
    
    $departmentLabels = array_column($departments, 'department_name');
    $departmentData = array_column($departments, 'count');

    // Fetch salary data
    $query = "SELECT MONTH(pay_date) as month, SUM(amount) as total 
              FROM salaries 
              WHERE YEAR(pay_date) = YEAR(CURRENT_DATE)
              GROUP BY MONTH(pay_date)
              ORDER BY month";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($salaries === false) {
        throw new Exception('Failed to fetch salary data');
    }
    
    $salaryMonths = array_map(function($row) {
        return "Tháng " . $row['month'];
    }, $salaries);
    $salaryData = array_column($salaries, 'total');

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'departmentLabels' => $departmentLabels,
            'departmentData' => $departmentData,
            'salaryMonths' => $salaryMonths,
            'salaryData' => $salaryData
        ]
    ]);

} catch (Exception $e) {
    // Log error
    logError($e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching dashboard data',
        'error' => $e->getMessage()
    ]);
}
?>
