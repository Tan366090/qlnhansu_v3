<?php
require_once __DIR__ . '/../config/database.php';

class Position {
    private $db;
    private $table = 'positions';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $query = "SELECT p.*, d.name as department_name, COUNT(e.id) as employee_count 
                 FROM {$this->table} p 
                 LEFT JOIN departments d ON p.department_id = d.id 
                 LEFT JOIN employees e ON p.id = e.position_id 
                 GROUP BY p.id 
                 ORDER BY p.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT p.*, d.name as department_name, COUNT(e.id) as employee_count 
                 FROM {$this->table} p 
                 LEFT JOIN departments d ON p.department_id = d.id 
                 LEFT JOIN employees e ON p.id = e.position_id 
                 WHERE p.id = ? 
                 GROUP BY p.id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByDepartment($departmentId) {
        $query = "SELECT p.*, COUNT(e.id) as employee_count 
                 FROM {$this->table} p 
                 LEFT JOIN employees e ON p.id = e.position_id 
                 WHERE p.department_id = ? 
                 GROUP BY p.id 
                 ORDER BY p.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($data));
        
        $query = "UPDATE {$this->table} 
                 SET " . implode(', ', $fields) . " 
                 WHERE id = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id) {
        // Check if position has employees
        $query = "SELECT COUNT(*) as count FROM employees WHERE position_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            throw new Exception("Không thể xóa chức vụ vì vẫn còn nhân viên");
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function validate($data) {
        $errors = [];

        // Validate required fields
        $requiredFields = ['name', 'department_id', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Trường $field là bắt buộc";
            }
        }

        // Validate name length
        if (!empty($data['name']) && strlen($data['name']) < 2) {
            $errors[] = "Tên chức vụ phải có ít nhất 2 ký tự";
        }

        // Validate description length
        if (!empty($data['description']) && strlen($data['description']) < 10) {
            $errors[] = "Mô tả chức vụ phải có ít nhất 10 ký tự";
        }

        // Validate department exists
        if (!empty($data['department_id'])) {
            $query = "SELECT COUNT(*) as count FROM departments WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$data['department_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] == 0) {
                $errors[] = "Phòng ban không tồn tại";
            }
        }

        return $errors;
    }
} 