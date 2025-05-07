<?php
namespace App\Services;

use PDO;
use Exception;

class DataService {
    private static $instance = null;
    private $pdo;
    private $data = [];
    private $lastSync = [];
    private $syncInterval = 300; // 5 minutes

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
            $dbConfig = require __DIR__ . '/../config/database.php';
            $this->pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4",
                $dbConfig['username'],
                $dbConfig['password'],
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

    public function getData($table, $forceRefresh = false) {
        // Check if data needs to be refreshed
        if ($forceRefresh || 
            !isset($this->data[$table]) || 
            !isset($this->lastSync[$table]) || 
            (time() - $this->lastSync[$table] > $this->syncInterval)) {
            
            $this->refreshData($table);
        }

        return $this->data[$table] ?? [];
    }

    private function refreshData($table) {
        try {
            $query = "SELECT * FROM `$table`";
            $stmt = $this->pdo->query($query);
            $this->data[$table] = $stmt->fetchAll();
            $this->lastSync[$table] = time();
        } catch (Exception $e) {
            error_log("Failed to refresh data for table $table: " . $e->getMessage());
            throw new Exception("Failed to refresh data");
        }
    }

    public function getFilteredData($table, $conditions = [], $forceRefresh = false) {
        $data = $this->getData($table, $forceRefresh);
        
        if (empty($conditions)) {
            return $data;
        }

        return array_filter($data, function($item) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($item[$key]) || $item[$key] != $value) {
                    return false;
                }
            }
            return true;
        });
    }

    public function getRelatedData($table, $relatedTable, $foreignKey, $id, $forceRefresh = false) {
        $data = $this->getData($table, $forceRefresh);
        $relatedData = $this->getData($relatedTable, $forceRefresh);

        $result = array_filter($data, function($item) use ($foreignKey, $id) {
            return $item[$foreignKey] == $id;
        });

        return array_map(function($item) use ($relatedData, $relatedTable) {
            $item[$relatedTable] = array_filter($relatedData, function($related) use ($item) {
                return $related['id'] == $item['id'];
            });
            return $item;
        }, $result);
    }

    public function clearCache($table = null) {
        if ($table) {
            unset($this->data[$table]);
            unset($this->lastSync[$table]);
        } else {
            $this->data = [];
            $this->lastSync = [];
        }
    }

    public function updateData($table, $id, $data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            
            $setClause = implode(' = ?, ', $fields) . ' = ?';
            $query = "UPDATE `$table` SET $setClause WHERE id = ?";
            
            $stmt = $this->pdo->prepare($query);
            $values[] = $id;
            $stmt->execute($values);
            
            // Clear cache for this table
            $this->clearCache($table);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to update data: " . $e->getMessage());
            return false;
        }
    }

    public function insertData($table, $data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            
            $fieldList = implode(', ', $fields);
            $placeholders = str_repeat('?, ', count($fields) - 1) . '?';
            
            $query = "INSERT INTO `$table` ($fieldList) VALUES ($placeholders)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            
            // Clear cache for this table
            $this->clearCache($table);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Failed to insert data: " . $e->getMessage());
            return false;
        }
    }

    public function deleteData($table, $id) {
        try {
            $query = "DELETE FROM `$table` WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            // Clear cache for this table
            $this->clearCache($table);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to delete data: " . $e->getMessage());
            return false;
        }
    }
}
?> 