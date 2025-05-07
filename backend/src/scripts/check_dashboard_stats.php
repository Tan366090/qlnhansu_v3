<?php
require_once __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/database.php';

try {
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // 1. Total employees
    $sql = "SELECT COUNT(*) as total FROM employees";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Total employees: " . $row['total'] . "\n";

    // 2. Average KPI completion rate
    $sql = "SELECT AVG(completion_rate) as avg_kpi FROM kpi_records WHERE DATE(date) = CURRENT_DATE";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Average KPI completion rate: " . number_format($row['avg_kpi'], 1) . "%\n";

    // 3. New candidates
    $sql = "SELECT COUNT(*) as total FROM candidates WHERE status = 'new'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "New candidates: " . $row['total'] . "\n";

    // 4. Active projects
    $sql = "SELECT COUNT(*) as total FROM projects WHERE status = 'active'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Active projects: " . $row['total'] . "\n";

    // 5. Active employees
    $sql = "SELECT COUNT(*) as total FROM employees WHERE status = 'active'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Active employees: " . $row['total'] . "\n";

    // 6. Today's attendance rate
    $sql = "SELECT 
            (SELECT COUNT(*) FROM attendance WHERE DATE(date) = CURRENT_DATE AND status = 'present') * 100.0 / 
            (SELECT COUNT(*) FROM employees WHERE status = 'active') as attendance_rate";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Today's attendance rate: " . number_format($row['attendance_rate'], 1) . "%\n";

    // 7. Pending leave requests
    $sql = "SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Pending leave requests: " . $row['total'] . "\n";

    // 8. Total payroll
    $sql = "SELECT SUM(net_salary) as total FROM payroll WHERE MONTH(date) = MONTH(CURRENT_DATE) AND YEAR(date) = YEAR(CURRENT_DATE)";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Total monthly payroll: " . number_format($row['total'], 0, '.', ',') . " VND\n";

    // 9. Inactive employees
    $sql = "SELECT COUNT(*) as total FROM employees WHERE status = 'inactive'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "Inactive employees: " . $row['total'] . "\n";

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 