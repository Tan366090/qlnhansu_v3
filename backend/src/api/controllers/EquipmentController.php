<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class EquipmentController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index($page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM equipment_assignments 
                    WHERE status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated equipment assignments
            $sql = "SELECT ea.*, e.name as employee_name, e.employee_code 
                    FROM equipment_assignments ea 
                    LEFT JOIN employees e ON ea.employee_id = e.id 
                    WHERE ea.status = 'active' 
                    ORDER BY ea.assigned_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $equipments = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'equipments' => $equipments,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Equipment assignments retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get equipment assignments: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT ea.*, e.name as employee_name, e.employee_code 
                    FROM equipment_assignments ea 
                    LEFT JOIN employees e ON ea.employee_id = e.id 
                    WHERE ea.id = :id AND ea.status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $equipment = $stmt->fetch();
            
            if (!$equipment) {
                return ResponseHandler::error('Equipment assignment not found', 404);
            }
            
            return ResponseHandler::success($equipment, 'Equipment assignment retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get equipment assignment: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['employee_id']) || empty($data['equipment_name']) || 
                empty($data['assigned_date'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "INSERT INTO equipment_assignments (employee_id, equipment_name, 
                    equipment_type, serial_number, assigned_date, notes, status) 
                    VALUES (:employee_id, :equipment_name, :equipment_type, 
                    :serial_number, :assigned_date, :notes, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':equipment_name' => $data['equipment_name'],
                ':equipment_type' => $data['equipment_type'] ?? null,
                ':serial_number' => $data['serial_number'] ?? null,
                ':assigned_date' => $data['assigned_date'],
                ':notes' => $data['notes'] ?? null
            ]);
            
            $equipmentId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("Equipment assignment created: {$equipmentId}");
            
            return ResponseHandler::success([
                'id' => $equipmentId
            ], 'Equipment assignment created successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to create equipment assignment: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['equipment_name']) || empty($data['assigned_date'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "UPDATE equipment_assignments 
                    SET equipment_name = :equipment_name, 
                        equipment_type = :equipment_type, 
                        serial_number = :serial_number, 
                        assigned_date = :assigned_date, 
                        notes = :notes 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':equipment_name' => $data['equipment_name'],
                ':equipment_type' => $data['equipment_type'] ?? null,
                ':serial_number' => $data['serial_number'] ?? null,
                ':assigned_date' => $data['assigned_date'],
                ':notes' => $data['notes'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Equipment assignment not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Equipment assignment updated: {$id}");
            
            return ResponseHandler::success(null, 'Equipment assignment updated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to update equipment assignment: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE equipment_assignments 
                    SET status = 'deleted', 
                        deleted_at = NOW() 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Equipment assignment not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Equipment assignment deleted: {$id}");
            
            return ResponseHandler::success(null, 'Equipment assignment deleted successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to delete equipment assignment: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function returnEquipment($id) {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            $conn->beginTransaction();
            
            $sql = "UPDATE equipment_assignments 
                    SET return_date = :return_date, 
                        return_condition = :return_condition, 
                        return_notes = :return_notes 
                    WHERE id = :id AND status = 'active' 
                    AND return_date IS NULL";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':return_date' => date('Y-m-d H:i:s'),
                ':return_condition' => $data['return_condition'] ?? 'good',
                ':return_notes' => $data['return_notes'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Equipment assignment not found or already returned', 404);
            }
            
            $conn->commit();
            
            Logger::info("Equipment returned: {$id}");
            
            return ResponseHandler::success(null, 'Equipment returned successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to return equipment: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByEmployee($employeeId, $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM equipment_assignments 
                    WHERE employee_id = :employee_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':employee_id' => $employeeId]);
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated equipment assignments
            $sql = "SELECT * FROM equipment_assignments 
                    WHERE employee_id = :employee_id AND status = 'active' 
                    ORDER BY assigned_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':employee_id', $employeeId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $equipments = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'equipments' => $equipments,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Employee equipment assignments retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get employee equipment assignments: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 