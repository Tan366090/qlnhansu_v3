<?php
namespace App\Models;

class Document extends BaseModel {
    protected $table = 'documents';
    
    protected $fillable = [
        'name',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'tags',
        'status',
        'uploaded_by',
        'department_id',
        'project_id',
        'expiry_date',
        'version',
        'is_public'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND d.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY d.uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.department_id = ?
            ORDER BY d.uploaded_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getByProject($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.project_id = ?
            ORDER BY d.uploaded_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    public function getByCategory($category) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.category = ?
            ORDER BY d.uploaded_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    public function getByUploader($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.uploaded_by = ?
            ORDER BY d.uploaded_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getExpiredDocuments() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.expiry_date < NOW()
            AND d.status = 'active'
            ORDER BY d.expiry_date ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getExpiringDocuments($days = 30) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
            AND d.status = 'active'
            ORDER BY d.expiry_date ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
    
    public function getPublicDocuments() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.is_public = 1
            AND d.status = 'active'
            ORDER BY d.uploaded_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getDocumentVersions($documentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT dv.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name
            FROM document_versions dv
            JOIN employees e ON dv.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            WHERE dv.document_id = ?
            ORDER BY dv.version DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$documentId]);
        return $stmt->fetchAll();
    }
    
    public function addVersion($documentId, $filePath, $fileType, $fileSize, $notes = null) {
        $conn = $this->db->getConnection();
        
        // Get current version
        $stmt = $conn->prepare("
            SELECT MAX(version) as current_version
            FROM document_versions
            WHERE document_id = ?
        ");
        $stmt->execute([$documentId]);
        $result = $stmt->fetch();
        $newVersion = $result['current_version'] + 1;
        
        // Add new version
        $stmt = $conn->prepare("
            INSERT INTO document_versions 
            (document_id, version, file_path, file_type, file_size, notes, uploaded_by, uploaded_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $documentId, $newVersion, $filePath, $fileType, $fileSize, $notes, $_SESSION['user_id']
        ]);
    }
    
    public function updateDocumentStatus($documentId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE documents 
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $documentId]);
    }
    
    public function updateDocumentExpiry($documentId, $expiryDate) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE documents 
            SET expiry_date = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$expiryDate, $documentId]);
    }
    
    public function searchDocuments($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   dep.name as department_name,
                   p.name as project_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments dep ON d.department_id = dep.id
            LEFT JOIN projects p ON d.project_id = p.id
            WHERE d.name LIKE ? 
            OR d.description LIKE ?
            OR d.tags LIKE ?
            AND d.status = 'active'
            ORDER BY d.uploaded_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 