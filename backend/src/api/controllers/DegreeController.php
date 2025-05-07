<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class DegreeController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, e.name as employee_name, e.employee_code,
                    dep.name as department_name 
                    FROM degrees d 
                    JOIN employees e ON d.employee_id = e.id 
                    JOIN departments dep ON e.department_id = dep.id 
                    WHERE d.status != 'deleted' 
                    ORDER BY d.issue_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $degrees = $stmt->fetchAll();
            
            return ResponseHandler::success($degrees);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch degrees: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, e.name as employee_name, e.employee_code,
                    dep.name as department_name 
                    FROM degrees d 
                    JOIN employees e ON d.employee_id = e.id 
                    JOIN departments dep ON e.department_id = dep.id 
                    WHERE d.id = :id AND d.status != 'deleted'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $degree = $stmt->fetch();
            
            if (!$degree) {
                return ResponseHandler::error('Degree not found', 404);
            }
            
            return ResponseHandler::success($degree);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch degree: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['employee_id']) || empty($data['type']) || 
                empty($data['name']) || empty($data['institution']) || 
                empty($data['issue_date'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            // Validate dates
            $issueDate = strtotime($data['issue_date']);
            $expiryDate = !empty($data['expiry_date']) ? strtotime($data['expiry_date']) : null;
            
            if ($issueDate === false) {
                return ResponseHandler::error('Invalid issue date format');
            }
            
            if ($expiryDate !== null && $expiryDate < $issueDate) {
                return ResponseHandler::error('Expiry date must be after issue date');
            }
            
            $conn->beginTransaction();
            
            $sql = "INSERT INTO degrees (employee_id, type, name, 
                    institution, issue_date, expiry_date, 
                    description, status) 
                    VALUES (:employee_id, :type, :name, 
                    :institution, :issue_date, :expiry_date, 
                    :description, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':type' => $data['type'],
                ':name' => $data['name'],
                ':institution' => $data['institution'],
                ':issue_date' => $data['issue_date'],
                ':expiry_date' => $data['expiry_date'] ?? null,
                ':description' => $data['description'] ?? null
            ]);
            
            $degreeId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("New degree created: {$degreeId}");
            
            return ResponseHandler::success(['id' => $degreeId], 'Degree created successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to create degree: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['type']) || empty($data['name']) || 
                empty($data['institution']) || empty($data['issue_date'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            // Validate dates
            $issueDate = strtotime($data['issue_date']);
            $expiryDate = !empty($data['expiry_date']) ? strtotime($data['expiry_date']) : null;
            
            if ($issueDate === false) {
                return ResponseHandler::error('Invalid issue date format');
            }
            
            if ($expiryDate !== null && $expiryDate < $issueDate) {
                return ResponseHandler::error('Expiry date must be after issue date');
            }
            
            $conn->beginTransaction();
            
            $sql = "UPDATE degrees 
                    SET type = :type, 
                        name = :name, 
                        institution = :institution, 
                        issue_date = :issue_date, 
                        expiry_date = :expiry_date, 
                        description = :description 
                    WHERE id = :id AND status != 'deleted'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':type' => $data['type'],
                ':name' => $data['name'],
                ':institution' => $data['institution'],
                ':issue_date' => $data['issue_date'],
                ':expiry_date' => $data['expiry_date'] ?? null,
                ':description' => $data['description'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Degree not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Degree updated: {$id}");
            
            return ResponseHandler::success(null, 'Degree updated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to update degree: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE degrees 
                    SET status = 'deleted' 
                    WHERE id = :id AND status != 'deleted'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Degree not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Degree deleted: {$id}");
            
            return ResponseHandler::success(null, 'Degree deleted successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to delete degree: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByEmployee($employeeId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, e.name as employee_name, e.employee_code,
                    dep.name as department_name 
                    FROM degrees d 
                    JOIN employees e ON d.employee_id = e.id 
                    JOIN departments dep ON e.department_id = dep.id 
                    WHERE d.employee_id = :employee_id AND d.status != 'deleted' 
                    ORDER BY d.issue_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':employee_id' => $employeeId]);
            
            $degrees = $stmt->fetchAll();
            
            return ResponseHandler::success($degrees);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch employee degrees: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByType($type) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, e.name as employee_name, e.employee_code,
                    dep.name as department_name 
                    FROM degrees d 
                    JOIN employees e ON d.employee_id = e.id 
                    JOIN departments dep ON e.department_id = dep.id 
                    WHERE d.type = :type AND d.status != 'deleted' 
                    ORDER BY d.issue_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':type' => $type]);
            
            $degrees = $stmt->fetchAll();
            
            return ResponseHandler::success($degrees);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch degrees by type: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getExpiringSoon($days = 30) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, e.name as employee_name, e.employee_code,
                    dep.name as department_name 
                    FROM degrees d 
                    JOIN employees e ON d.employee_id = e.id 
                    JOIN departments dep ON e.department_id = dep.id 
                    WHERE d.status != 'deleted' 
                    AND d.expiry_date IS NOT NULL 
                    AND d.expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY) 
                    ORDER BY d.expiry_date ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':days' => $days]);
            
            $degrees = $stmt->fetchAll();
            
            return ResponseHandler::success($degrees);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch expiring degrees: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getExpired() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT d.*, e.name as employee_name, e.employee_code,
                    dep.name as department_name 
                    FROM degrees d 
                    JOIN employees e ON d.employee_id = e.id 
                    JOIN departments dep ON e.department_id = dep.id 
                    WHERE d.status != 'deleted' 
                    AND d.expiry_date IS NOT NULL 
                    AND d.expiry_date < NOW() 
                    ORDER BY d.expiry_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $degrees = $stmt->fetchAll();
            
            return ResponseHandler::success($degrees);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch expired degrees: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 