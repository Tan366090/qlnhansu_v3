<?php
namespace App\Models;

use PDO;
use PDOException;
use App\Core\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model {
    protected $table = 'documents';
    protected $primaryKey = 'document_id';

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'uploaded_by',
        'department_id',
        'is_public',
        'created_at',
        'updated_at'
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function getPublicDocuments()
    {
        return $this->where('is_public', true)
                   ->with(['uploader', 'department'])
                   ->get();
    }

    public function getDepartmentDocuments($departmentId)
    {
        return $this->where('department_id', $departmentId)
                   ->with(['uploader', 'department'])
                   ->get();
    }

    public function getUserDocuments($userId)
    {
        return $this->where('uploaded_by', $userId)
                   ->with(['uploader', 'department'])
                   ->get();
    }

    public function getDocumentsByCategory($category)
    {
        return $this->where('category', $category)
                   ->with(['uploader', 'department'])
                   ->get();
    }

    public function getDocumentsByType($type)
    {
        return $this->where('file_type', $type)
                   ->with(['uploader', 'department'])
                   ->get();
    }

    public function searchDocuments($keyword)
    {
        return $this->where('title', 'LIKE', "%{$keyword}%")
                   ->orWhere('description', 'LIKE', "%{$keyword}%")
                   ->with(['uploader', 'department'])
                   ->get();
    }

    public function getDocumentStatistics()
    {
        return [
            'total_documents' => $this->count(),
            'public_documents' => $this->where('is_public', true)->count(),
            'private_documents' => $this->where('is_public', false)->count(),
            'documents_by_category' => $this->select('category', DB::raw('count(*) as total'))
                                          ->groupBy('category')
                                          ->get(),
            'documents_by_type' => $this->select('file_type', DB::raw('count(*) as total'))
                                      ->groupBy('file_type')
                                      ->get()
        ];
    }

    public function getRecentDocuments($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }

    public function getDocumentsBySizeRange($minSize, $maxSize)
    {
        return $this->whereBetween('file_size', [$minSize, $maxSize])
                   ->orderBy('file_size', 'ASC')
                   ->get();
    }

    public function createDocument($employeeId, $documentType, $documentName, $documentPath, $issueDate = null, $expiryDate = null, $status = 'active', $notes = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'document_type' => $documentType,
                'document_name' => $documentName,
                'document_path' => $documentPath,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'status' => $status,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $documentId = $this->create($data);
            return [
                'success' => true,
                'document_id' => $documentId
            ];
        } catch (PDOException $e) {
            error_log("Create Document Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateDocument($documentId, $documentType, $documentName, $documentPath, $issueDate = null, $expiryDate = null, $notes = null) {
        try {
            $data = [
                'document_type' => $documentType,
                'document_name' => $documentName,
                'document_path' => $documentPath,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($issueDate !== null) {
                $data['issue_date'] = $issueDate;
            }

            if ($expiryDate !== null) {
                $data['expiry_date'] = $expiryDate;
            }

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return $this->update($documentId, $data);
        } catch (PDOException $e) {
            error_log("Update Document Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateDocumentStatus($documentId, $status, $notes = null) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return $this->update($documentId, $data);
        } catch (PDOException $e) {
            error_log("Update Document Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getDocumentDetails($documentId) {
        try {
            $query = "SELECT d.*, e.full_name as employee_name, e.employee_code, p.position_name, de.department_name 
                     FROM {$this->table} d
                     JOIN employees e ON d.employee_id = e.employee_id
                     JOIN positions p ON e.position_id = p.position_id
                     JOIN departments de ON e.department_id = de.department_id
                     WHERE d.document_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$documentId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Document Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeDocuments($employeeId, $documentType = null, $status = null) {
        try {
            $query = "SELECT d.* 
                     FROM {$this->table} d
                     WHERE d.employee_id = ?";
            $params = [$employeeId];

            if ($documentType) {
                $query .= " AND d.document_type = ?";
                $params[] = $documentType;
            }

            if ($status) {
                $query .= " AND d.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY d.created_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Documents Error: " . $e->getMessage());
            return [];
        }
    }

    public function getExpiringDocuments($days = 30) {
        try {
            $query = "SELECT d.*, e.full_name as employee_name, e.employee_code, de.department_name 
                     FROM {$this->table} d
                     JOIN employees e ON d.employee_id = e.employee_id
                     JOIN departments de ON e.department_id = de.department_id
                     WHERE d.expiry_date IS NOT NULL 
                     AND d.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                     AND d.status = 'active'
                     ORDER BY d.expiry_date ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$days]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Expiring Documents Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDocumentStats($departmentId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT d.document_id) as total_documents,
                        COUNT(DISTINCT d.employee_id) as employees_with_documents,
                        COUNT(DISTINCT CASE WHEN d.status = 'active' THEN d.document_id END) as active_documents,
                        COUNT(DISTINCT CASE WHEN d.status = 'expired' THEN d.document_id END) as expired_documents,
                        COUNT(DISTINCT d.document_type) as document_types
                     FROM {$this->table} d";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON d.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Document Stats Error: " . $e->getMessage());
            return false;
        }
    }
} 