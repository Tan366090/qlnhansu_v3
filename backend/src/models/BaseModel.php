<?php
require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $conn;
    protected $table_name;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll($limit = null, $offset = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);
        
        $query = "INSERT INTO " . $this->table_name . " 
                (" . implode(", ", $fields) . ") 
                VALUES (" . implode(", ", $placeholders) . ")";
                
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $fields = array_keys($data);
        $set_clause = array_map(function($field) {
            return "$field = :$field";
        }, $fields);
        
        $query = "UPDATE " . $this->table_name . " 
                SET " . implode(", ", $set_clause) . "
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function count($conditions = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        if (!empty($conditions)) {
            $where_clause = array_map(function($field) {
                return "$field = :$field";
            }, array_keys($conditions));
            $query .= " WHERE " . implode(" AND ", $where_clause);
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
} 