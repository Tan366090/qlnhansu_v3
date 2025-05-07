<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;

class DepartmentController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            // Get all departments with employee count
            $stmt = $conn->prepare("
                SELECT d.*, 
                       COUNT(e.id) as employee_count,
                       p.name as parent_department_name
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id
                LEFT JOIN departments p ON d.parent_id = p.id
                WHERE d.status = 'active'
                GROUP BY d.id
                ORDER BY d.name
            ");
            $stmt->execute();
            $departments = $stmt->fetchAll();
            
            // Build department tree
            $tree = $this->buildDepartmentTree($departments);
            
            return ResponseHandler::sendSuccess([
                'departments' => $departments,
                'tree' => $tree
            ]);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($data['name'])) {
                return ResponseHandler::sendError('Department name is required', 400);
            }
            
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Insert department
            $stmt = $conn->prepare("
                INSERT INTO departments (
                    name, parent_id, description, status, created_at, updated_at
                ) VALUES (
                    :name, :parent_id, :description, 'active', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'parent_id' => $data['parent_id'] ?? null,
                'description' => $data['description'] ?? null
            ]);
            
            $departmentId = $conn->lastInsertId();
            
            // Get created department
            $stmt = $conn->prepare("
                SELECT d.*, p.name as parent_department_name
                FROM departments d
                LEFT JOIN departments p ON d.parent_id = p.id
                WHERE d.id = ?
            ");
            $stmt->execute([$departmentId]);
            $department = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($department, 'Department created successfully', 201);
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($data['name'])) {
                return ResponseHandler::sendError('Department name is required', 400);
            }
            
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Update department
            $stmt = $conn->prepare("
                UPDATE departments 
                SET name = :name,
                    parent_id = :parent_id,
                    description = :description,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'parent_id' => $data['parent_id'] ?? null,
                'description' => $data['description'] ?? null
            ]);
            
            // Get updated department
            $stmt = $conn->prepare("
                SELECT d.*, p.name as parent_department_name
                FROM departments d
                LEFT JOIN departments p ON d.parent_id = p.id
                WHERE d.id = ?
            ");
            $stmt->execute([$id]);
            $department = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($department, 'Department updated successfully');
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
            
            // Check if department has employees
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE department_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ResponseHandler::sendError('Cannot delete department with employees', 400);
            }
            
            // Check if department has child departments
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM departments WHERE parent_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ResponseHandler::sendError('Cannot delete department with child departments', 400);
            }
            
            // Soft delete department
            $stmt = $conn->prepare("UPDATE departments SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess([], 'Department deleted successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    private function buildDepartmentTree($departments, $parentId = null) {
        $tree = [];
        
        foreach ($departments as $department) {
            if ($department['parent_id'] == $parentId) {
                $children = $this->buildDepartmentTree($departments, $department['id']);
                if ($children) {
                    $department['children'] = $children;
                }
                $tree[] = $department;
            }
        }
        
        return $tree;
    }
} 