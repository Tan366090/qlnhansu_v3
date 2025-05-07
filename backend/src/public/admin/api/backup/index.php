<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$db_host = 'localhost';
$db_name = 'qlnhansu';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Backup configuration
$backup_dir = __DIR__ . '/../../../../backups/';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && $path_parts[5] === 'list') {
            // List all backups
            $backups = [];
            $files = glob($backup_dir . '*.sql');
            
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
            
            echo json_encode($backups);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'download') {
            // Download backup file
            $filename = isset($_GET['filename']) ? $_GET['filename'] : '';
            $filepath = $backup_dir . $filename;
            
            if (file_exists($filepath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                readfile($filepath);
                exit;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Backup file not found']);
            }
        } else {
            // Get backup status
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM activities) as activities_count,
                    (SELECT COUNT(*) FROM attendance) as attendance_count,
                    (SELECT COUNT(*) FROM benefits) as benefits_count,
                    (SELECT COUNT(*) FROM candidates) as candidates_count,
                    (SELECT COUNT(*) FROM certificates) as certificates_count,
                    (SELECT COUNT(*) FROM contracts) as contracts_count,
                    (SELECT COUNT(*) FROM departments) as departments_count,
                    (SELECT COUNT(*) FROM documents) as documents_count,
                    (SELECT COUNT(*) FROM leave_requests) as leave_requests_count,
                    (SELECT COUNT(*) FROM positions) as positions_count,
                    (SELECT COUNT(*) FROM salaries) as salaries_count,
                    (SELECT COUNT(*) FROM users) as users_count
            ");
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        if (isset($path_parts[5]) && $path_parts[5] === 'create') {
            // Create backup
            $tables = [
                'activities',
                'attendance',
                'benefits',
                'candidates',
                'certificates',
                'contracts',
                'departments',
                'documents',
                'leave_requests',
                'positions',
                'salaries',
                'users'
            ];
            
            $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $handle = fopen($backup_file, 'w');
            
            if ($handle) {
                foreach ($tables as $table) {
                    // Get table structure
                    $stmt = $pdo->query("SHOW CREATE TABLE $table");
                    $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
                    fwrite($handle, $create_table['Create Table'] . ";\n\n");
                    
                    // Get table data
                    $stmt = $pdo->query("SELECT * FROM $table");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $values = array_map(function($value) use ($pdo) {
                            return $value === null ? 'NULL' : $pdo->quote($value);
                        }, $row);
                        
                        fwrite($handle, "INSERT INTO $table VALUES (" . implode(', ', $values) . ");\n");
                    }
                    fwrite($handle, "\n");
                }
                
                fclose($handle);
                echo json_encode([
                    'message' => 'Backup created successfully',
                    'filename' => basename($backup_file)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create backup file']);
            }
        } else if (isset($path_parts[5]) && $path_parts[5] === 'restore') {
            // Restore from backup
            if (!isset($_FILES['backup'])) {
                http_response_code(400);
                echo json_encode(['error' => 'No backup file uploaded']);
                exit;
            }
            
            $backup = $_FILES['backup'];
            if ($backup['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'File upload failed']);
                exit;
            }
            
            // Read and execute SQL file
            $sql = file_get_contents($backup['tmp_name']);
            $queries = explode(';', $sql);
            
            try {
                $pdo->beginTransaction();
                
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $pdo->exec($query);
                    }
                }
                
                $pdo->commit();
                echo json_encode(['message' => 'Backup restored successfully']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to restore backup: ' . $e->getMessage()]);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 