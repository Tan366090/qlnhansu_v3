<?php
require_once 'config.php';
require_once 'auth.php';

// Check authentication
checkAuth();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getDocument($_GET['id']);
        } else {
            getDocuments();
        }
        break;
    case 'POST':
        uploadDocument();
        break;
    case 'PUT':
        updateDocument();
        break;
    case 'DELETE':
        deleteDocument();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// Get all documents
function getDocuments() {
    global $conn;
    
    try {
        $sql = "SELECT d.*, e.name as employee_name, dp.name as department_name 
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN departments dp ON e.department_id = dp.id
                ORDER BY d.upload_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'file_path' => $row['file_path'],
                'file_type' => $row['file_type'],
                'file_size' => $row['file_size'],
                'description' => $row['description'],
                'upload_date' => $row['upload_date'],
                'employee_id' => $row['employee_id'],
                'employee_name' => $row['employee_name'],
                'department_name' => $row['department_name']
            ];
        }
        
        echo json_encode($documents);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Get document by ID
function getDocument($id) {
    global $conn;
    
    try {
        $sql = "SELECT d.*, e.name as employee_name, dp.name as department_name 
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN departments dp ON e.department_id = dp.id
                WHERE d.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Document not found']);
            return;
        }
        
        $document = $result->fetch_assoc();
        echo json_encode([
            'id' => $document['id'],
            'name' => $document['name'],
            'file_path' => $document['file_path'],
            'file_type' => $document['file_type'],
            'file_size' => $document['file_size'],
            'description' => $document['description'],
            'upload_date' => $document['upload_date'],
            'employee_id' => $document['employee_id'],
            'employee_name' => $document['employee_name'],
            'department_name' => $document['department_name']
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Upload new document
function uploadDocument() {
    global $conn;
    
    try {
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error');
        }
        
        $file = $_FILES['file'];
        $employee_id = $_POST['employee_id'] ?? null;
        $description = $_POST['description'] ?? null;
        
        // Validate file
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only PDF and Word documents are allowed');
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Insert document record
        $sql = "INSERT INTO documents (name, file_path, file_type, file_size, description, employee_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssisi', 
            $file['name'],
            $file_path,
            $file['type'],
            $file['size'],
            $description,
            $employee_id
        );
        
        if (!$stmt->execute()) {
            // Delete uploaded file if database insert fails
            unlink($file_path);
            throw new Exception('Failed to save document record');
        }
        
        echo json_encode(['message' => 'Document uploaded successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Update document
function updateDocument() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            throw new Exception('Document ID is required');
        }
        
        $id = $data['id'];
        $description = $data['description'] ?? null;
        
        $sql = "UPDATE documents SET description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $description, $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update document');
        }
        
        echo json_encode(['message' => 'Document updated successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Delete document
function deleteDocument() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            throw new Exception('Document ID is required');
        }
        
        $id = $data['id'];
        
        // Get file path before deleting record
        $sql = "SELECT file_path FROM documents WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Document not found');
        }
        
        $document = $result->fetch_assoc();
        $file_path = $document['file_path'];
        
        // Delete record
        $sql = "DELETE FROM documents WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete document record');
        }
        
        // Delete file
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        echo json_encode(['message' => 'Document deleted successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?> 