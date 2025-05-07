<?php
namespace App\Services;

use PDO;
use Exception;

class DataStore {
    private static $instance = null;
    private $pdo;
    private $config;
    private $cache = []; // Sử dụng array thay vì Redis
    private $cacheTime = 300; // 5 minutes cache

    private function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
        if (!is_array($this->config)) {
            throw new Exception("Invalid database configuration");
        }
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
            if (!isset($this->config['host']) || !isset($this->config['database']) || 
                !isset($this->config['username']) || !isset($this->config['password']) ||
                !isset($this->config['charset'])) {
                throw new Exception("Missing required database configuration parameters");
            }

            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']}"
                ]
            );
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    private function handleError($message, $code = 500) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }

    private function tableExists($table) {
        try {
            // First try direct query with case-insensitive comparison
            $stmt = $this->pdo->prepare("
                SELECT TABLE_NAME 
                FROM information_schema.tables 
                WHERE table_schema = ? 
                AND LOWER(TABLE_NAME) = LOWER(?)
            ");
            $stmt->execute([$this->config['database'], $table]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $result['TABLE_NAME']; // Return actual table name
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error checking table existence: " . $e->getMessage());
            return false;
        }
    }

    public function getData($table, $conditions = [], $forceRefresh = false) {
        $actualTable = $this->tableExists($table);
        if (!$actualTable) {
            $this->handleError("Table $table does not exist", 404);
        }

        $cacheKey = $actualTable . '_' . md5(json_encode($conditions));
        
        // Try to get from cache first
        if (!$forceRefresh && isset($this->cache[$cacheKey]) && 
            time() - $this->cache[$cacheKey]['timestamp'] < $this->cacheTime) {
            return $this->cache[$cacheKey]['data'];
        }

        try {
            $query = "SELECT * FROM `$actualTable`";
            $params = [];

            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    if (is_array($value)) {
                        // Handle IN clause
                        $placeholders = str_repeat('?,', count($value) - 1) . '?';
                        $where[] = "`$key` IN ($placeholders)";
                        $params = array_merge($params, $value);
                    } else {
                        $where[] = "`$key` = ?";
                        $params[] = $value;
                    }
                }
                $query .= " WHERE " . implode(" AND ", $where);
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            // Cache the result
            $this->cache[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];

            return $data;
        } catch (Exception $e) {
            error_log("Error getting data from $actualTable: " . $e->getMessage());
            $this->handleError("Error getting data from $actualTable", 500);
        }
    }

    public function insertData($table, $data) {
        if (!$this->tableExists($table)) {
            $this->handleError("Table $table does not exist", 404);
        }

        try {
            $columns = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($values), '?');

            $query = "INSERT INTO `$table` (" . implode(', ', $columns) . ") 
                     VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);

            // Clear cache for this table
            $this->clearTableCache($table);

            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error inserting data into $table: " . $e->getMessage());
            $this->handleError("Error inserting data into $table", 500);
        }
    }

    public function updateData($table, $data, $conditions) {
        if (!$this->tableExists($table)) {
            $this->handleError("Table $table does not exist", 404);
        }

        try {
            $set = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $set[] = "`$key` = ?";
                $params[] = $value;
            }

            $where = [];
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    // Handle IN clause
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $where[] = "`$key` IN ($placeholders)";
                    $params = array_merge($params, $value);
                } else {
                    $where[] = "`$key` = ?";
                    $params[] = $value;
                }
            }

            $query = "UPDATE `$table` SET " . implode(', ', $set) . 
                    " WHERE " . implode(' AND ', $where);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            // Clear cache for this table
            $this->clearTableCache($table);

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error updating data in $table: " . $e->getMessage());
            $this->handleError("Error updating data in $table", 500);
        }
    }

    public function deleteData($table, $conditions) {
        if (!$this->tableExists($table)) {
            $this->handleError("Table $table does not exist", 404);
        }

        try {
            $where = [];
            $params = [];

            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    // Handle IN clause
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $where[] = "`$key` IN ($placeholders)";
                    $params = array_merge($params, $value);
                } else {
                    $where[] = "`$key` = ?";
                    $params[] = $value;
                }
            }

            $query = "DELETE FROM `$table` WHERE " . implode(' AND ', $where);
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            // Clear cache for this table
            $this->clearTableCache($table);

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error deleting data from $table: " . $e->getMessage());
            $this->handleError("Error deleting data from $table", 500);
        }
    }

    private function clearTableCache($table) {
        foreach ($this->cache as $key => $value) {
            if (strpos($key, $table . '_') === 0) {
                unset($this->cache[$key]);
            }
        }
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollBack();
    }

    public function getPDO() {
        return $this->pdo;
    }

    public function getTableStructure($table) {
        $actualTable = $this->tableExists($table);
        if (!$actualTable) {
            throw new Exception("Table $table does not exist");
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
                FROM information_schema.columns 
                WHERE table_schema = ? AND table_name = ?
                ORDER BY ORDINAL_POSITION
            ");
            $stmt->execute([$this->config['database'], $actualTable]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting table structure: " . $e->getMessage());
            throw new Exception("Error getting table structure: " . $e->getMessage());
        }
    }

    public function getTableCount($table) {
        $actualTable = $this->tableExists($table);
        if (!$actualTable) {
            throw new Exception("Table $table does not exist");
        }

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$actualTable`");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error getting table count: " . $e->getMessage());
            throw new Exception("Error getting table count: " . $e->getMessage());
        }
    }

    public function getAllData($table, $options = []) {
        // Kiểm tra bảng tồn tại và lấy tên bảng thực tế
        $actualTable = $this->tableExists($table);
        if (!$actualTable) {
            error_log("Table $table does not exist in database {$this->config['database']}");
            throw new Exception("Table $table does not exist");
        }

        // Tạo cache key dựa trên bảng và options
        $cacheKey = $actualTable . '_all_' . md5(json_encode($options));
        
        // Try to get from cache first
        if (isset($this->cache[$cacheKey]) && 
            time() - $this->cache[$cacheKey]['timestamp'] < $this->cacheTime) {
            return $this->cache[$cacheKey]['data'];
        }

        try {
            $query = "SELECT * FROM `$actualTable`";
            $params = [];

            // Xử lý sắp xếp
            if (!empty($options['order_by'])) {
                $orderBy = $options['order_by'];
                $direction = isset($options['order_direction']) ? strtoupper($options['order_direction']) : 'ASC';
                if ($direction !== 'ASC' && $direction !== 'DESC') {
                    $direction = 'ASC';
                }
                $query .= " ORDER BY `$orderBy` $direction";
            }

            // Xử lý phân trang
            if (!empty($options['page']) && !empty($options['per_page'])) {
                $page = max(1, intval($options['page']));
                $perPage = max(1, intval($options['per_page']));
                $offset = ($page - 1) * $perPage;
                $query .= " LIMIT $offset, $perPage";
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            // Lấy tổng số bản ghi nếu có phân trang
            $total = null;
            if (!empty($options['page']) && !empty($options['per_page'])) {
                $countQuery = "SELECT COUNT(*) as total FROM `$actualTable`";
                $countStmt = $this->pdo->prepare($countQuery);
                $countStmt->execute();
                $total = $countStmt->fetch()['total'];
            }

            // Cache the result
            $this->cache[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];

            return [
                'data' => $data,
                'total' => $total,
                'page' => $options['page'] ?? 1,
                'per_page' => $options['per_page'] ?? null
            ];
        } catch (Exception $e) {
            error_log("Error getting all data from $actualTable: " . $e->getMessage());
            throw new Exception("Error getting all data from $actualTable: " . $e->getMessage());
        }
    }

    public function checkStorageStatus() {
        $status = [
            'cache_size' => count($this->cache),
            'cache_items' => [],
            'cache_time' => $this->cacheTime,
            'database_connection' => $this->pdo ? 'Connected' : 'Disconnected',
            'database_name' => $this->config['database']
        ];

        // Lấy thông tin chi tiết về các items trong cache
        foreach ($this->cache as $key => $value) {
            $status['cache_items'][] = [
                'key' => $key,
                'size' => strlen(serialize($value['data'])),
                'age' => time() - $value['timestamp'],
                'expires_in' => $this->cacheTime - (time() - $value['timestamp'])
            ];
        }

        // Lấy thông tin về các bảng trong database
        try {
            $stmt = $this->pdo->prepare("
                SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ?
            ");
            $stmt->execute([$this->config['database']]);
            $status['tables'] = $stmt->fetchAll();
        } catch (Exception $e) {
            $status['tables'] = 'Error getting table info: ' . $e->getMessage();
        }

        return $status;
    }

    public function clearAllCache() {
        $this->cache = [];
        return true;
    }
}
?> 