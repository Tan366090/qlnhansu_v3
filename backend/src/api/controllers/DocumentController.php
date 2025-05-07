<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;

class DocumentController {
    private $db;
    private $uploadDir;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadDir = __DIR__ . '/../../uploads/documents/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            // Get all documents with employee details
            $stmt = $conn->prepare("
                SELECT d.*, 
                       e.name as employee_name,
                       e.employee_code,
                       u.name as uploaded_by_name
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.status = 'active'
                ORDER BY d.created_at DESC
            ");
            $stmt->execute();
            $documents = $stmt->fetchAll();
            
            return ResponseHandler::sendSuccess($documents);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            // Get document details
            $stmt = $conn->prepare("
                SELECT d.*, 
                       e.name as employee_name,
                       e.employee_code,
                       u.name as uploaded_by_name
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.id = ? AND d.status = 'active'
            ");
            $stmt->execute([$id]);
            $document = $stmt->fetch();
            
            if (!$document) {
                return ResponseHandler::sendError('Document not found', 404);
            }
            
            return ResponseHandler::sendSuccess($document);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Handle file upload
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return ResponseHandler::sendError('File upload failed', 400);
            }
            
            $file = $_FILES['file'];
            $fileName = $this->generateUniqueFileName($file['name']);
            $filePath = $this->uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ResponseHandler::sendError('Failed to save file', 500);
            }
            
            // Get form data
            $data = [
                'employee_id' => $_POST['employee_id'] ?? null,
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'type' => $_POST['type'] ?? 'other',
                'uploaded_by' => $_SESSION['user']['id'] ?? null
            ];
            
            // Validate input
            if (empty($data['title'])) {
                unlink($filePath); // Delete uploaded file
                return ResponseHandler::sendError('Document title is required', 400);
            }
            
            // Insert document record
            $stmt = $conn->prepare("
                INSERT INTO documents (
                    employee_id, title, description, file_name,
                    file_path, file_type, file_size, type,
                    uploaded_by, status, created_at, updated_at
                ) VALUES (
                    :employee_id, :title, :description, :file_name,
                    :file_path, :file_type, :file_size, :type,
                    :uploaded_by, 'active', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'employee_id' => $data['employee_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'type' => $data['type'],
                'uploaded_by' => $data['uploaded_by']
            ]);
            
            $documentId = $conn->lastInsertId();
            
            // Get created document
            $stmt = $conn->prepare("
                SELECT d.*, 
                       e.name as employee_name,
                       e.employee_code,
                       u.name as uploaded_by_name
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$documentId]);
            $document = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($document, 'Document uploaded successfully', 201);
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Get current document
            $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $currentDocument = $stmt->fetch();
            
            if (!$currentDocument) {
                return ResponseHandler::sendError('Document not found', 404);
            }
            
            $data = [
                'title' => $_POST['title'] ?? $currentDocument['title'],
                'description' => $_POST['description'] ?? $currentDocument['description'],
                'type' => $_POST['type'] ?? $currentDocument['type']
            ];
            
            // Handle file update if new file is uploaded
            $filePath = $currentDocument['file_path'];
            $fileName = $currentDocument['file_name'];
            
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $fileName = $this->generateUniqueFileName($file['name']);
                $newFilePath = $this->uploadDir . $fileName;
                
                if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
                    return ResponseHandler::sendError('Failed to save new file', 500);
                }
                
                // Delete old file
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                $filePath = $newFilePath;
            }
            
            // Update document record
            $stmt = $conn->prepare("
                UPDATE documents 
                SET title = :title,
                    description = :description,
                    file_name = :file_name,
                    file_path = :file_path,
                    file_type = :file_type,
                    file_size = :file_size,
                    type = :type,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'description' => $data['description'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => isset($file) ? $file['type'] : $currentDocument['file_type'],
                'file_size' => isset($file) ? $file['size'] : $currentDocument['file_size'],
                'type' => $data['type']
            ]);
            
            // Get updated document
            $stmt = $conn->prepare("
                SELECT d.*, 
                       e.name as employee_name,
                       e.employee_code,
                       u.name as uploaded_by_name
                FROM documents d
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$id]);
            $document = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($document, 'Document updated successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Get document details
            $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch();
            
            if (!$document) {
                return ResponseHandler::sendError('Document not found', 404);
            }
            
            // Soft delete document record
            $stmt = $conn->prepare("UPDATE documents SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Delete file from storage
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess([], 'Document deleted successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function download($id) {
        try {
            $conn = $this->db->getConnection();
            
            // Get document details
            $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND status = 'active'");
            $stmt->execute([$id]);
            $document = $stmt->fetch();
            
            if (!$document) {
                return ResponseHandler::sendError('Document not found', 404);
            }
            
            if (!file_exists($document['file_path'])) {
                return ResponseHandler::sendError('File not found', 404);
            }
            
            // Set headers for file download
            header('Content-Type: ' . $document['file_type']);
            header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
            header('Content-Length: ' . $document['file_size']);
            
            // Output file
            readfile($document['file_path']);
            exit;
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    private function generateUniqueFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '_' . time() . '.' . $extension;
        return $uniqueName;
    }
} 