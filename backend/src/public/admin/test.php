<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to HTML for better readability
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Test</h1>";

try {
    // Test database configuration file
    $dbConfigPath = '../../../src/config/database.php';
    if (!file_exists($dbConfigPath)) {
        throw new Exception("Database configuration file not found at: " . $dbConfigPath);
    }
    echo "<p>Database config file found at: " . $dbConfigPath . "</p>";

    // Include database configuration
    require_once $dbConfigPath;
    echo "<p>Database configuration loaded successfully</p>";

    // Test if Database class exists
    if (!class_exists('Database')) {
        throw new Exception("Database class not found in configuration file");
    }
    echo "<p>Database class found</p>";

    // Test database connection
    try {
        $db = Database::getConnection();
        echo "<p style='color: green;'>Database connection successful</p>";
    } catch (Exception $e) {
        throw new Exception("Failed to connect to database: " . $e->getMessage());
    }
    
    // Test employee table structure
    echo "<h2>Employee Table Structure</h2>";
    try {
        $stmt = $db->query("DESCRIBE employees");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($columns)) {
            throw new Exception("Employee table is empty or does not exist");
        }
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($column['Type'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($column['Null'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($column['Key'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        throw new Exception("Failed to get employee table structure: " . $e->getMessage());
    }
    
    // First, check if roles table exists and has data
    echo "<h2>Checking Roles Table</h2>";
    try {
        $stmt = $db->query("SELECT * FROM roles");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($roles)) {
            echo "<p>No roles found. Creating default roles...</p>";
            // Create default roles
            $defaultRoles = [
                ['name' => 'admin', 'description' => 'System Administrator'],
                ['name' => 'manager', 'description' => 'Department Manager'],
                ['name' => 'employee', 'description' => 'Regular Employee']
            ];
            
            $stmt = $db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
            foreach ($defaultRoles as $role) {
                $stmt->execute([$role['name'], $role['description']]);
                echo "<p>Created role: " . $role['name'] . "</p>";
            }
            
            // Get the employee role ID
            $stmt = $db->query("SELECT id FROM roles WHERE name = 'employee'");
            $employeeRole = $stmt->fetch(PDO::FETCH_ASSOC);
            $roleId = $employeeRole['id'];
        } else {
            echo "<p>Found existing roles:</p>";
            echo "<ul>";
            foreach ($roles as $role) {
                echo "<li>" . htmlspecialchars($role['name']) . " (ID: " . $role['id'] . ")</li>";
            }
            echo "</ul>";
            
            // Get the employee role ID
            $stmt = $db->query("SELECT id FROM roles WHERE name = 'employee'");
            $employeeRole = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$employeeRole) {
                throw new Exception("No employee role found in roles table");
            }
            $roleId = $employeeRole['id'];
        }
    } catch (PDOException $e) {
        throw new Exception("Error checking roles table: " . $e->getMessage());
    }

    // Now create test user with the correct role_id
    echo "<h2>Creating Test User</h2>";
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, role_id, created_at) VALUES (?, ?, ?, ?, ?)");
        $testUser = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'email' => 'test' . time() . '@example.com',
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $stmt->execute(array_values($testUser));
        $userId = $db->lastInsertId();
        echo "<p>Created test user with ID: " . $userId . "</p>";
    } catch (PDOException $e) {
        throw new Exception("Error creating test user: " . $e->getMessage());
    }
    
    // Test inserting a sample employee
    echo "<h2>Test Adding Sample Employee</h2>";
    
    // First, check if user exists
    $stmt = $db->query("SELECT user_id FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Create a test user first
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, role_id, created_at) VALUES (?, ?, ?, ?, ?)");
        $testUser = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'email' => 'test' . time() . '@example.com',
            'role_id' => 1, // Assuming role_id 1 exists
            'created_at' => date('Y-m-d H:i:s')
        ];
        $stmt->execute(array_values($testUser));
        $userId = $db->lastInsertId();
        echo "<p>Created test user with ID: " . $userId . "</p>";
    } else {
        $userId = $user['user_id'];
        echo "<p>Using existing user with ID: " . $userId . "</p>";
    }
    
    // Create test departments and positions
    echo "<h2>Creating Test Departments and Positions</h2>";
    try {
        // Check if departments exist
        $stmt = $db->query("SELECT * FROM departments");
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($departments)) {
            echo "<p>No departments found. Creating default departments...</p>";
            // Create default departments
            $defaultDepartments = [
                ['name' => 'Human Resources', 'description' => 'HR Department'],
                ['name' => 'Information Technology', 'description' => 'IT Department'],
                ['name' => 'Finance', 'description' => 'Finance Department']
            ];
            
            $stmt = $db->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            foreach ($defaultDepartments as $dept) {
                $stmt->execute([$dept['name'], $dept['description']]);
                echo "<p>Created department: " . $dept['name'] . "</p>";
            }
        } else {
            echo "<p>Found existing departments:</p>";
            echo "<ul>";
            foreach ($departments as $dept) {
                echo "<li>" . htmlspecialchars($dept['name']) . " (ID: " . $dept['id'] . ")</li>";
            }
            echo "</ul>";
        }
        
        // Get HR department ID
        $stmt = $db->query("SELECT id FROM departments WHERE name = 'Human Resources'");
        $hrDept = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hrDept) {
            throw new Exception("HR department not found");
        }
        $departmentId = $hrDept['id'];
        
        // Check if positions exist
        $stmt = $db->query("SELECT * FROM positions");
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($positions)) {
            echo "<p>No positions found. Creating default positions...</p>";
            // Create default positions
            $defaultPositions = [
                ['name' => 'HR Manager', 'department_id' => $departmentId, 'description' => 'HR Department Manager'],
                ['name' => 'HR Staff', 'department_id' => $departmentId, 'description' => 'HR Department Staff'],
                ['name' => 'IT Manager', 'department_id' => $departmentId, 'description' => 'IT Department Manager']
            ];
            
            $stmt = $db->prepare("INSERT INTO positions (name, department_id, description) VALUES (?, ?, ?)");
            foreach ($defaultPositions as $pos) {
                $stmt->execute([$pos['name'], $pos['department_id'], $pos['description']]);
                echo "<p>Created position: " . $pos['name'] . "</p>";
            }
        } else {
            echo "<p>Found existing positions:</p>";
            echo "<ul>";
            foreach ($positions as $pos) {
                echo "<li>" . htmlspecialchars($pos['name']) . " (ID: " . $pos['id'] . ")</li>";
            }
            echo "</ul>";
        }
        
        // Get HR Staff position ID
        $stmt = $db->query("SELECT id FROM positions WHERE name = 'HR Staff'");
        $hrStaffPos = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hrStaffPos) {
            throw new Exception("HR Staff position not found");
        }
        $positionId = $hrStaffPos['id'];
        
    } catch (PDOException $e) {
        throw new Exception("Error creating departments and positions: " . $e->getMessage());
    }

    // Now create test employee with the correct department_id and position_id
    echo "<h2>Creating Test Employee</h2>";
    try {
        $sampleData = [
            'user_id' => $userId,
            'employee_code' => 'EMP' . date('YmdHis'),
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        echo "<p>Attempting to insert sample data:</p>";
        echo "<pre>" . print_r($sampleData, true) . "</pre>";
        
        $sql = "INSERT INTO employees (user_id, employee_code, department_id, position_id, hire_date, status, created_at, updated_at) 
                VALUES (:user_id, :employee_code, :department_id, :position_id, :hire_date, :status, :created_at, :updated_at)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($sampleData);
        
        if ($result) {
            $employeeId = $db->lastInsertId();
            echo "<p style='color: green;'>Sample employee added successfully with ID: " . $employeeId . "</p>";
            
            // Verify the inserted data
            $stmt = $db->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$employeeId]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>Verification of inserted data:</p>";
            echo "<pre>" . print_r($employee, true) . "</pre>";
        } else {
            throw new Exception("Failed to add sample employee: " . implode(", ", $stmt->errorInfo()));
        }
    } catch (PDOException $e) {
        throw new Exception("Database error while adding employee: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<h3>Error Details:</h3>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    if ($e->getPrevious()) {
        echo "<p><strong>Previous Error:</strong> " . $e->getPrevious()->getMessage() . "</p>";
    }
    echo "</div>";
}

// Display PHP info for debugging
echo "<h2>PHP Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "</p>";
echo "<p>Loaded Extensions:</p>";
echo "<pre>" . print_r(get_loaded_extensions(), true) . "</pre>";
?> 