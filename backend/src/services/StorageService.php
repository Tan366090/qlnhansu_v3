<?php
namespace App\Services;

use PDO;
use Exception;

class StorageService {
    private static $instance = null;
    private $pdo;
    private $cache = [];
    private $cacheTime = 300; // 5 minutes

    private function __construct() {
        $this->connectDatabase();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connectDatabase() {
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;dbname=qlnhansu;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function getData($table, $conditions = [], $forceRefresh = false) {
        $cacheKey = $table . '_' . md5(json_encode($conditions));
        
        // Kiểm tra cache
        if (!$forceRefresh && isset($this->cache[$cacheKey]) && 
            (time() - $this->cache[$cacheKey]['timestamp']) < $this->cacheTime) {
            return $this->cache[$cacheKey]['data'];
        }

        try {
            $query = "SELECT * FROM `$table`";
            $params = [];

            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "`$key` = ?";
                    $params[] = $value;
                }
                $query .= " WHERE " . implode(" AND ", $where);
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            // Lưu vào cache
            $this->cache[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];

            return $data;
        } catch (Exception $e) {
            error_log("Failed to get data from $table: " . $e->getMessage());
            throw new Exception("Failed to get data from $table");
        }
    }

    public function getRelatedData($table, $relatedTable, $foreignKey, $id) {
        try {
            $query = "SELECT t.*, rt.* 
                     FROM `$table` t 
                     LEFT JOIN `$relatedTable` rt ON t.`$foreignKey` = rt.id 
                     WHERE t.id = ?";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Failed to get related data: " . $e->getMessage());
            throw new Exception("Failed to get related data");
        }
    }

    public function insertData($table, $data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';

            $query = "INSERT INTO `$table` (" . implode(',', $fields) . ") 
                     VALUES ($placeholders)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            
            // Xóa cache của bảng này
            $this->clearCache($table);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Failed to insert data into $table: " . $e->getMessage());
            throw new Exception("Failed to insert data");
        }
    }

    public function updateData($table, $id, $data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $set = implode('=?,', $fields) . '=?';
            
            $query = "UPDATE `$table` SET $set WHERE id = ?";
            $values[] = $id;
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            
            // Xóa cache của bảng này
            $this->clearCache($table);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to update data in $table: " . $e->getMessage());
            throw new Exception("Failed to update data");
        }
    }

    public function deleteData($table, $id) {
        try {
            $query = "DELETE FROM `$table` WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            // Xóa cache của bảng này
            $this->clearCache($table);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to delete data from $table: " . $e->getMessage());
            throw new Exception("Failed to delete data");
        }
    }

    public function clearCache($table = null) {
        if ($table) {
            foreach ($this->cache as $key => $value) {
                if (strpos($key, $table . '_') === 0) {
                    unset($this->cache[$key]);
                }
            }
        } else {
            $this->cache = [];
        }
    }
}
?> 