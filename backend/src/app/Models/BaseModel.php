<?php
namespace App\Models;

use PDO;
use App\Config\Database;

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function find($id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findAll($conditions = [], $orderBy = null, $limit = null, $offset = null) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        if ($offset) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        $fields = array_keys($data);
        $values = array_values($data);
        
        $sql = "INSERT INTO {$this->table} (" . implode(", ", $fields) . ") 
                VALUES (" . str_repeat("?, ", count($fields) - 1) . "?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        
        return $conn->lastInsertId();
    }
    
    public function update($id, $data) {
        $conn = $this->db->getConnection();
        $fields = array_keys($data);
        $values = array_values($data);
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . 
               implode(" = ?, ", $fields) . " = ? 
               WHERE {$this->primaryKey} = ?";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    public function softDelete($id) {
        return $this->update($id, ['status' => 'inactive']);
    }
    
    public function beginTransaction() {
        return $this->db->getConnection()->beginTransaction();
    }
    
    public function commit() {
        return $this->db->getConnection()->commit();
    }
    
    public function rollBack() {
        return $this->db->getConnection()->rollBack();
    }
} 