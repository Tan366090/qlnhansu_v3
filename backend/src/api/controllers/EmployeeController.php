<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;

class EmployeeController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            // Get all employees with department and position info
            $stmt = $conn->prepare("
                SELECT e.*, d.name as department_name, p.name as position_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.status = 'active'
                ORDER BY e.created_at DESC
            ");
            $stmt->execute();
            $employees = $stmt->fetchAll();
            
            return ResponseHandler::sendSuccess($employees);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Insert employee
            $stmt = $conn->prepare("
                INSERT INTO employees (
                    name, email, phone, address, department_id, position_id,
                    salary, join_date, status, created_at, updated_at
                ) VALUES (
                    :name, :email, :phone, :address, :department_id, :position_id,
                    :salary, :join_date, 'active', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'salary' => $data['salary'] ?? 0,
                'join_date' => $data['join_date'] ?? date('Y-m-d')
            ]);
            
            $employeeId = $conn->lastInsertId();
            
            // Get created employee with department and position info
            $stmt = $conn->prepare("
                SELECT e.*, d.name as department_name, p.name as position_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.id = ?
            ");
            $stmt->execute([$employeeId]);
            $employee = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($employee, 'Employee created successfully', 201);
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT e.*, d.name as department_name, p.name as position_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.id = ? AND e.status = 'active'
            ");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();
            
            if (!$employee) {
                return ResponseHandler::sendNotFound('Employee not found');
            }
            
            return ResponseHandler::sendSuccess($employee);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function update($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Update employee
            $stmt = $conn->prepare("
                UPDATE employees 
                SET name = :name,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    department_id = :department_id,
                    position_id = :position_id,
                    salary = :salary,
                    updated_at = NOW()
                WHERE id = :id AND status = 'active'
            ");
            
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'salary' => $data['salary'] ?? 0
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::sendNotFound('Employee not found');
            }
            
            // Get updated employee with department and position info
            $stmt = $conn->prepare("
                SELECT e.*, d.name as department_name, p.name as position_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.id = ?
            ");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($employee, 'Employee updated successfully');
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
            
            // Soft delete employee
            $stmt = $conn->prepare("
                UPDATE employees 
                SET status = 'inactive',
                    updated_at = NOW()
                WHERE id = ? AND status = 'active'
            ");
            
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::sendNotFound('Employee not found');
            }
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess([], 'Employee deleted successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
} 