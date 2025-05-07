<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
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

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Get template by ID
            $stmt = $pdo->prepare("
                SELECT et.*, u.full_name as created_by_name
                FROM email_templates et
                LEFT JOIN users u ON et.created_by = u.id
                WHERE et.id = ?
            ");
            $stmt->execute([$path_parts[5]]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($template ?: ['error' => 'Template not found']);
        } else if (isset($_GET['type'])) {
            // Get templates by type
            $stmt = $pdo->prepare("
                SELECT et.*, u.full_name as created_by_name
                FROM email_templates et
                LEFT JOIN users u ON et.created_by = u.id
                WHERE et.type = ?
                ORDER BY et.created_at DESC
            ");
            $stmt->execute([$_GET['type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['status'])) {
            // Get templates by status
            $stmt = $pdo->prepare("
                SELECT et.*, u.full_name as created_by_name
                FROM email_templates et
                LEFT JOIN users u ON et.created_by = u.id
                WHERE et.status = ?
                ORDER BY et.created_at DESC
            ");
            $stmt->execute([$_GET['status']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all templates with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("
                SELECT et.*, u.full_name as created_by_name
                FROM email_templates et
                LEFT JOIN users u ON et.created_by = u.id
                ORDER BY et.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM email_templates");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $templates,
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
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['name']) || !isset($data['subject']) || !isset($data['content']) || !isset($data['type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO email_templates (
                name,
                subject,
                content,
                type,
                status,
                created_by,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['name'],
            $data['subject'],
            $data['content'],
            $data['type'],
            $data['status'] ?? 'active',
            $data['created_by'] ?? null
        ]);
        
        echo json_encode([
            'message' => 'Template created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Build update query
            $updates = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updates[] = 'name = ?';
                $params[] = $data['name'];
            }
            if (isset($data['subject'])) {
                $updates[] = 'subject = ?';
                $params[] = $data['subject'];
            }
            if (isset($data['content'])) {
                $updates[] = 'content = ?';
                $params[] = $data['content'];
            }
            if (isset($data['type'])) {
                $updates[] = 'type = ?';
                $params[] = $data['type'];
            }
            if (isset($data['status'])) {
                $updates[] = 'status = ?';
                $params[] = $data['status'];
            }
            
            if (!empty($updates)) {
                $updates[] = 'updated_at = NOW()';
                $params[] = $path_parts[5];
                
                $stmt = $pdo->prepare("
                    UPDATE email_templates 
                    SET " . implode(', ', $updates) . "
                    WHERE id = ?
                ");
                
                $stmt->execute($params);
                echo json_encode(['message' => 'Template updated successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No fields to update']);
            }
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM email_templates WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Template deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 