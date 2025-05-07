<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;
    private $connection;
    private $connectionPool = [];
    private $maxConnections = 10;
    private $activeConnections = 0;

    private function __construct() {
        // Private constructor to prevent direct instantiation
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function createConnection() {
        try {
            $config = require __DIR__ . '/../../config/database.php';
            
            if (!is_array($config)) {
                throw new Exception("Invalid database configuration");
            }

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['database'],
                $config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];

            return new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        if ($this->activeConnections < $this->maxConnections) {
            $this->connection = $this->createConnection();
            $this->activeConnections++;
            return $this->connection;
        }
        throw new Exception("Maximum number of connections reached");
    }

    public function releaseConnection($connection) {
        if ($connection !== null) {
            $connection = null;
            $this->activeConnections--;
        }
    }

    public function query($sql, $params = []) {
        $connection = $this->getConnection();
        try {
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . "\nSQL: " . $sql);
            throw new Exception("Query execution failed: " . $e->getMessage());
        } finally {
            $this->releaseConnection($connection);
        }
    }

    public function beginTransaction() {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            return $connection;
        } catch (PDOException $e) {
            error_log("Transaction start failed: " . $e->getMessage());
            throw new Exception("Transaction start failed: " . $e->getMessage());
        }
    }

    public function commit($connection) {
        try {
            $connection->commit();
        } catch (PDOException $e) {
            error_log("Transaction commit failed: " . $e->getMessage());
            throw new Exception("Transaction commit failed: " . $e->getMessage());
        } finally {
            $this->releaseConnection($connection);
        }
    }

    public function rollback($connection) {
        try {
            $connection->rollBack();
        } catch (PDOException $e) {
            error_log("Transaction rollback failed: " . $e->getMessage());
            throw new Exception("Transaction rollback failed: " . $e->getMessage());
        } finally {
            $this->releaseConnection($connection);
        }
    }

    public function __destruct() {
        if ($this->connection !== null) {
            $this->connection = null;
        }
    }
} 