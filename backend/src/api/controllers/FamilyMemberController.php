<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class FamilyMemberController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index($page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM family_members WHERE status = 'active'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated family members
            $sql = "SELECT fm.*, e.name as employee_name, e.employee_code 
                    FROM family_members fm 
                    LEFT JOIN employees e ON fm.employee_id = e.id 
                    WHERE fm.status = 'active' 
                    ORDER BY fm.employee_id, fm.relationship 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $familyMembers = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'family_members' => $familyMembers,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Family members retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get family members: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT fm.*, e.name as employee_name, e.employee_code 
                    FROM family_members fm 
                    LEFT JOIN employees e ON fm.employee_id = e.id 
                    WHERE fm.id = :id AND fm.status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $familyMember = $stmt->fetch();
            
            if (!$familyMember) {
                return ResponseHandler::error('Family member not found', 404);
            }
            
            return ResponseHandler::success($familyMember, 'Family member retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get family member: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['employee_id']) || empty($data['name']) || 
                empty($data['relationship'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "INSERT INTO family_members (employee_id, name, relationship, 
                    date_of_birth, phone_number, occupation, is_dependent, 
                    address, notes, status) 
                    VALUES (:employee_id, :name, :relationship, :date_of_birth, 
                    :phone_number, :occupation, :is_dependent, :address, 
                    :notes, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':name' => $data['name'],
                ':relationship' => $data['relationship'],
                ':date_of_birth' => $data['date_of_birth'] ?? null,
                ':phone_number' => $data['phone_number'] ?? null,
                ':occupation' => $data['occupation'] ?? null,
                ':is_dependent' => $data['is_dependent'] ?? false,
                ':address' => $data['address'] ?? null,
                ':notes' => $data['notes'] ?? null
            ]);
            
            $familyMemberId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("Family member created: {$familyMemberId}");
            
            return ResponseHandler::success([
                'id' => $familyMemberId
            ], 'Family member created successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to create family member: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['name']) || empty($data['relationship'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "UPDATE family_members 
                    SET name = :name, 
                        relationship = :relationship, 
                        date_of_birth = :date_of_birth, 
                        phone_number = :phone_number, 
                        occupation = :occupation, 
                        is_dependent = :is_dependent, 
                        address = :address, 
                        notes = :notes 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':relationship' => $data['relationship'],
                ':date_of_birth' => $data['date_of_birth'] ?? null,
                ':phone_number' => $data['phone_number'] ?? null,
                ':occupation' => $data['occupation'] ?? null,
                ':is_dependent' => $data['is_dependent'] ?? false,
                ':address' => $data['address'] ?? null,
                ':notes' => $data['notes'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Family member not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Family member updated: {$id}");
            
            return ResponseHandler::success(null, 'Family member updated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to update family member: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE family_members 
                    SET status = 'deleted', 
                        deleted_at = NOW() 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Family member not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Family member deleted: {$id}");
            
            return ResponseHandler::success(null, 'Family member deleted successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to delete family member: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByEmployee($employeeId, $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM family_members 
                    WHERE employee_id = :employee_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':employee_id' => $employeeId]);
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated family members
            $sql = "SELECT * FROM family_members 
                    WHERE employee_id = :employee_id AND status = 'active' 
                    ORDER BY relationship 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':employee_id', $employeeId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $familyMembers = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'family_members' => $familyMembers,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Employee family members retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get employee family members: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getDependents($employeeId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM family_members 
                    WHERE employee_id = :employee_id 
                    AND is_dependent = true 
                    AND status = 'active' 
                    ORDER BY relationship";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':employee_id' => $employeeId]);
            
            $dependents = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'dependents' => $dependents
            ], 'Employee dependents retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get employee dependents: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 