<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
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

// File upload configuration
$upload_dir = __DIR__ . '/../../../../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Get file by ID
            $stmt = $pdo->prepare("
                SELECT f.*, u.full_name as uploaded_by_name, d.name as department_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE f.id = ?
            ");
            $stmt->execute([$path_parts[5]]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($file) {
                $file_path = $upload_dir . $file['file_path'];
                if (file_exists($file_path)) {
                    header('Content-Type: ' . $file['mime_type']);
                    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
                    readfile($file_path);
                    exit;
                }
            }
            echo json_encode(['error' => 'File not found']);
        } else if (isset($_GET['department_id'])) {
            // Get files by department
            $stmt = $pdo->prepare("
                SELECT f.*, u.full_name as uploaded_by_name, d.name as department_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE f.department_id = ?
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$_GET['department_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['type'])) {
            // Get files by type
            $stmt = $pdo->prepare("
                SELECT f.*, u.full_name as uploaded_by_name, d.name as department_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN departments d ON f.department_id = d.id
                WHERE f.type = ?
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$_GET['type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all files with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("
                SELECT f.*, u.full_name as uploaded_by_name, d.name as department_name
                FROM files f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN departments d ON f.department_id = d.id
                ORDER BY f.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM files");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $files,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;

    case 'POST':
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No file uploaded']);
            exit;
        }

        $file = $_FILES['file'];
        $original_name = $file['name'];
        $mime_type = $file['type'];
        $size = $file['size'];
        $error = $file['error'];

        if ($error !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'File upload failed']);
            exit;
        }

        // Generate unique filename
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $file_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $stmt = $pdo->prepare("
                INSERT INTO files (
                    original_name,
                    file_path,
                    mime_type,
                    size,
                    type,
                    department_id,
                    uploaded_by,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $original_name,
                $filename,
                $mime_type,
                $size,
                $_POST['type'] ?? 'document',
                $_POST['department_id'] ?? null,
                $_POST['uploaded_by'] ?? null
            ]);
            
            echo json_encode([
                'message' => 'File uploaded successfully',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save file']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Get file info before deleting
            $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($file) {
                $file_path = $upload_dir . $file['file_path'];
                
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
                $stmt->execute([$path_parts[5]]);
                
                // Delete physical file
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                echo json_encode(['message' => 'File deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'File not found']);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 