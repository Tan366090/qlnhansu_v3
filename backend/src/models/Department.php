<?php
require_once __DIR__ . '/../config/database.php';

class Department {
    private $db;
    private $table = 'departments';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $query = "SELECT d.*, COUNT(e.id) as employee_count 
                 FROM {$this->table} d 
                 LEFT JOIN employees e ON d.id = e.department_id 
                 GROUP BY d.id 
                 ORDER BY d.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT d.*, COUNT(e.id) as employee_count 
                 FROM {$this->table} d 
                 LEFT JOIN employees e ON d.id = e.department_id 
                 WHERE d.id = ? 
                 GROUP BY d.id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        // Check if department has employees
        $query = "SELECT COUNT(*) as count FROM employees WHERE department_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            throw new Exception("Không thể xóa phòng ban vì vẫn còn nhân viên");
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function validate($data) {
        $errors = [];

        // Validate required fields
        $requiredFields = ['name', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Trường $field là bắt buộc";
            }
        }

        // Validate name length
        if (!empty($data['name']) && strlen($data['name']) < 2) {
            $errors[] = "Tên phòng ban phải có ít nhất 2 ký tự";
        }

        // Validate description length
        if (!empty($data['description']) && strlen($data['description']) < 10) {
            $errors[] = "Mô tả phòng ban phải có ít nhất 10 ký tự";
        }

        return $errors;
    }
} 